<?php
/**
 * Phase 6: create office sites from the registry.
 *
 *   wp eval-file scripts/create-offices.php tampa            (one office)
 *   wp eval-file scripts/create-offices.php st-petersburg orlando …
 *   wp eval-file scripts/create-offices.php all               (every office without a site)
 *
 * (WP-CLI rejects `--all` as an unknown flag before the script runs, so the
 * keyword is `all`; `--all` is still accepted when run some other way.)
 *
 * For each office: creates the site at /{slug}/ (Sitelets' new-site sync
 * copies the Sitelet Pages, Theme Builder templates and kit, and sets the
 * Office Home as the front page), links the registry record to the new site,
 * seeds the Office Profile with sample content, compiles Elementor's
 * custom-breakpoint CSS on the site, and adds one local editor
 * ({slug}-editor, role editor on that site only). The editor gets a random
 * password that is not shown: set it in Network Admin → Users.
 * Offices that already have a site are skipped.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_main_site() ) {
	WP_CLI::error( 'Run on the main site (site 1).' );
}
foreach ( array( '\SiteletsWP\Sitelets_Sync', '\FortifyOffices\Registry', '\FortifyOffices\Profile' ) as $class ) {
	if ( ! class_exists( $class ) ) {
		WP_CLI::error( "$class is missing: Sitelets and fortify-offices must be active." );
	}
}

$slugs = isset( $args ) ? (array) $args : array();
if ( array_intersect( array( 'all', '--all' ), $slugs ) ) {
	$slugs = wp_list_pluck( fortify_offices( array( 'status' => array( 'live', 'coming_soon', 'hidden' ) ) ), 'slug' );
}
if ( ! $slugs ) {
	WP_CLI::error( 'Name the offices to create (slugs), or pass all.' );
}

$network = get_network();
foreach ( $slugs as $slug ) {
	$office = fortify_office( $slug );
	if ( ! $office ) {
		WP_CLI::warning( "No office '$slug' in the registry; skipped." );
		continue;
	}
	if ( $office['blog_id'] && get_site( $office['blog_id'] ) ) {
		WP_CLI::line( "$slug: already has site #{$office['blog_id']}; skipped." );
		continue;
	}
	$path = '/' . $slug . '/';
	if ( get_site_by_path( $network->domain, $path ) && get_site_by_path( $network->domain, $path )->path === $path ) {
		WP_CLI::warning( "$slug: a site already exists at $path but is not linked to the registry; skipped." );
		continue;
	}

	$blog_id = wpmu_create_blog( $network->domain, $path, 'Fortify ' . $office['name'], get_current_user_id() ? get_current_user_id() : 1, array( 'public' => 0 ), $network->id );
	if ( is_wp_error( $blog_id ) ) {
		WP_CLI::warning( "$slug: " . $blog_id->get_error_message() );
		continue;
	}

	update_post_meta( $office['id'], '_fo_blog_id', $blog_id ); // Flushes the registry cache.

	switch_to_blog( $blog_id );
	\FortifyOffices\Profile::update( \FortifyOffices\Profile::sample( fortify_office( $slug ) ) );
	\Elementor\Core\Breakpoints\Manager::compile_stylesheet_templates();
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	$front = (int) get_option( 'page_on_front' );
	$pages = wp_list_pluck( get_posts( array( 'post_type' => MULTISITE_SITELETS_CUSTOM_POST_TYPE, 'numberposts' => -1 ) ), 'post_name' );
	$tpls  = count( get_posts( array( 'post_type' => 'elementor_library', 'numberposts' => -1 ) ) );
	restore_current_blog();

	$login = $slug . '-editor';
	$uid   = username_exists( $login );
	if ( ! $uid ) {
		$uid = wp_insert_user( array(
			'user_login'   => $login,
			'user_pass'    => wp_generate_password( 32, true, true ),
			'user_email'   => $login . '@example.invalid',
			'display_name' => $office['name'] . ' editor (demo)',
			'role'         => '',
		) );
		if ( is_wp_error( $uid ) ) {
			WP_CLI::warning( "$slug: editor not created: " . $uid->get_error_message() );
			$uid = 0;
		}
	}
	if ( $uid ) {
		remove_user_from_blog( $uid, get_main_site_id() );
		add_user_to_blog( $blog_id, $uid, 'editor' );
	}

	WP_CLI::line( sprintf(
		'%s: site #%d %s · front page %s · Sitelet Pages: %s · templates: %d · editor: %s',
		$slug,
		$blog_id,
		get_home_url( $blog_id, '/' ),
		$front ? get_blog_post( $blog_id, $front )->post_name : 'NOT SET',
		implode( ', ', $pages ),
		$tpls,
		$uid ? $login : 'none'
	) );
}

\FortifyOffices\Registry::flush();
WP_CLI::success( 'Done.' );
