<?php
/**
 * Phase 3: corporate homepage, plus the Theme Builder header and footer it
 * needs (finalized in phase 5), on site 1.
 *
 *   wp eval-file scripts/build-home.php
 *
 * Re-runnable: finds its own posts by the `_fortify_build_key` meta and
 * replaces their Elementor data. Source: tfbceo/Fortify-Listings-FE-Website
 * index.html (2026-07-28). Every top-level container has a CSS ID.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_main_site() ) {
	WP_CLI::error( 'Run on the main site (site 1).' );
}
if ( ! class_exists( '\ElementorPro\Plugin' ) ) {
	WP_CLI::error( 'Elementor Pro is required for the Theme Builder header and footer.' );
}

require_once __DIR__ . '/lib/fx-elements.php';

/* ====================================================================
 * Shared values
 * ================================================================== */

$img     = wp_make_link_relative( get_stylesheet_directory_uri() ) . '/assets/img';
$lic_url = 'https://www.myfloridalicense.com/portalsearches/VerifyLicensee/LicenseDetail?ID=2F584AC97289A0AF647A608799927DA4';
$app_url = 'https://app.fortifylistings.com/';
$check   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';

/* ====================================================================
 * Menu (header nav)
 * ================================================================== */

$menu_name = 'Fortify primary';
$menu      = wp_get_nav_menu_object( $menu_name );
$menu_id   = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );
foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $old ) {
	wp_delete_post( $old->ID, true );
}
foreach ( array(
	array( 'How it works', '/how-it-works/' ),
	array( 'Plans', '/plans/' ),
	array( 'FAQ', '/faqs/' ),
	array( 'Find your market', '/locations/' ),
) as $i => $item ) {
	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'    => $item[0],
		'menu-item-url'      => $item[1],
		'menu-item-type'     => 'custom',
		'menu-item-status'   => 'publish',
		'menu-item-position' => $i + 1,
	) );
}
$menu_slug = wp_get_nav_menu_object( $menu_id )->slug;

/* ====================================================================
 * Header (Theme Builder)
 * ================================================================== */

$header = array(
	fx_con(
		array(
			'_title'                => 'Site header',
			'_element_id'           => 'site-header',
			'css_classes'           => 'fx-header',
			'html_tag'              => 'div',
			'content_width'         => 'boxed',
			'flex_direction'        => 'row',
			'flex_justify_content'  => 'space-between',
			'flex_align_items'      => 'center',
			'flex_wrap'             => 'nowrap',
			'min_height'            => array( 'unit' => 'px', 'size' => 68 ),
			'padding'               => fx_pad( 0, 24, 0, 24 ),
		),
		array(
			fx_w( 'image', array(
				'_title'       => 'Logo',
				'image'        => array( 'url' => $img . '/fortify-logo-wordmark.svg', 'id' => '', 'alt' => 'Fortify Listing Services', 'source' => 'library' ),
				'image_size'   => 'full',
				'link_to'      => 'custom',
				'link'         => array( 'url' => '/', 'is_external' => '', 'nofollow' => '' ),
				'_css_classes' => 'fx-logo',
			) ),
			fx_con(
				array(
					'_title'               => 'Navigation',
					'flex_direction'       => 'row',
					'flex_justify_content' => 'flex-end',
					'flex_align_items'     => 'center',
					'flex_gap'         => fx_gap( 16 ),
					'width'            => array( 'unit' => 'custom', 'size' => 'auto' ),
				),
				array(
					fx_w( 'nav-menu', array(
						'menu'              => $menu_slug,
						'layout'            => 'horizontal',
						'pointer'           => 'none',
						'dropdown'          => 'none',
						'toggle'            => '',
						'hide_tablet'       => 'hidden-tablet',
						'hide_mobile_extra' => 'hidden-mobile_extra',
						'hide_mobile'       => 'hidden-mobile',
						'color_menu_item'        => '#1E1B4B',
						'color_menu_item_hover'  => '#4F46E5',
						'color_menu_item_active' => '#1E1B4B',
						'__globals__'            => array(
							'color_menu_item'        => 'globals/colors?id=secondary',
							'color_menu_item_hover'  => 'globals/colors?id=primary',
							'color_menu_item_active' => 'globals/colors?id=secondary',
						),
						'_css_classes'      => 'fx-nav',
					) ),
					fx_text( '<a href="' . esc_url( $app_url ) . '">Sign in</a>', 'fx-nav-signin', array(
						'hide_tablet'       => 'hidden-tablet',
						'hide_mobile_extra' => 'hidden-mobile_extra',
						'hide_mobile'       => 'hidden-mobile',
					) ),
					fx_button( 'See my preview', '/#start', 'primary', 'sm' ),
				)
			),
		),
		false
	),
);

/* ====================================================================
 * Footer (Theme Builder)
 * ================================================================== */

$foot_col = static function ( $title, array $links ) {
	return fx_con(
		array( '_title' => $title . ' links', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 16 ) ),
		array(
			fx_heading( $title, 'p', 'fx-foot-head' ),
			fx_list( $links, 'fx-foot-links', '' ),
		)
	);
};

