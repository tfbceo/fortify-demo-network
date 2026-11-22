<?php
/**
 * Office context for widgets that show one office's data: on an office site,
 * that office; in the Elementor editor on the main site, the office chosen in
 * "Preview with office" so templates can be authored with real data.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use FortifyOffices\Elementor_Integration;

defined( 'ABSPATH' ) || exit;

trait Office_Context {

	protected function add_preview_office_control() {
		$options = array( '' => esc_html__( '— None (corporate) —', 'fortify-offices' ) );
		foreach ( fortify_offices( array( 'status' => array( 'live', 'coming_soon', 'hidden' ) ) ) as $o ) {
			$options[ $o['slug'] ] = $o['name'];
		}
		$this->add_control( 'preview_office', array(
			'label'       => esc_html__( 'Preview with office', 'fortify-offices' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => '',
			'options'     => $options,
			'description' => esc_html__( 'Editor only. On an office site the widget always shows that office.', 'fortify-offices' ),
		) );
	}

	/**
	 * Office Profile to render: the office site's own profile; in the editor on
	 * site 1, sample content (for the previewed office, if one is chosen);
	 * otherwise an empty profile.
	 */
	protected function context_profile( array $settings ) {
		if ( fortify_offices_context_blog_id() !== get_main_site_id() ) {
			return \FortifyOffices\Profile::get();
		}
		if ( Elementor_Integration::is_editing() ) {
			$office = ! empty( $settings['preview_office'] ) ? fortify_office( $settings['preview_office'] ) : null;
			return \FortifyOffices\Profile::sample( $office );
		}
		return \FortifyOffices\Profile::normalize( array() );
	}

	/** The office to render, or null for the corporate site. */
	protected function context_office( array $settings ) {
		$office = fortify_office_for_site();
		if ( ! $office && ! empty( $settings['preview_office'] ) && Elementor_Integration::is_editing() ) {
			$office = fortify_office( $settings['preview_office'] );
		}
		return $office;
	}
}
