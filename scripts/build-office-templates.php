<?php
/**
 * Phase 5: the six office Sitelet Pages on site 1, with the lock map applied.
 *
 *   wp eval-file scripts/build-office-templates.php
 *
 * Run after build-home.php: the corporate sections of the office home page
 * (how it works, calculator, comparison, plans, closing call to action, and
 * the hero layout) are copied from the corporate home page, so the two stay
 * in step when both scripts are re-run. Re-runnable: finds its own posts by
 * `_fortify_build_key`.
 *
 * Pages (slugs): office (marked as the Sitelet home page), service-area,
 * team, reviews, local-faq, visit. Every top-level container has a CSS ID.
 *
 * Lock map (BUILD-SPEC section 8), applied as Sitelets lock keys
 * (`{control or section id}_multisite_sitelet_permissions = yes` hides it in
 * the office editor):
 * - Default: every section of every element is locked.
 * - Class `fx-local-text`: only the text control stays editable (Heading
 *   title, Text Editor content, Nested Accordion item titles).
 * - Class `fx-local-card`: the named content sections stay editable (Listing
 *   Card photo, details and note); every other section stays locked.
 * Registry and Office Profile widgets are locked: their content comes from
 * the registry or the office's Office Profile screen, not from the page.
 * Template notes for corporate editors sit in a top-level container set to
 * "Available on: Admin only", which Sitelets drops when it copies the page.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_main_site() ) {
	WP_CLI::error( 'Run on the main site (site 1).' );
}
foreach ( array( '\SiteletsWP\Sitelets_Sync', '\FortifyOffices\Registry' ) as $class ) {
	if ( ! class_exists( $class ) ) {
		WP_CLI::error( "$class is missing: Sitelets and fortify-offices must be active." );
	}
}

require_once __DIR__ . '/lib/fx-elements.php';

const FX_LOCK = '_multisite_sitelet_permissions';

/* ====================================================================
 * Source: the corporate home page
 * ================================================================== */

$home_ids = get_posts( array(
	'post_type'   => 'page',
	'post_status' => 'any',
	'meta_key'    => '_fortify_build_key',
	'meta_value'  => 'home',
	'fields'      => 'ids',
) );
if ( ! $home_ids ) {
	WP_CLI::error( 'Corporate home page not found. Run build-home.php first.' );
}
$home_data = json_decode( get_post_meta( $home_ids[0], '_elementor_data', true ), true );
$home      = array();
foreach ( $home_data as $el ) {
	if ( ! empty( $el['settings']['_element_id'] ) ) {
		$home[ $el['settings']['_element_id'] ] = $el;
	}
}

/** Fresh element IDs throughout a copied tree. */
function fx_reid( array $el ) {
	$el['id'] = fx_eid();
	foreach ( $el['elements'] ?? array() as $i => $child ) {
		$el['elements'][ $i ] = fx_reid( $child );
	}
	return $el;
}

/** Copy a corporate home section under a new CSS ID. */
function fx_copy_section( array $home, $from, $to, $title ) {
	if ( ! isset( $home[ $from ] ) ) {
		WP_CLI::error( "Home section #$from not found." );
	}
	$el                            = fx_reid( $home[ $from ] );
	$el['settings']['_element_id'] = $to;
	$el['settings']['_title']      = $title;
	return $el;
}

/** Replace widgets matching $test (callable on the element) with $make($el). */
function fx_replace( array $el, callable $test, callable $make ) {
	if ( 'widget' === $el['elType'] && $test( $el ) ) {
		return $make( $el );
	}
	foreach ( $el['elements'] ?? array() as $i => $child ) {
		$el['elements'][ $i ] = fx_replace( $child, $test, $make );
	}
	return $el;
}

function fx_has_class( array $el, $class ) {
	$c = $el['settings']['_css_classes'] ?? ( $el['settings']['css_classes'] ?? '' );
	return in_array( $class, preg_split( '/\s+/', (string) $c ), true );
}

/* ====================================================================
 * Lock map
 * ================================================================== */

