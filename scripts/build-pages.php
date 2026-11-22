<?php
/**
 * Phase 3: corporate inner pages and the /preview/ demo stub, on site 1.
 *
 *   wp eval-file scripts/build-pages.php
 *
 * Run after build-home.php (the header, footer and kit come from there and
 * from apply-kit.php). Re-runnable: finds its own pages by the
 * `_fortify_build_key` meta and replaces their Elementor data.
 *
 * Source: tfbceo/Fortify-Listings-FE-Website (2026-07-28), pages how-it-works,
 * plans, faqs, about, contact and the five legal pages. Every top-level
 * container has a CSS ID prefixed with the page (hiw-, plans-, faqs-, about-,
 * contact-, legal-, sla-, preview-). Calls to action that pointed at the
 * Fortify app go to the homepage address field (/#start) instead; the demo
 * has no app.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_main_site() ) {
	WP_CLI::error( 'Run on the main site (site 1).' );
}

require_once __DIR__ . '/lib/fx-elements.php';

/* ====================================================================
 * Shared values and page parts
 * ================================================================== */

$start   = '/#start';
$help    = 'https://help.fortifylistings.com';
$tel     = 'tel:4077774657';
$phone   = '(407) 777-4-MLS';
$lic_url = 'https://www.myfloridalicense.com/portalsearches/VerifyLicensee/LicenseDetail?ID=2F584AC97289A0AF647A608799927DA4';
$sla_pdf = ''; // URL of the sample listing agreement PDF. Missing from the source repo; set it once the file is in the media library.

$lav_bg = array(
	'background_background'     => 'gradient',
	'background_color'          => '#F6F4FD',
	'background_color_b'        => '#ECEAFB',
	'background_gradient_angle' => array( 'unit' => 'deg', 'size' => 180 ),
	'__globals__'               => array( 'background_color' => 'globals/colors?id=lav1', 'background_color_b' => 'globals/colors?id=lav2' ),
);
$one_col = array( 'unit' => 'fr', 'size' => 1 );

/** Top-level section with explicit top/bottom padding (scale values), capped at 64 on Tablet. */
$sec = static function ( $id, $title, $classes, array $children, $top, $bottom, array $extra = array() ) {
	return fx_section( $id, $title, $classes, $children, array_merge( array(
		'padding'        => fx_pad( $top, 24, $bottom, 24 ),
		'padding_tablet' => fx_pad( min( $top, 64 ), 24, min( $bottom, 64 ), 24 ),
	), $extra ) );
};

$hero = static function ( $id, $eyebrow, $title, $sub, array $after = array() ) use ( $sec, $lav_bg ) {
	return $sec( $id, 'Page hero', 'fx-page-hero', array_merge( array(
		fx_eyebrow( $eyebrow ),
		fx_heading( $title, 'h1', 'fx-page-title' ),
		fx_text( '<p>' . $sub . '</p>', 'fx-page-sub' ),
	), $after ), 64, 48, array_merge( $lav_bg, array( 'padding_mobile_extra' => fx_pad( 48, 24, 32, 24 ) ) ) );
};

$meta = static function ( array $items ) {
	return fx_list( $items, 'fx-hero-meta', 'fas fa-check', 'fa-solid', array( 'view' => 'inline' ) );
};

$intro = static function ( $eyebrow, $title, $lead = '', $classes = 'fx-intro', $hclass = 'fx-h2m' ) {
	$children = array( fx_eyebrow( $eyebrow ), fx_heading( $title, 'h2', $hclass ) );
	if ( $lead ) {
		$children[] = fx_text( '<p>' . $lead . '</p>', 'fx-lead' );
	}
	return fx_box( 'Section intro', $classes, $children );
};

$btn_row = static function ( array $buttons, $justify = 'flex-start', $classes = 'fx-btn-row' ) {
	return fx_con( array(
		'_title'               => 'Buttons',
		'css_classes'          => $classes,
		'flex_direction'       => 'row',
		'flex_wrap'            => 'wrap',
		'flex_gap'             => fx_gap( 14 ),
		'flex_justify_content' => $justify,
		'flex_align_items'     => 'center',
	), $buttons );
};

$cta = static function ( $id, $title, $text, array $buttons, $fine_html ) use ( $sec, $btn_row ) {
	return $sec( $id, 'Call to action', '', array(
		fx_box( 'CTA banner', 'fx-cta-banner', array(
			fx_heading( $title, 'h2', 'fx-cta-title', array( 'align' => 'center' ), true ),
			fx_text( '<p>' . $text . '</p>', 'fx-cta-text' ),
			$btn_row( $buttons, 'center' ),
			fx_text( $fine_html, 'fx-cta-fine' ),
		), array( 'flex_align_items' => 'center' ) ),
	), 64, 64 );
};

$equity = static function ( $second_text, $second_url ) use ( $start ) {
	return array(
		fx_button( 'Get my free listing preview', $start, 'equity' ),
		fx_button( $second_text, $second_url, 'ghost-dark' ),
	);
};

$grid = static function ( $title, $classes, $cols, $cols_tablet, $cols_mobile_extra, $gap, array $children, array $extra = array() ) use ( $one_col ) {
	$m = is_int( $cols_mobile_extra ) ? array( 'unit' => 'fr', 'size' => $cols_mobile_extra ) : array( 'unit' => 'custom', 'size' => $cols_mobile_extra );
	return fx_grid( $cols, $cols_tablet, $gap, $gap, $children, array_merge( array(
		'_title'                        => $title,
		'css_classes'                   => $classes,
		'grid_columns_grid_mobile_extra' => $m,
		'grid_columns_grid_mobile'      => $one_col,
	), $extra ) );
};

/** Card with an uppercase label (h4) and a check list. $variant: us (emerald), you (indigo), plain. */
$tl_card = static function ( $label, array $items, $variant = 'you', $list_class = 'fx-role-list', $small = false ) {
	$green = 'us' === $variant;
	return fx_box( 'Card: ' . wp_strip_all_tags( $label ), 'fx-tl-card is-' . $variant, array(
		fx_heading_in( $label, 'h4', 'fx-card-label' . ( $small ? ' is-small' : '' ), $green ? 'accent' : 'primary', $green ? '#0E9F6E' : '#4F46E5' ),
		fx_list( $items, $list_class ),
	) );
};

$faq_items = static function ( array $rows ) {
	$out = array();
	foreach ( $rows as $r ) {
		$out[] = array( $r[0], str_replace( 'class="inline-callout"', 'class="fx-inline-callout"', $r[1] ) );
	}
	return $out;
};

/* ====================================================================
 * How it works
 * ================================================================== */

$tl_step = static function ( $n, $label, $title, $text_html, array $extras = array(), $complete = false ) {
	return fx_con( array(
		'_title'               => 'Step ' . $n,
		'css_classes'          => 'fx-tl-step' . ( $complete ? ' is-complete' : '' ),
		'flex_direction'       => 'row',
		'flex_wrap'            => 'nowrap',
		'flex_align_items'     => 'flex-start',
		'flex_gap'             => fx_gap( 32 ),
		'flex_gap_mobile_extra' => fx_gap( 18 ),
	), array(
		fx_heading( (string) $n, 'div', 'fx-tl-dot' ),
		fx_box( 'Step body', 'fx-tl-body', array_merge( array(
			fx_heading( $label, 'p', 'fx-step-label' ),
			fx_heading( $title, 'h3', 'fx-tl-title' ),
			fx_text( $text_html, 'fx-tl-text' ),
		), $extras ) ),
	) );
};
$phase = static function ( $label ) {
	return fx_heading( $label, 'div', 'fx-phase' );
};
$badge = static function ( $label ) {
	return fx_heading( $label, 'div', 'fx-tl-badge' );
};
$two = static function ( $a, $b ) use ( $grid ) {
	return $grid( 'Two cards', 'fx-tl-two', 2, 2, 1, 14, array( $a, $b ) );
};
$elite = ' <span class="fx-elite-tag">(Elite)</span>';

