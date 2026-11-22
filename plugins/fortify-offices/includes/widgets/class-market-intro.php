<?php
/**
 * Market intro from the office's Office Profile.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

class Market_Intro extends Widget_Base {

	use Office_Context;

	public function get_name() {
		return 'fortify-market-intro';
	}

	public function get_title() {
		return esc_html__( 'Market Intro', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-text';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'office', 'profile', 'intro', 'market' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_intro', array( 'label' => esc_html__( 'Market intro', 'fortify-offices' ) ) );
		$this->add_control( 'help', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Text comes from the office’s Office Profile screen.', 'fortify-offices' ),
			'content_classes' => 'elementor-descriptor',
		) );
		$this->add_control( 'fallback', array(
			'label'       => esc_html__( 'Text when the profile has no intro', 'fortify-offices' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => '',
			'description' => esc_html__( 'Empty hides the widget.', 'fortify-offices' ),
		) );
		$this->add_preview_office_control();
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$intro = $this->context_profile( $s )['intro'];
		if ( '' === trim( wp_strip_all_tags( $intro ) ) ) {
			$intro = $s['fallback'] ? '<p>' . esc_html( $s['fallback'] ) . '</p>' : '';
		}
		if ( '' !== $intro ) {
			echo '<div class="fo-intro">' . wp_kses_post( wpautop( $intro ) ) . '</div>';
		}
	}
}