$footer = array(
	fx_con(
		array(
			'_title'         => 'Site footer',
			'_element_id'    => 'site-footer',
			'css_classes'    => 'fx-footer',
			'html_tag'       => 'footer',
			'content_width'  => 'boxed',
			'flex_direction' => 'column',
			'padding'        => fx_pad( 64, 24, 48, 24 ),
		),
		array(
			fx_grid( '1.7fr 1fr 1fr 1.1fr', 2, 32, 32, array(
				fx_con(
					array( '_title' => 'Brand', 'css_classes' => 'fx-foot-brand', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 12 ) ),
					array(
						fx_w( 'image', array(
							'_title'       => 'Logo (light)',
							'image'        => array( 'url' => $img . '/fortify-logo-wordmark-white.svg', 'id' => '', 'alt' => 'Fortify Listing Services', 'source' => 'library' ),
							'image_size'   => 'full',
							'link_to'      => 'custom',
							'link'         => array( 'url' => '/', 'is_external' => '', 'nofollow' => '' ),
							'align'        => 'left',
							'_css_classes' => 'fx-logo fx-logo--light',
						) ),
						fx_text( '<p>Broker-supported flat fee MLS listing for capable Florida sellers.</p>', 'fx-foot-blurb' ),
						fx_text( '<p><a href="tel:4077774657">(407) 777-4-MLS</a></p>', 'fx-foot-phone' ),
					)
				),
				$foot_col( 'Learn', array(
					array( 'text' => 'How it works', 'url' => '/how-it-works/' ),
					array( 'text' => 'Plans & pricing', 'url' => '/plans/' ),
					array( 'text' => 'FAQ', 'url' => '/faqs/' ),
					array( 'text' => 'About Fortify', 'url' => '/about/' ),
				) ),
				$foot_col( 'Support', array(
					array( 'text' => 'Contact', 'url' => '/contact/' ),
					array( 'text' => 'Help center', 'url' => 'https://help.fortifylistings.com', 'external' => true ),
					array( 'text' => 'Sign in', 'url' => $app_url ),
				) ),
				$foot_col( 'Legal', array(
					array( 'text' => 'Privacy policy', 'url' => '/fortify-privacy-policy/' ),
					array( 'text' => 'Terms of service', 'url' => '/fortify-terms-of-service/' ),
					array( 'text' => 'Equal housing', 'url' => '/fortify-equal-housing-statement/' ),
					array( 'text' => 'Payment terms', 'url' => '/fortify-payment-terms/' ),
					array( 'text' => 'Sample agreement', 'url' => '/fortify-sample-listing-agreement/' ),
				) ),
			), array( '_title' => 'Footer columns', 'css_classes' => 'fx-foot-top', 'grid_columns_grid_mobile' => array( 'unit' => 'fr', 'size' => 1 ) ) ),
			// Office directory from the registry (fortify-offices).
			fx_con(
				array(
					'_title'         => 'Office directory',
					'_element_id'    => 'footer-offices',
					'css_classes'    => 'fx-foot-offices',
					'flex_direction' => 'column',
					'flex_gap'       => fx_gap( 12 ),
				),
				array(
					fx_heading( 'Our offices', 'p', 'fx-foot-head' ),
					fx_w( 'fortify-office-directory', array(
						'layout'       => 'list',
						'_css_classes' => 'fx-foot-office-list',
					) ),
				)
			),
			fx_text(
				'<p>Fortify Listing Services LLC · Licensed Florida Real Estate Brokerage · <a href="' . esc_url( $lic_url ) . '" target="_blank" rel="noopener noreferrer">Florida Brokerage License CQ1074119</a> · Licensed in FL, AZ &amp; TN · Serving Stellar MLS markets statewide · (407) 777-4-MLS.</p>' .
				'<p>Buyer-agent compensation, if offered, is separate and set by the seller. Savings comparisons are estimates based on typical listing-side percentage commissions and do not include buyer-agent compensation, closing costs, concessions, or other seller expenses. Flat fee due at closing only if the home sells, subject to the listing agreement. © 2026 Fortify Listing Services LLC.</p>',
				'fx-foot-fine'
			),
		),
		false
	),
);

/* ====================================================================
 * Homepage
 * ================================================================== */

// Hero: decorative sample-listing card (HTML; sample content, not editable copy).
$hero_card = <<<HTML
<div class="fx-pc-wrap">
	<div class="fx-stamp"><span class="chk"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg></span><div><b>Broker review complete</b><small>Fortify Listing Services · Licensed FL Broker</small></div></div>
	<div class="fx-pc fx-pc--tilt">
		<div class="fx-pc-top"><span class="fx-pc-brand">Fortify</span><span class="fx-pc-tag">Example listing preview</span></div>
		<div class="fx-pc-photos">
			<div class="ph main" style="background-image:url({$img}/listing-preview-front-v1.webp)"><span class="ph-badge">COMING TO MLS</span><span class="ph-count">5 of 32 photos</span><span class="ph-wm">SAMPLE LISTING</span></div>
			<div class="ph" style="background-image:url({$img}/listing-preview-detail-v1.webp)"></div>
			<div class="ph" style="background-image:url({$img}/listing-preview-living-v1.webp)"><span class="ph-actions"><i>↗ Share</i><i>♡ Save</i></span></div>
			<div class="ph" style="background-image:url({$img}/listing-preview-kitchen-v1.webp)"></div>
			<div class="ph" style="background-image:url({$img}/listing-preview-interior-v1.webp)"></div>
		</div>
		<div class="fx-pc-body">
			<div class="fx-pc-price"><div><span class="p fx-num">$1,100,000</span><div class="meta">3 bd · 2 ba · 1,900 sqft · den</div></div><span class="fx-pc-live"><span class="fx-dot"></span> Live in ~24 hrs</span></div>
			<div class="fx-pc-addr">6913 Saona Court, Naples FL 34113</div>
			<div class="fx-pc-by">Listed by Fortify Listing Services LLC</div>
		</div>
		<div class="fx-pc-foot"><span class="fx-pc-ava"></span><div><b>Broker-prepared &amp; reviewed</b><small>Every Fortify listing — not a form, not an algorithm</small></div></div>
	</div>
