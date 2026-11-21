<?php
/**
 * Savings calculator: sale-price slider against a typical percentage
 * commission and Fortify's flat fee. Ported from the static site's
 * SavingsCalculator (index.html #savings).
 */

namespace FortifyChild\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Savings_Calculator extends Widget_Base {

	public function get_name() {
		return 'fortify-savings-calculator';
	}

	public function get_title() {
		return esc_html__( 'Savings Calculator', 'fortify-child' );
	}

	public function get_icon() {
		return 'eicon-calculator';
	}

	public function get_categories() {
		return array( 'fortify' );
	}

	public function get_keywords() {
		return array( 'fortify', 'savings', 'calculator', 'commission', 'equity' );
	}

	public function get_script_depends() {
		return array( 'fortify-savings-calculator' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_copy', array( 'label' => esc_html__( 'Text', 'fortify-child' ) ) );

		$this->add_control( 'eyebrow', array(
			'label'   => esc_html__( 'Eyebrow', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Your equity, protected',
		) );
		$this->add_control( 'heading', array(
			'label'   => esc_html__( 'Heading', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'See what you keep at your price',
		) );
		$this->add_control( 'price_label', array(
			'label'   => esc_html__( 'Slider label', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Your home’s sale price',
		) );
		$this->add_control( 'commission_label', array(
			'label'       => esc_html__( 'Commission row label', 'fortify-child' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Typical agent commission ({rate}%)',
			'description' => esc_html__( '{rate} is replaced with the commission rate.', 'fortify-child' ),
		) );
		$this->add_control( 'fee_label', array(
			'label'   => esc_html__( 'Fee row label', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Fortify Pro flat fee, at closing',
		) );
		$this->add_control( 'keep_label', array(
			'label'   => esc_html__( 'Result row label', 'fortify-child' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'You keep',
		) );
		$this->add_control( 'note', array(
			'label'   => esc_html__( 'Note', 'fortify-child' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Comparison based on a typical 2.5–3% listing-side commission, shown here at 3%. Buyer-agent compensation, if you choose to offer it, is separate, set by you, and not included. Fortify’s flat fee is due at closing only if your home sells.',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_numbers', array( 'label' => esc_html__( 'Numbers', 'fortify-child' ) ) );

		$this->add_control( 'min', array(
			'label'   => esc_html__( 'Lowest price', 'fortify-child' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 200000,
			'min'     => 0,
		) );
		$this->add_control( 'max', array(
			'label'   => esc_html__( 'Highest price', 'fortify-child' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 2000000,
			'min'     => 0,
		) );
		$this->add_control( 'step', array(
			'label'   => esc_html__( 'Step', 'fortify-child' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 10000,
			'min'     => 1,
		) );
		$this->add_control( 'start', array(
			'label'   => esc_html__( 'Starting price', 'fortify-child' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 450000,
			'min'     => 0,
		) );
		$this->add_control( 'rate', array(
			'label'   => esc_html__( 'Commission rate (%)', 'fortify-child' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 0,
			'max'     => 10,
			'step'    => 0.1,
		) );
		$this->add_control( 'fee', array(
			'label'   => esc_html__( 'Flat fee', 'fortify-child' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 2495,
			'min'     => 0,
		) );

		$this->end_controls_section();
	}

	private static function money( $n ) {
		return '$' . number_format( round( $n ) );
	}

	private static function short( $n ) {
		return $n >= 1000000
			? '$' . rtrim( rtrim( number_format( $n / 1000000, 1 ), '0' ), '.' ) . 'M'
			: '$' . round( $n / 1000 ) . 'K';
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$min   = max( 0, (int) $s['min'] );
		$max   = max( $min + 1, (int) $s['max'] );
		$step  = max( 1, (int) $s['step'] );
		$start = min( $max, max( $min, (int) $s['start'] ) );
		$rate  = (float) $s['rate'];
		$fee   = (float) $s['fee'];
		$comm  = round( $start * $rate / 100 );
		$keep  = $comm - $fee;
		$id    = 'fx-sv-' . $this->get_id();
		$rate_text = rtrim( rtrim( number_format( $rate, 2 ), '0' ), '.' );
		?>
		<div class="fx-sv" data-fx-sv data-rate="<?php echo esc_attr( $rate ); ?>" data-fee="<?php echo esc_attr( $fee ); ?>">
			<?php if ( '' !== $s['eyebrow'] ) : ?>
				<div class="fx-eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></div>
			<?php endif; ?>
			<?php if ( '' !== $s['heading'] ) : ?>
				<h2 class="fx-sv-title"><?php echo esc_html( $s['heading'] ); ?></h2>
			<?php endif; ?>
			<div class="fx-sv-grid">
				<div class="fx-sv-slider">
					<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $s['price_label'] ); ?></label>
					<div class="fx-sv-price fx-num" data-fx-sv-price><?php echo esc_html( self::money( $start ) ); ?></div>
					<input id="<?php echo esc_attr( $id ); ?>" type="range" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>" value="<?php echo esc_attr( $start ); ?>" aria-label="<?php echo esc_attr( $s['price_label'] ); ?>">
					<div class="fx-sv-scale"><span><?php echo esc_html( self::short( $min ) ); ?></span><span><?php echo esc_html( self::short( $max ) ); ?></span></div>
				</div>
				<div class="fx-sv-rows" aria-live="polite">
					<div class="fx-sv-row"><span class="lbl"><?php echo esc_html( str_replace( '{rate}', $rate_text, $s['commission_label'] ) ); ?></span><span class="val fx-num" data-fx-sv-comm>−<?php echo esc_html( self::money( $comm ) ); ?></span></div>
					<div class="fx-sv-row"><span class="lbl"><?php echo esc_html( $s['fee_label'] ); ?></span><span class="val fx-num">−<?php echo esc_html( self::money( $fee ) ); ?></span></div>
					<div class="fx-sv-row keep"><span class="lbl"><?php echo esc_html( $s['keep_label'] ); ?></span><span class="val fx-num" data-fx-sv-keep><?php echo esc_html( ( $keep < 0 ? '−' : '+' ) . self::money( abs( $keep ) ) ); ?></span></div>
				</div>
			</div>
			<?php if ( '' !== $s['note'] ) : ?>
				<p class="fx-sv-note"><?php echo esc_html( $s['note'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
