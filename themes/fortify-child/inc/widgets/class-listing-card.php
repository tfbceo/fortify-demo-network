<?php
/**
 * Listing Card: a sample listing with its photo and details as separate
 * controls, so an office can change the content while the layout stays
 * locked (lock map: content sections unlocked, layout and style locked).
 */

namespace FortifyChild\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Listing_Card extends Widget_Base {

	public function get_name() {
		return 'fortify-listing-card';
	}

	public function get_title() {
		return esc_html__( 'Listing Card', 'fortify-child' );
	}

	public function get_icon() {
		return 'eicon-image-box';
	}

	public function get_categories() {
		return array( 'fortify' );
	}

	public function get_keywords() {
		return array( 'fortify', 'listing', 'sample', 'property', 'card' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_photo', array( 'label' => esc_html__( 'Photo', 'fortify-child' ) ) );
		$this->add_control( 'image', array(
			'label'   => esc_html__( 'Photo', 'fortify-child' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => array( 'url' => get_stylesheet_directory_uri() . '/assets/img/listing-preview-front-v1.webp' ),
		) );
		$this->add_control( 'badge', array(
			'label'   => esc_html__( 'Badge', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Sample listing',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_details', array( 'label' => esc_html__( 'Details', 'fortify-child' ) ) );
		$this->add_control( 'price', array(
			'label'   => esc_html__( 'Price', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '$450,000',
		) );
		$this->add_control( 'beds', array(
			'label'   => esc_html__( 'Beds', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '3',
		) );
		$this->add_control( 'baths', array(
			'label'   => esc_html__( 'Baths', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '2',
		) );
		$this->add_control( 'sqft', array(
			'label'   => esc_html__( 'Square feet', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '1,850',
		) );
		$this->add_control( 'address', array(
			'label'   => esc_html__( 'Address or area', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Sample street, your neighborhood',
		) );
		$this->add_control( 'description', array(
			'label'   => esc_html__( 'Short description', 'fortify-child' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Sample description. The office replaces this with a recent local listing, with the seller’s permission.',
		) );
		$this->add_control( 'savings', array(
			'label'   => esc_html__( 'Savings line', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Fortify Pro: $2,495 at closing instead of about $13,500 at 3%',
		) );
		$this->add_control( 'link', array(
			'label' => esc_html__( 'Link (optional)', 'fortify-child' ),
			'type'  => Controls_Manager::URL,
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_note', array( 'label' => esc_html__( 'Disclosure', 'fortify-child' ) ) );
		$this->add_control( 'note', array(
			'label'   => esc_html__( 'Note under the card', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Sample listing — not a real property.',
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$img   = ! empty( $s['image']['url'] ) ? $s['image']['url'] : '';
		$facts = array_filter( array(
			$s['beds'] ? $s['beds'] . ' bd' : '',
			$s['baths'] ? $s['baths'] . ' ba' : '',
			$s['sqft'] ? $s['sqft'] . ' sqft' : '',
		) );
		$href  = ! empty( $s['link']['url'] ) ? $s['link']['url'] : '';
		?>
		<article class="fx-lc">
			<div class="fx-lc-photo"<?php echo $img ? ' style="background-image:url(' . esc_url( $img ) . ')"' : ''; ?> role="img" aria-label="<?php echo esc_attr( $s['address'] ); ?>">
				<?php if ( $s['badge'] ) : ?>
					<span class="fx-lc-badge"><?php echo esc_html( $s['badge'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="fx-lc-body">
				<p class="fx-lc-price fx-num"><?php echo esc_html( $s['price'] ); ?></p>
				<?php if ( $facts ) : ?>
					<p class="fx-lc-facts"><?php echo esc_html( implode( ' · ', $facts ) ); ?></p>
				<?php endif; ?>
				<p class="fx-lc-addr"><?php echo esc_html( $s['address'] ); ?></p>
				<?php if ( $s['description'] ) : ?>
					<p class="fx-lc-desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( $s['savings'] ) : ?>
					<p class="fx-lc-savings"><span class="fx-dot"></span> <?php echo esc_html( $s['savings'] ); ?></p>
				<?php endif; ?>
				<?php if ( $href ) : ?>
					<a class="fx-lc-link" href="<?php echo esc_url( $href ); ?>"><?php esc_html_e( 'View listing →', 'fortify-child' ); ?></a>
				<?php endif; ?>
			</div>
		</article>
		<?php if ( $s['note'] ) : ?>
			<p class="fx-lc-note"><?php echo esc_html( $s['note'] ); ?></p>
		<?php endif; ?>
		<?php
	}
}