</div>
HTML;

$rail = <<<HTML
<div class="fx-rail-cap">How it works</div>
<div class="fx-rail">
	<div class="fx-rail-step"><span class="n">1</span><div><b>Enter address</b><small>No signup to preview</small></div></div>
	<div class="fx-rail-step"><span class="n">2</span><div><b>Get your preview</b><small>Broker-reviewed</small></div></div>
	<div class="fx-rail-step"><span class="n">3</span><div><b>Choose your plan</b><small>Only after you see it</small></div></div>
	<div class="fx-rail-step"><span class="n">4</span><div><b>List &amp; sell</b><small>Pay at closing</small></div></div>
</div>
HTML;

$showcase_card = <<<HTML
<div class="fx-pc">
	<div class="fx-pc-top"><span class="fx-pc-brand">Fortify</span><span class="fx-pc-tag">Prepared for your review</span></div>
	<div class="fx-pc-photos">
		<div class="ph main" style="background-image:url({$img}/listing-preview-front-v1.webp)"><span class="ph-badge">COMING TO MLS</span><span class="ph-count">5 of 32 photos</span></div>
		<div class="ph" style="background-image:url({$img}/listing-preview-detail-v1.webp)"></div>
		<div class="ph" style="background-image:url({$img}/listing-preview-living-v1.webp)"><span class="ph-actions"><i>↗ Share</i><i>♡ Save</i></span></div>
		<div class="ph" style="background-image:url({$img}/listing-preview-kitchen-v1.webp)"></div>
		<div class="ph" style="background-image:url({$img}/listing-preview-interior-v1.webp)"></div>
	</div>
	<div class="fx-pc-body">
		<div class="fx-pc-price"><div><span class="p fx-num">$1,100,000</span><div class="meta">3 bd · 2 ba · 1,900 sqft · den · 4,791 sqft lot</div></div><span class="fx-pc-live"><span class="fx-dot"></span> Live within 24 hrs of approval</span></div>
		<div class="fx-pc-addr">6913 Saona Court, Naples FL 34113</div>
		<div class="fx-pc-by">Villa · Isles of Collier Preserve · Lake &amp; preserve views · SW exposure</div>
		<div class="fx-pc-stats">
			<div><small>Year built</small><b>2023 · Fresia</b></div>
			<div><small>Pool</small><b>Saltwater + spa</b></div>
			<div><small>Lanai</small><b>Picture-frame</b></div>
			<div><small>HOA</small><b>$724 / mo</b></div>
			<div><small>Flood zone</small><b>X — no flood insurance required</b></div>
			<div><small>Garage</small><b>2-car attached</b></div>
		</div>
	</div>
	<div class="fx-pc-about"><div class="lab">Draft headline &amp; description</div><p><b>Upgraded 2023 Fresia villa with lake &amp; preserve views.</b> The largest villa model at the Isles of Collier Preserve — saltwater pool &amp; spa, open-concept great room, Cambria quartz kitchen, plantation shutters and impact glass throughout, with coveted southwest exposure…</p></div>
	<div class="fx-pc-plan"><div><span class="lab">Fortify recommends</span><b>Pro — $2,495 at closing</b><br><small>Best fit for this price point &amp; timeline</small></div><a class="fx-btn fx-btn-indigo fx-btn-sm" href="#home-plans">View plans</a></div>
	<div class="fx-pc-disc">Sample preview — an actual Fortify-listed Naples home, shown with the seller’s permission. Your preview is built from your property records, broker-reviewed, and emailed to you to approve. Nothing goes live until you say so.</div>
</div>
HTML;