$hiw = array(
	$hero( 'hiw-hero', 'The Fortify Process', 'From your address to<br><span class="fx-accent">active MLS listing</span> — step by step',
		"No long forms before you're ready to commit. We prepare your listing preview first — you review it, choose your plan, and only then does the process move forward. A licensed broker is behind every step.",
		array( $meta( array( 'Licensed broker oversight at every step', 'On the MLS ~24 hours after approval', '$0 today — flat fee due only at closing' ) ) )
	),

	$sec( 'hiw-journey', 'The full journey', '', array(
		$intro( 'The Full Journey', 'What actually happens, in order', "Most flat fee services ask you to fill out a long form before you've seen what you're getting. Fortify works differently — we build your listing preview first, so you can evaluate the product before you commit to anything." ),
		fx_box( 'Timeline', 'fx-timeline', array(
			$phase( 'Before you commit' ),
			$tl_step( 1, 'Step 1', 'Enter your address — nothing else yet',
				"<p>You don't fill out a long form or create an account to get started. Just enter your property address. That's all we need to begin preparing your listing preview.</p><p>No commitment. No credit card. No plan selection — not yet.</p>",
				array( $badge( 'About 30 seconds' ) ) ),
			$tl_step( 2, 'Step 2', 'We prepare your listing preview',
				'<p>Our team pulls public property data for your address and builds a draft MLS listing — formatted exactly as it would appear to buyer agents and on Zillow, Realtor.com, and Redfin. A licensed broker reviews it before you see it.</p>',
				array( $two(
					$tl_card( 'What Fortify does', array( 'Pulls property data from public records', 'Drafts MLS-formatted listing copy', 'Broker reviews for compliance and accuracy', 'Delivers preview to your inbox' ), 'us' ),
					$tl_card( 'What you do', array( 'Wait for your preview — typically same day', 'No forms to fill, no decisions to make yet' ), 'you' )
				) ) ),
			$tl_step( 3, 'Step 3', 'Review your listing preview',
				"<p>You receive a complete draft listing — the same format buyer agents and buyers will see on Zillow, Realtor.com, and Redfin. Review it before you commit to anything. If it looks right, you choose a plan and move forward. If something needs adjusting, you'll have the opportunity to clarify once you're in the platform.</p><p>This is the moment Fortify earns your trust — before asking for anything in return.</p>",
				array( $badge( 'No obligation to proceed' ) ) ),
			$phase( 'Getting started' ),
			$tl_step( 4, 'Step 4', 'Choose your plan and sign your listing agreement',
				"<p>Once you've seen your preview and are ready to move forward, you select the plan that fits your sale — Essentials, Pro, or Elite. You then review and sign your listing agreement digitally. It's the same agreement available on our website before you ever enter your address.</p>",
				array(
					$tl_card( 'Plan overview', array(
						'<strong>Essentials — $995 at closing.</strong> MLS listing, syndication, 25 photos, and a written broker price opinion. For confident sellers who manage their own showings and negotiations.',
						'<strong>Pro — $2,495 at closing.</strong> Everything in Essentials plus electronic lockbox, ShowingTime scheduling, unlimited edits, and priority support. Most sellers choose this.',
						'<strong>Elite — from $3,995 at closing.</strong> Everything in Pro plus pricing strategy &amp; active price management, professional photography, offer strategy, negotiation guidance, and closing guidance.',
					), 'plain', 'fx-role-list', true ),
					$badge( 'Upgrade anytime — no penalties' ),
				) ),
			$tl_step( 5, 'Step 5', 'Answer a few clarifying questions',
				"<p>Now that you're in the platform, we ask for the details that make your listing accurate and complete — things the public record doesn't tell us. Pricing, buyer-agent compensation, property features, disclosures, and your photos.</p><p>This is a short, guided process — not a long form. Most sellers complete it in under fifteen minutes.</p>",
				array( $two(
					$tl_card( 'You provide', array( 'Asking price', 'Buyer-agent compensation amount', 'Property photos (up to plan limit)', 'Required seller disclosures', 'Showing preferences and access instructions' ), 'you' ),
					$tl_card( 'We handle', array( 'MLS field formatting and compliance', 'Required disclosure structuring', 'Guiding you through each input' ), 'us' )
				) ) ),
			$phase( 'Going live' ),
			$tl_step( 6, 'Step 6', 'Broker review and your final approval',
				"<p>Before anything goes to the MLS, a licensed broker reviews your completed listing for accuracy, MLS compliance, and required documentation. If anything needs attention, we'll reach out — most listings clear this step without changes.</p><p>Once the broker approves it, you see the final version and give your sign-off. Nothing goes live until you've approved it.</p>",
				array( $two(
					$tl_card( 'Broker checks', array( 'MLS rule compliance', 'Required disclosure completeness', 'Listing field accuracy', 'Photo quality and count' ), 'us' ),
					$tl_card( 'You confirm', array( 'Review the final listing version', 'Give approval to go live', 'Nothing posts without your sign-off' ), 'you' )
				) ) ),
			$tl_step( 7, "Step 7 — You're live", 'Your home appears everywhere buyers search',
				'<p>Within approximately 24 hours of your approval, your listing is active in the MLS and automatically on Zillow, Realtor.com, Redfin, Homes.com, and 100+ additional sites. Buyer agents can see it, request showings, and submit offers — the same as any traditionally listed home.</p>',
				array( $tl_card( 'Where your listing appears', array( 'Florida MLS (your local board)', 'Zillow', 'Realtor.com', 'Redfin', 'Homes.com', '100+ additional sites' ), 'us', 'fx-role-list is-2col' ) ),
				true ),
			$phase( 'Managing your active listing' ),
			$tl_step( 8, 'Step 8 — Active sale', 'Showings, offers, and closing',
				'<p>Your level of support from here depends on the plan you chose. Pro and Elite sellers have showing scheduling handled through ShowingTime, direct broker access for offer review and negotiation, and closing guidance. Essentials sellers manage these steps directly — with the platform always available for reference.</p><p>At closing, your Fortify flat fee is settled as part of the transaction — exactly like a buyer-agent commission. No surprise charges, no upfront costs at any point.</p>',
				array( $two(
					$tl_card( 'Pro &amp; Elite sellers get', array( 'ShowingTime automated scheduling', 'Electronic lockbox access', 'Priority support line', 'Offer review &amp; negotiation guidance' . $elite, 'Closing guidance' . $elite ), 'us' ),
					$tl_card( 'All sellers keep', array( 'Full control over pricing decisions', 'Right to accept, reject, or counter', 'Option to upgrade plan anytime', 'Cancel before you accept an offer — $0' ), 'you' )
				) ),
				true ),
		) ),
	), 88, 88 ),

	$sec( 'hiw-roles', 'Roles and responsibilities', '', array(
		$intro( 'Roles &amp; Responsibilities', 'What you handle. What we handle.', "Fortify is not a hands-off filing service, and it's not a full-service agent. Here's exactly where the lines are drawn — and they stay consistent regardless of which plan you choose.", 'fx-intro fx-roles-intro' ),
		$grid( 'Role cards', 'fx-roles', 2, 1, 1, 22, array(
			fx_box( 'You', 'fx-role-card is-you', array(
				fx_heading( 'You stay in control of', 'h3' ),
				fx_list( array( 'Setting and adjusting your asking price', 'Deciding whether to offer buyer-agent compensation and how much', 'Approving or declining showing requests (Essentials)', 'Reviewing, accepting, rejecting, or countering offers', 'Deciding which offer to accept', 'Managing inspection requests and repair negotiations', 'Communicating directly with buyers and buyer agents' ), 'fx-role-list' ),
			) ),
			fx_box( 'Fortify', 'fx-role-card is-us', array(
				fx_heading( 'We take care of', 'h3', '', array(), true ),
				fx_list( array( 'Preparing your listing preview before you commit', 'Licensed broker review at every submission stage', 'MLS entry and compliance verification', 'Syndication to Zillow, Realtor.com, Redfin, and 100+ sites', 'Electronic lockbox setup (Pro &amp; Elite)', 'ShowingTime scheduling configuration (Pro &amp; Elite)', 'Offer review and negotiation guidance (Elite)', 'Closing guidance (Elite)', 'Support available throughout — never left on your own' ), 'fx-role-list' ),
			) ),
		) ),
	), 0, 88 ),

	$sec( 'hiw-faq', 'Process questions', '', array(
		$intro( 'Common Questions', 'Questions about the process', '', 'fx-intro', 'fx-h2s' ),
		fx_box( 'FAQ list', 'fx-faq-wrap', array( fx_faq( $faq_items( array(
			array( 'How long does it take to get my listing live?', "<p>Most listings go live within 24 hours of your final approval. The timeline has two parts: we prepare your preview, typically same day. Once you've signed your agreement and submitted your details, broker review usually completes within a few hours during business hours. From your approval to MLS activation is typically same-day.</p>" ),
			array( 'What if my preview listing has wrong information?', "<p>The preview is built from public property records, which aren't always perfectly up to date. You'll have the opportunity to correct anything once you're in the platform — square footage, bedroom and bathroom count, property features, and anything else that needs adjusting. Nothing goes to the MLS until you've approved the final version.</p>" ),
			array( "Can I change my plan after I've started?", "<p>Yes — you can upgrade from Essentials to Pro or Elite, or from Pro to Elite, at any point during your listing period. There are no upgrade fees and no penalties. Many sellers start with Essentials and upgrade to Pro once showings begin and they want the scheduling automation. If you receive an offer and realize you'd like broker guidance reviewing it, you can upgrade to Elite at that point.</p>" ),
			array( 'Will buyer agents show my Fortify listing?', "<p>Yes. Your listing is entered into the same MLS every buyer agent in your market searches. It appears identical to any other active listing — agents can't tell from the MLS that it was listed through Fortify rather than a traditional agent. Whether you offer buyer-agent compensation and how much is your decision; many sellers do (typically 2–3%) because it expands the buyer pool, but it's not required. Under current MLS rules, compensation offers can no longer be published on the MLS itself; we communicate your terms through permitted channels and use the MLS seller-concessions field where available. Whatever you decide, it's negotiable at offer time — nothing is locked until you sign a contract.</p>" ),
			array( "What happens if my home doesn't sell?", "<p>You pay nothing. Fortify's flat fee is due only upon a successful closing. If you cancel before accepting an offer, you owe nothing. If the listing expires or you withdraw before any offer is accepted, no flat fee is owed.</p>" ),
			array( 'Is Fortify only available in Florida?', '<p>Fortify currently serves select Florida MLS markets, including Alachua, Broward, Charlotte, DeSoto, Flagler, Hillsborough, Lake, Marion, Martin, Miami-Dade, Okeechobee, Orange, Osceola, Palm Beach, Pasco, Polk, St. Lucie, Sarasota, Sumter, and Volusia counties. We also hold active broker licenses in Arizona and Tennessee and are expanding to serve those markets. Enter your address to confirm your county is currently covered.</p>' ),
		) ) ) ) ),
		fx_text( '<p>More questions? <a href="/faqs/">See the full FAQ →</a> or visit our <a href="' . esc_url( $help ) . '">Help Center</a>.</p>', 'fx-more-line' ),
	), 64, 64, $lav_bg ),

	$sec( 'hiw-nudge', 'Plan nudge', '', array(
		$grid( 'Nudge', 'fx-nudge', 'minmax(0,1fr) auto', 'minmax(0,1fr) auto', 1, 32, array(
			fx_box( 'Nudge copy', '', array(
				fx_eyebrow( 'Ready to see your listing?' ),
				fx_heading( 'Start with your free preview — no commitment required', 'h2', 'fx-h2s', array( '_margin' => fx_pad( 12, 0, 0, 0 ) ) ),
				fx_text( "<p>Enter your address and we'll prepare a broker-reviewed listing draft. See exactly what your home would look like on Zillow and Realtor.com before choosing a plan or signing anything.</p>", 'fx-lead' ),
			) ),
			fx_con( array( '_title' => 'Nudge buttons', 'css_classes' => 'fx-nudge-ctas', 'flex_direction' => 'column', 'flex_gap' => fx_gap( 12 ), 'flex_align_items' => 'flex-end', 'flex_align_items_mobile_extra' => 'flex-start' ), array(
				fx_button( 'See my free listing preview', $start, 'primary' ),
				fx_button( 'Compare plans &amp; pricing →', '/plans/', 'ghost' ),
			) ),
		), array( 'grid_align_items' => 'center' ) ),
	), 64, 0 ),

	$cta( 'hiw-cta', "Your equity is worth protecting.<br>See what you'd keep.",
		'A traditional 3% listing commission on a $450,000 home is $13,500. Fortify Pro is $2,495 at closing — only if you sell.',
		$equity( 'Call ' . $phone, $tel ),
		'<p>$0 today · No signup to preview · Cancel before you accept an offer and owe nothing · Flat fee due only at closing</p>' ),
);

/* ====================================================================
 * Plans
 * ================================================================== */

$x = 'far fa-circle';

$price_block = static function ( $variant, $when ) {
	return fx_text( '<p class="today">Today</p><p class="amount fx-num">$0</p><p class="when">' . $when . '</p><p class="only">Only if your home sells</p>', 'fx-price-block' . ( $variant ? ' is-' . $variant : '' ) );
};
$plan_head = static function ( $badge_text, $badge_variant, $title, $price ) use ( $grid ) {
	return $grid( 'Plan header', 'fx-plan-head', 'minmax(0,1fr) auto', 'minmax(0,1fr) auto', 1, 24, array(
		fx_box( 'Plan name', '', array(
			fx_heading( $badge_text, 'div', 'fx-plan-badge is-' . $badge_variant ),
			fx_heading( $title, 'h2', 'fx-plan-title' ),
		) ),
		$price,
	), array( 'grid_align_items' => 'start' ) );
};
$detail = static function ( $label, $label_color, array $items, $classes, $note = '' ) {
	$hex      = array( 'accent' => '#0E9F6E', 'muted' => '#8B8AA3' );
	$children = array(
		fx_heading_in( $label, 'h4', 'fx-card-label', $label_color, $hex[ $label_color ] ),
		fx_list( $items, 'fx-role-list' ),
	);
	if ( $note ) {
		$children[] = fx_text( '<p>' . $note . '</p>', 'fx-muted-note' );
	}
	return fx_box( 'Card: ' . $label, 'fx-detail ' . $classes, $children );
};
$up = static function ( array $texts ) use ( $x ) {
	return array_map( static function ( $t ) use ( $x ) {
		return array( 'text' => $t, 'icon' => $x, 'library' => 'fa-regular' );
	}, $texts );
};
$bestfor = static function ( $text ) {
	return fx_text( '<p><strong>Best for:</strong> ' . $text . '</p>', 'fx-bestfor' );
};
$plan_sec = static function ( $id, $title, array $children, array $extra = array() ) use ( $sec ) {
	return $sec( $id, $title, 'fx-plan-sec', $children, 64, 64, $extra );
};

// Comparison table rows: feature, note, essentials, pro, elite. true = check, false = dash; 'p:' prefix = partial.
$ct_sections = array(
	'Foundation'         => array(
		array( 'Licensed broker listing review', 'Reviewed before every MLS submission', true, true, true ),
		array( 'MLS entry + full syndication', 'Zillow, Realtor.com, Redfin, Homes.com + 100+ sites', true, true, true ),
		array( 'Free listing preview before commitment', '', true, true, true ),
		array( 'Buyer’s agent fee — optional, and handled for you', 'Paying the buyer’s agent is now optional. You decide if and how much — we handle the paperwork either way.', true, true, true ),
		array( 'Seller dashboard', '', true, true, true ),
	),
	'Listing Tools'      => array(
		array( 'Photos', '', '25 photos', 'Expanded', 'Expanded' ),
		array( 'Professional photography &amp; floor plan', 'Professional shoot + floor plan, scheduled at launch (aerial where community rules permit)', 'p:$495 add-on', 'p:$495 add-on', true ),
		array( 'Listing edits after going live', 'Price changes always free on every plan', 'p:Up to 3', 'Unlimited', 'Unlimited' ),
		array( 'Electronic lockbox', 'Supra eKey-compatible, agent-accessible', false, true, true ),
		array( 'Automated showing scheduling', 'ShowingTime or equivalent', false, true, true ),
	),
	'Pricing &amp; strategy' => array(
		array( 'What your home is worth — in writing, signed by the broker', 'A formal Broker Price Opinion with nearby comparable sales — from a licensed broker, not an algorithm.', true, true, true ),
		array( 'Pricing strategy session before you list', 'Where to price, the lowest offer you’d take, and when you’d adjust — decided with your broker before day one.', false, false, true ),
		array( 'Ongoing pricing guidance while you’re on the market', 'If showings slow down, we tell you why and what to change — before your listing goes stale.', false, false, true ),
	),
	'Support'            => array(
		array( 'Support channel', '', 'Email &amp; portal', 'Priority email &amp; portal', 'Priority + broker line' ),
		array( 'Offer review &amp; response strategy', '', false, false, true ),
		array( 'Contract &amp; addenda guidance', '', false, false, true ),
		array( 'Negotiation support', '', false, false, true ),
		array( 'Inspection &amp; repair guidance', '', false, false, true ),
		array( 'Closing guidance', 'We walk you through what happens, when, and what to sign', false, false, true ),
	),
	'Terms'              => array(
		array( 'Due today', '', '$0', '$0', '$0' ),
		array( 'Due at closing', '', '$995', '$2,495', 'From $3,995' ),
		array( 'Cancel before you accept an offer', 'If your listing term ends without a sale, you owe nothing either', '$0', '$0', '$0' ),
		array( 'Upgrade anytime', '', true, true, '—' ),
	),
);
$ct_cell = static function ( $v, $col ) {
	$c = $col ? ' class="' . $col . '"' : '';
	if ( true === $v ) {
		return '<td' . $c . '><span class="yes">✓</span></td>';
	}
	if ( false === $v ) {
		return '<td' . $c . '><span class="dash">—</span></td>';
	}
	if ( 0 === strpos( $v, 'p:' ) ) {
		return '<td' . $c . '><span class="partial">' . substr( $v, 2 ) . '</span></td>';
	}
	return '<td' . $c . '>' . $v . '</td>';
};
$ct_body = '';
foreach ( $ct_sections as $name => $rows ) {
	$ct_body .= '<tr class="section-row"><td colspan="4">' . $name . '</td></tr>';
	foreach ( $rows as $r ) {
		$ct_body .= '<tr><td>' . $r[0] . ( $r[1] ? '<small>' . $r[1] . '</small>' : '' ) . '</td>' . $ct_cell( $r[2], '' ) . $ct_cell( $r[3], 'col-pro' ) . $ct_cell( $r[4], 'col-eli' ) . '</tr>';
	}
}
$ctable = <<<HTML
<div class="fx-ctable-wrap">
<table class="fx-ctable">
	<thead><tr><th>Feature</th><th>Essentials<small>\$995 at closing</small></th><th class="col-pro">Pro ★<small>\$2,495 at closing</small></th><th class="col-eli">Elite<small>from \$3,995 at closing</small></th></tr></thead>
	<tbody>{$ct_body}</tbody>
	<tfoot><tr><td colspan="4">All plans: \$0 today · No signup required to preview · Flat fee due only on a successful sale · No upgrade fees · Cancel before you accept an offer and owe nothing</td></tr></tfoot>
