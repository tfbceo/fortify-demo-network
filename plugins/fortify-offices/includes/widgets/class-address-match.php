<?php
/**
 * Address match: reads the address the Address Field sent (?address=…),
 * finds the office for its ZIP and shows where the seller goes next: the
 * office site, a coming-soon note, or the not-covered result.
 */

namespace FortifyOffices\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use FortifyOffices\Elementor_Integration;
use FortifyOffices\Registry;

defined( 'ABSPATH' ) || exit;

class Address_Match extends Widget_Base {

	public function get_name() {
		return 'fortify-address-match';
	}

	public function get_title() {
		return esc_html__( 'Address Match', 'fortify-offices' );
	}

	public function get_icon() {
		return 'eicon-search-results';
	}

	public function get_categories() {
		return array( Elementor_Integration::CATEGORY );
	}

	public function get_keywords() {
		return array( 'fortify', 'zip', 'routing', 'office', 'address', 'preview' );
	}

	public function get_style_depends(): array {
		return array( 'fortify-offices' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_match', array( 'label' => esc_html__( 'Messages', 'fortify-offices' ) ) );
		$this->add_control( 'help', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Placeholders: {zip}, {office}, {state}. The address comes from ?address= in the page URL.', 'fortify-offices' ),
			'content_classes' => 'elementor-descriptor',
		) );
		$this->add_control( 'matched', array(
			'label'   => esc_html__( 'Matched', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => '{zip} is served by our {office} office, where a local broker prepares your preview.',
		) );
		$this->add_control( 'here', array(
			'label'       => esc_html__( 'Matched this office (office sites)', 'fortify-offices' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => '{zip} is in the {office} area, so you are in the right place. A local broker prepares your preview.',
			'description' => esc_html__( 'Shown on an office site when the ZIP belongs to that office.', 'fortify-offices' ),
		) );
		$this->add_control( 'matched_button', array(
			'label'   => esc_html__( 'Matched button', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Continue with {office} →',
		) );
		$this->add_control( 'soon', array(
			'label'   => esc_html__( 'Office coming soon', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => "{zip} is in our {office} market, which opens soon. Leave your details and we'll contact you when it does.",
		) );
		$this->add_control( 'not_covered', array(
			'label'   => esc_html__( 'Not covered', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => "We don't serve {zip} yet. Tell us about your home and we'll let you know when coverage reaches your area.",
		) );
		$this->add_control( 'contact_button', array(
			'label'   => esc_html__( 'Contact button', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Contact us →',
		) );
		$this->add_control( 'no_zip', array(
			'label'   => esc_html__( 'No ZIP in the address', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Add your five-digit ZIP code so we can match you with your local office.',
		) );
		$this->add_control( 'finder_button', array(
			'label'   => esc_html__( 'Finder button', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Find your office →',
		) );
		$this->add_control( 'finder_url', array(
			'label'   => esc_html__( 'Finder page', 'fortify-offices' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '/locations/',
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing of a GET value.
		$address = isset( $_GET['address'] ) ? sanitize_text_field( wp_unslash( $_GET['address'] ) ) : '';
		if ( '' === $address ) {
			if ( Elementor_Integration::is_editing() ) {
				echo '<div class="fo-match is-info"><p>' . esc_html__( 'Address Match: shows the routing result for ?address= on the live page.', 'fortify-offices' ) . '</p></div>';
			}
			return;
		}

		$zip    = Registry::extract_zip( $address );
		$office = $zip ? fortify_office_for_zip( $zip ) : null;
		$vars   = array( '{zip}' => $zip, '{office}' => $office['name'] ?? '', '{state}' => $office['state_name'] ?? '' );

		if ( ! $zip ) {
			$state = 'no-zip';
			$text  = $s['no_zip'];
			$url   = add_query_arg( 'q', rawurlencode( $address ), $s['finder_url'] );
			$label = $s['finder_button'];
		} elseif ( $office && $office['blog_id'] && get_current_blog_id() === $office['blog_id'] ) {
			$state = 'here';
			$text  = $s['here'];
			$url   = '';
			$label = '';
		} elseif ( $office && 'live' === $office['status'] ) {
			$state = 'matched';
			$text  = $s['matched'];
			$url   = $office['url']; // The office's home page. Office sites have no /preview/ page of their own.
			$label = $s['matched_button'];
		} elseif ( $office ) {
			$state = 'soon';
			$text  = $s['soon'];
			$url   = fortify_offices_contact_url();
			$label = $s['contact_button'];
		} else {
			$state = 'not-covered';
			$text  = $s['not_covered'];
			$url   = fortify_offices_contact_url();
			$label = $s['contact_button'];
		}
		?>
		<div class="fo-match is-<?php echo esc_attr( $state ); ?>" data-fo-match="<?php echo esc_attr( $state ); ?>"<?php echo $office ? ' data-fo-office="' . esc_attr( $office['slug'] ) . '"' : ''; ?>>
			<p><?php echo esc_html( strtr( $text, $vars ) ); ?></p>
			<?php if ( $url ) : ?>
				<a class="fo-match-button" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( strtr( $label, $vars ) ); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}
}
