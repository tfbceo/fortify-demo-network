<?php
/**
 * Element helpers shared by the build scripts: they return Elementor element
 * arrays (containers and widgets) and save them to posts the scripts own.
 * Loaded with require_once by scripts/build-*.php.
 */

defined( 'ABSPATH' ) || exit;

/* ====================================================================
 * Element helpers
 * ================================================================== */

function fx_eid() {
	return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
}

function fx_con( array $settings, array $children = array(), $inner = true ) {
	$defaults = array( 'content_width' => 'full', 'flex_gap' => fx_gap( 0 ) );
	if ( $inner ) {
		// The kit's 24px side padding is for top-level sections; inner containers get none.
		$defaults['padding'] = fx_pad( 0, 0, 0, 0 );
	}
	return array(
		'id'       => fx_eid(),
		'elType'   => 'container',
		'isInner'  => $inner,
		'settings' => array_merge( $defaults, $settings ),
		'elements' => $children,
	);
}

function fx_w( $type, array $settings ) {
	return array(
		'id'         => fx_eid(),
		'elType'     => 'widget',
		'widgetType' => $type,
		'isInner'    => false,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

function fx_pad( $t, $r, $b, $l ) {
	return array( 'unit' => 'px', 'top' => (string) $t, 'right' => (string) $r, 'bottom' => (string) $b, 'left' => (string) $l, 'isLinked' => false );
}

function fx_gap( $row, $col = null ) {
	$col = null === $col ? $row : $col;
	return array( 'unit' => 'px', 'row' => (string) $row, 'column' => (string) $col, 'isLinked' => $row === $col, 'size' => $row );
}

/** Grid container. $cols: number of equal columns or a custom template string. */
function fx_grid( $cols, $cols_tablet, $gap_row, $gap_col, array $children, array $extra = array() ) {
	$c = is_int( $cols ) ? array( 'unit' => 'fr', 'size' => $cols ) : array( 'unit' => 'custom', 'size' => $cols );
	$t = is_int( $cols_tablet ) ? array( 'unit' => 'fr', 'size' => $cols_tablet ) : array( 'unit' => 'custom', 'size' => $cols_tablet );
	return fx_con(
		array_merge(
			array(
				'container_type'           => 'grid',
				'grid_columns_grid'        => $c,
				'grid_columns_grid_tablet' => $t,
				'grid_rows_grid'           => array( 'unit' => 'custom', 'size' => 'auto' ),
				'grid_gaps'                => fx_gap( $gap_row, $gap_col ),
				'grid_auto_flow'           => 'row',
			),
			$extra
		),
		$children
	);
}

/** Top-level section: boxed, 88px top/bottom (64px at Tablet and below). */
function fx_section( $css_id, $title, $classes, array $children, array $extra = array() ) {
	return fx_con(
		array_merge(
			array(
				'_title'         => $title,
				'_element_id'    => $css_id,
				'css_classes'    => trim( 'fx-section ' . $classes ),
				'html_tag'       => 'section',
				'content_width'  => 'boxed',
				'flex_direction' => 'column',
				'padding'        => fx_pad( 88, 24, 88, 24 ),
				'padding_tablet' => fx_pad( 64, 24, 64, 24 ),
			),
			$extra
		),
		$children,
		false
	);
}

/**
 * Heading widget. Real headings (h1–h6) get the kit's Ink color, or Paper when
 * $on_dark; Elementor's own default would be Primary (indigo). Label-style
 * headings (tag div/p) keep the default and are colored by fortify.css.
 */
function fx_heading( $text, $tag = 'h2', $classes = '', array $extra = array(), $on_dark = false ) {
	$s = array( 'title' => $text, 'header_size' => $tag, '_css_classes' => $classes );
	if ( preg_match( '/^h[1-6]$/', $tag ) ) {
		$s['title_color'] = $on_dark ? '#FFFFFF' : '#1E1B4B';
		$s['__globals__'] = array( 'title_color' => 'globals/colors?id=' . ( $on_dark ? 'paper' : 'secondary' ) );
	}
	return fx_w( 'heading', array_merge( $s, $extra ) );
}

function fx_eyebrow( $text, $classes = '' ) {
	return fx_heading( $text, 'div', trim( 'fx-eyebrow ' . $classes ) );
}

function fx_text( $html, $classes = '', array $extra = array() ) {
	return fx_w( 'text-editor', array_merge( array( 'editor' => $html, '_css_classes' => $classes ), $extra ) );
}

function fx_html( $title, $html, $classes = '' ) {
	return fx_w( 'html', array( '_title' => $title, 'html' => $html, '_css_classes' => $classes ) );
}

/** Button. $variant: primary (ink), indigo, ghost. $size: '', sm, lg. */
function fx_button( $text, $url, $variant = 'indigo', $size = '', $align = 'left', array $extra = array() ) {
	$classes = 'fx-button fx-button--' . $variant . ( $size ? ' fx-button--' . $size : '' );
	return fx_w( 'button', array_merge( array(
		'text'         => $text,
		'link'         => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
		'align'        => $align,
		'_css_classes' => $classes,
	), $extra ) );
}

function fx_icon( $value, $library = 'fa-solid' ) {
	return array( 'value' => $value, 'library' => $library );
}

/**
 * Icon list. Items are strings or arrays with text, url, external, and an
 * optional icon (overrides the list icon for that row). $extra merges into
 * the widget settings (for example 'view' => 'inline').
 */
function fx_list( array $items, $classes, $icon = 'fas fa-check', $library = 'fa-solid', array $extra = array() ) {
	$list = array();
	foreach ( $items as $item ) {
		$row  = is_array( $item ) ? $item : array( 'text' => $item );
		$ico  = array_key_exists( 'icon', $row ) ? $row['icon'] : $icon;
		$list[] = array(
			'_id'           => fx_eid(),
			'text'          => $row['text'],
			'selected_icon' => $ico ? fx_icon( $ico, $row['library'] ?? $library ) : array( 'value' => '', 'library' => '' ),
			'link'          => array( 'url' => $row['url'] ?? '', 'is_external' => ! empty( $row['external'] ) ? 'on' : '', 'nofollow' => '' ),
		);
	}
	return fx_w( 'icon-list', array_merge( array( 'icon_list' => $list, '_css_classes' => $classes ), $extra ) );
}

/** Heading in a kit color other than Ink: $color_id is a kit color id, $hex its value. */
function fx_heading_in( $text, $tag, $classes, $color_id, $hex ) {
	return fx_heading( $text, $tag, $classes, array(
		'title_color' => $hex,
		'__globals__' => array( 'title_color' => 'globals/colors?id=' . $color_id ),
	) );
}

/** Plain flex column container with a CSS class. */
function fx_box( $title, $classes, array $children, array $extra = array() ) {
	return fx_con( array_merge( array( '_title' => $title, 'css_classes' => $classes, 'flex_direction' => 'column', 'flex_gap' => fx_gap( 0 ) ), $extra ), $children );
}

/**
 * Nested accordion (native details/summary). $items: [question, answer HTML].
 * Each answer is a Text Editor widget inside the item's own container.
 * $open_first: first item open on load; otherwise all start closed.
 */
function fx_faq( array $items, $classes = 'fx-faq-cards', $open_first = false ) {
	$repeater = array();
	$children = array();
	foreach ( $items as $q ) {
		$repeater[] = array( '_id' => fx_eid(), 'item_title' => $q[0], 'element_css_id' => '' );
		$children[] = fx_con(
			array( '_title' => 'Answer', 'css_classes' => 'fx-faq-ans', 'flex_direction' => 'column' ),
			array( fx_text( $q[1] ) )
		);
	}
	$w = fx_w( 'nested-accordion', array(
		'items'                            => $repeater,
		'title_tag'                        => 'h3',
		'default_state'                    => $open_first ? 'expanded' : 'all_collapsed',
		'max_items_expended'               => 'multiple',
		'accordion_item_title_icon'        => fx_icon( 'fas fa-plus' ),
		'accordion_item_title_icon_active' => fx_icon( 'fas fa-plus' ),
		'_css_classes'                     => $classes,
	) );
	$w['elements'] = $children;
	return $w;
}

/** Find or create the post this script owns under $key. */
function fx_owned_post( $key, array $postarr ) {
	$found = get_posts( array(
		'post_type'      => $postarr['post_type'],
		'post_status'    => 'any',
		'meta_key'       => '_fortify_build_key',
		'meta_value'     => $key,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( $found ) {
		$postarr['ID'] = $found[0];
		wp_update_post( wp_slash( $postarr ) );
		return $found[0];
	}
	$id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id );
	}
	update_post_meta( $id, '_fortify_build_key', $key );
	return $id;
}

function fx_save_elementor( $post_id, array $elements, $template_type, array $page_settings = array() ) {
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', $template_type );
	update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
	update_post_meta( $post_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
	if ( $page_settings ) {
		update_post_meta( $post_id, '_elementor_page_settings', $page_settings );
	}
	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_element_cache' );
}