</table>
</div>
HTML;

$protect = static function ( $icon, $title, $text ) {
	return fx_w( 'icon-box', array(
		'selected_icon'    => fx_icon( $icon ),
		'title_text'       => $title,
		'description_text' => $text,
		'position'         => 'top',
		'text_align'       => 'left',
		'title_size'       => 'h3',
		'_css_classes'     => 'fx-icard',
	) );
};
$uni = static function ( $title, $text ) {
	return fx_w( 'icon-box', array(
		'selected_icon'    => fx_icon( 'fas fa-check' ),
		'title_text'       => $title,
		'description_text' => $text,
		'position'         => 'left',
		'title_size'       => 'p',
		'_css_classes'     => 'fx-uni',
	) );
};

$plans = array(
	$hero( 'plans-hero', 'Plans &amp; Pricing', 'One flat fee at closing.<br><span class="fx-accent is-green">Zero commission.</span> Zero upfront.',
		'Every Fortify plan gives you licensed broker oversight and full MLS exposure. The plan you choose determines how much support you want when showings, offers, and negotiations get real.',
		array(
			$meta( array( '$0 today', "\$0 if your home doesn't sell", '$0 if you cancel' ) ),
			fx_text( '<p>Every plan is due only at closing. See your broker price opinion and listing preview free, before you commit to anything.</p>', 'fx-page-sub' ),
		)
	),

	fx_section( 'plans-anchors', 'Plan anchor bar', 'fx-anchors', array(
		fx_list( array(
			array( 'text' => 'Essentials — $995', 'url' => '#plans-essentials' ),
			array( 'text' => 'Pro — $2,495', 'url' => '#plans-pro' ),
			array( 'text' => 'Elite — from $3,995', 'url' => '#plans-elite' ),
			array( 'text' => 'Compare all →', 'url' => '#plans-compare' ),
		), 'fx-anchor-links fx-spy', '' ),
	), array( 'padding' => fx_pad( 0, 24, 0, 24 ), 'padding_tablet' => fx_pad( 0, 24, 0, 24 ) ) ),

	$sec( 'plans-universal', 'Every plan includes', '', array(
		$intro( 'Every plan includes', 'The foundation every seller needs — regardless of which plan you choose', '', 'fx-intro-wide', 'fx-h2s' ),
		$grid( 'Included in every plan', 'fx-card-grid', 3, 2, 1, 14, array(
			$uni( 'Licensed broker listing review', 'Every submission reviewed before going to the MLS' ),
			$uni( 'Broker Price Opinion, included', 'A written, broker-signed opinion of value with model-match/nearby comps before you go live (an opinion of value, not an appraisal)' ),
			$uni( 'MLS entry &amp; syndication', 'Zillow, Realtor.com, Redfin, Homes.com + 100+ sites' ),
			$uni( 'Free listing preview first', 'See your listing before you commit to anything' ),
			$uni( 'Buyer-agent compensation, handled correctly', 'You decide whether to offer it and how much; we show you the all-in math and communicate your terms through MLS-permitted channels — offers are no longer published on the MLS itself' ),
			$uni( 'Seller dashboard', 'Manage your listing, view status, upload updates' ),
			$uni( 'Upgrade anytime, free', 'Start lean, add support as your sale develops' ),
		) ),
	), 64, 64 ),

	$plan_sec( 'plans-essentials', 'Essentials', array(
		$plan_head( 'Essentials', 'ess', 'For confident, self-directed sellers', $price_block( '', '$995 due at closing' ) ),
		fx_text( "<p>Essentials gives you everything you need to get your home onto the MLS professionally and in front of every buyer agent in your market. You manage showings directly, coordinate with buyers and agents yourself, and handle negotiations independently. If you've sold a home before and know what you're doing, this is the lean option that saves the most money.</p>", 'fx-plan-desc' ),
		$grid( 'Plan details', '', 2, 2, 1, 22, array(
			$detail( "What's included", 'accent', array(
				'Licensed broker listing review &amp; MLS submission',
				'<strong>Broker Price Opinion</strong> — a written opinion of value with model-match/nearby comparables, prepared and signed by a licensed Florida broker before your listing goes live <em>(an opinion of value, not an appraisal)</em>',
				'Full syndication to Zillow, Realtor.com, Redfin + 100 sites',
				'Up to 25 seller-uploaded photos',
				'Buyer-agent compensation handled correctly — you decide whether to offer it and how much',
				'Live on MLS within ~24 hours of your approval',
				'Up to 3 post-live listing edits (price changes always free); unlimited edits on Pro and Elite',
				'Email &amp; portal support',
				'Seller dashboard for status and updates',
			), 'is-included' ),
			$detail( 'Not included — available by upgrading', 'muted', $up( array(
				'Electronic lockbox — required for automated showing access',
				'ShowingTime / automated showing scheduling',
				'Unlimited listing edits and expanded photos',
				'Professional photography &amp; floor plan — $495 add-on (included with Elite)',
				'Priority support line',
				'Pricing strategy, before and during your listing (Elite)',
				'Closing guidance — we walk you through what happens, when, and what to sign',
			) ), 'is-upgrade', 'Upgrade to Pro or Elite anytime — no fees, no penalties.' ),
		) ),
		$btn_row( array(
			fx_button( 'Start with Essentials — $0 today', $start, 'primary' ),
			fx_button( 'See what Pro adds →', '#plans-pro', 'ghost' ),
		) ),
		$bestfor( 'Sellers who have sold before, are comfortable coordinating showings and communicating directly with buyer agents, and want to minimize total selling costs.' ),
	) ),

	$plan_sec( 'plans-pro', 'Pro', array(
		$plan_head( 'Pro — Most sellers choose this', 'pro', 'Full listing tools. Broker support when it matters.', $price_block( 'pro', '$2,495 due at closing' ) ),
		fx_text( "<p>Pro is the plan most sellers choose because it makes a seller-led listing operate like a traditionally-listed home. Buyer agents can request showings through ShowingTime exactly as they would on any listing. Your electronic lockbox handles access automatically. You get unlimited listing edits, expanded photo capacity, and priority support. It's the practical choice for anyone who wants professional tools without paying traditional commission.</p>", 'fx-plan-desc' ),
		$grid( 'Plan details', '', 2, 2, 1, 22, array(
			$detail( 'Everything in Essentials, plus', 'accent', array(
				'<strong>Electronic lockbox</strong> — Supra eKey-compatible, agent-accessible for all showings',
				'<strong>ShowingTime</strong> (or equivalent) — automated showing request and scheduling system',
				'<strong>Unlimited listing edits</strong> — price changes, description updates, photo swaps, subject to MLS rules',
				'<strong>Expanded photo capacity</strong> — where permitted by your local MLS',
				'<strong>Error &amp; rule check</strong> before you go live',
				'<strong>Priority support</strong> — email &amp; portal with faster response times',
			), 'is-included is-pro' ),
			$detail( 'Available by upgrading to Elite', 'muted', $up( array(
				'Pricing Strategy &amp; Active Price Management — broker-led strategy session before you go live, plus re-pricing counsel for the life of your listing',
				'Offer review and response strategy guidance',
				'Contract and addenda guidance',
				'Negotiation support as needed',
				'Inspection &amp; repairs guidance',
				'Closing guidance — we walk you through what happens, when, and what to sign',
				'Professional photography &amp; floor plan (included with Elite; $495 add-on on Pro)',
				'Priority broker access line',
			) ), 'is-upgrade', 'Upgrade to Elite anytime — even after you receive an offer.' ),
		) ),
		$btn_row( array(
			fx_button( 'Start with Pro — $0 today', $start, 'indigo' ),
			fx_button( 'See what Elite adds →', '#plans-elite', 'ghost' ),
		) ),
		$bestfor( 'Most sellers — especially those listing for the first time, selling a higher-value home, or wanting the showing automation and support tools that make their listing compete with traditionally-listed homes.' ),
	), array( 'background_background' => 'classic', 'background_color' => '#F9F9FE' ) ),

	$plan_sec( 'plans-elite', 'Elite', array(
		$plan_head( 'Elite', 'eli', 'Broker guidance for higher-stakes decisions', $price_block( 'eli', 'From $3,995 due at closing' ) ),
		fx_text( "<p>Elite includes everything in Pro, plus active broker involvement at the moments that carry the most financial risk — pricing strategy before you go live, offer review and response guidance when offers arrive, negotiation support through the back-and-forth, inspection and appraisal guidance, and closing guidance. It's the plan for sellers who want the savings of a flat fee without giving up the strategic guidance a full-service agent would provide.</p>", 'fx-plan-desc' ),
		$grid( 'Plan details', '', 2, 2, 1, 22, array(
			$detail( 'Everything in Pro, plus', 'accent', array(
				'<strong>Pricing Strategy &amp; Active Price Management</strong> — a broker-led strategy session before you go live (list-price positioning against your comps, your offer-price floor, pre-agreed price-reduction triggers), plus re-pricing counsel for the life of your listing as showings, feedback, and days-on-market data come in. The BPO tells you what your home is worth today; Elite manages your price until it closes.',
				'<strong>Professional photography &amp; floor plan</strong> — a professional shoot (including exterior/outdoor-living coverage) and a floor plan, scheduled at listing launch <em>(aerial where community rules permit)</em>',
				'<strong>Offer review &amp; response strategy</strong> — broker reviews terms, flags issues, advises on counter',
				"<strong>Contract &amp; addenda guidance</strong> — understand what you're signing",
				'<strong>Negotiation support</strong> — strategy and guidance through counteroffers and buyer requests',
				'<strong>Buyer inspection &amp; repair request guidance</strong>',
				'<strong>Transaction guidance through closing</strong> — timeline and deadline tracking, with reminders at every contractual milestone',
				'<strong>Closing guidance</strong> — we walk you through what happens, when, and what to sign',
				'<strong>Priority broker access line</strong> — direct line when you need a fast answer',
			), 'is-included is-eli' ),
			fx_box( 'Card: Elite pricing caps', 'fx-detail is-caps', array(
				fx_heading_in( 'Elite pricing caps by sale price', 'h4', 'fx-card-label', 'accent', '#0E9F6E' ),
				fx_text( "<p>Elite fees are capped by final sale price so the cost stays predictable regardless of your home's value.</p>", 'fx-caps-text' ),
				fx_text(
					'<div class="fx-cap-row is-base"><span class="range">Up to $600,000</span><span class="fee">$3,995</span></div>' .
					'<div class="fx-cap-row"><span class="range">$600,001 – $1,000,000</span><span class="fee">$5,495</span></div>' .
					'<div class="fx-cap-row"><span class="range">$1,000,001 – $2,000,000</span><span class="fee">$6,995</span></div>' .
					'<div class="fx-cap-row"><span class="range">$2,000,001 – $3,000,000</span><span class="fee">$8,995</span></div>',
					'fx-caps-rows'
				),
				fx_text( "<p>Elite eligibility is based on list price (homes listed up to \$3,000,000). Your Elite fee is set by the final sale price band at closing — if your home sells into a lower band than it was listed in, you pay the lower band's fee.</p><p>Elite is currently available for homes listed up to \$3,000,000. All fees due at closing only if your home sells.</p>", 'fx-caps-notes' ),
			) ),
		) ),
		fx_box( "What Elite is — and isn't", 'fx-elite-is', array(
			fx_heading_in( "What Elite is — and isn't", 'h4', 'fx-card-label', 'accent', '#0E9F6E' ),
			fx_text( "<p><strong>What Elite is:</strong> strategic guidance from a licensed Florida broker — by phone, video, and in writing, during business hours, with priority-line responses within 4 business hours. We guide; you decide.</p><p><strong>What no Fortify plan includes:</strong> hosting or attending showings or open houses; in-person attendance at inspections, appraisals, or closing; drafting custom legal provisions (we'll tell you when you need an attorney); vendor or repair management; staging or media production beyond included photography; representation of the buyer.</p>", 'fx-lead' ),
		) ),
		$btn_row( array( fx_button( 'Start with Elite — $0 today', $start, 'green' ) ) ),
		$bestfor( 'Sellers with higher-value homes, unusual properties, first-time sellers, multiple-offer situations, relocation pressure, appraisal or inspection risk, or anyone who wants broker-level strategic guidance without paying a percentage-based commission.' ),
	), array( 'background_background' => 'classic', 'background_color' => '#F8FCFA' ) ),

	$sec( 'plans-compare', 'Comparison table', '', array(
		$intro( 'Side by side', 'Everything, compared', "Every feature across every tier — so you know exactly what you're getting before you commit to anything.", 'fx-intro' ),
		fx_html( 'Feature comparison table', $ctable ),
	), 64, 64, $lav_bg ),

	$sec( 'plans-protections', 'Built-in protections', '', array(
		$intro( 'Built-in protections', 'No surprises — by design', '', 'fx-intro', 'fx-h2s' ),
		$grid( 'Protections', 'fx-card-grid', 4, 2, 1, 20, array(
			$protect( 'fas fa-wallet', '$0 upfront', 'One flat fee, disclosed in full before you sign, due only when your home successfully closes. No listing deposit, no retainer.' ),
			$protect( 'fas fa-door-open', 'You can walk away', "Cancel your listing before you accept an offer and you owe nothing. If your listing term ends without a sale, you owe nothing. The only charges that can ever apply are (1) a lockbox replacement fee if Pro/Elite hardware isn't returned within 14 days of cancellation or expiration, and (2) your plan fee if your home sells shortly after cancellation to a buyer who was introduced to it during your listing — the same protection-period standard used across Florida brokerage agreements. Both are spelled out in plain language in the <a href=\"/fortify-sample-listing-agreement/\">sample agreement</a>." ),
			$protect( 'fas fa-arrow-up', 'Upgrade anytime, free', 'Start on Essentials and upgrade to Pro or Elite at any point — even after you receive an offer. No upgrade fees, no lock-in period.' ),
			$protect( 'fas fa-shield-alt', 'Read the agreement first', 'Our listing agreement is available before you enter your address. No fine print revealed at checkout. <a href="/fortify-sample-listing-agreement/">View sample agreement →</a>' ),
		) ),
	), 64, 64 ),

	$sec( 'plans-faq', 'Pricing questions', '', array(
		$intro( 'Pricing questions', 'What sellers ask before choosing a plan', '', 'fx-intro', 'fx-h2s' ),
		fx_box( 'FAQ list', 'fx-faq-wrap', array( fx_faq( $faq_items( array(
			array( 'Do I have to choose a plan before I see my listing preview?', "<p>No. You enter your address and receive a broker-reviewed listing preview before you select a plan or sign anything. The preview shows you exactly what your listing would look like on the MLS and major sites. You choose a plan only when you're ready to move forward.</p>" ),
			array( 'When exactly is the flat fee due?', "<p>Your Fortify flat fee is settled at closing as part of the transaction — the same way a buyer-agent commission is handled. It appears on your closing disclosure and is paid from your sale proceeds. You pay nothing before closing, and nothing at all if your home doesn't sell.</p>" ),
			array( 'What if I start on Essentials and realize I need more support?', "<p>Upgrade anytime — there are no penalties and no additional fees beyond the difference between your original plan and the new one. Many sellers start on Essentials and upgrade to Pro once showings begin and they want the ShowingTime automation, or upgrade to Elite when an offer arrives and they want broker guidance reviewing it. You're never locked in.</p>" ),
			array( "What's the difference between Pro and Elite in practice?", "<p>Pro gives you all the listing tools — lockbox, automated showings, unlimited edits, priority support — and makes your listing operate like a traditionally-listed home from a buyer-agent perspective. You still make all the strategic decisions yourself.</p><p>Elite adds active broker involvement at the moments where those decisions carry the most financial risk: pricing before you go live, reviewing offers when they arrive, advising on counteroffers, flagging contract issues, and staying alongside you through inspection and closing. If you'd normally rely on an agent for guidance at those moments, Elite provides it.</p>" ),
			array( "I got a free price opinion before signing up — what's different about Elite's pricing help?", '<p>The free preview and the plan-included BPO are a snapshot: what your home is worth today, and how it would be presented. Elite adds the process: a strategy session on where to list against your comps, what offer price to hold, when a reduction is warranted — and a broker revisiting those calls with you for as long as your home is on the market.</p>' ),
			array( 'Can I upgrade to Elite after an offer arrives?', '<p>You can upgrade to Elite at any time — even after an offer arrives — for the difference in fee. Elite guidance applies from the date of upgrade forward; it does not include review of, or responsibility for, pricing decisions, offers, or contract terms executed before the upgrade.</p>' ),
			array( 'Does the Elite price cap mean the fee goes up on more expensive homes?', '<p>Yes — but it caps at each tier rather than scaling as a percentage. A $900,000 home pays $5,495 with Elite. A traditional 3% listing commission on that same home would be $27,000. Even the highest Elite tier ($8,995 for homes up to $3M) is a fraction of what a percentage-based commission would cost at that price point.</p>' ),
			array( 'Is there a buyer-agent commission I also have to pay?', "<p>That's your decision, not ours. You decide whether to offer buyer-agent compensation and how much — many sellers do, typically 2–3%, because it incentivizes buyer agents to show the home and widens your buyer pool. Some choose not to, particularly when listing for a buyer they've already identified. Under current MLS rules, compensation offers can no longer be published on the MLS itself; we communicate your terms through permitted channels, use the MLS seller-concessions field where available, and handle compensation accurately in offers and negotiations. Whatever you decide, it's negotiable at offer time — nothing is locked until you sign a contract. The Fortify flat fee is separate from and in addition to any buyer-agent compensation you choose to offer.</p>" ),
		) ) ) ) ),
		fx_text( '<p>More questions? <a href="/faqs/">See the full FAQ →</a></p>', 'fx-more-line' ),
	), 64, 64, $lav_bg ),

	$cta( 'plans-cta', 'See your listing before you choose a plan',
		'Free broker price opinion + broker-reviewed listing preview, delivered in writing within 3 business days — prepared by a licensed Florida broker. No signup, no commitment, $0 today.',
		$equity( 'Call ' . $phone, $tel ),
		'<p>$0 today · No signup required to preview · Upgrade anytime · Pay only if your home sells</p><p>An opinion of value, not an appraisal · Derived from public-record and MLS data · Not intended to solicit property currently listed with another broker.</p>' ),
);

