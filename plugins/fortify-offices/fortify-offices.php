<?php
/**
 * Plugin Name:       Fortify Offices
 * Description:       Office registry for the Fortify demo network: one record per market office on the main site, read by every site, plus an Office Profile (team, reviews, market intro) on each office site. Provides the location finder, ZIP routing and registry widgets for Elementor.
 * Version:           0.2.2
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Network:           true
 * Author:            Fortify demo network
 * Text Domain:       fortify-offices
 *
 * See docs/BUILD-SPEC.md section 6 in the fortify-demo-network repo.
 */

defined( 'ABSPATH' ) || exit;

define( 'FORTIFY_OFFICES_VERSION', '0.2.2' );
define( 'FORTIFY_OFFICES_FILE', __FILE__ );
define( 'FORTIFY_OFFICES_DIR', __DIR__ );

require_once __DIR__ . '/includes/class-registry.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/class-admin.php';
require_once __DIR__ . '/includes/class-frontend.php';
require_once __DIR__ . '/includes/class-profile.php';
require_once __DIR__ . '/includes/class-seo.php';

\FortifyOffices\Registry::init();
\FortifyOffices\Admin::init();
\FortifyOffices\Frontend::init();
\FortifyOffices\Profile::init();
\FortifyOffices\Seo::init();

add_action(
	'elementor/init',
	static function () {
		require_once __DIR__ . '/includes/class-elementor.php';
		\FortifyOffices\Elementor_Integration::init();
	}
);
