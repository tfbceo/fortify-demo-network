<?php
/**
 * Phase 2: apply the Fortify design system to the site 1 Elementor kit.
 *
 *   wp eval-file scripts/apply-kit.php
 *
 * Re-runnable. Overwrites colors, typography, layout, breakpoints and the kit
 * custom CSS; leaves every other kit setting alone. The kit reaches a sitelet
 * only when the sitelet is created, so run this before phase 6.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_main_site() ) {
	WP_CLI::error( 'Run on the main site (site 1).' );
}

$plugin = \Elementor\Plugin::$instance;
$kit    = $plugin->kits_manager->get_active_kit();
if ( ! $kit || ! $kit->get_id() ) {
	WP_CLI::error( 'No active Elementor kit.' );
}

$settings = $kit->get_meta( '_elementor_page_settings' );
$settings = is_array( $settings ) ? $settings : array();

/* ---------- Colors ---------- */

$settings['system_colors'] = array(
	array( '_id' => 'primary',   'title' => 'Indigo',  'color' => '#4F46E5' ),
	array( '_id' => 'secondary', 'title' => 'Ink',     'color' => '#1E1B4B' ),
	array( '_id' => 'text',      'title' => 'Slate',   'color' => '#56546E' ),
	array( '_id' => 'accent',    'title' => 'Emerald', 'color' => '#0E9F6E' ),
);

$custom = array(
	// Indigo.
	'indigo700'     => array( 'Indigo 700', '#4338CA' ),
	'indigodeep'    => array( 'Indigo Deep', '#2A2666' ),
	'indigotint'    => array( 'Indigo Tint', '#EEF0FE' ),
	'indigoline'    => array( 'Indigo Line', '#DDDBF7' ),
	// Ink.
	'ink2'          => array( 'Ink 2', '#15132F' ),
	'night'         => array( 'Night', '#272350' ),
	// Surfaces.
	'paper'         => array( 'Paper', '#FFFFFF' ),
	'lav0'          => array( 'Lavender 0', '#FAF9FF' ),
	'lav1'          => array( 'Lavender 1', '#F6F4FD' ),
	'lav2'          => array( 'Lavender 2', '#ECEAFB' ),
	// Text on light.
	'muted'         => array( 'Muted', '#8B8AA3' ),
	'line'          => array( 'Line', '#E9E7F4' ),
	// Text on dark.
	'ondark'        => array( 'On Dark', '#CFCDEE' ),
	'ondarkmuted'   => array( 'On Dark Muted', '#9794AD' ),
	'ondarkaccent'  => array( 'On Dark Accent', '#B9B4F5' ),
	// Green.
	'emeralddark'   => array( 'Emerald Dark', '#0A7A54' ),
	'emeraldbright' => array( 'Emerald Bright', '#34D399' ),
	'emeraldtint'   => array( 'Emerald Tint', '#E7F7F0' ),
	// Other.
	'star'          => array( 'Review Star', '#FBBC05' ),
	// Translucent (#RRGGBBAA).
	'glass'         => array( 'Glass', '#FFFFFFF0' ),       // white 94%
	'ondarkline'    => array( 'On Dark Line', '#FFFFFF1F' ), // white 12%
	'ondarkfill'    => array( 'On Dark Fill', '#FFFFFF12' ), // white 7%
	'scrim'         => array( 'Scrim', '#1A1838D1' ),        // rgba(26,24,56,.82)
);
$settings['custom_colors'] = array();
foreach ( $custom as $id => $c ) {
	$settings['custom_colors'][] = array( '_id' => $id, 'title' => $c[0], 'color' => $c[1] );
}

/* ---------- Typography ---------- */

$font = 'Plus Jakarta Sans';
$t    = static function ( $id, $title, $weight, $extra = array() ) use ( $font ) {
	return array_merge(
		array(
			'_id'                       => $id,
			'title'                     => $title,
			'typography_typography'     => 'custom',
			'typography_font_family'    => $font,
			'typography_font_weight'    => (string) $weight,
		),
		$extra
	);
};
$settings['system_typography'] = array(
	$t( 'primary', 'Headings', 800, array(
		'typography_line_height'    => array( 'unit' => 'em', 'size' => 1.08 ),
		'typography_letter_spacing' => array( 'unit' => 'em', 'size' => -0.02 ),
	) ),
	$t( 'secondary', 'Subheadings', 700 ),
	$t( 'text', 'Body', 400, array(
		'typography_font_size'   => array( 'unit' => 'px', 'size' => 17 ),
		'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
	) ),
	$t( 'accent', 'Buttons and eyebrows', 700 ),
);
$settings['custom_typography'] = array();

$settings['default_generic_fonts']       = 'system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
$settings['body_typography_typography']  = 'custom';
$settings['body_typography_font_family'] = $font;
$settings['body_typography_font_weight'] = '400';
$settings['body_typography_font_size']   = array( 'unit' => 'px', 'size' => 17 );
$settings['body_typography_line_height'] = array( 'unit' => 'em', 'size' => 1.6 );
$settings['body_color']                  = '#56546E';
$settings['body_background_background']  = 'classic';
$settings['body_background_color']       = '#FFFFFF';
$settings['link_normal_color']           = '#4F46E5';
$settings['link_hover_color']            = '#4338CA';