/** Control definitions for an element (cached per type). */
function fx_controls( array $el ) {
	static $cache = array();
	$key = 'widget' === $el['elType'] ? $el['widgetType'] : $el['elType'];
	if ( ! isset( $cache[ $key ] ) ) {
		$p    = \Elementor\Plugin::$instance;
		$type = 'widget' === $el['elType'] ? $p->widgets_manager->get_widget_types( $key ) : $p->elements_manager->get_element_types( $key );
		if ( ! $type ) {
			WP_CLI::error( "Unknown element type $key." );
		}
		$cache[ $key ] = $type->get_controls();
	}
	return $cache[ $key ];
}

/** Text controls left editable on `fx-local-text` elements. */
const FX_TEXT_CONTROLS = array(
	'heading'          => array( 'title' ),
	'text-editor'      => array( 'editor' ),
	'nested-accordion' => array( 'items' ),
);

/**
 * Content sections left editable on `fx-local-card` elements. Named
 * explicitly: Elementor does not mark the shared Advanced-tab sections with
 * their tab in get_controls(), so "lock everything but Content" would miss them.
 */
const FX_CARD_SECTIONS = array(
	'fortify-listing-card' => array( 'section_photo', 'section_details', 'section_note' ),
);

function fx_apply_locks( array $el, array &$stats ) {
	$controls = fx_controls( $el );
	$sections = array();
	foreach ( $controls as $id => $c ) {
		if ( 'section' === ( $c['type'] ?? '' ) ) {
			$sections[ $id ] = $c['tab'] ?? 'content';
		}
	}

	$mode = 'locked';
	if ( fx_has_class( $el, 'fx-local-text' ) ) {
		$mode = 'text';
	} elseif ( fx_has_class( $el, 'fx-local-card' ) ) {
		$mode = 'card';
	}

	if ( 'text' === $mode ) {
		$keep = FX_TEXT_CONTROLS[ $el['widgetType'] ?? '' ] ?? array();
		if ( ! $keep ) {
			WP_CLI::error( 'fx-local-text on an element without a known text control: ' . ( $el['widgetType'] ?? $el['elType'] ) );
		}
		$text_section = $controls[ $keep[0] ]['section'] ?? '';
		foreach ( $sections as $id => $tab ) {
			if ( $id !== $text_section ) {
				$el['settings'][ $id . FX_LOCK ] = 'yes';
			}
		}
		foreach ( $controls as $id => $c ) {
			if ( ( $c['section'] ?? '' ) === $text_section && 'section' !== ( $c['type'] ?? '' ) && ! in_array( $id, $keep, true ) ) {
				$el['settings'][ $id . FX_LOCK ] = 'yes';
			}
		}
		$stats['text']++;
	} elseif ( 'card' === $mode ) {
		$open = FX_CARD_SECTIONS[ $el['widgetType'] ?? '' ] ?? null;
		if ( null === $open ) {
			WP_CLI::error( 'fx-local-card on an element without a known content section list: ' . ( $el['widgetType'] ?? $el['elType'] ) );
		}
		foreach ( $sections as $id => $tab ) {
			if ( ! in_array( $id, $open, true ) ) {
				$el['settings'][ $id . FX_LOCK ] = 'yes';
			}
		}
		$stats['card']++;
	} else {
		foreach ( $sections as $id => $tab ) {
			$el['settings'][ $id . FX_LOCK ] = 'yes';
		}
		$stats['locked']++;
	}

	foreach ( $el['elements'] ?? array() as $i => $child ) {
		$el['elements'][ $i ] = fx_apply_locks( $child, $stats );
	}
	return $el;
}

/* ====================================================================
 * Building blocks
 * ================================================================== */

$lav_bg = array(
	'background_background'     => 'gradient',
	'background_color'          => '#F6F4FD',
	'background_color_b'        => '#ECEAFB',
	'background_gradient_angle' => array( 'unit' => 'deg', 'size' => 180 ),
	'__globals__'               => array( 'background_color' => 'globals/colors?id=lav1', 'background_color_b' => 'globals/colors?id=lav2' ),
);

$sec = static function ( $id, $title, $classes, array $children, $top, $bottom, array $extra = array() ) {
	return fx_section( $id, $title, $classes, $children, array_merge( array(
		'padding'        => fx_pad( $top, 24, $bottom, 24 ),
		'padding_tablet' => fx_pad( min( $top, 64 ), 24, min( $bottom, 64 ), 24 ),
	), $extra ) );
};

