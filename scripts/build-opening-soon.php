<?php
/**
 * The "Opening Soon" Sitelet Page on site 1, published to every office site.
 *
 *   wp eval-file scripts/build-opening-soon.php
 *
 * Coming-soon offices show this page as their home instead of the full office
 * (fortify-offices Opening_Soon); live offices redirect /opening-soon/ to their
 * home. The page: a registry-driven title ("Fortify Listings is coming to
 * Phoenix"), a short description with the counties the office will serve, an
 * interest form, and links to How it works and Plans. No sample listing, team
 * or address-to-preview flow. Demo-labelled like the other sample content.
 *
 * The interest form (Elementor Pro) saves to Submissions on the office site
 * and emails the labelled demo inbox Opening_Soon::DEMO_INBOX, never a real
 * Fortify address. Its hidden `office` field is set on the server from the
 * site the entry arrives on.
 *
 * Every element is locked for the office editor. Touches only its own page:
 * re-runnable (found by `_fortify_build_key`), and publishing sends only this
 * page to the sitelets, so the other office pages and local edits are left alone.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_main_site() ) {
	WP_CLI::error( 'Run on the main site (site 1).' );
}
foreach ( array( '\SiteletsWP\Sitelets_Sync', '\FortifyOffices\Opening_Soon' ) as $class ) {
	if ( ! class_exists( $class ) ) {
		WP_CLI::error( "$class is missing: Sitelets and fortify-offices 0.3.0 must be active." );
	}
}

require_once __DIR__ . '/lib/fx-elements.php';

const FX_OS_LOCK = '_multisite_sitelet_permissions';

/** Locks every control section of every element (the office edits nothing here). */
function fx_os_lock_all( array $el ) {
	static $cache = array();
	$key = 'widget' === $el['elType'] ? $el['widgetType'] : $el['elType'];
	if ( ! isset( $cache[ $key ] ) ) {
		$p    = \Elementor\Plugin::$instance;
		$type = 'widget' === $el['elType'] ? $p->widgets_manager->get_widget_types( $key ) : $p->elements_manager->get_element_types( $key );
		if ( ! $type ) {
			WP_CLI::error( "Unknown element type $key." );
		}
		$cache[ $key ] = array_keys( array_filter( $type->get_controls(), static fn( $c ) => 'section' === ( $c['type'] ?? '' ) ) );
	}
	foreach ( $cache[ $key ] as $section ) {
		$el['settings'][ $section . FX_OS_LOCK ] = 'yes';
	}
	foreach ( $el['elements'] ?? array() as $i => $child ) {
		$el['elements'][ $i ] = fx_os_lock_all( $child );
	}
	return $el;
}

$lav_bg = array(
	'background_background'     => 'gradient',
	'background_color'          => '#F6F4FD',
	'background_color_b'        => '#ECEAFB',
	'background_gradient_angle' => array( 'unit' => 'deg', 'size' => 180 ),
	'__globals__'               => array( 'background_color' => 'globals/colors?id=lav1', 'background_color_b' => 'globals/colors?id=lav2' ),
);

$demo_tag = static function ( $text ) {
	return fx_text( '<p class="fo-sample-tag">' . esc_html( $text ) . '</p>', 'fx-demo-tag' );
};

$field = static function ( $id, $type, $label, array $extra = array() ) {
	return array_merge( array(
		'_id'         => fx_eid(),
		'custom_id'   => $id,
		'field_type'  => $type,
		'field_label' => $label,
		'placeholder' => '',
		'required'    => '',
		'width'       => '100',
	), $extra );
};

$form = fx_w( 'form', array(
	'_title'          => 'Interest form (demo inbox)',
	'form_name'       => \FortifyOffices\Opening_Soon::FORM_NAME,
	'form_fields'     => array(
		$field( 'name', 'text', 'Name', array( 'required' => 'true', 'width' => '50' ) ),
		$field( 'email', 'email', 'Email', array( 'required' => 'true', 'width' => '50' ) ),
		$field( 'phone', 'tel', 'Phone (optional)', array( 'width' => '50' ) ),
		$field( 'address', 'text', 'Property address or ZIP', array( 'required' => 'true', 'width' => '50' ) ),
		$field( 'timeframe', 'select', 'When are you thinking of selling?', array(
			'field_options' => "Within 3 months\n3–6 months\n6+ months\nJust exploring",
		) ),
		$field( 'consent', 'acceptance', '', array(
			'required'        => 'true',
			'acceptance_text' => 'Contact me about the office opening. (Demo form: entries go to a demo inbox, not to Fortify.)',
		) ),
		$field( 'office', 'hidden', 'Office', array( 'field_value' => 'set by the server' ) ),
	),
	'button_text'     => 'Let me know when it opens',
	'button_size'     => 'md',
	'submit_actions'  => array( 'save-to-database', 'email' ),
	'email_to'        => \FortifyOffices\Opening_Soon::DEMO_INBOX,
	'email_subject'   => '[DEMO] Opening soon interest',
	'email_content'   => '[all-fields]',
	'email_from_name' => 'Fortify demo network',
	'email_reply_to'  => 'email',
	'custom_messages' => 'yes',
	'success_message' => 'Thanks. We will let you know when this office opens. (Demo: this entry went to a demo inbox.)',
	'_css_classes'    => 'fx-interest-form',
) );

