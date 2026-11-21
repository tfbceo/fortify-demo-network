<?php
/**
 * Fortify Child theme.
 *
 * Kept deliberately small: design tokens belong in the Elementor kit so they
 * sync to sitelets with the kit. Custom widgets (savings calculator) are
 * registered from here in phase 3.
 */

defined( 'ABSPATH' ) || exit;

define( 'FORTIFY_CHILD_VERSION', '0.1.0' );

/**
 * Child stylesheet, after the parent's. No dependency on the parent handles:
 * Hello Elementor enqueues those conditionally, and a missing dependency would
 * silently drop this stylesheet.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'fortify-child',
			get_stylesheet_uri(),
			array(),
			FORTIFY_CHILD_VERSION
		);
	},
	20
);
