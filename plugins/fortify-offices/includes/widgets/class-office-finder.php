<?php
/**
 * Location finder: search by ZIP, city or county, filter by state, see the
 * offices on a Leaflet map (OpenStreetMap tiles) and in a result list. An
 * unmatched ZIP shows the not-covered result. ?q= in the URL runs a search.
 * The server renders the full list, so the page works without JavaScript.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;
use FortifyOffices\Registry;

defined( 'ABSPATH' ) || exit;

class Office_Finder extends Widget_Base {

	public function get_name() {
		return 'fortify-office-finder';
	}

	public function get_title() {
		return esc_html__( 'Location Finder', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'finder', 'locations', 'map', 'zip', 'office' );
	}

	public function get_style_depends(): array {
		return array( 'leaflet', 'fortify-offices' );
	}

	public function get_script_depends(): array {
		return array( 'fortify-offices-finder' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_search', array( 'label' => esc_html__( 'Search', 'fortify-offices' ) ) );
		$this->add_control( 'placeholder', array(
			'label'   => esc_html__( 'Placeholder', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'ZIP code, city or county',
		) );
		$this->add_control( 'button_text', array(
			'label'   => esc_html__( 'Button text', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Find my office',
		) );
		$this->add_control( 'all_states', array(
			'label'   => esc_html__( 'State filter: all', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'All states',
		) );
		$this->add_control( 'show_map', array(
			'label'        => esc_html__( 'Show map', 'fortify-offices' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );
		$this->add_control( 'map_height', array(
			'label'     => esc_html__( 'Map height (px)', 'fortify-offices' ),
			'type'      => Controls_Manager::NUMBER,
			'default'   => 480,
			'min'       => 240,
			'max'       => 900,
			'condition' => array( 'show_map' => 'yes' ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_results', array( 'label' => esc_html__( 'Results', 'fortify-offices' ) ) );
		$this->add_control( 'zip_match', array(
			'label'       => esc_html__( 'ZIP match heading', 'fortify-offices' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '{zip} is served by our {office} office.',
			'description' => esc_html__( 'Placeholders: {zip}, {office}.', 'fortify-offices' ),
		) );
		$this->add_control( 'zip_soon', array(
			'label'   => esc_html__( 'ZIP in a coming-soon market', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '{zip} is in our {office} market, which opens soon.',
		) );
		$this->add_control( 'not_covered', array(
			'label'   => esc_html__( 'Not-covered text', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => "We don't serve {zip} yet. Tell us about your home and we'll let you know when coverage reaches your area.",
		) );
		$this->add_control( 'not_covered_link', array(
			'label'   => esc_html__( 'Not-covered link text', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Contact us →',
		) );
		$this->add_control( 'no_results', array(
			'label'   => esc_html__( 'No-results text', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'No office matches “{query}”. Try a ZIP code.',
		) );
		$this->add_control( 'live_label', array(
			'label'   => esc_html__( 'Live label', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Live',
		) );
		$this->add_control( 'soon_label', array(
			'label'   => esc_html__( 'Coming-soon label', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Coming soon',
		) );
		$this->add_control( 'visit_label', array(
			'label'   => esc_html__( 'Office link text', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Visit office →',
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$offices = fortify_offices();
		$labels  = array( 'live' => $s['live_label'], 'soon' => $s['soon_label'], 'visit' => $s['visit_label'] );
		$states  = array();
		foreach ( $offices as $o ) {
			$states[ $o['state'] ] = $o['state_name'];
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only search value.
		$query = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$uid   = 'fo-finder-' . $this->get_id();

		$data = array(
			'offices' => array_map( static function ( $o ) {
				return array_intersect_key( $o, array_flip( array( 'name', 'slug', 'state', 'state_name', 'city', 'mls', 'counties', 'zips', 'lat', 'lng', 'status', 'url' ) ) );
			}, $offices ),
			'labels'  => array_merge( $labels, array(
				'zipMatch'       => $s['zip_match'],
				'zipSoon'        => $s['zip_soon'],
				'and'            => __( 'and', 'fortify-offices' ),
				'notCovered'     => $s['not_covered'],
				'notCoveredLink' => $s['not_covered_link'],
				'noResults'      => $s['no_results'],
				'county'         => __( 'County', 'fortify-offices' ),
				'counties'       => __( 'Counties', 'fortify-offices' ),
			) ),
			'contact' => fortify_offices_contact_url(),
		);
		?>
		<div class="fo-finder" id="<?php echo esc_attr( $uid ); ?>" data-fo-finder>
			<form class="fo-finder-form" role="search" method="get" action="">
				<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-q"><?php esc_html_e( 'ZIP code, city or county', 'fortify-offices' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-q" type="search" name="q" value="<?php echo esc_attr( $query ); ?>" placeholder="<?php echo esc_attr( $s['placeholder'] ); ?>" autocomplete="postal-code">
				<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-state"><?php esc_html_e( 'State', 'fortify-offices' ); ?></label>
				<select id="<?php echo esc_attr( $uid ); ?>-state" name="state">
					<option value=""><?php echo esc_html( $s['all_states'] ); ?></option>
					<?php foreach ( $states as $code => $name ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="fo-finder-button"><?php echo esc_html( $s['button_text'] ); ?></button>
			</form>
			<div class="fo-finder-body<?php echo 'yes' === $s['show_map'] ? ' has-map' : ''; ?>">
				<?php if ( 'yes' === $s['show_map'] ) : ?>
					<div class="fo-finder-map" style="height:<?php echo (int) $s['map_height']; ?>px" aria-label="<?php esc_attr_e( 'Map of offices', 'fortify-offices' ); ?>" role="region"></div>
				<?php endif; ?>
				<div class="fo-finder-results" aria-live="polite">
					<p class="fo-finder-status"></p>
					<div class="fo-finder-list">
						<?php
						foreach ( $offices as $o ) {
							echo Elementor_Integration::office_card( $o, $labels ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in office_card().
						}
						?>
					</div>
				</div>
			</div>
			<script type="application/json" class="fo-finder-data"><?php echo wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
		</div>
		<?php
	}
}