/* ====================================================================
 * FAQs
 * ================================================================== */

$faq_groups = array(
	array( 'getting-started', 'Getting started', 'Before you decide', "What Fortify is, who it's for, and what to expect when you begin.", array(
		array( 'What is Fortify Listings and how does it work?', '<p>Fortify Listings is a licensed Florida brokerage that offers flat fee MLS listing services for home sellers. Instead of paying a percentage-based listing commission, you pay a single flat fee — due at closing only if your home sells.</p><p>The process starts with your address. We prepare a broker-reviewed listing preview at no cost and no commitment, so you can see exactly what your listing would look like before choosing a plan or signing anything.</p>' ),
		array( 'How is Fortify different from a traditional real estate agent?', '<p>A traditional listing agent charges a percentage-based commission — typically 2.5–3% of your sale price — and manages most decisions on your behalf. Fortify charges a flat fee and puts you in control of pricing, showings, and negotiation decisions, with licensed broker support available at the level you choose.</p><p>On a $450,000 home, a 3% listing commission is $13,500. Fortify Pro is $2,495 — due only at closing.</p><div class="inline-callout"><strong>The key difference:</strong> with Fortify, you make the decisions. We provide the professional listing infrastructure, MLS entry, and broker oversight. Higher plans add strategic guidance at the moments that matter most.</div>' ),
		array( 'Is Fortify right for me?', "<p>Fortify works best for sellers who are comfortable being actively involved in their sale — coordinating or approving showings, reviewing offers, and communicating with buyer agents. Experience with a previous home sale helps, but isn't required, especially with Pro or Elite plans.</p><p>Fortify is probably not the right fit if you want a traditional agent to manage every step of the process on your behalf with minimal involvement from you.</p><ul><li>First-time sellers who want guidance — consider Elite, which provides broker support at every decision point</li><li>Experienced sellers comfortable with the process — Essentials or Pro handles everything you need</li><li>Sellers who want to be fully hands-off — a traditional agent may be a better fit</li></ul>" ),
		array( 'Do I need to create an account to see my listing preview?', '<p>No. You enter your address and receive a broker-reviewed listing preview before creating an account, selecting a plan, or signing anything. The preview shows you exactly what your listing would look like on the MLS and on Zillow, Realtor.com, and Redfin. You move forward only if you like what you see.</p>' ),
		array( 'What happens after I enter my address?', "<p>Our team pulls public property data for your address and prepares a draft MLS listing — formatted exactly as it would appear to buyer agents and on major real estate sites. A licensed broker reviews it before you see it. You receive your preview typically the same day.</p><p>From there you can review it, ask questions, choose a plan when you're ready, and move through the listing process at your own pace. Nothing is due and nothing is committed until you sign your listing agreement.</p>" ),
	) ),
	array( 'pricing', 'Pricing &amp; plans', 'What it costs and what you get', "Everything about Fortify's flat fee structure and how the three plans differ.", array(
		array( 'What does it cost to list my home with Fortify?', '<p>Fortify offers three plans. All are $0 today — the flat fee is due only at closing if your home sells.</p><ul><li><strong>Essentials — $995 at closing.</strong> MLS listing, syndication, 25 photos, a written broker price opinion, and email support. For confident self-directed sellers.</li><li><strong>Pro — $2,495 at closing.</strong> Everything in Essentials plus electronic lockbox, ShowingTime scheduling, unlimited edits, and priority support. Most sellers choose this.</li><li><strong>Elite — from $3,995 at closing.</strong> Everything in Pro plus pricing strategy &amp; active price management, professional photography, offer strategy, negotiation guidance, and closing guidance.</li></ul><div class="inline-callout">For comparison: a traditional 3% listing commission on a $450,000 home is $13,500. Fortify Pro saves $11,005 on that same sale.</div>' ),
		array( 'Are there any hidden fees or upfront costs?', "<p>No. Your Fortify flat fee is disclosed in full before you sign your listing agreement and is due only at closing. There are no listing deposits, no retainers, and nothing due at any point before closing.</p><p>You can also walk away at no cost: cancel your listing before you accept an offer and you owe nothing, and if your listing term ends without a sale, you owe nothing either. The only charges that can ever apply are a lockbox replacement fee if Pro/Elite hardware isn't returned within 14 days of cancellation or expiration, and — under the standard Florida protection-period rule — your plan fee if your home sells shortly after cancellation to a buyer introduced during your listing.</p>" ),
		array( "What's the difference between Essentials, Pro, and Elite?", "<p><strong>Essentials</strong> covers the professional listing fundamentals — MLS entry, full syndication, broker review, and email support. You manage showings directly with buyer agents and handle offers independently.</p><p><strong>Pro</strong> adds the tools that make a seller-led listing operate like a traditionally-listed home: an electronic lockbox for agent access, ShowingTime for automated showing requests, unlimited listing edits, and priority support. Most sellers choose Pro because it removes the friction of coordinating showing access manually.</p><p><strong>Elite</strong> adds active broker involvement at the highest-stakes moments — pricing strategy and active price management, offer review and response guidance, negotiation support, inspection and appraisal guidance, and closing guidance. It's the plan for sellers who want full strategic guidance without paying a percentage-based commission.</p>" ),
		array( "Can I upgrade my plan after I've started?", "<p>Yes — anytime, with no penalties and no upgrade fees. Many sellers start on Essentials and upgrade to Pro once showings begin and they want the scheduling automation. Others upgrade from Pro to Elite when an offer arrives and they want broker guidance reviewing it. You're never locked into the plan you started with.</p><p>You can upgrade to Elite at any time — even after an offer arrives — for the difference in fee. Elite guidance applies from the date of upgrade forward; it does not include review of, or responsibility for, pricing decisions, offers, or contract terms executed before the upgrade.</p>" ),
		array( 'What are the Elite pricing caps?', "<p>Elite fees are capped by final sale price to keep the cost predictable regardless of your home's value.</p><ul><li>Up to \$600,000 → \$3,995</li><li>\$600,001 – \$1,000,000 → \$5,495</li><li>\$1,000,001 – \$2,000,000 → \$6,995</li><li>\$2,000,001 – \$3,000,000 → \$8,995</li></ul><p>Elite eligibility is based on list price (homes listed up to \$3,000,000). Your Elite fee is set by the final sale price band at closing — if your home sells into a lower band than it was listed in, you pay the lower band's fee. Even at the highest tier, the savings compared to a percentage-based commission at that price point are substantial.</p>" ),
		array( "What happens if my home doesn't sell?", "<p>You pay nothing. Fortify's flat fee is due only upon a successful closing. If you cancel before accepting an offer, you owe nothing. If your listing expires or you withdraw before any offer is accepted, no flat fee is owed.</p>" ),
	) ),
	array( 'listing-setup', 'Listing setup', 'Getting your listing ready', "What's involved in the listing process and what you'll need to provide.", array(
		array( 'What information do I need to provide to create my listing?', "<p>After you've reviewed your preview and selected a plan, we'll ask you for the details public records don't give us. This is a guided process — not a long form. Most sellers complete it in under fifteen minutes.</p><ul><li>Your asking price</li><li>Buyer-agent compensation amount (your decision)</li><li>Property photos (up to your plan's photo limit)</li><li>Required seller disclosures</li><li>Showing preferences and property access instructions</li><li>Any property features or details you want highlighted</li></ul>" ),
		array( 'What if the listing preview has wrong information?', "<p>The preview is built from public property records, which aren't always perfectly current. Square footage, bedroom and bathroom count, and property features sometimes differ from what's on record. You'll have the opportunity to correct anything once you're in the platform. Nothing goes to the MLS until you've reviewed and approved the final version.</p>" ),
		array( 'Will a broker review my listing before it goes live?', "<p>Yes — always. Every Fortify listing is reviewed by a licensed broker for MLS compliance, required disclosure completeness, listing field accuracy, and photo quality before it's submitted to the MLS. If anything needs attention, we'll reach out before posting. Nothing goes live until you've given your final approval.</p>" ),
		array( 'Can I make changes to my listing after it goes live?', '<p>Yes. Essentials includes a limited number of edits after going live. Pro and Elite include unlimited listing edits subject to your local MLS rules — price changes, description updates, photo swaps, and more can typically be made within 24 hours of your request.</p>' ),
		array( 'How do I set my asking price?', "<p>You set your own price. Essentials sellers are responsible for their own pricing research — your dashboard gives you access to listing status and you can adjust your price at any time. Pro and Elite sellers receive a broker pricing and positioning review before going live, including a comparative market analysis to inform your initial price.</p><p>If you're uncertain about pricing on Essentials, you can always upgrade to Pro or Elite before your listing goes live.</p>" ),
	) ),
	array( 'mls-exposure', 'MLS &amp; exposure', 'Where your listing appears and how it looks', 'How Fortify listings reach buyers and how they compare to traditionally-listed homes.', array(
		array( 'Will my listing look the same as a traditionally-listed home?', '<p>Yes. Fortify listings are entered into the MLS by a licensed broker using the same fields, same format, and same syndication as any other listing. On Zillow, Realtor.com, Redfin, and every other site, your listing is indistinguishable from a traditionally-listed home. Buyer agents see the same listing they see for every other property in the MLS.</p>' ),
		array( 'Will buyer agents show my Fortify listing?', "<p>Yes. Your listing appears in the same MLS search every buyer agent uses. There's nothing in the MLS that identifies it as a Fortify listing — it looks identical to every other active listing. Buyer agents are incentivized to show listings that offer buyer-agent compensation, which you control.</p><div class=\"inline-callout\"><strong>On buyer-agent compensation:</strong> whether to offer it and how much is entirely your decision — many sellers offer 2–3% to maximize their buyer pool, and we'll show you the all-in math either way. Under current MLS rules, compensation offers can no longer be published on the MLS itself; we communicate your terms through permitted channels, use the MLS seller-concessions field where available, and handle compensation accurately in offers and negotiations. Whatever you decide, it's negotiable at offer time — nothing is locked until you sign a contract.</div>" ),
		array( 'Where exactly does my listing appear?', "<p>Your listing is entered into your local Florida MLS board and syndicated automatically to major real estate sites including Zillow, Realtor.com, Redfin, Homes.com, and more than 100 additional sites where buyers search. Syndication happens automatically — you don't need to submit your listing to each site individually.</p>" ),
		array( 'How long does it take for my listing to go live?', '<p>Most listings go live within approximately 24 hours of your final approval. Broker review typically completes within a few hours during business hours. Once your listing is active in the MLS, syndication to Zillow and other sites usually follows within a few hours, though some sites can take up to 24–48 hours to reflect the new listing.</p>' ),
	) ),
	array( 'showings', 'Showings &amp; access', 'How showings work on each plan', "Showing coordination works differently depending on which plan you're on.", array(
		array( 'How do showing requests work?', '<p>How showings are managed depends on your plan.</p><p><strong>Pro and Elite:</strong> Showing requests are handled through ShowingTime (or equivalent). Buyer agents request a showing time through the system, you receive a notification, and you approve, decline, or suggest an alternative time. The electronic lockbox handles agent access automatically for approved showings.</p><p><strong>Essentials:</strong> You coordinate showings directly with buyer agents by phone, text, or email. You manage access to your home yourself — a lockbox is recommended but you source and set it up independently.</p>' ),
		array( 'What is the electronic lockbox and do I need one?', '<p>An electronic lockbox is a secure key storage device that attaches to your property and allows buyer agents with an authorized key fob or smartphone app to access your home for showings. It logs every entry automatically.</p><p>Pro and Elite plans include a Supra eKey-compatible electronic lockbox — the standard used by agents throughout Florida. Essentials sellers manage their own access arrangements. A lockbox of some kind is strongly recommended for any listing because agents expect to be able to schedule and access showings without direct coordination with the seller every time.</p>' ),
		array( 'Can I restrict showing times or require notice before showings?', '<p>Yes. When you set up your showing preferences, you specify your available windows and any notice requirements. Pro and Elite sellers configure this through ShowingTime, which enforces those preferences automatically. Essentials sellers communicate their preferences directly to buyer agents who contact them.</p>' ),
		array( 'What about a yard sign?', '<p>A professional yard sign is available as a flat-fee add-on for any plan. You can order it after your listing is accepted and live. Yard signs are optional but recommended — they generate walk-by interest and signal to neighbors and passersby that the home is for sale.</p>' ),
	) ),
	array( 'offers', 'Offers &amp; negotiation', 'When buyers are interested', 'How offers are delivered, what to look for, and how much help you get depending on your plan.', array(
		array( 'How will I receive offers on my home?', "<p>Offers are delivered to you electronically — typically via email and through your seller dashboard — along with all relevant documents including the purchase and sale agreement, any addenda, and proof of financing. Pro and Elite sellers also have broker support available to help review and interpret what they've received.</p>" ),
		array( 'What should I look at beyond the offer price?', '<p>Price is one factor. The full picture of an offer includes:</p><ul><li>Financing type — cash, conventional, FHA, VA — and strength of proof of funds or pre-approval</li><li>Contingencies — inspection, financing, appraisal — and their timeframes</li><li>Closing timeline and whether it aligns with your needs</li><li>Earnest money deposit amount</li><li>Any special terms or requests — seller concessions, closing cost contributions, included items</li></ul><p>Elite sellers receive broker guidance evaluating each of these factors and advising on response strategy. Essentials and Pro sellers review and decide independently.</p>' ),
		array( 'Can I counter an offer?', "<p>Yes — you can accept, reject, or counter any offer. Countering typically involves responding with revised terms — price, closing date, contingency modifications, or other conditions. Negotiations can go back and forth until both parties agree or one party decides not to proceed.</p><p>Elite sellers have broker guidance throughout this process. Essentials and Pro sellers handle counteroffers directly with the buyer's agent.</p>" ),
		array( 'Can I have multiple offers at the same time?', "<p>Yes. If multiple offers arrive, you can review them simultaneously and respond to each one, run a highest-and-best scenario, or accept the one that best meets your needs. Florida real estate law permits sellers to handle multiple offers — you're not obligated to respond to them in order of receipt.</p><p>Elite sellers can get broker guidance on how to structure a multiple-offer situation to maximize their outcome.</p>" ),
	) ),
	array( 'under-contract', 'Under contract', 'After you accept an offer', 'What happens between accepted offer and closing — and who handles what.', array(
		array( 'What happens after I accept an offer?', "<p>Once you accept an offer, the transaction moves into the contract phase. This typically includes:</p><ul><li>Inspection period — the buyer schedules and conducts inspections, and may request repairs or credits</li><li>Financing and appraisal — the buyer's lender orders an appraisal if applicable</li><li>Title and escrow — a title company opens escrow and begins the closing process</li><li>Meeting contractual deadlines — there are specific timeframes for each phase that must be respected</li></ul><p>Elite sellers have broker guidance tracking deadlines, responding to inspection requests, and navigating issues that arise. Essentials and Pro sellers manage this process independently, coordinating directly with the buyer's agent, title company, and any other parties.</p>" ),
		array( 'What if the buyer requests repairs after the inspection?', "<p>Inspection repair requests are negotiable. You can agree to make repairs, offer a credit at closing, reduce the price, or decline. The right response depends on the nature of the items requested, how they compare to your sale price, and how motivated you are to close with this buyer.</p><p>Elite sellers receive broker guidance evaluating repair requests and determining the best response strategy. Essentials and Pro sellers negotiate directly with the buyer's agent.</p>" ),
		array( 'What if the appraisal comes in below the contract price?', '<p>A low appraisal can be handled several ways — you can reduce the price to the appraised value, negotiate a split between you and the buyer, challenge the appraisal with comparable sales data, or if the buyer has a financing contingency, allow them to exit the contract. Elite sellers get broker guidance working through appraisal gaps. This is one of the situations where the difference between plans is most pronounced.</p>' ),
	) ),
	array( 'closing', 'Closing &amp; payment', 'How closing works and when you pay', '', array(
		array( 'When exactly do I pay the Fortify flat fee?', "<p>Your Fortify flat fee is settled at closing as part of the transaction — it appears on your closing disclosure and is paid from your sale proceeds, exactly like a buyer-agent commission. You pay nothing before closing, and nothing at all if your home doesn't sell.</p>" ),
		array( 'What does the closing process look like?', "<p>Closing is coordinated by a title company or closing attorney. You'll review and sign the closing disclosure, which itemizes all costs including the Fortify flat fee and any buyer-agent compensation you offered. Most Florida closings are handled at a title company office, though remote or mail-away closings are increasingly common.</p><p>Elite sellers have closing guidance — we walk you through what happens, when, and what to sign, with reminders at every contractual milestone. Essentials and Pro sellers work directly with the title company.</p>" ),
		array( 'Can I cancel my listing if I change my mind?', "<p>Yes. You can cancel your listing at any time before you accept an offer, and you owe nothing. Once you've accepted an offer and are under contract, cancellation is governed by the terms of your purchase and sale agreement with the buyer — at that point it's a contract matter between you and the buyer, not a Fortify matter.</p>" ),
	) ),
	array( 'coverage', 'Coverage &amp; eligibility', 'Where Fortify operates', '', array(
		array( 'Which Florida counties does Fortify currently serve?', '<p>Fortify currently serves select Florida MLS markets. Active counties include: Alachua, Broward, Charlotte, DeSoto, Flagler, Hillsborough, Lake, Marion, Martin, Miami-Dade, Okeechobee, Orange, Osceola, Palm Beach, Pasco, Polk, St. Lucie, Sarasota, Sumter, and Volusia.</p><p>Enter your address to confirm your county is currently covered — coverage continues to expand.</p>' ),
		array( 'Does Fortify operate outside of Florida?', "<p>Fortify Listing Services LLC holds active broker licenses in Florida, Arizona, and Tennessee. Florida is our primary active market today. Arizona and Tennessee expansion is in progress — contact us if you're interested in listing in either of those states and we'll let you know when coverage becomes available in your area.</p>" ),
		array( 'What types of properties can I list with Fortify?', "<p>Fortify handles residential properties — single-family homes, condominiums, townhomes, and similar residential listings. Elite plan pricing caps apply to homes listed up to \$3,000,000. If you have a property outside these parameters, contact us before starting and we'll confirm whether Fortify is a good fit.</p>" ),
		array( 'Is Fortify licensed and compliant?', '<p>Yes. Fortify Listing Services LLC is a licensed Florida brokerage. Every listing is submitted to the MLS under active broker supervision and complies with all MLS rules and Florida real estate law. Our license is verifiable at MyFloridaLicense.com. A sample listing agreement is available on our website before you enter your address — no fine print revealed at checkout.</p>' ),
	) ),
);