/** "Fortify Tampa office" from the registry; "Fortify local office" on site 1. */
$office_eyebrow = static function ( $classes = 'fx-office-eyebrow' ) {
	return fx_w( 'fortify-office-info', array(
		'field'          => 'name',
		'before'         => 'Fortify ',
		'after'          => ' office',
		'tag'            => 'p',
		'fallback'       => 'Fortify local office',
		'preview_office' => 'tampa',
		'_css_classes'   => $classes,
	) );
};

/** Office page hero: registry eyebrow, corporate title and lead. */
$office_hero = static function ( $id, $title, $sub ) use ( $sec, $lav_bg, $office_eyebrow ) {
	return $sec( $id, 'Page hero', 'fx-page-hero', array(
		$office_eyebrow(),
		fx_heading( $title, 'h1', 'fx-page-title', array( '_margin' => fx_pad( 14, 0, 0, 0 ) ) ),
		fx_text( '<p>' . $sub . '</p>', 'fx-page-sub' ),
	), 64, 48, array_merge( $lav_bg, array( 'padding_mobile_extra' => fx_pad( 48, 24, 32, 24 ) ) ) );
};

$intro = static function ( $eyebrow, $title, $lead = '' ) {
	$children = array( fx_eyebrow( $eyebrow ), fx_heading( $title, 'h2', 'fx-h2m' ) );
	if ( $lead ) {
		$children[] = fx_text( '<p>' . $lead . '</p>', 'fx-lead' );
	}
	return fx_box( 'Section intro', 'fx-intro', $children );
};

$admin_note = static function ( $id, array $lines ) {
	return fx_con( array(
		'_title'                 => 'Template notes (Admin only: removed from office sites)',
		'_element_id'            => $id,
		'css_classes'            => 'fx-admin-note',
		'content_width'          => 'boxed',
		'flex_direction'         => 'column',
		'_advanced_sitelet_hide' => 'admin',
		'margin'                 => array( 'unit' => 'px', 'top' => '24', 'right' => '24', 'bottom' => '24', 'left' => '24', 'isLinked' => false ),
	), array( fx_text( '<p>' . implode( '</p><p>', $lines ) . '</p>' ) ), false );
};

$profile = static function ( $type, array $extra = array() ) {
	return fx_w( $type, array_merge( array( 'preview_office' => 'tampa' ), $extra ) );
};

/* ====================================================================
 * 1. Office home (slug: office)
 * ================================================================== */

$hero = fx_copy_section( $home, 'home-hero', 'office-hero', 'Hero' );
$hero = fx_replace(
	$hero,
	static fn( $w ) => fx_has_class( $w, 'fx-hero-eyebrow' ),
	static fn( $w ) => $office_eyebrow( 'fx-office-eyebrow fx-hero-eyebrow' )
);
$hero = fx_replace(
	$hero,
	static fn( $w ) => fx_has_class( $w, 'fx-hero-title' ),
	static function ( $w ) {
		$w['id']                        = fx_eid();
		$w['settings']['title']         = 'Sell your home with a local broker.<br><span class="fx-accent">Skip the full commission.</span>';
		$w['settings']['_css_classes'] .= ' fx-local-text';
		$w['settings']['_title']        = 'Local headline (editable by the office)';
		return $w;
	}
);

