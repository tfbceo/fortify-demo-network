<?php
/**
 * Phase 4: seed the office registry on site 1.
 *
 *   wp eval-file scripts/seed-offices.php
 *
 * Reads plugins/fortify-offices/data/offices.json (records, sample contact
 * fields) and office-zips.json (ZIP codes from the Census relationship file,
 * built by scripts/tools/build-zip-map.py). Re-runnable: updates records by
 * slug. Also sets the corporate brokerage details used by the Licensing Block.
 * blog_id is left alone, so a value set in phase 6 survives a re-seed.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_main_site() ) {
	WP_CLI::error( 'Run on the main site (site 1).' );
}
if ( ! class_exists( '\FortifyOffices\Registry' ) ) {
	WP_CLI::error( 'Network-activate the fortify-offices plugin first.' );
}

$data_dir = dirname( __DIR__ ) . '/plugins/fortify-offices/data';
$seed     = json_decode( file_get_contents( $data_dir . '/offices.json' ), true );
$zipmap   = json_decode( file_get_contents( $data_dir . '/office-zips.json' ), true );
if ( ! $seed || ! $zipmap ) {
	WP_CLI::error( 'Could not read the seed files in ' . $data_dir );
}

// Every ZIP must belong to exactly one office.
$owner = array();
foreach ( $zipmap['offices'] as $slug => $zips ) {
	foreach ( $zips as $z ) {
		if ( isset( $owner[ $z ] ) ) {
			WP_CLI::error( "ZIP $z is assigned to both {$owner[ $z ]} and $slug." );
		}
		$owner[ $z ] = $slug;
	}
}

foreach ( $seed['offices'] as $o ) {
	$zips = $zipmap['offices'][ $o['slug'] ] ?? array();
	if ( ! $zips ) {
		WP_CLI::warning( "No ZIP codes for {$o['slug']}." );
	}
	$existing = get_posts( array(
		'post_type'      => 'office',
		'name'           => $o['slug'],
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	$postarr = array(
		'post_type'   => 'office',
		'post_status' => 'publish',
		'post_title'  => $o['name'],
		'post_name'   => $o['slug'],
	);
	if ( $existing ) {
		$postarr['ID'] = $existing[0];
	}
	$id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id );
	}

	$meta = array(
		'state'    => $o['state'],
		'city'     => $o['city'],
		'mls'      => $o['mls'],
		'counties' => $o['counties'],
		'zips'     => $zips,
		'phone'    => $o['phone'],
		'address'  => sprintf( '100 Example Ave, %s, %s %s', $o['city'], $o['state'], $o['zip'] ),
		'hours'    => $seed['defaults']['hours'],
		'broker'   => $seed['defaults']['broker'],
		'license'  => sprintf( '%s-SAMPLE-%05d', $o['state'], $o['license_no'] ),
		'lat'      => (string) $o['lat'],
		'lng'      => (string) $o['lng'],
		'status'   => $o['status'],
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, '_fo_' . $k, $v );
	}
	if ( '' === get_post_meta( $id, '_fo_blog_id', true ) ) {
		update_post_meta( $id, '_fo_blog_id', 0 );
	}
	WP_CLI::line( sprintf( '%-17s %4d  %-12s %3d ZIPs', $o['slug'], $id, $o['status'], count( $zips ) ) );
}

update_site_option( 'fortify_offices_brokerage', array(
	'name'     => 'Fortify Listing Services LLC',
	'city'     => 'Sarasota, FL',
	'licenses' => array(
		array(
			'state'  => 'FL',
			'label'  => 'FL Brokerage License',
			'number' => 'CQ1074119',
			'url'    => 'https://www.myfloridalicense.com/portalsearches/VerifyLicensee/LicenseDetail?ID=2F584AC97289A0AF647A608799927DA4',
		),
	),
) );

\FortifyOffices\Registry::flush();
WP_CLI::success( count( $seed['offices'] ) . ' offices seeded, ' . count( $owner ) . ' ZIP codes.' );
