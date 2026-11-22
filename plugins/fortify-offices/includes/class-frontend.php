<?php
/**
 * Front-end pieces: widget styles and scripts, and local business structured
 * data on office sites.
 */

namespace FortifyOffices;

defined( 'ABSPATH' ) || exit;

class Frontend {

	/** Leaflet from a CDN, pinned. Tiles come from OpenStreetMap. */
	const LEAFLET = '1.9.4';

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ), 5 );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'preview_styles' ) );
		add_action( 'wp_head', array( __CLASS__, 'structured_data' ) );
	}

	public static function asset_version( $relative ) {
		$file = FORTIFY_OFFICES_DIR . '/' . $relative;
		return FORTIFY_OFFICES_VERSION . ( file_exists( $file ) ? '.' . filemtime( $file ) : '' );
	}

	public static function register_assets() {
		$url = plugins_url( '', FORTIFY_OFFICES_FILE );
		wp_register_style( 'fortify-offices', $url . '/assets/fortify-offices.css', array(), self::asset_version( 'assets/fortify-offices.css' ) );
		wp_register_style( 'leaflet', 'https://unpkg.com/leaflet@' . self::LEAFLET . '/dist/leaflet.css', array(), self::LEAFLET );
		wp_register_script( 'leaflet', 'https://unpkg.com/leaflet@' . self::LEAFLET . '/dist/leaflet.js', array(), self::LEAFLET, array( 'in_footer' => true ) );
		wp_register_script( 'fortify-offices-finder', $url . '/assets/finder.js', array( 'leaflet' ), self::asset_version( 'assets/finder.js' ), array( 'in_footer' => true ) );
		// The registry widgets appear in the site footer, so the small stylesheet loads everywhere.
		wp_enqueue_style( 'fortify-offices' );
	}

	public static function preview_styles() {
		self::register_assets();
		wp_enqueue_style( 'leaflet' );
	}

	/** RealEstateAgent JSON-LD on an office site's front page. */
	public static function structured_data() {
		if ( ! is_front_page() ) {
			return;
		}
		$o = fortify_office_for_site();
		if ( ! $o || 'live' !== $o['status'] ) {
			return;
		}
		$b    = fortify_offices_brokerage();
		$data = array(
			'@context'  => 'https://schema.org',
			'@type'     => 'RealEstateAgent',
			'name'      => trim( ( $b['name'] ? $b['name'] . ' — ' : '' ) . $o['name'] ),
			'url'       => $o['url'],
			'telephone' => $o['phone'],
			'address'   => array( '@type' => 'PostalAddress', 'streetAddress' => $o['address'], 'addressRegion' => $o['state'], 'addressCountry' => 'US' ),
			'areaServed' => array_map( static function ( $c ) use ( $o ) {
				return array( '@type' => 'AdministrativeArea', 'name' => $c . ', ' . $o['state_name'] );
			}, $o['counties'] ),
		);
		if ( null !== $o['lat'] && null !== $o['lng'] ) {
			$data['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => $o['lat'], 'longitude' => $o['lng'] );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	}
}