$office_home = array(
	$hero,

	$sec( 'office-intro', 'Market intro (Office Profile)', '', array(
		fx_grid( 'minmax(0,1fr) minmax(0,1.2fr)', 1, 24, 56, array(
			fx_box( 'Heading', 'fx-intro', array(
				fx_eyebrow( 'Your local market' ),
				fx_w( 'fortify-office-info', array(
					'field'          => 'name',
					'before'         => 'Selling in the ',
					'after'          => ' area',
					'tag'            => 'h2',
					'fallback'       => 'Selling in your area',
					'preview_office' => 'tampa',
					'_css_classes'   => 'fx-office-title',
				) ),
				fx_w( 'fortify-office-info', array(
					'field'          => 'counties',
					'before'         => 'Serving ',
					'preview_office' => 'tampa',
					'_css_classes'   => 'fx-office-counties',
				) ),
			) ),
			$profile( 'fortify-market-intro' ),
		), array( '_title' => 'Intro grid', 'grid_align_items' => 'start' ) ),
	), 88, 88 ),

	$sec( 'office-sample-listing', 'Sample listing (editable by the office)', 'fx-sec-lav', array(
		fx_grid( 'minmax(0,1fr) minmax(0,1fr)', 1, 32, 56, array(
			fx_box( 'Copy', 'fx-intro', array(
				fx_eyebrow( 'Recently listed' ),
				fx_heading( 'What a Fortify listing looks like here', 'h2', 'fx-h2m' ),
				fx_text( '<p>Every listing from this office goes on the local MLS and every major home-search site, reviewed by a licensed broker first. The seller pays one flat fee at closing.</p>', 'fx-lead' ),
			) ),
			fx_w( 'fortify-listing-card', array( '_css_classes' => 'fx-local-card', '_title' => 'Sample listing (photo and details editable)' ) ),
		), array( '_title' => 'Listing grid', 'grid_align_items' => 'center' ) ),
	), 88, 88, $lav_bg ),

	fx_copy_section( $home, 'home-how', 'office-howitworks', 'How it works' ),
	fx_copy_section( $home, 'home-savings', 'office-calculator', 'Savings calculator' ),
	fx_copy_section( $home, 'home-compare', 'office-comparison', 'Comparison' ),
	fx_copy_section( $home, 'home-plans', 'office-plans', 'Plans' ),
	fx_copy_section( $home, 'home-cta', 'office-cta', 'Final call to action' ),

	$admin_note( 'office-admin-notes', array(
		'<b>Template notes (corporate editors only).</b> This box is set to Available on: Admin only, so Sitelets removes it when the page is copied to an office site.',
		'Editable by the office: the hero headline text and the sample listing card (photo and details). Everything else is locked.',
		'From the registry: office name, counties. From the office’s Office Profile screen: the market intro.',
		'Corporate sections copied from the home page by build-office-templates.php: hero layout, how it works, calculator, comparison, plans, closing call to action. Change them on the home page and re-run the script.',
	) ),
);

/* ====================================================================
 * 2. Service area (slug: service-area)
 * ================================================================== */

$service_area = array(
	$office_hero( 'sa-hero', 'Where we list homes', 'The counties and ZIP codes this office covers, and what sellers here should know.' ),

	$sec( 'sa-overview', 'Coverage (registry)', '', array(
		fx_grid( 'minmax(0,1fr) minmax(0,1fr)', 1, 24, 48, array(
			fx_box( 'Coverage facts', 'fx-office-facts', array(
				fx_eyebrow( 'Coverage' ),
				fx_w( 'fortify-office-info', array( 'field' => 'counties', 'before' => 'Counties: ', 'preview_office' => 'tampa' ) ),
				fx_w( 'fortify-office-info', array( 'field' => 'mls', 'before' => 'Listings go on ', 'after' => ', the MLS local agents search.', 'preview_office' => 'tampa' ) ),
				fx_w( 'fortify-office-info', array( 'field' => 'state', 'before' => 'Licensed in ', 'preview_office' => 'tampa' ) ),
			) ),
			fx_box( 'ZIP codes', 'fx-office-facts', array(
				fx_eyebrow( 'ZIP codes served' ),
				fx_w( 'fortify-office-info', array( 'field' => 'zips', 'preview_office' => 'tampa', '_css_classes' => 'fx-office-zips' ) ),
				fx_text( '<p>Not sure? <a href="/locations/">Check any ZIP code →</a></p>', 'fx-local-note' ),
			) ),
		), array( '_title' => 'Coverage grid', 'grid_align_items' => 'start' ) ),
	), 64, 64 ),

	$sec( 'sa-neighborhoods', 'Neighborhoods (editable by the office)', 'fx-sec-lav', array(
		fx_box( 'Neighborhoods', 'fx-intro', array(
			fx_eyebrow( 'Neighborhoods' ),
			fx_heading( 'Neighborhoods we know', 'h2', 'fx-h2m' ),
			fx_text( '<p>Sample text. The office lists the neighborhoods and communities it sells in most, with a line on what makes each one distinct.</p>', 'fx-lead fx-local-text', array( '_title' => 'Neighborhoods text (editable)' ) ),
		) ),
	), 64, 64, $lav_bg ),

	$sec( 'sa-market-notes', 'Market notes (editable by the office)', '', array(
		fx_box( 'Market notes', 'fx-intro', array(
			fx_eyebrow( 'Local market notes' ),
			fx_heading( 'What sellers here should know', 'h2', 'fx-h2m' ),
			fx_text( '<p>Sample text. The office adds local notes: typical days on market, seasonal patterns, HOA or flood-zone questions buyers ask, and anything else that affects pricing here.</p>', 'fx-lead fx-local-text', array( '_title' => 'Market notes text (editable)' ) ),
		) ),
	), 64, 88 ),
);

