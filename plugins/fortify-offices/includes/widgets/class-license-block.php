<?php
/**
 * Licensing block: on an office site, the office's name, address, broker of
 * record and state license; on the corporate site, the brokerage and its
 * state licenses (network option `fortify_offices_brokerage`).
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

class License_Block extends Widget_Base {

	use Office_Context;

	public function get_name() {
		return 'fortify-license-block';
	}

	public function get_title() {
		return esc_html__( 'Licensing Block', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-info-box';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'license', 'broker', 'brokerage', 'compliance' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_license', array( 'label' => esc_html__( 'Licensing', 'fortify-offices' ) ) );
		$this->add_control( 'broker_label', array(
			'label'   => esc_html__( 'Broker label (office sites)', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Broker of record:',
		) );
		$this->add_control( 'license_label', array(
			'label'       => esc_html__( 'License label (office sites)', 'fortify-offices' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '{state} License #',
			'description' => esc_html__( 'Placeholder: {state}.', 'fortify-offices' ),
		) );
		$this->add_control( 'verify_label', array(
			'label'   => esc_html__( 'Verify link text', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Verify license →',
		) );
		$this->add_preview_office_control();
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$b      = fortify_offices_brokerage();
		$office = $this->context_office( $s );
		$lines  = array();

		if ( $office ) {
			$lines[] = esc_html( trim( $b['name'] . ' — ' . $office['name'], ' —' ) );
			if ( $office['address'] ) {
				$lines[] = esc_html( $office['address'] );
			}
			if ( $office['broker'] ) {
				$lines[] = esc_html( trim( $s['broker_label'] . ' ' . $office['broker'] ) );
			}
			if ( $office['license'] ) {
				$lines[] = esc_html( strtr( $s['license_label'], array( '{state}' => $office['state'] ) ) . $office['license'] );
			}
		} else {
			if ( $b['name'] ) {
				$lines[] = esc_html( $b['name'] );
			}
			if ( $b['city'] ) {
				$lines[] = esc_html( $b['city'] );
			}
			foreach ( (array) $b['licenses'] as $lic ) {
				$lines[] = esc_html( ( $lic['label'] ?? $lic['state'] . ' License' ) . ' #' . $lic['number'] );
				if ( ! empty( $lic['url'] ) && $s['verify_label'] ) {
					$lines[] = '<a href="' . esc_url( $lic['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $s['verify_label'] ) . '</a>';
				}
			}
		}
		if ( $lines ) {
			echo '<p class="fo-license">' . implode( '<br>', $lines ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lines escaped above.
		}
	}
}
