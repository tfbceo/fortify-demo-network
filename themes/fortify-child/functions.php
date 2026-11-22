<?php
/**
 * Fortify Child theme.
 *
 * Design tokens (colors, type, spacing, breakpoints) live in the Elementor kit
 * so they reach sitelets with the kit. This theme holds what the kit cannot:
 * Fortify component styles (assets/css/fortify.css), small behaviors, and the
 * Fortify widgets (savings calculator, address field, listing card).
 */

defined( 'ABSPATH' ) || exit;

define( 'FORTIFY_CHILD_VERSION', '0.4.0' );

/** Asset version: the theme version plus the file's modified time, so edits are never served stale. */
function fortify_child_asset_version( $relative ) {
	$file = get_stylesheet_directory() . '/' . $relative;
	return FORTIFY_CHILD_VERSION . ( file_exists( $file ) ? '.' . filemtime( $file ) : '' );
}

/**
 * Styles and scripts. No dependency on the parent handles: Hello Elementor
 * enqueues those conditionally, and a missing dependency would silently drop
 * these files.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		$uri = get_stylesheet_directory_uri();
		wp_enqueue_style( 'fortify-child', get_stylesheet_uri(), array(), fortify_child_asset_version( 'style.css' ) );
		wp_enqueue_style( 'fortify-components', $uri . '/assets/css/fortify.css', array(), fortify_child_asset_version( 'assets/css/fortify.css' ) );
		wp_enqueue_style( 'fortify-pages', $uri . '/assets/css/fortify-pages.css', array( 'fortify-components' ), fortify_child_asset_version( 'assets/css/fortify-pages.css' ) );
		wp_enqueue_script( 'fortify', $uri . '/assets/js/fortify.js', array(), fortify_child_asset_version( 'assets/js/fortify.js' ), array( 'in_footer' => true ) );
		wp_register_script( 'fortify-savings-calculator', $uri . '/assets/js/savings-calculator.js', array(), fortify_child_asset_version( 'assets/js/savings-calculator.js' ), array( 'in_footer' => true ) );
	},
	20
);

// Load the component styles inside the Elementor editor preview too.
add_action(
	'elementor/preview/enqueue_styles',
	static function () {
		wp_enqueue_style( 'fortify-components', get_stylesheet_directory_uri() . '/assets/css/fortify.css', array(), fortify_child_asset_version( 'assets/css/fortify.css' ) );
		wp_enqueue_style( 'fortify-pages', get_stylesheet_directory_uri() . '/assets/css/fortify-pages.css', array( 'fortify-components' ), fortify_child_asset_version( 'assets/css/fortify-pages.css' ) );
	}
);

// "Fortify" category in the Elementor widget panel.
add_action(
	'elementor/elements/categories_registered',
	static function ( $elements_manager ) {
		$elements_manager->add_category( 'fortify', array( 'title' => esc_html__( 'Fortify', 'fortify-child' ), 'icon' => 'fa fa-home' ) );
	}
);

add_action(
	'elementor/widgets/register',
	static function ( $widgets_manager ) {
		require_once __DIR__ . '/inc/widgets/class-savings-calculator.php';
		require_once __DIR__ . '/inc/widgets/class-address-field.php';
		require_once __DIR__ . '/inc/widgets/class-listing-card.php';
		$widgets_manager->register( new \FortifyChild\Widgets\Savings_Calculator() );
		$widgets_manager->register( new \FortifyChild\Widgets\Address_Field() );
		$widgets_manager->register( new \FortifyChild\Widgets\Listing_Card() );
	}
);

/**
 * [fortify_preview_address] for the /preview/ demo stub: shows the address
 * the Address Field sent (?address=…), escaped. The stub stores nothing.
 */
add_shortcode(
	'fortify_preview_address',
	static function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display of a GET value.
		$address = isset( $_GET['address'] ) ? sanitize_text_field( wp_unslash( $_GET['address'] ) ) : '';
		if ( '' === $address ) {
			return '<p>' . esc_html__( 'No address was entered. Enter one below to try the form.', 'fortify-child' ) . '</p>';
		}
		return '<p>' . esc_html__( 'Address received:', 'fortify-child' ) . ' <b>' . esc_html( $address ) . '</b></p>';
	}
);

/**
 * On office sites, keep visitors on the office's site: root-relative links and
 * form actions ("/", "/#start", "/plans/", "/preview/" …) point at the office's
 * own path instead of the corporate root. Sitelets (1.1.4+) serves the
 * corporate page at the same path on the office site when the office has no
 * page of that slug.
 */
function fortify_child_office_links( $html, $home ) {
	if ( false === strpos( $html, '="/' ) ) {
		return $html;
	}
	return preg_replace( '#\b(href|action)="/(?!/)#', '$1="' . esc_url( trailingslashit( $home ) ), $html );
}

// Office pages and the shared header and footer.
add_filter(
	'elementor/widget/render_content',
	static function ( $content ) {
		return is_main_site() ? $content : fortify_child_office_links( $content, home_url( '/' ) );
	}
);

// Corporate pages served on an office site by the Sitelets fallback: they
// render on the main site, so the filter above does not apply to them.
add_filter(
	'sitelets_fallback_content',
	static function ( $html, $page, $origin_blog_id ) {
		return fortify_child_office_links( $html, get_home_url( $origin_blog_id, '/' ) );
	},
	10,
	3
);

/** Favicon from the theme on every site that has no Site Icon of its own. */
add_action(
	'wp_head',
	static function () {
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" type="image/png" href="' . esc_url( get_stylesheet_directory_uri() . '/assets/img/favicon-color.png' ) . '">' . "\n";
		}
	}
);
