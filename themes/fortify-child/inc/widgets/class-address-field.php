<?php
/**
 * Address field: a plain ZIP/address form, no Google Places and no API key
 * (decision 2026-11-21). Submits a GET request with the typed value.
 * ZIP-to-office routing is added in phase 4 by fortify-offices, through the
 * `fortify_address_field_action` filter.
 */

namespace FortifyChild\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Address_Field extends Widget_Base {

	public function get_name() {
		return 'fortify-address-field';
	}

	public function get_title() {
		return esc_html__( 'Address Field', 'fortify-child' );
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	public function get_categories() {
		return array( 'fortify' );
	}

	public function get_keywords() {
		return array( 'fortify', 'address', 'zip', 'preview', 'form' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_field', array( 'label' => esc_html__( 'Field', 'fortify-child' ) ) );

		$this->add_control( 'placeholder', array(
			'label'   => esc_html__( 'Placeholder', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Enter your address or ZIP',
		) );
		$this->add_control( 'button_text', array(
			'label'   => esc_html__( 'Button text', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Get my free preview',
		) );
		$this->add_control( 'action', array(
			'label'       => esc_html__( 'Send to', 'fortify-child' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '/preview/',
			'description' => esc_html__( 'Page that receives ?address=… (the preview stub).', 'fortify-child' ),
		) );
		$this->add_control( 'variant', array(
			'label'   => esc_html__( 'Background', 'fortify-child' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'light',
			'options' => array(
				'light' => esc_html__( 'On light', 'fortify-child' ),
				'dark'  => esc_html__( 'On dark', 'fortify-child' ),
			),
		) );
		$this->add_control( 'align', array(
			'label'   => esc_html__( 'Alignment', 'fortify-child' ),
			'type'    => Controls_Manager::CHOOSE,
			'default' => 'left',
			'options' => array(
				'left'   => array( 'title' => esc_html__( 'Left', 'fortify-child' ), 'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => esc_html__( 'Center', 'fortify-child' ), 'icon' => 'eicon-text-align-center' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$action = apply_filters( 'fortify_address_field_action', $s['action'] ? $s['action'] : '/preview/', $this );
		$id     = 'fx-addr-' . $this->get_id();
		$class  = 'fx-addr fx-addr--' . ( 'dark' === $s['variant'] ? 'dark' : 'light' ) . ( 'center' === $s['align'] ? ' fx-addr--center' : '' );
		?>
		<form class="<?php echo esc_attr( $class ); ?>" method="get" action="<?php echo esc_url( $action ); ?>" role="search">
			<div class="fx-addr-row">
				<label class="fx-addr-field" for="<?php echo esc_attr( $id ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
					<span class="screen-reader-text"><?php esc_html_e( 'Property address or ZIP', 'fortify-child' ); ?></span>
					<input id="<?php echo esc_attr( $id ); ?>" type="text" name="address" autocomplete="street-address" required placeholder="<?php echo esc_attr( $s['placeholder'] ); ?>">
				</label>
				<button type="submit" class="fx-btn fx-btn-indigo"><?php echo esc_html( $s['button_text'] ); ?></button>
			</div>
		</form>
		<?php
	}
}