// Feature table rows: feature, note, essentials, pro, elite. true = check, false = dash.
$matrix_rows = array(
	array( 'Licensed broker listing review', 'Every listing reviewed before it goes live', true, true, true ),
	array( 'Error &amp; rule check before you go live', 'We double-check your listing for mistakes and rule violations that could delay or derail your sale.', false, true, true ),
	array( 'On the MLS + every major site', 'Zillow, Realtor.com, Redfin, Homes.com + 100 sites', true, true, true ),
	array( 'Listing photos', '', 'Up to 25', 'Unlimited', 'Unlimited' ),
	array( 'Professional photography &amp; floor plan', 'Professional shoot + floor plan, scheduled at launch', '$495 add-on', '$495 add-on', true ),
	array( 'Listing edits after going live', 'Price changes always free on every plan', 'Up to 3', 'Unlimited', 'Unlimited' ),
	array( 'Electronic lockbox for agent showings', 'Buyer’s agents can access your home securely, and the lockbox logs every entry. (Supra eKEY-compatible.)', false, true, true ),
	array( 'Showings scheduled for you — no phone tag', 'Showings get requested, confirmed, and tracked automatically. (Powered by ShowingTime.)', false, true, true ),
	array( 'Buyer’s agent fee — optional, and handled for you', 'Paying the buyer’s agent is now optional. You decide if and how much — we handle the paperwork either way.', true, true, true ),
	array( 'What your home is worth — in writing, signed by the broker', 'A formal Broker Price Opinion with nearby comparable sales — from a licensed broker, not an algorithm.', true, true, true ),
	array( 'Pricing strategy session before you list', 'Where to price, the lowest offer you’d take, and when you’d adjust — decided with your broker before day one.', false, false, true ),
	array( 'Ongoing pricing guidance while you’re on the market', 'If showings slow down, we tell you why and what to change — before your listing goes stale.', false, false, true ),
	array( 'Support channel', '', 'Email &amp; portal', 'Priority email &amp; phone', 'Direct broker line' ),
	array( 'Offer review &amp; negotiation guidance', 'Broker reviews terms, flags issues, advises on counter', false, false, true ),
	array( 'Contract &amp; closing guidance', 'Timeline and deadline tracking, inspection response, closing walk-through', false, false, true ),
);
$cell = static function ( $v, $pro = false ) {
	$c = 'val' . ( $pro ? ' col-pro' : '' );
	if ( true === $v ) {
		return '<td class="' . $c . ' yes">✓</td>';
	}
	if ( false === $v ) {
		return '<td class="' . $c . '"><span class="dash">—</span></td>';
	}
	return '<td class="' . $c . '">' . $v . '</td>';
};
$rows_html = '';
foreach ( $matrix_rows as $r ) {
	$rows_html .= '<tr><td class="feat">' . $r[0] . ( $r[1] ? '<small>' . $r[1] . '</small>' : '' ) . '</td>' . $cell( $r[2] ) . $cell( $r[3], true ) . $cell( $r[4] ) . '</tr>';
}
$matrix = <<<HTML
<div class="fx-matrix-toggle"><button type="button" data-fx-toggle aria-expanded="false" aria-controls="fx-matrix" data-label-closed="Compare every feature, side by side" data-label-open="Hide full feature comparison"><span data-fx-toggle-label>Compare every feature, side by side</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button></div>
<div class="fx-matrix-wrap" id="fx-matrix">
	<table class="fx-matrix">
		<thead><tr><th>Feature</th><th class="col">Essentials<span class="price">$995 at closing</span></th><th class="col pro">Pro<span class="price">$2,495 at closing</span></th><th class="col">Elite<span class="price">from $3,995 at closing</span></th></tr></thead>
		<tbody>{$rows_html}</tbody>
	</table>
</div>
HTML;

$plan = static function ( $name, $bestfor, $price_html, array $features, $button, $variant, $flag = '' ) {
	$children = array();
	if ( $flag ) {
		$children[] = fx_heading( $flag, 'div', 'fx-plan-flag' );
	}
	$children[] = fx_heading( $name, 'h3', 'fx-plan-name' );
	$children[] = fx_text( '<p>' . $bestfor . '</p>', 'fx-plan-bestfor' );
	$children[] = fx_text( $price_html, 'fx-plan-price' );
	$children[] = fx_list( $features, 'fx-checklist' );
	$children[] = fx_button( $button, '#start', $variant, '', 'justify' );
	return fx_con(
		array( '_title' => $name . ' plan', 'css_classes' => 'fx-plan' . ( $flag ? ' is-pro' : '' ), 'flex_direction' => 'column', 'flex_gap' => fx_gap( 0 ) ),
		$children
	);
};

$step = static function ( $n, $title, $text ) {
	return fx_con(
		array( '_title' => 'Step ' . $n, 'css_classes' => 'fx-step', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 0 ) ),
		array(
			fx_heading( $n, 'div', 'fx-step-n' ),
			fx_heading( $title, 'h3', 'fx-step-title' ),
			fx_text( '<p>' . $text . '</p>', 'fx-step-text' ),
		)
	);
};

$opt = static function ( $kicker, $title, $desc, $big, $small, $featured = false ) {
	$children = array();
	if ( $featured ) {
		$children[] = fx_text( '<span class="fx-dot fx-dot--bright"></span> Best of both', 'fx-opt-pill' );
	}
	$children[] = fx_heading( $kicker, 'div', 'fx-opt-kicker' );
	$children[] = fx_heading( $title, 'h3', 'fx-opt-title', array(), $featured );
	$children[] = fx_text( '<p>' . $desc . '</p>', 'fx-opt-desc' );
	$children[] = fx_text( '<span class="big fx-num">' . $big . '</span><small>' . $small . '</small>', 'fx-opt-cost' );
	return fx_con(
		array( '_title' => $kicker, 'css_classes' => 'fx-opt' . ( $featured ? ' is-fortify' : '' ), 'flex_direction' => 'column', 'flex_gap' => fx_gap( 0 ) ),
		$children
	);
};

$callout = static function ( $icon, $title, $text, $green = false ) {
	return fx_w( 'icon-box', array(
		'selected_icon'    => fx_icon( $icon ),
		'title_text'       => $title,
		'description_text' => $text,
		'position'         => 'left',
		'title_size'       => 'div',
		'_css_classes'     => 'fx-callout' . ( $green ? ' is-green' : '' ),
	) );
};

$lic_item = static function ( $label, $value, $sub ) {
	return fx_text( '<small>' . $label . '</small><b>' . $value . '</b><span>' . $sub . '</span>', 'fx-lic-item' );
};

