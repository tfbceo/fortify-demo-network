<?php
/**
 * Team members from the office's Office Profile.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

class Team_List extends Widget_Base {

	use Office_Context;

	public function get_name() {
		return 'fortify-team-list';
	}

	public function get_title() {
		return esc_html__( 'Team List', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'office', 'profile', 'team', 'agents', 'broker' );
	}

	public function get_style_depends(): array {
		return array( 'fortify-offices' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_team', array( 'label' => esc_html__( 'Team', 'fortify-offices' ) ) );
		$this->add_control( 'help', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'People come from the office’s Office Profile screen.', 'fortify-offices' ),
			'content_classes' => 'elementor-descriptor',
		) );
		$this->add_control( 'columns', array(
			'label'   => esc_html__( 'Columns', 'fortify-offices' ),
			'type'    => Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
		) );
		$this->add_control( 'show_photo', array(
			'label'        => esc_html__( 'Show photos', 'fortify-offices' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );
		$this->add_control( 'show_bio', array(
			'label'        => esc_html__( 'Show bios', 'fortify-offices' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );
		$this->add_control( 'sample_label', array(
			'label'   => esc_html__( 'Label on sample entries', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Sample profile — demo',
		) );
		$this->add_control( 'empty_text', array(
			'label'   => esc_html__( 'Text when there is no team', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
		) );
		$this->add_preview_office_control();
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$team = $this->context_profile( $s )['team'];
		if ( ! $team ) {
			if ( $s['empty_text'] ) {
				echo '<p class="fo-empty">' . esc_html( $s['empty_text'] ) . '</p>';
			}
			return;
		}
		echo '<div class="fo-team fo-cols-' . (int) $s['columns'] . '">';
		foreach ( $team as $m ) {
			echo '<article class="fo-person">';
			if ( 'yes' === $s['show_photo'] ) {
				if ( $m['photo'] ) {
					echo '<img class="fo-person-photo" src="' . esc_url( $m['photo'] ) . '" alt="' . esc_attr( $m['name'] ) . '" loading="lazy">';
				} else {
					$initials = implode( '', array_map( static fn( $w ) => mb_substr( $w, 0, 1 ), array_slice( preg_split( '/\s+/', trim( preg_replace( '/\(.*\)/', '', $m['name'] ) ) ), 0, 2 ) ) );
					echo '<span class="fo-person-photo is-initials" aria-hidden="true">' . esc_html( mb_strtoupper( $initials ) ) . '</span>';
				}
			}
			echo '<h3 class="fo-person-name">' . esc_html( $m['name'] ) . '</h3>';
			if ( $m['title'] ) {
				echo '<p class="fo-person-title">' . esc_html( $m['title'] ) . '</p>';
			}
			if ( 'yes' === $s['show_bio'] && $m['bio'] ) {
				echo '<p class="fo-person-bio">' . esc_html( $m['bio'] ) . '</p>';
			}
			if ( $m['sample'] && $s['sample_label'] ) {
				echo '<p class="fo-sample-tag">' . esc_html( $s['sample_label'] ) . '</p>';
			}
			echo '</article>';
		}
		echo '</div>';
	}
}