/* ====================================================================
 * 3. Team (slug: team)
 * ================================================================== */

$team = array(
	$office_hero( 'team-hero', 'Meet your local team', 'The broker and team behind every listing from this office.' ),

	$sec( 'team-list', 'Team (Office Profile)', '', array(
		$profile( 'fortify-team-list', array( 'columns' => '3' ) ),
	), 64, 64 ),

	$sec( 'team-compliance', 'Licensing and compliance', 'fx-sec-lav', array(
		fx_grid( 'minmax(0,1.2fr) minmax(0,1fr)', 1, 24, 48, array(
			fx_box( 'Compliance copy', 'fx-intro', array(
				fx_eyebrow( 'Licensing' ),
				fx_heading( 'Broker oversight on every listing', 'h2', 'fx-h2m' ),
				fx_text( '<p>Every listing from this office is reviewed by the broker of record before it goes to the MLS. Fortify Listing Services is a licensed real estate brokerage, and each office lists under the license for its state.</p>', 'fx-lead' ),
			) ),
			fx_box( 'License details', 'fx-broker-info', array(
				fx_heading( 'Office licensing', 'h3' ),
				fx_w( 'fortify-license-block', array( 'preview_office' => 'tampa', '_css_classes' => 'fx-lead' ) ),
			) ),
		), array( '_title' => 'Compliance grid', 'grid_align_items' => 'center' ) ),
	), 64, 88, $lav_bg ),
);

/* ====================================================================
 * 4. Reviews (slug: reviews)
 * ================================================================== */

$reviews = array(
	$office_hero( 'reviews-hero', 'What local sellers say', 'Reviews from sellers this office has worked with.' ),

	$sec( 'reviews-list', 'Reviews (Office Profile)', '', array(
		$profile( 'fortify-local-reviews', array( 'columns' => '3' ) ),
	), 64, 48 ),

	$sec( 'reviews-disclosure', 'Review disclosure', '', array(
		fx_text( '<p>Reviews on this page are provided by the local office from sellers it has worked with. Reviews marked as sample are demo content, not real customer reviews.</p>', 'fx-local-note' ),
	), 0, 88 ),
);

/* ====================================================================
 * 5. Local FAQ (slug: local-faq)
 * ================================================================== */

$local_faq_items = array(
	array( 'How long do homes take to sell here?', '<p>Sample answer. The office replaces this with local figures: typical days on market and how that changes through the year.</p>' ),
	array( 'Which MLS will my listing appear on?', '<p>Sample answer. The office names its MLS and explains that the listing also appears on Zillow, Realtor.com, Redfin and other sites.</p>' ),
	array( 'Do you handle showings in my area?', '<p>Sample answer. The office explains how showings and lockbox access work locally on each plan.</p>' ),
);
$local_faq = fx_faq( $local_faq_items, 'fx-faq-cards fx-local-text', true );
foreach ( $local_faq['elements'] as $i => $answer ) {
	foreach ( $answer['elements'] as $j => $w ) {
		$local_faq['elements'][ $i ]['elements'][ $j ]['settings']['_css_classes'] = 'fx-local-text';
	}
}
$local_faq['settings']['_title'] = 'Local FAQ entries (editable by the office)';