$faq = array(
	array( 'Do I pay anything today?', 'No. It’s $0 today. You pay one flat fee out of your sale proceeds at closing — and only if your home sells. Cancel before you accept an offer and you owe nothing; if your listing term ends without a sale, you owe nothing either.' ),
	array( 'Do I have to commit before seeing my preview?', 'No. Entering your address just starts a free, broker-reviewed preview. You choose a plan only after you’ve seen how your home is positioned — nothing goes live until you approve.' ),
	array( 'Do I still have to pay the buyer’s agent?', 'No — it’s optional. Since the 2024 industry rule changes, sellers decide whether to offer the buyer’s agent anything, and how much. Many sellers still choose to, to attract more showings, but it’s entirely your call. Whatever you decide, Fortify handles the paperwork correctly.' ),
	array( 'Is Fortify a licensed brokerage?', 'Yes. Fortify Listing Services LLC is a licensed Florida brokerage (FL #CQ1074119), with active licenses in Florida, Arizona and Tennessee. Real broker oversight on every listing — verifiable at <a href="' . esc_url( $lic_url ) . '" target="_blank" rel="noopener noreferrer">MyFloridaLicense.com</a>.' ),
	array( 'How is this different from FSBO or basic flat fee?', 'FSBO and many basic flat-fee MLS services can reduce listing costs, but sellers often handle more of the pricing, presentation, showing, offer and closing work themselves. Fortify combines professional listing exposure with broker review and optional support levels, so sellers can choose how much help they want.' ),
	array( 'What happens after I choose a plan?', 'You approve your listing, sign a simple listing agreement (about 60 seconds — it’s called a “limited-service agreement,” and it’s what lets us charge a flat fee instead of a commission), and your home goes live on the MLS, typically within 24 hours after approval. You can upgrade your support level anytime, free.' ),
);
$faq_tabs = array();
foreach ( $faq as $q ) {
	$faq_tabs[] = array( '_id' => fx_eid(), 'tab_title' => $q[0], 'tab_content' => '<p>' . $q[1] . '</p>' );
}

$stars = str_repeat( '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14l-5-4.87 7.1-1.01z"/></svg>', 5 );
$google = '<svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true"><path fill="#4285F4" d="M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v8.51h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.17z"/><path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z"/><path fill="#FBBC05" d="M11.69 28.18C11.25 26.86 11 25.45 11 24s.25-2.86.69-4.18v-5.7H4.34A21.99 21.99 0 0 0 2 24c0 3.55.85 6.91 2.34 9.88l7.35-5.7z"/><path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.35 5.7c1.73-5.2 6.58-9.07 12.31-9.07z"/></svg>';

