<?php
/**
 * Office directory: every office grouped by state, as a compact footer list or
 * a markets grid of cards.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

class Office_Directory extends Widget_Base {

	public function get_name() {
		return 'fortify-office-directory';
	}

	public function get_title() {
		return esc_html__( 'Office Directory', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'office', 'directory', 'markets', 'footer' );
	}

	public function get_style_depends(): array {
		return array( 'fortify-offices' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_directory', array( 'label' => esc_html__( 'Directory', 'fortify-offices' ) ) );
		$this->add_control( 'layout', array(
			'label'   => esc_html__( 'Layout', 'fortify-offices' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'list',
			'options' => array(
				'list' => esc_html__( 'Compact list (footer)', 'fortify-offices' ),
				'grid' => esc_html__( 'Markets grid', 'fortify-offices' ),
			),
		) );
		$this->add_control( 'show_coming_soon', array(
			'label'        => esc_html__( 'Show coming-soon offices', 'fortify-offices' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );
		$this->add_control( 'link_offices', array(
			'label'        => esc_html__( 'Link live offices to their sites', 'fortify-offices' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );
		$this->add_control( 'separator', array(
			'label'     => esc_html__( 'Separator', 'fortify-offices' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => ' · ',
			'condition' => array( 'layout' => 'list' ),
		) );
		$this->add_control( 'soon_label', array(
			'label'   => esc_html__( 'Coming-soon label', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Coming soon',
		) );
		$this->add_control( 'visit_label', array(
			'label'     => esc_html__( 'Card link text', 'fortify-offices' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => 'Visit office →',
			'condition' => array( 'layout' => 'grid' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s        = $this->get_settings_for_display();
		$statuses = 'yes' === $s['show_coming_soon'] ? array( 'live', 'coming_soon' ) : array( 'live' );
		$by_state = array();
		foreach ( fortify_offices( array( 'status' => $statuses ) ) as $o ) {
			$by_state[ $o['state_name'] ][] = $o;
		}
		if ( ! $by_state ) {
			return;
		}

		if ( 'grid' === $s['layout'] ) {
			$labels = array( 'live' => esc_html__( 'Live', 'fortify-offices' ), 'soon' => $s['soon_label'], 'visit' => $s['visit_label'] );
			echo '<div class="fo-markets">';
			foreach ( $by_state as $state => $offices ) {
				echo '<section class="fo-markets-state"><h3 class="fo-markets-title">' . esc_html( $state ) . '</h3><div class="fo-markets-grid">';
				foreach ( $offices as $o ) {
					echo Elementor_Integration::office_card( $o, $labels ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in office_card().
				}
				echo '</div></section>';
			}
			echo '</div>';
			return;
		}

		echo '<div class="fo-directory">';
		foreach ( $by_state as $state => $offices ) {
			$items = array();
			foreach ( $offices as $o ) {
				$name = esc_html( $o['name'] );
				if ( 'live' === $o['status'] && 'yes' === $s['link_offices'] ) {
					$name = '<a href="' . esc_url( $o['url'] ) . '">' . $name . '</a>';
				}
				if ( 'coming_soon' === $o['status'] ) {
					$name .= ' <span class="fx-soon fo-soon">' . esc_html( $s['soon_label'] ) . '</span>';
				}
				$items[] = $name;
			}
			echo '<p><b>' . esc_html( $state ) . '</b> ' . implode( esc_html( $s['separator'] ), $items ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- items escaped above.
		}
		echo '</div>';
	}
}