$elements = array(
	fx_section( 'opening-hero', 'Opening soon (registry title)', 'fx-page-hero', array(
		fx_eyebrow( 'Opening soon' ),
		fx_w( 'fortify-office-info', array(
			'field'          => 'name',
			'before'         => 'Fortify Listings is coming to ',
			'tag'            => 'h1',
			'fallback'       => 'Fortify Listings is coming to your area',
			'preview_office' => 'phoenix',
			'_css_classes'   => 'fx-page-title',
			'_margin'        => fx_pad( 14, 0, 0, 0 ),
		) ),
		fx_text( '<p>Flat fee MLS listings with a local broker. Tell us about your home and we will let you know when the office opens.</p>', 'fx-page-sub' ),
		fx_w( 'fortify-office-info', array(
			'field'          => 'counties',
			'before'         => 'Counties this office will serve: ',
			'preview_office' => 'phoenix',
			'_css_classes'   => 'fx-office-counties',
		) ),
		$demo_tag( 'Sample page — demo' ),
	), array_merge( $lav_bg, array( 'padding' => fx_pad( 64, 24, 48, 24 ) ) ) ),

	fx_section( 'start', 'Interest form', '', array(
		fx_eyebrow( 'Register your interest' ),
		fx_heading( 'Be first to know when we open', 'h2', 'fx-h2m', array( '_margin' => fx_pad( 0, 0, 20, 0 ) ) ),
		$form,
		$demo_tag( 'Demo form — entries go to a demo inbox, not to Fortify' ),
	), array( 'padding' => fx_pad( 64, 24, 48, 24 ) ) ),

	fx_section( 'opening-more', 'Learn more', '', array(
		fx_heading( 'While you wait', 'h2', 'fx-h2m' ),
		fx_con( array( '_title' => 'Links', 'flex_direction' => 'row', 'flex_wrap' => 'wrap', 'flex_gap' => fx_gap( 12 ) ), array(
			fx_button( 'How it works', '/how-it-works/', 'indigo' ),
			fx_button( 'See plans', '/plans/', 'ghost' ),
		) ),
	), array( 'padding' => fx_pad( 0, 24, 88, 24 ) ) ),
);

$elements = array_map( 'fx_os_lock_all', $elements );

// Template notes for corporate editors; Sitelets drops "Admin only" containers when it copies the page.
$elements[] = fx_con( array(
	'_title'                 => 'Template notes (Admin only: removed from office sites)',
	'_element_id'            => 'opening-admin-notes',
	'css_classes'            => 'fx-admin-note',
	'content_width'          => 'boxed',
	'flex_direction'         => 'column',
	'_advanced_sitelet_hide' => 'admin',
	'margin'                 => array( 'unit' => 'px', 'top' => '24', 'right' => '24', 'bottom' => '24', 'left' => '24', 'isLinked' => false ),
), array( fx_text(
	'<p><b>Template notes (corporate editors only).</b> Coming-soon offices show this page as their home; live offices redirect /opening-soon/ to their home (fortify-offices Opening_Soon). Flip the office status in the registry to switch.</p>'
	. '<p>From the registry: office name and counties. Everything else is corporate and locked.</p>'
	. '<p>The interest form saves to Submissions on the office site and emails the demo inbox ' . \FortifyOffices\Opening_Soon::DEMO_INBOX . '. Its hidden Office field is set on the server.</p>'
) ), false );

if ( get_page_by_path( \FortifyOffices\Opening_Soon::SLUG, OBJECT, 'page' ) ) {
	WP_CLI::error( "A corporate page already uses the slug '" . \FortifyOffices\Opening_Soon::SLUG . "'." );
}

$id = fx_owned_post( 'office-opening-soon', array(
	'post_type'     => MULTISITE_SITELETS_CUSTOM_POST_TYPE,
	'post_status'   => 'publish',
	'post_title'    => 'Opening Soon',
	'post_name'     => \FortifyOffices\Opening_Soon::SLUG,
	'page_template' => 'elementor_header_footer',
) );
fx_save_elementor( $id, $elements, 'wp-page', array( 'hide_title' => 'yes' ) );
update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
$actual = get_post_field( 'post_name', $id );
if ( \FortifyOffices\Opening_Soon::SLUG !== $actual ) {
	WP_CLI::error( "Slug became '$actual'." );
}
WP_CLI::line( "Opening Soon: Sitelet Page $id " . get_permalink( $id ) );

// Publish this page only, as Sitelets' "Publish to Sitelets" does.
$publisher = null;
foreach ( $GLOBALS['wp_filter']['admin_init']->callbacks ?? array() as $callbacks ) {
	foreach ( $callbacks as $cb ) {
		if ( is_array( $cb['function'] ) && is_object( $cb['function'][0] ) && method_exists( $cb['function'][0], 'update_sitelet_posts' ) ) {
			$publisher = $cb['function'][0];
		}
	}
}
if ( ! $publisher ) {
	WP_CLI::error( 'Sitelets publisher not found.' );
}
update_post_meta( $id, '_' . MULTISITE_SITELETS_PERMISSIONS_SLUG . '_published_date', current_time( 'mysql' ) );
update_post_meta( $id, '_' . MULTISITE_SITELETS_PERMISSIONS_SLUG . '_published_by', get_current_user_id() );
$post = get_post( $id );
foreach ( get_sites( array( 'fields' => 'ids' ) ) as $blog_id ) {
	if ( get_main_site_id() === (int) $blog_id ) {
		continue;
	}
	$publisher->update_sitelet_posts( array( $post ), $blog_id );
	switch_to_blog( $blog_id );
	$copy = get_page_by_path( \FortifyOffices\Opening_Soon::SLUG, OBJECT, MULTISITE_SITELETS_CUSTOM_POST_TYPE );
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	restore_current_blog();
	WP_CLI::line( sprintf( '  site #%d: %s', $blog_id, $copy ? 'copy ' . $copy->ID : 'NOT COPIED' ) );
}
\Elementor\Plugin::$instance->files_manager->clear_cache();
WP_CLI::success( 'Opening Soon built and published to the office sites.' );
