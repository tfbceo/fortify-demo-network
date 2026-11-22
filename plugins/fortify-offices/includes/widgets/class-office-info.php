<?php
/**
 * One field of the current office (name, phone, address, hours, MLS line,
 * counties, ZIPs, broker, license). For office pages: one widget type covers
 * every field, which keeps the widget-type count down.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

class Office_Info extends Widget_Base {

	use Office_Context;

	public function get_name() {
		return 'fortify-office-info';
	}

	public function get_title() {
		return esc_html__( 'Office Info', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-site-identity';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'office', 'phone', 'address', 'hours', 'mls', 'license' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_info', array( 'label' => esc_html__( 'Office field', 'fortify-offices' ) ) );
		$this->add_control( 'field', array(
			'label'   => esc_html__( 'Field', 'fortify-offices' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'name',
			'options' => array(
				'name'     => esc_html__( 'Office name', 'fortify-offices' ),
				'phone'    => esc_html__( 'Phone (linked)', 'fortify-offices' ),
				'address'  => esc_html__( 'Address', 'fortify-offices' ),
				'hours'    => esc_html__( 'Hours', 'fortify-offices' ),
				'mls'      => esc_html__( 'MLS line', 'fortify-offices' ),
				'counties' => esc_html__( 'Counties served', 'fortify-offices' ),
				'zips'     => esc_html__( 'ZIP codes served', 'fortify-offices' ),
				'broker'   => esc_html__( 'Broker of record', 'fortify-offices' ),
				'license'  => esc_html__( 'State license number', 'fortify-offices' ),
				'state'    => esc_html__( 'State name', 'fortify-offices' ),
			),
		) );
		$this->add_control( 'before', array(
			'label'       => esc_html__( 'Text before', 'fortify-offices' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'description' => esc_html__( 'For the MLS line, for example "Listings appear on ".', 'fortify-offices' ),
		) );
		$this->add_control( 'after', array(
			'label'   => esc_html__( 'Text after', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
		) );
		$this->add_control( 'tag', array(
			'label'   => esc_html__( 'HTML tag', 'fortify-offices' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'p',
			'options' => array( 'p' => 'p', 'span' => 'span', 'div' => 'div', 'h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4' ),
		) );
		$this->add_control( 'fallback', array(
			'label'       => esc_html__( 'Text when there is no office', 'fortify-offices' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'description' => esc_html__( 'Shown on the corporate site. Empty hides the widget.', 'fortify-offices' ),
		) );
		$this->add_preview_office_control();
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$office = $this->context_office( $s );
		$tag    = in_array( $s['tag'], array( 'p', 'span', 'div', 'h1', 'h2', 'h3', 'h4' ), true ) ? $s['tag'] : 'p';

		if ( ! $office ) {
			if ( $s['fallback'] ) {
				printf( '<%1$s class="fo-info">%2$s</%1$s>', tag_escape( $tag ), esc_html( $s['fallback'] ) );
			}
			return;
		}

		switch ( $s['field'] ) {
			case 'phone':
				$value = $office['phone'] ? '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $office['phone'] ) ) . '">' . esc_html( $office['phone'] ) . '</a>' : '';
				break;
			case 'counties':
				$value = esc_html( implode( ', ', $office['counties'] ) );
				break;
			case 'zips':
				$value = esc_html( implode( ', ', $office['zips'] ) );
				break;
			case 'state':
				$value = esc_html( $office['state_name'] );
				break;
			default:
				$value = esc_html( (string) ( $office[ $s['field'] ] ?? '' ) );
		}
		if ( '' === $value ) {
			return;
		}
		printf(
			'<%1$s class="fo-info fo-info--%2$s">%3$s%4$s%5$s</%1$s>',
			tag_escape( $tag ),
			esc_attr( $s['field'] ),
			esc_html( $s['before'] ),
			$value, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			esc_html( $s['after'] )
		);
	}
}