$faq_nav    = array();
$faq_blocks = array();
foreach ( $faq_groups as $i => $g ) {
	$faq_nav[] = array( 'text' => $g[1], 'url' => '#' . $g[0] );
	$head      = array( fx_eyebrow( $g[1] ), fx_heading( $g[2], 'h2' ) );
	if ( $g[3] ) {
		$head[] = fx_text( '<p>' . $g[3] . '</p>', 'fx-lead' );
	}
	$faq_blocks[] = fx_box( 'Group: ' . wp_strip_all_tags( html_entity_decode( $g[1] ) ), 'fx-faq-group', array(
		fx_box( 'Group heading', 'fx-group-head', $head ),
		fx_faq( $faq_items( $g[4] ), 'fx-faq-cards', 0 === $i ),
	), array( '_element_id' => $g[0] ) );
}
$faq_blocks[] = fx_box( 'Still have questions', 'fx-still', array(
	fx_eyebrow( 'Still have questions?' ),
	fx_heading( "We're here before you commit to anything", 'h3' ),
	fx_text( '<p>Our Help Center has detailed guides on every stage of the process. Or call us directly — no pressure, no sales pitch.</p>', 'fx-lead' ),
	$btn_row( array(
		fx_button( 'Visit the Help Center', $help, 'primary' ),
		fx_button( $phone, $tel, 'ghost' ),
	) ),
) );

