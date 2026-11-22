<?php
/**
 * The office registry: an `office` post type on the main site, its fields,
 * and a read of all records that any site can use.
 */

namespace FortifyOffices;

defined( 'ABSPATH' ) || exit;

class Registry {

	const POST_TYPE = 'office';
	const CACHE_KEY = 'fortify_offices_all';
	const STATUSES  = array( 'live', 'coming_soon', 'hidden' );

	/** Field key => label. Stored as post meta `_fo_{key}`; title is the office name, slug the post name. */
	const FIELDS = array(
		'blog_id'  => 'Site (blog) ID',
		'state'    => 'State (two letters)',
		'city'     => 'City (for addresses and the map)',
		'mls'      => 'MLS name',
		'counties' => 'Counties (one per line)',
		'zips'     => 'ZIP codes (comma or line separated)',
		'phone'    => 'Phone',
		'address'  => 'Address',
		'hours'    => 'Hours',
		'broker'   => 'Broker of record',
		'license'  => 'State license number',
		'lat'      => 'Latitude',
		'lng'      => 'Longitude',
		'status'   => 'Status',
	);

	const STATE_NAMES = array(
		'FL' => 'Florida',
		'AZ' => 'Arizona',
		'TN' => 'Tennessee',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'flush' ) );
		add_action( 'deleted_post', array( __CLASS__, 'flush' ) );
		add_action( 'trashed_post', array( __CLASS__, 'flush' ) );
		// Scripts that set fields directly (phase 6 sets blog_id) must not leave a stale cache.
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'flush_on_meta' ), 10, 3 );
		}
	}

	public static function flush_on_meta( $meta_id, $object_id, $meta_key ) {
		if ( is_string( $meta_key ) && 0 === strpos( $meta_key, '_fo_' ) ) {
			self::flush();
		}
	}

	/** The post type lives on the main site only; other sites read it through all(). */
	public static function register_post_type() {
		if ( ! is_main_site() ) {
			return;
		}
		register_post_type( self::POST_TYPE, array(
			'labels'       => array(
				'name'          => __( 'Offices', 'fortify-offices' ),
				'singular_name' => __( 'Office', 'fortify-offices' ),
				'add_new_item'  => __( 'Add office', 'fortify-offices' ),
				'edit_item'     => __( 'Edit office', 'fortify-offices' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => false,
			'menu_icon'    => 'dashicons-location',
			'supports'     => array( 'title' ),
			'rewrite'      => false,
		) );
	}

	/** Records read in this request (null until first read). */
	private static $records = null;

	public static function flush() {
		self::$records = null;
		delete_site_transient( self::CACHE_KEY ); // Cache used by 0.2.0 and earlier.
	}

	/** Split a list typed as lines or commas into clean values. */
	public static function split_list( $value ) {
		if ( is_array( $value ) ) {
			return array_values( array_filter( array_map( 'trim', $value ), 'strlen' ) );
		}
		return array_values( array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', (string) $value ) ), 'strlen' ) );
	}

	/**
	 * Every office record (hidden ones included), sorted by state then name.
	 * Read from the main site once per request. (A network-wide transient was
	 * used before 0.2.1; it could keep a stale site ID after an office site
	 * was created, so it was dropped: twelve records are cheap to read.)
	 */
	public static function all() {
		if ( is_array( self::$records ) ) {
			return self::$records;
		}

		$switched = ! is_main_site();
		if ( $switched ) {
			switch_to_blog( get_main_site_id() );
		}
		$posts = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );
		$offices = array();
		foreach ( $posts as $p ) {
			$offices[] = self::to_record( $p );
		}
		if ( $switched ) {
			restore_current_blog();
		}

		$order = array_keys( self::STATE_NAMES );
		usort( $offices, static function ( $a, $b ) use ( $order ) {
			$sa = array_search( $a['state'], $order, true );
			$sb = array_search( $b['state'], $order, true );
			$sa = false === $sa ? 99 : $sa;
			$sb = false === $sb ? 99 : $sb;
			return $sa === $sb ? strcasecmp( $a['name'], $b['name'] ) : $sa - $sb;
		} );

		self::$records = $offices;
		return $offices;
	}

	private static function to_record( \WP_Post $p ) {
		$m = static function ( $key ) use ( $p ) {
			return get_post_meta( $p->ID, '_fo_' . $key, true );
		};
		$state  = strtoupper( (string) $m( 'state' ) );
		$status = in_array( $m( 'status' ), self::STATUSES, true ) ? $m( 'status' ) : 'hidden';
		$record = array(
			'id'         => $p->ID,
			'name'       => $p->post_title,
			'slug'       => $p->post_name,
			'blog_id'    => (int) $m( 'blog_id' ),
			'state'      => $state,
			'state_name' => self::STATE_NAMES[ $state ] ?? $state,
			'city'       => (string) $m( 'city' ),
			'mls'        => (string) $m( 'mls' ),
			'counties'   => self::split_list( $m( 'counties' ) ),
			'zips'       => self::split_list( $m( 'zips' ) ),
			'phone'      => (string) $m( 'phone' ),
			'address'    => (string) $m( 'address' ),
			'hours'      => (string) $m( 'hours' ),
			'broker'     => (string) $m( 'broker' ),
			'license'    => (string) $m( 'license' ),
			'lat'        => '' === $m( 'lat' ) ? null : (float) $m( 'lat' ),
			'lng'        => '' === $m( 'lng' ) ? null : (float) $m( 'lng' ),
			'status'     => $status,
		);
		$record['url'] = self::url_for( $record );
		return $record;
	}

	/**
	 * Office site URL: the real site once it exists (blog_id set), otherwise the
	 * subdirectory it will get. Until phase 6 those links 404.
	 */
	public static function url_for( array $office ) {
		if ( $office['blog_id'] && get_site( $office['blog_id'] ) ) {
			return trailingslashit( get_home_url( $office['blog_id'] ) );
		}
		return trailingslashit( network_home_url( $office['slug'] ) );
	}

	/** Five-digit ZIP from free text ("123 Main St, Tampa FL 33602" or "33602-1234"). */
	public static function extract_zip( $text ) {
		return preg_match( '/(?<!\d)(\d{5})(?:-\d{4})?(?!\d)/', (string) $text, $m ) ? $m[1] : '';
	}
}
