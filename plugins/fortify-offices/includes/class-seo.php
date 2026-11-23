<?php
/**
 * Per-office title tags and meta descriptions, generated from the registry
 * record, so the office sites don't share identical ones.
 *
 * Demo stopgap: Sitelets has no local SEO fields yet (plugin roadmap #37).
 * Templates are keyed by the office page slug and can be changed with the
 * `fortify_offices_seo_templates` filter.
 */

namespace FortifyOffices;

defined( 'ABSPATH' ) || exit;

class Seo {

	const BRAND           = 'Fortify Listings';
	const DESCRIPTION_MAX = 160;

	public static function init() {
		add_filter( 'document_title_parts', array( __CLASS__, 'title_parts' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'meta_description' ), 1 );
	}

	/** Templates by page slug. `default` covers any other office page. */
	public static function templates() {
		$t = array(
			'office'       => array(
				'title'       => 'Flat Fee MLS Listings in {city}, {state}',
				'description' => 'Sell your {city} home for a flat fee with {office}. Broker-reviewed listings on {mls} for sellers in {counties}, {state_name}.',
			),
			'visit'        => array(
				'title'       => 'Visit Our {city} Office',
				'description' => 'Visit {office} at {address}. Open {hours}. Call {phone}.',
			),
			'service-area' => array(
				'title'       => '{counties} Service Area',
				'description' => '{office} serves home sellers in {counties}, {state_name}: {zip_count} ZIP codes, with every listing on {mls}.',
			),
			'team'         => array(
				'title'       => 'Our {city} Team',
				'description' => 'Meet the {office} team. Broker of record {broker}, {state} license {license}. Flat fee MLS listings for {city}-area sellers.',
			),
			'reviews'      => array(
				'title'       => '{city} Seller Reviews',
				'description' => 'Read reviews from {city}-area home sellers who listed with {office} for a flat fee on {mls}.',
			),
			'local-faq'    => array(
				'title'       => '{city} Home Seller FAQ',
				'description' => 'Answers for {city} home sellers: how flat fee MLS listings on {mls} work, what is included, and how to get started with {office}.',
			),
			'default'      => array(
				'title'       => '{page}',
				'description' => '{office}: flat fee MLS listing services for home sellers in {counties}, {state_name}. Call {phone}.',
			),
		);
		return apply_filters( 'fortify_offices_seo_templates', $t );
	}

	/** Office and template for the current request, or null when not an office page. */
	private static function context() {
		if ( is_admin() || is_404() || is_feed() ) {
			return null;
		}
		$o = fortify_office_for_site();
		if ( ! $o || 'live' !== $o['status'] ) {
			return null;
		}

		$post = is_singular() ? get_queried_object() : null;
		if ( ! is_front_page() && ! ( $post instanceof \WP_Post ) ) {
			return null;
		}

		$templates = self::templates();
		$slug      = is_front_page() ? 'office' : $post->post_name;
		$template  = $templates[ $slug ] ?? $templates['default'];

		return array( $o, $template, $post ? get_the_title( $post ) : '' );
	}

	private static function fill( $text, $o, $page_title ) {
		$counties = $o['counties'];
		$last     = array_pop( $counties );
		$counties_text = $counties ? implode( ', ', $counties ) . ' and ' . $last : (string) $last;
		$counties_text = '' === $counties_text ? $o['city'] : $counties_text . ( 1 === count( $o['counties'] ) ? ' County' : ' Counties' );

		$vars = array(
			'{office}'     => self::BRAND . ' ' . $o['name'],
			'{name}'       => $o['name'],
			'{city}'       => $o['city'] ?: $o['name'],
			'{state}'      => $o['state'],
			'{state_name}' => $o['state_name'],
			'{counties}'   => $counties_text,
			'{mls}'        => $o['mls'] ?: 'the MLS',
			'{phone}'      => $o['phone'],
			'{address}'    => $o['address'],
			'{hours}'      => $o['hours'],
			'{broker}'     => $o['broker'],
			'{license}'    => $o['license'],
			'{zip_count}'  => (string) count( $o['zips'] ),
			'{page}'       => $page_title,
		);
		$text = strtr( $text, $vars );
		// Tidy what an empty field leaves behind ("Call .", "Open .").
		$text = preg_replace( array( '/\s+([.,])/', '/(Call|Open)\s*\./', '/\s{2,}/' ), array( '$1', '', ' ' ), $text );
		return trim( $text );
	}

	/** "Flat Fee MLS Listings in Tampa, FL | Fortify Listings Tampa". */
	public static function title_parts( $parts ) {
		$ctx = self::context();
		if ( ! $ctx ) {
			return $parts;
		}
		list( $o, $template, $page_title ) = $ctx;

		$title = self::fill( $template['title'], $o, $page_title );
		if ( '' === $title ) {
			return $parts;
		}
		return array(
			'title' => $title,
			'site'  => self::BRAND . ' ' . $o['name'],
		);
	}

	public static function meta_description() {
		// Leave descriptions to an SEO plugin if one is active.
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || class_exists( 'The_SEO_Framework\Load' ) ) {
			return;
		}
		$ctx = self::context();
		if ( ! $ctx ) {
			return;
		}
		list( $o, $template, $page_title ) = $ctx;

		$text = self::fill( $template['description'], $o, $page_title );
		if ( mb_strlen( $text ) > self::DESCRIPTION_MAX ) {
			$text = mb_substr( $text, 0, self::DESCRIPTION_MAX - 1 );
			$text = preg_replace( '/\s+\S*$/u', '', $text ) . '…';
		}
		if ( '' !== $text ) {
			echo '<meta name="description" content="' . esc_attr( $text ) . "\" />\n";
		}
	}
}