$faqs = array(
	$hero( 'faqs-hero', 'Frequently Asked Questions', 'Every question sellers ask before listing with Fortify',
		"Organized by the stage you're at — whether you're still deciding, ready to start, or deep into your sale." ),

	$sec( 'faqs-body', 'FAQ groups', '', array(
		$grid( 'FAQ layout', 'fx-faq-layout', '220px minmax(0,1fr)', 1, 1, 56, array(
			fx_box( 'Jump links', 'fx-faq-side', array(
				fx_text( '<p>Jump to section</p>', 'fx-side-label' ),
				fx_list( $faq_nav, 'fx-faq-nav fx-spy', '' ),
			) ),
			fx_box( 'FAQ content', 'fx-faq-content', $faq_blocks ),
		), array( 'grid_align_items' => 'start', 'grid_gaps_tablet' => fx_gap( 40, 40 ) ) ),
	), 64, 88 ),

	$cta( 'faqs-cta', 'Ready to see your listing?',
		"Enter your address and we'll prepare a free broker-reviewed preview — no account, no commitment, no plan selection required.",
		$equity( 'Compare plans →', '/plans/' ),
		'<p>$0 today · Pay only if your home sells · Cancel before you accept an offer and owe nothing</p>' ),
);

/* ====================================================================
 * About
 * ================================================================== */

$stat = static function ( $num_html, $label, $green = false ) {
	return fx_box( 'Stat', 'fx-stat', array(
		fx_heading( $num_html, 'p', 'fx-stat-num fx-num' . ( $green ? ' is-green' : '' ) ),
		fx_text( '<p>' . $label . '</p>', 'fx-stat-label' ),
	) );
};
$built = static function ( $icon, $tone, $title, $text ) {
	return fx_w( 'icon-box', array(
		'selected_icon'    => fx_icon( $icon ),
		'title_text'       => $title,
		'description_text' => $text,
		'position'         => 'top',
		'text_align'       => 'left',
		'title_size'       => 'h3',
		'_css_classes'     => 'fx-icard is-built is-' . $tone,
	) );
};
$tile = static function ( $label, $value, $sub ) {
	return fx_text( '<p class="label">' . $label . '</p><p class="value">' . $value . '</p><p class="sub">' . $sub . '</p>', 'fx-tile' );
};
$state = static function ( $name, $badge_html, $paras ) {
	return fx_box( 'State: ' . $name, 'fx-state', array(
		fx_heading( $name . ' ' . $badge_html, 'h3' ),
		fx_text( $paras, 'fx-lead' ),
	) );
};
$value = static function ( $n, $title, $text ) {
	return fx_con( array( '_title' => 'Value ' . $n, 'css_classes' => 'fx-value', 'flex_direction' => 'row', 'flex_wrap' => 'nowrap', 'flex_align_items' => 'flex-start', 'flex_gap' => fx_gap( 18 ) ), array(
		fx_heading( $n, 'div', 'fx-value-num' ),
		fx_box( 'Value body', 'fx-value-body', array(
			fx_heading( $title, 'h3' ),
			fx_text( '<p>' . $text . '</p>', 'fx-lead' ),
		) ),
	) );
};

$about = array(
	$hero( 'about-hero', 'About Fortify', 'Built to protect<br><span class="fx-accent">seller equity</span> — not erode it',
		'Fortify Listing Services LLC is a licensed real estate brokerage built on a simple premise: capable sellers deserve professional MLS exposure and licensed broker oversight without surrendering a percentage of their equity to get it.' ),

	$sec( 'about-mission', 'Mission', '', array(
		$grid( 'Mission grid', '', 2, 2, 1, 64, array(
			fx_box( 'Mission text', 'fx-mission-text', array(
				fx_eyebrow( 'Why Fortify exists' ),
				fx_heading( 'The traditional commission model was designed for a different era', 'h2', 'fx-h2m' ),
				fx_text( "<p>When percentage-based real estate commissions became standard, sellers had limited access to market data, limited ability to reach buyers, and limited tools to manage a transaction. The agent's commission reflected the value of that access.</p><p>That world no longer exists. Buyers search on Zillow. MLS data is widely available. Transaction tools are sophisticated. What sellers actually need hasn't changed — licensed broker oversight, professional listing quality, and reliable MLS entry — but the cost of delivering those things doesn't have to scale with the price of the home.</p><p>Fortify was built to close that gap. A flat fee that reflects the actual cost of the service, not a percentage of your equity.</p>", 'fx-lead' ),
			) ),
			fx_box( 'Stat block', 'fx-stat-block', array(
				$stat( '$<span>13,500</span>', 'Typical 3% listing commission on a $450,000 home' ),
				$stat( '$<span>2,495</span>', 'Fortify Pro flat fee — same MLS, same broker oversight, same exposure' ),
				$stat( '$11,005', 'Additional equity you keep — due only if your home sells', true ),
				fx_text( '<p>Illustration based on a typical 3% listing-side commission on a $450,000 sale. Buyer-agent compensation, if offered, is separate and set by the seller. Fortify’s flat fee is due at closing only if your home sells.</p>', 'fx-stat-disc' ),
			) ),
		), array( 'grid_align_items' => 'center', 'grid_gaps_mobile_extra' => fx_gap( 40, 40 ) ) ),
	), 88, 88 ),

	$sec( 'about-problem', 'The middle path', '', array(
		fx_box( 'Middle path panel', 'fx-panel-lav', array(
			fx_eyebrow( 'The middle path most sellers never knew existed' ),
			fx_heading( 'Not FSBO. Not full commission. Something built for how homes are actually sold today.', 'h2', 'fx-h2m' ),
			$grid( 'Before and after', 'fx-prob-cols', 2, 2, 1, 40, array(
				fx_box( 'Before', 'fx-prob-col is-before', array(
					fx_heading_in( 'What sellers were stuck choosing between', 'h3', '', 'muted', '#8B8AA3' ),
					fx_text( '<p>A traditional full-service agent at 2.5–3% — with expertise and support but at a cost that scales with your home price regardless of how much work the transaction actually requires.</p><p>Or FSBO — no commission, but no MLS entry, no professional listing quality, limited buyer agent engagement, and no licensed oversight at any stage.</p><p>Neither option was designed for the seller who is capable and informed but wants professional infrastructure without the percentage-based price tag.</p>', 'fx-lead' ),
				) ),
				fx_box( 'After', 'fx-prob-col is-after', array(
					fx_heading_in( 'What Fortify provides instead', 'h3', '', 'accent', '#0E9F6E' ),
					fx_text( '<p>Licensed broker oversight at every stage. Professional MLS entry and syndication. A flat fee that reflects the cost of the service, not the value of your home.</p><p>You control pricing, showings, and negotiation decisions. We provide the listing infrastructure, compliance review, and — on higher plans — active broker guidance at the moments where the stakes are highest.</p><p>The result is a seller who keeps significantly more of their equity while getting the professional listing quality and MLS exposure that actually drives buyer interest.</p>', 'fx-lead' ),
				) ),
			), array( 'grid_gaps_mobile_extra' => fx_gap( 28, 28 ) ) ),
		) ),
	), 0, 88 ),

	$sec( 'about-built', 'Built differently', '', array(
		$intro( 'How we operate', 'Built differently from the start', 'The flat fee MLS space has a reputation problem — hidden fees, poor support, no real broker involvement. Fortify was designed with those failure modes explicitly in mind.', 'fx-intro', 'fx-h2s' ),
		$grid( 'Operating principles', 'fx-card-grid', 3, 2, 1, 22, array(
			$built( 'fas fa-eye', 'indigo', 'Preview before you commit', "We prepare your listing before you choose a plan or sign anything. You evaluate the product before being asked for a commitment. Most flat fee services make you fill out long forms before showing you what you're getting. We don't." ),
			$built( 'fas fa-user-check', 'green', 'Licensed broker at every step', "Every listing is reviewed by a licensed broker before it goes to the MLS — not a virtual assistant, not an automated system. Broker oversight isn't an add-on at Fortify. It's built into every plan from the start." ),
			$built( 'fas fa-receipt', 'ink', 'Transparent pricing — no surprises', "Your flat fee is disclosed in the listing agreement before you sign. Our listing agreement is available on the website before you enter your address. There are no fees revealed at checkout that weren't shown upfront. Transparency is a design choice, not a marketing claim." ),
			$built( 'fas fa-layer-group', 'indigo', 'Scale without sacrificing quality', 'Fortify was built as a platform from day one — not a brokerage trying to bolt technology onto a traditional model. As we expand to new markets and grow our licensed team, the compliance and quality standards that every listing is built on stay consistent.' ),
			$built( 'fas fa-headset', 'green', "Support that doesn't disappear", "A common failure point in flat fee services is abandonment after listing — no one available when an offer arrives or a situation gets complicated. Fortify's tiered support model ensures you always have a path to reach someone, with Pro and Elite providing active broker availability throughout your sale." ),
			$built( 'fas fa-users', 'ink', 'Built to grow with a team', 'Fortify is not a one-person operation. The platform, the process, and the licensed oversight model are built to scale — additional licensed team members, expanded state coverage, and deeper local market support as the business grows. The quality you experience today is the floor, not the ceiling.' ),
		) ),
	), 0, 88 ),

	$sec( 'about-broker', 'Broker oversight', '', array(
		$grid( 'Broker panel', 'fx-panel-wash', 'minmax(0,1fr) minmax(0,2fr)', 1, 1, 56, array(
			fx_box( 'Broker', '', array(
				fx_eyebrow( 'Broker Oversight' ),
				fx_heading( 'Ralph Massetti<br>Licensed Real Estate Broker', 'h2' ),
				fx_list( array( 'Licensed FL · AZ · TN', '10+ Years Licensed', 'Broker-Supervised Listings' ), 'fx-pills', '', 'fa-solid', array( 'view' => 'inline' ) ),
				fx_text( '<p>Verify credentials</p><p><a href="' . esc_url( $lic_url ) . '" target="_blank" rel="noopener noreferrer">FL Brokerage License #CQ1074119 →</a></p>', 'fx-verify' ),
			) ),
			fx_text( "<p>Ralph Massetti serves as the licensed broker for Fortify Listing Services LLC, bringing more than 10 years of licensed real estate experience to the company's listing review, MLS submission standards, seller support, and transaction guidance.</p><p>Fortify was created for sellers who want professional MLS exposure and licensed broker oversight without paying a listing fee that increases simply because their home is worth more. Every Fortify listing is reviewed under licensed broker supervision before MLS submission, with pricing disclosed before commitment and support available through the listing process.</p><p>Fortify is not a FSBO listing site and it is not a traditional percentage-based listing model. It is a licensed flat-fee brokerage service built to give sellers a more transparent way to list their property while preserving access to broker review, compliance oversight, and transaction support.</p>", 'fx-bio' ),
		), array( 'grid_align_items' => 'start', 'grid_gaps_tablet' => fx_gap( 32, 32 ) ) ),
	), 0, 88 ),

	$sec( 'about-credentials', 'Credentials', '', array(
		fx_box( 'Credentials panel', 'fx-panel-dark', array(
			fx_eyebrow( 'Licensed &amp; verifiable' ),
			fx_heading( 'Everything about Fortify is verifiable before you commit', 'h2', 'fx-h2m', array(), true ),
			fx_text( "<p>We're a licensed brokerage operating under active real estate law in every state we serve. Our license is public record. Our listing agreement is on our website. Our pricing is disclosed before you sign. There's nothing here that requires trust without verification.</p>", 'fx-lead' ),
			$grid( 'Credential tiles', 'fx-card-grid', 4, 2, 2, 16, array(
				$tile( 'Brokerage', 'Fortify Listing Services LLC', 'Licensed real estate brokerage' ),
				$tile( 'FL Brokerage License', '#CQ1074119', '<a href="' . esc_url( $lic_url ) . '" target="_blank" rel="noopener noreferrer">Verify license CQ1074119 →</a>' ),
				$tile( 'Active licenses', 'Florida · Arizona · Tennessee', 'Serving FL markets today' ),
				$tile( 'Listing agreement', 'Available before you start', '<a href="/fortify-sample-listing-agreement/">View sample agreement →</a>' ),
			) ),
		) ),
	), 0, 88 ),

	$sec( 'about-coverage', 'Where we operate', '', array(
		$intro( 'Where we operate', 'Active today in Florida, expanding to Arizona and Tennessee', 'Fortify currently serves select Florida MLS markets. We hold active broker licenses in Arizona and Tennessee and are expanding to serve those markets.', 'fx-intro-wide', 'fx-h2s' ),
		$grid( 'States', 'fx-card-grid', 3, 3, 1, 20, array(
			$state( 'Florida', '<span class="fx-badge is-active">Active</span>', '<p>Serving select county MLS markets across the state.</p><p class="counties">Alachua · Broward · Charlotte · DeSoto · Flagler · Hillsborough · Lake · Marion · Martin · Miami-Dade · Okeechobee · Orange · Osceola · Palm Beach · Pasco · Polk · St. Lucie · Sarasota · Sumter · Volusia</p><p class="note">Enter your address to confirm your county is covered — coverage continues to expand.</p>' ),
			$state( 'Arizona', '<span class="fx-badge is-soon">Coming soon</span>', "<p>Active broker license held. Expansion to Arizona MLS markets in progress.</p><p class=\"note\">Contact us if you're interested in listing in Arizona and we'll notify you when coverage launches in your area.</p>" ),
			$state( 'Tennessee', '<span class="fx-badge is-soon">Coming soon</span>', "<p>Active broker license held. Expansion to Tennessee MLS markets in progress.</p><p class=\"note\">Contact us if you're interested in listing in Tennessee and we'll notify you when coverage launches in your area.</p>" ),
		) ),
	), 0, 88 ),

	$sec( 'about-values', 'Values', '', array(
		$intro( 'What we believe', 'The principles Fortify is built on', '', 'fx-intro', 'fx-h2s' ),
		$grid( 'Values', 'fx-card-grid', 2, 2, 1, 22, array(
			$value( '01', 'Seller equity belongs to the seller', "A home sale is often the largest financial transaction in a person's life. The cost of professional listing services should reflect the work involved — not a percentage of the asset being sold. Every dollar saved on commissions is a dollar that stays with the seller." ),
			$value( '02', 'Broker oversight is not optional', "Every Fortify listing is submitted to the MLS under active licensed broker supervision. This isn't a marketing claim — it's a legal and ethical commitment. Sellers deserve the protection that broker oversight provides, regardless of which plan they choose." ),
			$value( '03', 'Transparency is a design requirement', "Hidden fees, terms revealed at checkout, and fine print that changes the deal are endemic to the flat fee MLS space. Fortify's pricing, agreement terms, and cancellation policy are disclosed before you enter your address. No surprises is a design requirement, not a goal." ),
			$value( '04', 'Support that meets the moment', "The moments that matter most in a home sale — receiving an offer, navigating an inspection, understanding a contract — are the moments sellers most need support. Fortify's plan structure puts broker guidance at those exact moments for sellers who want it, without requiring everyone to pay for it." ),
		) ),
	), 64, 64, $lav_bg ),

	$cta( 'about-cta', 'See what Fortify looks like for your home',
		'Enter your address and receive a broker-reviewed listing preview — free, no commitment, no plan selection required. Then decide.',
		$equity( 'Call ' . $phone, $tel ),
		'<p>$0 today · Pay only if your home sells · Cancel before you accept an offer and owe nothing</p>' ),
);