$home = array(

	// 1. Hero.
	fx_section( 'home-hero', 'Hero', 'fx-hero', array(
		fx_grid( 'minmax(0,1fr) minmax(0,1.04fr)', 1, 48, 48, array(
			fx_con(
				array( '_title' => 'Hero copy', 'css_classes' => 'fx-hero-copy', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 0 ) ),
				array(
					fx_eyebrow( 'Licensed Florida brokerage · FL · AZ · TN', 'fx-hero-eyebrow' ),
					fx_heading( 'Sell with licensed support.<br><span class="fx-accent">Skip the full commission.</span>', 'h1', 'fx-hero-title' ),
					fx_text( '<p>Enter your address for a free broker-reviewed price opinion and listing preview — before you choose a plan.</p>', 'fx-hero-sub' ),
					fx_w( 'fortify-address-field', array(
						'_element_id'  => 'start',
						'placeholder'  => 'Enter your address or ZIP',
						'button_text'  => 'Get my free preview',
						'action'       => '/preview/',
						'variant'      => 'light',
						'_css_classes' => 'fx-hero-addr',
					) ),
					fx_text( '<p class="fx-payline">' . $check . ' $0 today · $0 if your home doesn\'t sell · $0 if you cancel — every plan due only at closing.</p>', 'fx-hero-payline' ),
					fx_text( '<p class="fx-trust"><span><span class="fx-dot"></span> Licensed FL broker</span><span><span class="fx-dot"></span> On the MLS ~24 hrs after approval</span><span><span class="fx-dot"></span> No signup to preview</span></p>', 'fx-hero-trust' ),
				)
			),
			fx_con(
				array( '_title' => 'Hero visual', 'css_classes' => 'fx-hero-visual', 'flex_direction' => 'column' ),
				array( fx_html( 'Sample listing preview card', $hero_card ) )
			),
		), array( '_title' => 'Hero grid', 'css_classes' => 'fx-hero-grid', 'flex_align_items' => 'center', 'grid_align_items' => 'center' ) ),
		fx_html( 'How it works rail', $rail, 'fx-hero-rail' ),
	), array(
		'padding'        => fx_pad( 64, 24, 24, 24 ),
		'padding_tablet' => fx_pad( 48, 24, 16, 24 ),
		'background_background'      => 'gradient',
		'background_color'           => '#F6F4FD',
		'background_color_b'         => '#ECEAFB',
		'background_gradient_angle'  => array( 'unit' => 'deg', 'size' => 180 ),
		'__globals__'                => array( 'background_color' => 'globals/colors?id=lav1', 'background_color_b' => 'globals/colors?id=lav2' ),
	) ),

	// 2. Listing sites strip.
	fx_section( 'home-sources', 'Listing sites strip', 'fx-sources', array(
		fx_text( '<p><span class="lab">Your listing appears on</span><span class="src">Zillow</span><span class="src">Realtor.com</span><span class="src">Redfin</span><span class="src">Homes.com</span><span class="more">+ 100 more sites</span></p>', 'fx-sources-row' ),
	), array( 'padding' => fx_pad( 24, 24, 24, 24 ), 'padding_tablet' => fx_pad( 24, 24, 24, 24 ) ) ),

	// 3. Savings calculator.
	fx_section( 'home-savings', 'Savings calculator', '', array(
		fx_w( 'fortify-savings-calculator', array(
			'eyebrow' => 'Your equity, protected',
			'heading' => 'See what you keep at your price',
			'min'     => 200000,
			'max'     => 2000000,
			'step'    => 10000,
			'start'   => 450000,
			'rate'    => 3,
			'fee'     => 2495,
		) ),
	) ),

	// 4. Listing preview showcase.
	fx_section( 'home-preview', 'Listing preview', 'fx-showcase', array(
		fx_con(
			array( '_title' => 'Section heading', 'css_classes' => 'fx-sec-head', 'flex_direction' => 'column' ),
			array(
				fx_eyebrow( 'Your listing preview' ),
				fx_heading( 'See your listing before you commit to anything', 'h2', 'fx-h2' ),
				fx_text( '<p>We prepare a broker-reviewed preview so you can see exactly how your home is positioned — then choose a Fortify plan only if you like what you see.</p>', 'fx-sec-lead' ),
			)
		),
		fx_grid( '1.5fr 1fr', 1, 32, 48, array(
			fx_con( array( '_title' => 'Preview card', 'flex_direction' => 'column' ), array( fx_html( 'Sample listing preview (detailed)', $showcase_card ) ) ),
			fx_con(
				array( '_title' => 'Callouts', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 16 ) ),
				array(
					$callout( 'fas fa-check', 'A licensed broker reviews it', 'Real broker oversight on every preview — pricing, positioning and MLS rules. A broker is the license level above an agent, and one signs off on every Fortify listing. Not a VA, not an algorithm.', true ),
					$callout( 'fas fa-align-left', 'Draft headline & description', 'See the exact copy buyers will read, written to position your home — yours to edit before anything goes live.' ),
					$callout( 'far fa-plus-square', 'A plan recommendation', 'The broker suggests the support level that fits your home and timeline — you decide. No plan is locked until you approve.' ),
					fx_button( 'Start my preview', '#start', 'primary', 'lg', 'justify' ),
				)
			),
		), array( '_title' => 'Preview grid', 'css_classes' => 'fx-showcase-grid', 'grid_align_items' => 'center' ) ),
	) ),

	// 5. How it works.
	fx_section( 'home-how', 'How it works', 'fx-sec-lav', array(
		fx_con(
			array( '_title' => 'Section heading', 'css_classes' => 'fx-sec-head', 'flex_direction' => 'column' ),
			array(
				fx_eyebrow( 'How it works' ),
				fx_heading( 'From address to active listing in about a day', 'h2', 'fx-h2' ),
			)
		),
		fx_grid( 3, 1, 32, 32, array(
			$step( '01', 'Enter your address', 'No signup, no commitment. We start preparing your free preview.' ),
			$step( '02', 'Answer a few quick questions', 'Asking price, timeline, a couple of details about the home.' ),
			$step( '03', 'Review your broker-prepared preview', 'See your listing, headline and pricing. Adjust anything you like.' ),
			$step( '04', 'Choose your support level', 'Pick the plan that fits — exposure only, or advisor guidance through closing.' ),
			$step( '05', 'List everywhere', 'On the MLS — the database every agent and every real estate site pulls from — typically within 24 hours after seller approval, and automatically on Zillow, Realtor.com, Redfin &amp; 100+ sites.' ),
			$step( '06', 'Pay at closing', 'One flat fee, due only when your home sells. Nothing before then.' ),
		), array( '_title' => 'Steps', 'css_classes' => 'fx-steps' ) ),
		fx_button( 'See how it works in detail →', '/how-it-works/', 'ghost', '', 'center', array( '_css_classes' => 'fx-button fx-button--ghost fx-more' ) ),
	), array(
		'background_background'     => 'gradient',
		'background_color'          => '#F6F4FD',
		'background_color_b'        => '#ECEAFB',
		'background_gradient_angle' => array( 'unit' => 'deg', 'size' => 180 ),
		'__globals__'               => array( 'background_color' => 'globals/colors?id=lav1', 'background_color_b' => 'globals/colors?id=lav2' ),
	) ),

	// 6. Middle-path comparison.
	fx_section( 'home-compare', 'Comparison', '', array(
		fx_con(
			array( '_title' => 'Section heading', 'css_classes' => 'fx-sec-head is-center', 'flex_direction' => 'column', 'flex_align_items' => 'center' ),
			array(
				fx_eyebrow( 'A smarter middle path' ),
				fx_heading( 'Not full commission. Not on your own.', 'h2', 'fx-h2' ),
				fx_text( '<p>Most sellers think the only choice is an expensive agent or going it alone. Fortify is the support model in between.</p>', 'fx-sec-lead' ),
			)
		),
		fx_grid( 3, 1, 24, 24, array(
			$opt( 'Traditional agent', 'Full service, full price', 'Hands-on help, but you typically pay a listing-side percentage commission whether or not you need the full package.', '~$13,500', 'est. 3% listing side on a $450k sale' ),
			$opt( 'Fortify', 'Full support, flat fee', 'Licensed broker review, full MLS exposure and as much guidance as you want — for one flat fee paid at closing. Every home sale already runs through a licensed broker — with Fortify you work with the brokerage directly, instead of paying an agent’s commission on top.', '$995–$3,995', 'flat, at closing, only if you sell', true ),
			$opt( 'Basic flat fee / FSBO', 'Lower cost, less support', 'Many basic flat-fee services focus mainly on MLS entry, leaving more of the pricing, showing, offer and closing work to the seller.', '~$400', 'upfront, no broker support after' ),
		), array( '_title' => 'Options', 'css_classes' => 'fx-three', 'grid_align_items' => 'center' ) ),
	) ),

	// 7. Plans.
	fx_section( 'home-plans', 'Plans', 'fx-sec-lav', array(
		fx_con(
			array( '_title' => 'Section heading', 'css_classes' => 'fx-sec-head is-center', 'flex_direction' => 'column', 'flex_align_items' => 'center' ),
			array(
				fx_eyebrow( 'Plans & pricing' ),
				fx_heading( 'Start with the support your sale needs', 'h2', 'fx-h2' ),
				fx_text( '<p>Every plan is $0 today, with one flat fee due at closing — only if your home sells.</p>', 'fx-sec-lead' ),
			)
		),
		fx_grid( 3, 1, 24, 24, array(
			$plan( 'Essentials', 'For confident sellers who mainly need professional listing exposure.',
				'<span class="today">$0</span><span class="at">today<br><b class="fx-num">$995</b> at closing</span>',
				array( 'Licensed broker listing review', 'What your home is worth — in writing, signed by the broker', 'Your home on Zillow, Realtor.com, Redfin + 100 more sites', 'On the MLS ~24 hrs after approval, up to 25 photos', 'Email & portal support' ),
				'Choose Essentials', 'ghost' ),
			$plan( 'Pro', 'For sellers who want a seller-led listing that runs like an agent-led one.',
				'<span class="today">$0</span><span class="at">today<br><b class="fx-num">$2,495</b> at closing</span>',
				array( 'Everything in Essentials', 'Electronic lockbox + automated showings', 'Unlimited edits, expanded photos', 'Error & rule check before you go live', 'Priority support' ),
				'Choose Pro', 'indigo', 'Most sellers choose Pro' ),
			$plan( 'Elite', 'For sellers who want expert guidance through offers, contracts and closing.',
				'<span class="today">$0</span><span class="at">today<br>from <b class="fx-num">$3,995</b> at closing</span>',
				array( 'Everything in Pro', 'Pricing strategy, before and during your listing', 'Professional photography & floor plan', 'Offer review & negotiation guidance', 'Contract guidance & closing guidance' ),
				'Choose Elite', 'ghost' ),
		), array( '_title' => 'Plan cards', 'css_classes' => 'fx-plans', 'grid_align_items' => 'start' ) ),
		fx_html( 'Feature comparison table', $matrix, 'fx-matrix-block' ),
		fx_text( '<p>All plans provide full MLS exposure · $0 today · Cancel before you accept an offer and owe nothing</p>', 'fx-allmls' ),
		fx_button( 'Compare all plans & pricing →', '/plans/', 'ghost', '', 'center', array( '_css_classes' => 'fx-button fx-button--ghost fx-more' ) ),
	), array(
		'background_background'     => 'gradient',
		'background_color'          => '#F6F4FD',
		'background_color_b'        => '#ECEAFB',
		'background_gradient_angle' => array( 'unit' => 'deg', 'size' => 180 ),
		'__globals__'               => array( 'background_color' => 'globals/colors?id=lav1', 'background_color_b' => 'globals/colors?id=lav2' ),
	) ),

	// 8. Licensing + FAQ.
	fx_section( 'home-trust', 'Licensing and FAQ', '', array(
		fx_grid( '0.9fr 1.1fr', 1, 32, 48, array(
			fx_con(
				array( '_title' => 'Licensing block (registry-driven in phase 4)', 'css_classes' => 'fx-lic', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 0 ) ),
				array(
					fx_eyebrow( 'Licensed & compliant' ),
					fx_heading( 'Reviewed and submitted by a licensed Florida broker.', 'h3', 'fx-lic-title', array(), true ),
					fx_text( '<p>Every Fortify listing is prepared under active broker supervision and complies with MLS rules and Florida real estate law — not a VA, not an algorithm, not a form.</p>', 'fx-lic-text' ),
					fx_grid( 2, 2, 16, 16, array(
						$lic_item( 'Brokerage license', 'FL #CQ1074119', 'Verifiable at <a href="' . esc_url( $lic_url ) . '" target="_blank" rel="noopener noreferrer">MyFloridaLicense.com</a>' ),
						$lic_item( 'Licensed states', 'FL · AZ · TN', 'Serving Florida MLS markets today' ),
						$lic_item( 'Every listing', 'Broker-reviewed', 'Before every submission' ),
						$lic_item( 'Listing agreement', 'Read it first', 'Sample available before you commit' ),
					), array( '_title' => 'License facts', 'css_classes' => 'fx-lic-grid', 'grid_columns_grid_mobile' => array( 'unit' => 'fr', 'size' => 1 ) ) ),
				)
			),
			fx_con(
				array( '_title' => 'FAQ', 'css_classes' => 'fx-faq', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 0 ) ),
				array(
					fx_eyebrow( 'Questions sellers ask' ),
					fx_heading( 'Before you decide', 'h2', 'fx-faq-title' ),
					fx_w( 'accordion', array(
						'tabs'                 => $faq_tabs,
						'selected_icon'        => fx_icon( 'fas fa-plus' ),
						'selected_active_icon' => fx_icon( 'fas fa-plus' ),
						'icon_align'           => 'right',
						'title_html_tag'       => 'h3',
						'title_color'          => '#1E1B4B',
						'tab_active_color'     => '#1E1B4B',
						'icon_color'           => '#4F46E5',
						'icon_active_color'    => '#4F46E5',
						'__globals__'          => array(
							'title_color'       => 'globals/colors?id=secondary',
							'tab_active_color'  => 'globals/colors?id=secondary',
							'icon_color'        => 'globals/colors?id=primary',
							'icon_active_color' => 'globals/colors?id=primary',
						),
						'_css_classes'         => 'fx-accordion',
					) ),
				)
			),
		), array( '_title' => 'Trust grid', 'css_classes' => 'fx-trust-grid', 'grid_align_items' => 'start' ) ),
		fx_button( 'Read all FAQs →', '/faqs/', 'ghost', '', 'center', array( '_css_classes' => 'fx-button fx-button--ghost fx-more' ) ),
	) ),

	// 9. Google review.
	fx_section( 'home-review', 'Google review', 'fx-sec-lav fx-review', array(
		fx_html( 'Review label and stars', '<div class="fx-gr-eyebrow">' . $google . '<span class="fx-gr-label">Verified Google review</span><span class="fx-stars">' . $stars . '</span></div>' ),
		fx_text( '<blockquote>“Fortify was simple and straightforward to work with. We started with one plan and later added more support, which ended up being a good fit for us. We liked the flexibility, the communication in the portal, and being able to save on commission while still having help when we needed it. We’d recommend them.”</blockquote>', 'fx-gr-quote' ),
		fx_text( '<p><b>Kevin Knox</b> · Bradenton, FL</p>', 'fx-gr-by' ),
		fx_html( 'View on Google link', '<a class="fx-gr-link" href="https://www.google.com/maps/contrib/114975901484233350025/reviews?hl=en" target="_blank" rel="noopener noreferrer"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg> View on Google</a>' ),
	), array(
		'flex_align_items'          => 'center',
		'background_background'     => 'gradient',
		'background_color'          => '#F6F4FD',
		'background_color_b'        => '#ECEAFB',
		'background_gradient_angle' => array( 'unit' => 'deg', 'size' => 180 ),
		'__globals__'               => array( 'background_color' => 'globals/colors?id=lav1', 'background_color_b' => 'globals/colors?id=lav2' ),
	) ),

	// 10. Final call to action.
	fx_section( 'home-cta', 'Final call to action', 'fx-final', array(
		fx_heading( 'Start with your address.<br>Decide after you see the path.', 'h2', 'fx-final-title', array( 'align' => 'center' ), true ),
		fx_text( '<p>Free broker price opinion + broker-reviewed listing preview, delivered in writing, ready in a few hours — prepared by a licensed Florida broker, before you choose your Fortify plan.</p>', 'fx-final-lead' ),
		fx_w( 'fortify-address-field', array(
			'_element_id' => 'final-start',
			'placeholder' => 'Enter your address or ZIP',
			'button_text' => 'Start my listing preview',
			'action'      => '/preview/',
			'variant'     => 'dark',
			'align'       => 'center',
		) ),
		fx_text( '<p class="fx-trust"><span><span class="fx-dot fx-dot--bright"></span> Licensed FL broker</span><span><span class="fx-dot fx-dot--bright"></span> $0 today · $0 if it doesn\'t sell</span><span><span class="fx-dot fx-dot--bright"></span> $0 if you cancel</span></p>', 'fx-final-trust' ),
		fx_text( '<p>An opinion of value, not an appraisal · Derived from public-record and MLS data · Not intended to solicit property currently listed with another broker.</p>', 'fx-final-fine' ),
	), array(
		'flex_align_items'          => 'center',
		'background_background'     => 'gradient',
		'background_color'          => '#272350',
		'background_color_b'        => '#15132F',
		'background_gradient_angle' => array( 'unit' => 'deg', 'size' => 160 ),
		'__globals__'               => array( 'background_color' => 'globals/colors?id=night', 'background_color_b' => 'globals/colors?id=ink2' ),
	) ),
);

