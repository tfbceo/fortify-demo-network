<?php
/**
 * Plugin Name: Fortify demo: network no-index
 * Description: Keeps every site in the network out of search engines, in every environment, including sitelets created later. Remove only for a real production launch.
 * Version:     1.0.0
 */

defined( 'ABSPATH' ) || exit;

// "Discourage search engines" on every site, regardless of the stored setting.
// Core then prints <meta name="robots" content="noindex, nofollow">, disallows
// all in the virtual robots.txt and turns off the core XML sitemaps.
add_filter( 'pre_option_blog_public', static fn() => '0' );

// New sites are created as non-public in wp_blogs too.
add_filter(
	'wp_normalize_site_data',
	static function ( $data ) {
		$data['public'] = 0;
		return $data;
	}
);

// Cover non-HTML responses (REST, feeds) as well.
add_action(
	'send_headers',
	static function () {
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}
);