/* ====================================================================
 * Contact
 * ================================================================== */

$channel = static function ( $badge_text, $title, $text, $link_html, $hours = '' ) {
	$children = array(
		fx_heading( $badge_text, 'div', 'fx-channel-badge' ),
		fx_heading( $title, 'h3' ),
		fx_text( '<p>' . $text . '</p>', 'fx-lead' ),
		fx_text( '<p>' . $link_html . '</p>', 'fx-channel-link' ),
	);
	if ( $hours ) {
		$children[] = fx_text( '<p>' . $hours . '</p>', 'fx-hours' );
	}
	return fx_box( 'Channel: ' . $title, 'fx-channel', $children );
};

$contact = array(
	$hero( 'contact-hero', 'Contact', 'We’re here when you need us.',
		'Questions about your listing, your preview, or how Fortify works? Reach out through any of the channels below and a licensed broker or support team member will get back to you.' ),

	$sec( 'contact-body', 'Contact channels', '', array(
		$grid( 'Contact grid', '', 'minmax(0,1.25fr) minmax(0,1fr)', 1, 1, 40, array(
			fx_box( 'Channels', '', array(
				$channel( 'Fastest response', 'Email Support', 'For listing questions, account help, or anything else — email is the best way to reach us. We typically respond within one business day.', '<a href="mailto:support@fortifylistings.com">support@fortifylistings.com</a>' ),
				$channel( 'Phone', 'Call or Text', 'Prefer to talk? Call or text our listing support line. Available Monday–Friday, 9am–5pm ET.', '<a href="' . $tel . '">' . $phone . '</a>', 'Mon–Fri · 9am–5pm ET' ),
				$channel( 'Self-service', 'Help Center', 'Find answers to common questions about the listing process, MLS rules, plans, and more — available any time.', '<a href="' . esc_url( $help ) . '">Visit help.fortifylistings.com</a>' ),
			), array( 'flex_gap' => fx_gap( 18 ) ) ),
			fx_box( 'Start and brokerage', '', array(
				fx_box( 'Start box', 'fx-start-box', array(
					fx_heading( 'Ready to see your free listing preview?', 'h2', '', array(), true ),
					fx_text( '<p>Enter your address and a licensed Florida broker will prepare your personalized MLS listing preview — no signup, no commitment, $0 today.</p>', 'fx-lead' ),
					fx_button( 'See my free listing preview', $start, 'indigo' ),
					fx_text( '<p>Pay only if you list · One flat fee at closing</p>', 'fx-hours' ),
				) ),
				fx_box( 'Brokerage information', 'fx-broker-info', array(
					fx_heading( 'Brokerage Information', 'h3' ),
					fx_text( '<p>Fortify Listing Services LLC<br>Sarasota, FL<br>FL Brokerage License #CQ1074119<br><a href="' . esc_url( $lic_url ) . '" target="_blank" rel="noopener noreferrer">Verify license →</a></p>', 'fx-lead' ),
				) ),
			) ),
		), array( 'grid_align_items' => 'start', 'grid_gaps_tablet' => fx_gap( 32, 32 ) ) ),
	), 64, 88 ),
);

/* ====================================================================
 * Legal pages
 * ================================================================== */

$contact_callout = '<div class="callout"><p><strong>Fortify Listing Services LLC</strong><br>Sarasota, FL<br><a href="mailto:support@fortifylistings.com">support@fortifylistings.com</a></p></div>';

$legal_page = static function ( $prefix, $title, $sub, $updated, $body, array $after_sub = array() ) use ( $hero, $sec ) {
	$after = $after_sub;
	if ( $updated ) {
		$after[] = fx_text( '<p>' . $updated . '</p>', 'fx-page-updated' );
	}
	return array(
		$hero( $prefix . '-hero', 'Legal', $title, $sub, $after ),
		$sec( $prefix . '-body', 'Legal text', '', array( fx_text( $body, 'fx-legal' ) ), 64, 88 ),
	);
};