/* ====================================================================
 * Save
 * ================================================================== */

$home_id = fx_owned_post( 'home', array(
	'post_type'     => 'page',
	'post_status'   => 'publish',
	'post_title'    => 'Home',
	'post_name'     => 'home',
	'page_template' => 'elementor_header_footer',
) );
fx_save_elementor( $home_id, $home, 'wp-page', array( 'hide_title' => 'yes' ) );
update_post_meta( $home_id, '_wp_page_template', 'elementor_header_footer' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );

$templates = array(
	'header' => array( 'Fortify header', $header ),
	'footer' => array( 'Fortify footer', $footer ),
);
foreach ( $templates as $type => $t ) {
	$tid = fx_owned_post( $type, array(
		'post_type'   => 'elementor_library',
		'post_status' => 'publish',
		'post_title'  => $t[0],
	) );
	wp_set_object_terms( $tid, $type, 'elementor_library_type' );
	fx_save_elementor( $tid, $t[1], $type );
	update_post_meta( $tid, '_elementor_conditions', array( 'include/general' ) );
	WP_CLI::line( "$type template: $tid" );
}

// Theme Builder conditions cache (helper from Sitelets 1.1.2+; it reads meta,
// so it also works where Elementor's own regenerate() would not).
if ( class_exists( '\SiteletsWP\Sitelets_Sync' ) ) {
	\SiteletsWP\Sitelets_Sync::rebuild_conditions_cache();
} else {
	\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_cache()->regenerate();
}

\Elementor\Plugin::$instance->files_manager->clear_cache();

WP_CLI::success( sprintf( 'Home page %d is the front page: %s', $home_id, home_url( '/' ) ) );