$globals = isset( $settings['__globals__'] ) && is_array( $settings['__globals__'] ) ? $settings['__globals__'] : array();
$globals['body_color']            = 'globals/colors?id=text';
$globals['body_background_color'] = 'globals/colors?id=paper';
$globals['link_normal_color']     = 'globals/colors?id=primary';
$globals['link_hover_color']      = 'globals/colors?id=indigo700';
foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $h ) {
	$settings[ $h . '_color' ]                 = '#1E1B4B';
	$globals[ $h . '_color' ]                  = 'globals/colors?id=secondary';
	$settings[ $h . '_typography_typography' ] = 'custom';
	$globals[ $h . '_typography_typography' ]  = 'globals/typography?id=primary';
}

/* ---------- Buttons (kit defaults; Fortify variants are CSS classes) ---------- */

$settings['button_typography_typography']  = 'custom';
$settings['button_typography_font_family'] = $font;
$settings['button_typography_font_weight'] = '700';
$settings['button_typography_font_size']   = array( 'unit' => 'px', 'size' => 17 );
$settings['button_text_color']             = '#FFFFFF';
$settings['button_background_color']       = '#4F46E5';
$settings['button_hover_background_color'] = '#4338CA';
$settings['button_border_radius']          = array( 'unit' => 'px', 'top' => '999', 'right' => '999', 'bottom' => '999', 'left' => '999', 'isLinked' => true );
$settings['button_padding']                = array( 'unit' => 'px', 'top' => '15', 'right' => '26', 'bottom' => '15', 'left' => '26', 'isLinked' => false );
$globals['button_text_color']             = 'globals/colors?id=paper';
$globals['button_background_color']       = 'globals/colors?id=primary';
$globals['button_hover_background_color'] = 'globals/colors?id=indigo700';

$settings['__globals__'] = $globals;

/* ---------- Layout ---------- */

// 1180px wide including 24px side padding, so 1132px of content.
$settings['container_width']       = array( 'unit' => 'px', 'size' => 1132 );
$settings['container_padding']     = array( 'unit' => 'px', 'top' => '0', 'right' => '24', 'bottom' => '0', 'left' => '24', 'isLinked' => false );
$settings['space_between_widgets'] = array( 'unit' => 'px', 'column' => '16', 'row' => '16', 'isLinked' => true, 'size' => 16 );
$settings['page_title_selector']   = 'h1.entry-title';

/* ---------- Breakpoints (Fortify 980 / 760 / 560; 860 folded into 980) ---------- */

$settings['viewport_mobile']       = 560;
$settings['viewport_mobile_extra'] = 760;
$settings['viewport_tablet']       = 980;
$settings['viewport_md']           = 561;
$settings['viewport_lg']           = 981;
$settings['active_breakpoints']    = array( 'viewport_mobile', 'viewport_mobile_extra', 'viewport_tablet' );

/* ---------- Spacing scale, shadows, radii (kit custom CSS) ---------- */

$settings['custom_css'] = <<<'CSS'
/* Fortify design tokens (phase 2). Colors are the kit's global colors:
   var(--e-global-color-primary), var(--e-global-color-night) and so on. */
:root {
	/* Spacing scale: the only values used for layout padding, margins and gaps. */
	--space-8: 8px;
	--space-12: 12px;
	--space-16: 16px;
	--space-24: 24px;
	--space-32: 32px;
	--space-48: 48px;
	--space-64: 64px;
	--space-88: 88px;
	/* Shapes. */
	--radius: 16px;
	--radius-lg: 24px;
	--radius-pill: 999px;
	--shadow-sm: 0 1px 2px rgba(30,27,75,.06), 0 4px 16px rgba(30,27,75,.05);
	--shadow-md: 0 12px 36px rgba(30,27,75,.10);
	--shadow-lg: 0 30px 70px -20px rgba(30,27,75,.28);
}
CSS;

// Written as meta, not through $kit->save(): from WP-CLI the kit's controls are
// not registered, and save() drops every setting it cannot match to a control.
update_post_meta( $kit->get_id(), '_elementor_page_settings', wp_slash( $settings ) );
clean_post_cache( $kit->get_id() );
$plugin->documents->get( $kit->get_id(), false ); // reload, bypassing the document cache
$kit = $plugin->kits_manager->get_active_kit();

// Rebuild the responsive stylesheets for the new breakpoints and drop cached CSS.
$plugin->breakpoints->refresh();
\Elementor\Core\Breakpoints\Manager::compile_stylesheet_templates();
$plugin->files_manager->clear_cache();

$check = $kit->get_meta( '_elementor_page_settings' );
WP_CLI::success( sprintf(
	'Kit %d: %d system + %d custom colors, font %s, breakpoints %d/%d/%d, content width %dpx.',
	$kit->get_id(),
	count( $check['system_colors'] ),
	count( $check['custom_colors'] ),
	$check['body_typography_font_family'],
	$check['viewport_mobile'],
	$check['viewport_mobile_extra'],
	$check['viewport_tablet'],
	$check['container_width']['size']
) );