$legal = array(
	'fortify-equal-housing-statement' => array( 'Equal Housing Opportunity Statement', $legal_page( 'legal-ehs', 'Equal Housing Opportunity Statement', 'Fortify Listing Services is committed to fair housing for all.', '',
		'<h2>Our Commitment</h2><p>Fortify Listing Services LLC is committed to providing equal housing opportunities and fully supports the principles of the Fair Housing Act.</p><p>We do not discriminate against any person based on race, color, religion, sex, disability, familial status, national origin, or any other protected class under applicable federal, state, or local law.</p><p>Fortify Listing Services LLC conducts its real estate services in compliance with all applicable fair housing laws and encourages equal opportunity in housing for all individuals.</p>' .
		'<h2>Report Discrimination</h2><p>If you believe you have experienced discrimination in connection with housing, you may contact the U.S. Department of Housing and Urban Development (HUD):</p><p><a href="https://www.hud.gov/fairhousing">https://www.hud.gov/fairhousing</a></p>' .
		'<div class="callout"><p>Fortify Listing Services LLC is a licensed Florida real estate brokerage (License #CQ1074119). Equal Housing Opportunity.</p></div>',
		array( fx_text( '<p>Equal Housing Opportunity</p>', 'fx-pill' ) ) ) ),

	'fortify-privacy-policy' => array( 'Privacy Policy', $legal_page( 'legal-privacy', 'Privacy Policy', 'How Fortify Listing Services collects, uses, and protects the information you share with us.', 'Effective Date: 07/21/2026',
		'<p class="lead">Fortify Listing Services LLC (“Fortify,” “we,” “our,” or “us”) respects your privacy and is committed to protecting the personal information you provide when using our website and services. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit fortifylistings.com or use our services.</p>' .
		'<h2>1. Information We Collect</h2><p><strong>Personal Information</strong> you voluntarily provide, including:</p><ul><li>Name, email address, and phone number</li><li>Property address</li><li>Account login credentials</li><li>Payment information (processed securely through third-party payment providers)</li></ul><p><strong>Listing Information</strong> submitted when listing a property, including property details, photos and media, pricing information, and transaction status updates.</p><p><strong>Automatically Collected Data</strong> when you visit our website, including IP address, browser type, device type, pages visited, referring websites, and cookies and analytics data.</p>' .
		'<h2>2. How We Use Your Information</h2><p>We use collected information to:</p><ul><li>Provide and operate our services</li><li>Process listing submissions</li><li>Communicate with users regarding listings or support</li><li>Process payments and transactions</li><li>Improve our website and services</li><li>Prevent fraud or misuse of the platform</li><li>Comply with legal and regulatory requirements</li></ul>' .
		'<h2>3. Sharing of Information</h2><p>We do not sell your personal information. We may share information with:</p><ul><li>MLS systems for listing publication</li><li>Service providers (hosting, analytics, payment processing)</li><li>Legal authorities if required by law</li><li>Our brokerage staff to process listing services</li></ul>' .
		'<h2>4. SMS Messaging and Mobile Information</h2><p>Mobile information will not be shared with third parties or affiliates for marketing or promotional purposes. All other categories exclude text messaging originator opt-in data and consent; this information will not be shared with any third parties.</p>' .
		'<h2>5. Cookies and Tracking Technologies</h2><p>We may use cookies and similar technologies to maintain account sessions, analyze website traffic, and improve user experience. Users may adjust browser settings to refuse cookies, although some features of the website may not function properly.</p>' .
		'<h2>6. Data Security</h2><p>We implement reasonable administrative, technical, and physical safeguards designed to protect personal information. However, no internet transmission or storage system can be guaranteed to be completely secure.</p>' .
		'<h2>7. Third-Party Services</h2><p>Our website may integrate with third-party services including Stripe (payment processing), analytics providers, and MLS listing platforms. These providers maintain their own privacy policies governing how they process data.</p>' .
		'<h2>8. User Accounts</h2><p>Users are responsible for maintaining the confidentiality of their account credentials. Fortify is not responsible for unauthorized account access resulting from user negligence.</p>' .
		'<h2>9. Your Rights</h2><p>You may request to access your personal information, correct inaccurate information, or request deletion of your account data where permitted by law. Requests may be submitted through our contact information below.</p>' .
		'<h2>10. Changes to This Policy</h2><p>We may update this Privacy Policy from time to time. Updates will be posted on this page with the revised effective date.</p>' .
		'<h2>11. Contact</h2>' . $contact_callout ) ),

	'fortify-terms-of-service' => array( 'Terms of Service', $legal_page( 'legal-terms', 'Terms of Service', 'The terms that govern your use of fortifylistings.com and Fortify Listing Services.', 'Effective Date: April 1, 2026',
		'<p class="lead">These Terms of Service (“Terms”) govern your use of the website and services provided by Fortify Listing Services LLC (“Fortify,” “we,” “our,” or “us”). By accessing or using our website or services, you agree to these Terms.</p>' .
		'<h2>1. Description of Services</h2><p>Fortify Listing Services provides brokerage-supported real estate listing services that allow property owners to submit properties for listing on participating Multiple Listing Services (MLS) and related marketing platforms. Fortify operates as a licensed real estate brokerage in the State of Florida.</p>' .
		'<h2>2. Eligibility</h2><p>Users must be at least 18 years of age and legally able to enter into contracts to use our services. By using the website, you represent that all information provided is accurate and complete.</p>' .
		'<h2>3. Listing Submissions</h2><p>Users submitting property listings agree that:</p><ul><li>The information provided is accurate and truthful</li><li>They have authority to list the property</li><li>Submitted content does not violate any laws or MLS rules</li></ul><p>Fortify reserves the right to reject or remove listings that violate applicable rules or policies.</p>' .
		'<h2>4. Brokerage Relationship</h2><p>Submitting information through the Fortify platform does not automatically create a brokerage relationship. A brokerage relationship is established only upon execution of a formal listing agreement between the property owner and Fortify Listing Services LLC.</p>' .
		'<h2>5. Fees and Payments</h2><p>Service fees and commission structures are disclosed through our pricing materials and listing agreements. Payments may be processed through third-party payment processors.</p>' .
		'<h2>6. User Conduct</h2><p>Users agree not to:</p><ul><li>Submit false or misleading information</li><li>Attempt unauthorized access to the platform</li><li>Interfere with system functionality</li><li>Use the platform for unlawful purposes</li></ul>' .
		'<h2>7. Intellectual Property</h2><p>All content on this website, including text, logos, graphics, and software, is owned by Fortify Listing Services LLC or its licensors and may not be reproduced without permission.</p>' .
		'<h2>8. Disclaimer of Warranties</h2><p>The website and services are provided “as is” and “as available” without warranties of any kind, express or implied.</p>' .
		'<h2>9. Limitation of Liability</h2><p>To the maximum extent permitted by law, Fortify Listing Services LLC shall not be liable for any indirect, incidental, or consequential damages arising from the use of our website or services.</p>' .
		'<h2>10. Governing Law</h2><p>These Terms are governed by the laws of the State of Florida.</p>' .
		'<h2>11. Modifications</h2><p>We reserve the right to update these Terms at any time. Continued use of the website constitutes acceptance of the updated Terms.</p>' .
		'<h2>12. Contact</h2>' . $contact_callout ) ),

	'fortify-payment-terms' => array( 'Statement of Charges & Payment Terms', $legal_page( 'legal-payment', 'Statement of Charges &amp; Payment Terms', 'How Fortify’s service fees, payment timing, and cancellation terms work.', 'Effective Date: April 1, 2026',
		'<div class="callout"><p>This document is provided for informational purposes. Actual fees and obligations are governed by the executed listing agreement between the seller and Fortify Listing Services LLC.</p></div>' .
		'<h2>1. Service Fees</h2><p>Fortify Listing Services offers listing services through various service plans. The applicable service plan and pricing will be disclosed prior to entering into a listing agreement. Service fees may include:</p><ul><li>Listing service fees</li><li>Optional service add-ons</li><li>Transaction coordination services</li><li>Buyer representation services</li><li>Administrative or processing fees</li></ul><p>The specific services included depend on the selected plan.</p>' .
		'<h2>2. Payment Timing</h2><p>Unless otherwise stated in the listing agreement, primary service fees are typically payable at closing of the property sale. These fees are normally paid through the settlement agent from the seller’s proceeds at closing.</p>' .
		'<h2>3. Optional Services</h2><p>Optional services selected by the seller may require payment at the time of ordering or at closing, depending on the service. Examples may include:</p><ul><li>Professional photography</li><li>Yard signage</li><li>Marketing upgrades</li><li>Contract negotiation support</li></ul>' .
		'<h2>4. Payment Authorization</h2><p>Users of the Fortify platform may be asked to provide a valid payment method to authorize certain charges permitted under the listing agreement. Providing payment information authorizes Fortify to charge applicable fees consistent with the terms of the listing agreement.</p>' .
		'<h2>5. Cancellation and Early Termination</h2><p>If a listing agreement is cancelled, withdrawn, or terminated prior to expiration, certain fees may apply. These may include:</p><ul><li>Administrative processing fees</li><li>Reimbursement for services already performed</li><li>Agreed cancellation fees as outlined in the listing agreement</li></ul><p>As of the effective date of these terms, all cancellation fees are fixed at <strong>$149.00</strong>. Applicable cancellation terms will be specified in the executed listing agreement.</p>' .
		'<h2>6. Seller Withdrawal or Off-Platform Sale</h2><p>If a seller withdraws the property from the market or sells the property outside of the listing agreement during the listing term, fees may apply as specified in the listing agreement.</p>' .
		'<h2>7. MLS Compliance</h2><p>Listings submitted through Fortify must comply with applicable MLS rules and brokerage policies. Failure to comply may result in listing suspension or additional administrative requirements.</p>' .
		'<h2>8. Changes to Fees</h2><p>Fortify reserves the right to update service pricing and fee structures. Any such changes will apply only to new listing agreements and will not retroactively affect executed agreements.</p>' .
		'<h2>9. Contact</h2>' . $contact_callout ) ),
);

/* ====================================================================
 * Sample listing agreement
 * ================================================================== */

$sla_actions = array();
if ( $sla_pdf ) {
	$sla_actions[] = fx_button( 'Download PDF', $sla_pdf, 'primary' );
}
$sla_actions[] = fx_button( 'See my free listing preview', $start, 'ghost' );
$sla_actions[] = fx_text( '<p>FL Brokerage License #CQ1074119</p>', 'fx-trust-note' );

$sla_viewer = $sla_pdf
	? fx_html( 'PDF viewer', '<object data="' . esc_url( $sla_pdf ) . '" type="application/pdf" style="display:block;width:100%;height:820px;border:0"><p style="padding:40px;text-align:center"><a href="' . esc_url( $sla_pdf ) . '">Download the agreement (PDF)</a></p></object>' )
	: fx_box( 'PDF placeholder', 'fx-pdf-empty', array(
		fx_heading( 'Sample agreement PDF not added yet', 'h3' ),
		fx_text( '<p>This demo does not have the agreement file. Once the PDF is in the media library, set its address in the build script and the page shows it here with a download button.</p>', 'fx-lead' ),
	), array( 'flex_align_items' => 'center' ) );

$sla_context = static function ( $title, $html ) {
	return fx_box( 'Context: ' . $title, 'fx-context', array( fx_heading( $title, 'h3' ), fx_text( $html, 'fx-lead' ) ) );
};

$sla = array(
	$hero( 'sla-hero', 'No surprises', 'Read the agreement before you commit to anything',
		'Most flat-fee MLS services hide the listing agreement until after checkout. We publish ours here — every term, every condition, before you enter your address. This is the actual Florida listing agreement Fortify uses.',
		array( $btn_row( $sla_actions, 'flex-start', 'fx-sla-actions' ) ) ),

	$sec( 'sla-document', 'Agreement document', '', array(
		fx_box( 'Document', 'fx-pdf', array(
			fx_text( '<p>Fortify Listings — Sample Listing Agreement</p>', 'fx-pdf-bar' ),
			$sla_viewer,
		) ),
	), 48, 48 ),

	$sec( 'sla-context', 'About the agreement', '', array(
		$grid( 'Context cards', '', 3, 3, 1, 22, array(
			$sla_context( 'What this agreement covers', '<p>This is the Florida standard listing agreement Fortify uses for all MLS listings. It covers listing period, flat fee, cancellation terms, seller responsibilities, buyer-agent compensation, and broker authority. Read it front to back before you commit.</p>' ),
			$sla_context( 'When do I sign?', '<p>You sign a personalized version of this agreement — pre-filled with your property address, list price, and selected plan — after you review your listing preview and choose to move forward. You sign digitally; nothing is due until then.</p>' ),
			$sla_context( 'Questions about specific terms?', '<p>Read our <a href="/faqs/">FAQ</a> for plain-language answers on the flat fee, cancellation, buyer-agent compensation, and what happens if your home doesn’t sell. Or <a href="/contact/">contact us</a> directly — we’ll explain any clause.</p>' ),
		) ),
		fx_con( array(
			'_title'                => 'CTA strip',
			'css_classes'           => 'fx-cta-strip',
			'flex_direction'        => 'row',
			'flex_wrap'             => 'wrap',
			'flex_justify_content'  => 'space-between',
			'flex_align_items'      => 'center',
			'flex_gap'              => fx_gap( 30 ),
		), array(
			fx_box( 'CTA copy', '', array(
				fx_heading( 'Ready to see your listing preview?', 'h2', '', array(), true ),
				fx_text( '<p>$0 today. No signup required. Your broker-prepared listing preview is ready in a few hours.</p>', 'fx-lead' ),
			), array( 'width' => array( 'unit' => 'custom', 'size' => 'auto' ) ) ),
			fx_button( 'See my free listing preview', $start, 'indigo', 'lg' ),
		) ),
	), 0, 88 ),
);

/* ====================================================================
 * Preview stub
 * ================================================================== */

$preview = array(
	$hero( 'preview-hero', 'Free listing preview', 'Your preview request',
		'Thanks. This is where a broker-reviewed preview of your home begins.',
		array( fx_w( 'shortcode', array( 'shortcode' => '[fortify_preview_address]', '_css_classes' => 'fx-preview-got' ) ) ) ),

	$sec( 'preview-body', 'Demo stub', '', array(
		fx_box( 'Demo note', 'fx-stub', array(
			fx_heading( 'This demo stops here', 'h2' ),
			fx_text( '<p>On fortifylistings.com this step hands your address to the Fortify app, which prepares the broker-reviewed preview and emails it to you. That flow is not part of this demo site, and nothing you enter here is stored.</p><p>Try another address:</p>', 'fx-lead' ),
			fx_w( 'fortify-address-field', array(
				'placeholder' => 'Enter your address or ZIP',
				'button_text' => 'Get my free preview',
				'action'      => '/preview/',
				'variant'     => 'light',
			) ),
		) ),
	), 64, 88 ),
);

/* ====================================================================
 * Save
 * ================================================================== */

$pages = array(
	'how-it-works'                     => array( 'How It Works', $hiw ),
	'plans'                            => array( 'Plans & Pricing', $plans ),
	'faqs'                             => array( 'FAQs', $faqs ),
	'about'                            => array( 'About', $about ),
	'contact'                          => array( 'Contact', $contact ),
	'fortify-sample-listing-agreement' => array( 'Sample Listing Agreement', $sla ),
	'preview'                          => array( 'Preview', $preview ),
);
foreach ( $legal as $slug => $l ) {
	$pages[ $slug ] = array( $l[0], $l[1] );
}

foreach ( $pages as $slug => $p ) {
	$id = fx_owned_post( 'page-' . $slug, array(
		'post_type'     => 'page',
		'post_status'   => 'publish',
		'post_title'    => $p[0],
		'post_name'     => $slug,
		'page_template' => 'elementor_header_footer',
	) );
	fx_save_elementor( $id, $p[1], 'wp-page', array( 'hide_title' => 'yes' ) );
	update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
	$actual = get_post_field( 'post_name', $id );
	if ( $actual !== $slug ) {
		WP_CLI::warning( "Page $id got slug '$actual' instead of '$slug' (another post has it)." );
	}
	WP_CLI::line( sprintf( '%-34s %d  %s', $slug, $id, get_permalink( $id ) ) );
}

\Elementor\Plugin::$instance->files_manager->clear_cache();
WP_CLI::success( count( $pages ) . ' pages built.' );
