<?php
/**
 * Public API. Any site in the network can call these; records come from the
 * main site's registry.
 */

defined( 'ABSPATH' ) || exit;

use FortifyOffices\Registry;

/**
 * Office records.
 *
 * @param array $args { @type string[] $status Statuses to include. Default live and coming_soon.
 *                      @type string   $state  Two-letter state filter. }
 * @return array[]
 */
function fortify_offices( array $args = array() ) {
	$status = $args['status'] ?? array( 'live', 'coming_soon' );
	$state  = isset( $args['state'] ) ? strtoupper( $args['state'] ) : '';
	return array_values( array_filter( Registry::all(), static function ( $o ) use ( $status, $state ) {
		return in_array( $o['status'], (array) $status, true ) && ( '' === $state || $o['state'] === $state );
	} ) );
}

/** One office by slug or post ID (any status), or null. */
function fortify_office( $slug_or_id ) {
	foreach ( Registry::all() as $o ) {
		if ( $o['slug'] === $slug_or_id || $o['id'] === (int) $slug_or_id ) {
			return $o;
		}
	}
	return null;
}

/**
 * The site whose office data widgets should show: the current site, or, while
 * Sitelets renders a corporate page for an office site (its 404 fallback,
 * 1.1.4+), that office site.
 */
function fortify_offices_context_blog_id() {
	return isset( $GLOBALS['sitelets_fallback_origin_blog_id'] ) ? (int) $GLOBALS['sitelets_fallback_origin_blog_id'] : get_current_blog_id();
}

/** The office whose site is $blog_id (default: the context site), or null on the corporate site. */
function fortify_office_for_site( $blog_id = null ) {
	$blog_id = $blog_id ? (int) $blog_id : fortify_offices_context_blog_id();
	foreach ( Registry::all() as $o ) {
		if ( $o['blog_id'] && $o['blog_id'] === $blog_id && 'hidden' !== $o['status'] ) {
			return $o;
		}
	}
	return null;
}

/** The office covering a ZIP (live or coming soon), or null when not covered. Accepts free text. */
function fortify_office_for_zip( $zip ) {
	$zip = Registry::extract_zip( $zip );
	if ( '' === $zip ) {
		return null;
	}
	foreach ( fortify_offices() as $o ) {
		if ( in_array( $zip, $o['zips'], true ) ) {
			return $o;
		}
	}
	return null;
}

/**
 * Offices matching a search: a ZIP, or text matched against office name, city,
 * county and state.
 */
function fortify_offices_search( $query, $state = '' ) {
	$query = trim( (string) $query );
	if ( '' === $query ) {
		return fortify_offices( array( 'state' => $state ) );
	}
	if ( preg_match( '/^\d{5}/', $query ) ) {
		$o = fortify_office_for_zip( $query );
		return $o && ( '' === $state || $o['state'] === strtoupper( $state ) ) ? array( $o ) : array();
	}
	$needle = strtolower( remove_accents( $query ) );
	$needle = preg_replace( '/\s+county$/', '', $needle );
	return array_values( array_filter( fortify_offices( array( 'state' => $state ) ), static function ( $o ) use ( $needle ) {
		$hay = strtolower( remove_accents( implode( ' | ', array_merge( array( $o['name'], $o['city'], $o['state'], $o['state_name'] ), $o['counties'] ) ) ) );
		return false !== strpos( $hay, $needle );
	} ) );
}

/** Corporate brokerage details (network option `fortify_offices_brokerage`). */
function fortify_offices_brokerage() {
	$b = get_site_option( 'fortify_offices_brokerage', array() );
	return wp_parse_args( is_array( $b ) ? $b : array(), array(
		'name'     => '',
		'city'     => '',
		'licenses' => array(),
	) );
}

/** Where unmatched ZIPs are sent: the corporate contact page. */
function fortify_offices_contact_url() {
	return apply_filters( 'fortify_offices_contact_url', network_home_url( '/contact/' ) );
}
