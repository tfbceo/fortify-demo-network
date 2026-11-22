<?php
/**
 * Coverage by state: one card per state in the registry, marked active when
 * the state has a live office, with the counties (or offices) it covers.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

class State_Coverage extends Widget_Base {

	public function get_name() {
		return 'fortify-state-coverage';
	}

	public function get_title() {
		return esc_html__( 'State Coverage', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'coverage', 'state', 'counties' );
	}

	public function get_style_depends(): array {
		return array( 'fortify-offices' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_coverage', array( 'label' => esc_html__( 'Coverage', 'fortify-offices' ) ) );
		$this->add_control( 'list', array(
			'label'   => esc_html__( 'List under active states', 'fortify-offices' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'counties',
			'options' => array(
				'counties' => esc_html__( 'Counties', 'fortify-offices' ),
				'offices'  => esc_html__( 'Offices', 'fortify-offices' ),
				'none'     => esc_html__( 'Nothing', 'fortify-offices' ),
			),
		) );
		$this->add_control( 'active_label', array(
			'label'   => esc_html__( 'Active label', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Active',
		) );
		$this->add_control( 'active_text', array(
			'label'       => esc_html__( 'Active state text', 'fortify-offices' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Serving select county MLS markets across the state.',
			'description' => esc_html__( 'Placeholders: {state}, {count} (live offices).', 'fortify-offices' ),
		) );
		$this->add_control( 'active_note', array(
			'label'   => esc_html__( 'Active state note', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Enter your address to confirm your county is covered — coverage continues to expand.',
		) );
		$this->add_control( 'soon_label', array(
			'label'     => esc_html__( 'Coming-soon label', 'fortify-offices' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => 'Coming soon',
			'separator' => 'before',
		) );
		$this->add_control( 'soon_text', array(
			'label'   => esc_html__( 'Coming-soon state text', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Active broker license held. Expansion to {state} MLS markets in progress.',
		) );
		$this->add_control( 'soon_note', array(
			'label'   => esc_html__( 'Coming-soon state note', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => "Contact us if you're interested in listing in {state} and we'll notify you when coverage launches in your area.",
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$states = array();
		foreach ( fortify_offices() as $o ) {
			$states[ $o['state'] ]['name']      = $o['state_name'];
			$states[ $o['state'] ]['offices'][] = $o;
		}
		if ( ! $states ) {
			return;
		}
		echo '<div class="fo-states">';
		foreach ( $states as $st ) {
			$live     = array_values( array_filter( $st['offices'], static fn( $o ) => 'live' === $o['status'] ) );
			$active   = (bool) $live;
			$replace  = array( '{state}' => $st['name'], '{count}' => count( $live ) );
			echo '<div class="fo-state">';
			echo '<h3 class="fo-state-name">' . esc_html( $st['name'] ) . ' <span class="fo-badge ' . ( $active ? 'is-live' : 'is-soon' ) . '">' . esc_html( $active ? $s['active_label'] : $s['soon_label'] ) . '</span></h3>';
			echo '<p>' . esc_html( strtr( $active ? $s['active_text'] : $s['soon_text'], $replace ) ) . '</p>';
			if ( $active && 'none' !== $s['list'] ) {
				$items = array();
				foreach ( $live as $o ) {
					if ( 'offices' === $s['list'] ) {
						$items[] = $o['name'];
						continue;
					}
					foreach ( $o['counties'] as $c ) {
						$items[] = trim( preg_replace( '/\s*\(.*\)$/', '', $c ) ); // "Palm Beach (south)" -> "Palm Beach".
					}
				}
				$items = array_unique( $items );
				sort( $items, SORT_NATURAL | SORT_FLAG_CASE );
				echo '<p class="fo-state-list">' . esc_html( implode( ' · ', $items ) ) . '</p>';
			}
			$note = $active ? $s['active_note'] : $s['soon_note'];
			if ( $note ) {
				echo '<p class="fo-state-note">' . esc_html( strtr( $note, $replace ) ) . '</p>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}
