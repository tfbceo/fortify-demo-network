<?php
/**
 * Local reviews from the office's Office Profile. Sample entries carry a
 * visible label.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

class Local_Reviews extends Widget_Base {

	use Office_Context;

	public function get_name() {
		return 'fortify-local-reviews';
	}

	public function get_title() {
		return esc_html__( 'Local Reviews', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-testimonial';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'office', 'profile', 'reviews', 'testimonials' );
	}

	public function get_style_depends(): array {
		return array( 'fortify-offices' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_reviews', array( 'label' => esc_html__( 'Reviews', 'fortify-offices' ) ) );
		$this->add_control( 'help', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Reviews come from the office’s Office Profile screen.', 'fortify-offices' ),
			'content_classes' => 'elementor-descriptor',
		) );
		$this->add_control( 'columns', array(
			'label'   => esc_html__( 'Columns', 'fortify-offices' ),
			'type'    => Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '1' => '1', '2' => '2', '3' => '3' ),
		) );
		$this->add_control( 'show_rating', array(
			'label'        => esc_html__( 'Show star rating', 'fortify-offices' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );
		$this->add_control( 'sample_label', array(
			'label'   => esc_html__( 'Label on sample reviews', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Sample review — demo',
		) );
		$this->add_control( 'empty_text', array(
			'label'   => esc_html__( 'Text when there are no reviews', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
		) );
		$this->add_preview_office_control();
		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$reviews = $this->context_profile( $s )['reviews'];
		if ( ! $reviews ) {
			if ( $s['empty_text'] ) {
				echo '<p class="fo-empty">' . esc_html( $s['empty_text'] ) . '</p>';
			}
			return;
		}
		echo '<div class="fo-reviews fo-cols-' . (int) $s['columns'] . '">';
		foreach ( $reviews as $r ) {
			echo '<figure class="fo-review">';
			if ( 'yes' === $s['show_rating'] && $r['rating'] ) {
				/* translators: %d: star rating out of 5 */
				echo '<p class="fo-stars" aria-label="' . esc_attr( sprintf( __( '%d out of 5 stars', 'fortify-offices' ), $r['rating'] ) ) . '"><span aria-hidden="true">' . esc_html( str_repeat( '★', $r['rating'] ) . str_repeat( '☆', 5 - $r['rating'] ) ) . '</span></p>';
			}
			echo '<blockquote class="fo-review-text">' . esc_html( $r['text'] ) . '</blockquote>';
			echo '<figcaption class="fo-review-by"><b>' . esc_html( $r['name'] ) . '</b>' . ( $r['location'] ? ' · ' . esc_html( $r['location'] ) : '' ) . '</figcaption>';
			if ( $r['sample'] && $s['sample_label'] ) {
				echo '<p class="fo-sample-tag">' . esc_html( $s['sample_label'] ) . '</p>';
			}
			echo '</figure>';
		}
		echo '</div>';
	}
}
