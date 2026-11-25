<?php
/**
 * Opening-soon offices.
 *
 * An office whose registry status is "coming soon" must not present a full
 * office with sample staff. Everything here is driven by the registry status
 * at request time, so flipping the status on site 1 switches the office over
 * immediately, both ways, with nothing stored on the office site:
 *
 * Coming soon
 * - The front page is the "Opening Soon" Sitelet Page (slug opening-soon),
 *   published from site 1 by scripts/build-opening-soon.php.
 * - The six office pages (office, visit, team, reviews, service-area,
 *   local-faq) stay in place, so Publish to Sitelets keeps them current, but
 *   redirect (302) to the office home, are noindexed, and are left out of the
 *   sitemap.
 *
 * Live
 * - The front page is the Office Home as usual.
 * - /opening-soon/ redirects (302) to the office home, is noindexed and is
 *   left out of the sitemap.
 *
 * The interest form on the Opening Soon page records which office it was
 * submitted on: the hidden `office` field is set on the server from the site
 * the submission arrives on, whatever the browser sent.
 */

namespace FortifyOffices;

defined( 'ABSPATH' ) || exit;

class Opening_Soon {

	const SLUG         = 'opening-soon';
	const OFFICE_PAGES = array( 'office', 'visit', 'team', 'reviews', 'service-area', 'local-faq' );
	const FORM_NAME    = 'Opening soon interest';

	/**
	 * Labelled demo inbox for interest-form leads. example.com is reserved
	 * (RFC 2606), so nothing reaches a real Fortify inbox; on LocalWP, Mailpit
	 * catches the message.
	 */
	const DEMO_INBOX = 'opening-soon-demo-leads@example.com';

	public static function init() {
		if ( is_main_site() ) {
			return;
		}
		add_filter( 'pre_option_page_on_front', array( __CLASS__, 'front_page' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirects' ), 1 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'sitemap' ), 10, 2 );
		add_filter( 'elementor_pro/forms/record/actions_before', array( __CLASS__, 'stamp_office' ), 10, 2 );
	}

	/** The current site's office, or null (main site, unlinked or hidden). */
	public static function office() {
		return fortify_office_for_site( get_current_blog_id() );
	}

	public static function is_coming_soon() {
		$o = self::office();
		return $o && 'coming_soon' === $o['status'];
	}

	private static function page_id( $slug ) {
		static $ids = array();
		$key = get_current_blog_id() . ':' . $slug;
		if ( ! isset( $ids[ $key ] ) ) {
			$p           = get_page_by_path( $slug, OBJECT, MULTISITE_SITELETS_CUSTOM_POST_TYPE );
			$ids[ $key ] = $p && 'publish' === $p->post_status ? (int) $p->ID : 0;
		}
		return $ids[ $key ];
	}

	/** Slugs hidden on this office right now. */
	private static function hidden_slugs() {
		$o = self::office();
		if ( ! $o ) {
			return array();
		}
		if ( 'coming_soon' === $o['status'] ) {
			return self::page_id( self::SLUG ) ? self::OFFICE_PAGES : array();
		}
		return array( self::SLUG );
	}

	public static function front_page( $pre ) {
		static $busy = false; // The registry read may read options itself.
		if ( $busy ) {
			return $pre;
		}
		$busy = true;
		$id   = self::is_coming_soon() ? self::page_id( self::SLUG ) : 0;
		$busy = false;
		return $id ? $id : $pre;
	}

	private static function current_hidden_page() {
		if ( is_front_page() || ! is_singular( MULTISITE_SITELETS_CUSTOM_POST_TYPE ) ) {
			return false;
		}
		$post = get_queried_object();
		return $post instanceof \WP_Post && in_array( $post->post_name, self::hidden_slugs(), true );
	}

	public static function redirects() {
		if ( is_preview() || ! self::current_hidden_page() ) {
			return;
		}
		header( 'X-Robots-Tag: noindex, follow', true );
		wp_safe_redirect( home_url( '/' ), 302, 'Fortify Offices' );
		exit;
	}

	public static function robots( $robots ) {
		if ( self::current_hidden_page() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}

	public static function sitemap( $args, $post_type ) {
		if ( MULTISITE_SITELETS_CUSTOM_POST_TYPE !== $post_type ) {
			return $args;
		}
		$ids = array_filter( array_map( array( __CLASS__, 'page_id' ), self::hidden_slugs() ) );
		if ( $ids ) {
			$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), $ids );
		}
		return $args;
	}

	/** Sets the hidden `office` field from the site the form was submitted on. */
	public static function stamp_office( $record, $handler ) {
		if ( self::FORM_NAME !== $record->get_form_settings( 'form_name' ) ) {
			return $record;
		}
		$o     = self::office();
		$value = $o ? sprintf( '%s (%s, site #%d)', $o['name'], $o['slug'], get_current_blog_id() ) : 'Unknown office (site #' . get_current_blog_id() . ')';
		$record->update_field( 'office', 'value', $value );
		$record->update_field( 'office', 'raw_value', $value );
		return $record;
	}
}