$local_faq_page = array(
	$office_hero( 'localfaq-hero', 'Questions sellers ask here', 'Local answers from this office. For questions about plans and pricing, see the full FAQ.' ),

	$sec( 'localfaq-entries', 'Local FAQ entries', '', array(
		fx_box( 'FAQ list', 'fx-faq-wrap', array( $local_faq ) ),
	), 64, 32 ),

	$sec( 'localfaq-corporate-link', 'Link to the full FAQ', '', array(
		fx_text( '<p>More questions? <a href="/faqs/">See the full FAQ →</a></p>', 'fx-more-line' ),
	), 0, 88 ),
);

/* ====================================================================
 * 6. Visit us (slug: visit)
 * ================================================================== */

$visit = array(
	$office_hero( 'visit-hero', 'Visit the office', 'Where to find us, and when.' ),

	$sec( 'visit-details', 'Office details and map (registry)', '', array(
		fx_grid( 'minmax(0,1fr) minmax(0,1.4fr)', 1, 24, 48, array(
			fx_box( 'Office details', 'fx-office-facts', array(
				fx_w( 'fortify-office-info', array( 'field' => 'name', 'after' => ' office', 'preview_office' => 'tampa' ) ),
				fx_w( 'fortify-office-info', array( 'field' => 'address', 'preview_office' => 'tampa' ) ),
				fx_w( 'fortify-office-info', array( 'field' => 'phone', 'preview_office' => 'tampa' ) ),
				fx_w( 'fortify-office-info', array( 'field' => 'hours', 'preview_office' => 'tampa' ) ),
				fx_w( 'fortify-office-info', array( 'field' => 'mls', 'before' => 'Listing on ', 'preview_office' => 'tampa' ) ),
				fx_button( 'Start my free preview', '/#start', 'indigo', '', 'left', array( '_margin' => fx_pad( 20, 0, 0, 0 ) ) ),
			) ),
			fx_w( 'fortify-office-info', array( 'field' => 'map', 'map_height' => 380, 'preview_office' => 'tampa' ) ),
		), array( '_title' => 'Visit grid', 'grid_align_items' => 'start' ) ),
	), 64, 88 ),
);

/* ====================================================================
 * Save, lock, mark the home page
 * ================================================================== */

$pages = array(
	'office'       => array( 'Office Home', $office_home ),
	'service-area' => array( 'Service Area', $service_area ),
	'team'         => array( 'Team', $team ),
	'reviews'      => array( 'Reviews', $reviews ),
	'local-faq'    => array( 'Local FAQ', $local_faq_page ),
	'visit'        => array( 'Visit Us', $visit ),
);

// Sitelet Page slugs must not collide with corporate pages (BUILD-SPEC section 5).
foreach ( array_keys( $pages ) as $slug ) {
	if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
		WP_CLI::error( "A corporate page already uses the slug '$slug'." );
	}
}

$stats = array( 'locked' => 0, 'text' => 0, 'card' => 0 );
$ids   = array();
foreach ( $pages as $slug => $p ) {
	$elements = array();
	foreach ( $p[1] as $el ) {
		$elements[] = fx_apply_locks( $el, $stats );
	}
	$id = fx_owned_post( 'office-' . $slug, array(
		'post_type'     => MULTISITE_SITELETS_CUSTOM_POST_TYPE,
		'post_status'   => 'publish',
		'post_title'    => $p[0],
		'post_name'     => $slug,
		'page_template' => 'elementor_header_footer',
	) );
	fx_save_elementor( $id, $elements, 'wp-page', array( 'hide_title' => 'yes' ) );
	update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
	$ids[ $slug ] = $id;
	$actual       = get_post_field( 'post_name', $id );
	WP_CLI::line( sprintf( '%-13s %4d  %s%s', $slug, $id, get_permalink( $id ), $actual !== $slug ? "  (slug became '$actual')" : '' ) );
}

update_option( \SiteletsWP\Sitelets_Sync::HOME_PAGE_OPTION, $ids['office'] );

\Elementor\Plugin::$instance->files_manager->clear_cache();
WP_CLI::success( sprintf(
	'6 Sitelet Pages built; Office Home (%d) is the Sitelet home page. Elements: %d fully locked, %d text-only editable, %d content-editable cards.',
	$ids['office'],
	$stats['locked'],
	$stats['text'],
	$stats['card']
) );
