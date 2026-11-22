<?php
/**
 * Elementor integration: a "Fortify registry" widget category and the
 * registry widgets. Each widget reads the registry when it renders, so the
 * same published page shows each office its own data.
 */

namespace FortifyOffices;

defined( 'ABSPATH' ) || exit;

class Elementor_Integration {

	const CATEGORY = 'fortify-registry';

	const WIDGETS = array(
		'class-office-directory.php' => 'Office_Directory',
		'class-state-coverage.php'   => 'State_Coverage',
		'class-license-block.php'    => 'License_Block',
		'class-office-info.php'      => 'Office_Info',
		'class-office-finder.php'    => 'Office_Finder',
		'class-address-match.php'    => 'Address_Match',
	);

	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'widgets' ) );
	}

	public static function category( $manager ) {
		$manager->add_category( self::CATEGORY, array( 'title' => esc_html__( 'Fortify registry', 'fortify-offices' ), 'icon' => 'fa fa-map-marker' ) );
	}

	public static function widgets( $manager ) {
		require_once __DIR__ . '/widgets/trait-office-context.php';
		foreach ( self::WIDGETS as $file => $class ) {
			require_once __DIR__ . '/widgets/' . $file;
			$fqcn = __NAMESPACE__ . '\\Widgets\\' . $class;
			$manager->register( new $fqcn() );
		}
	}

	/** True while Elementor's editor or its preview frame is rendering. */
	public static function is_editing() {
		$p = \Elementor\Plugin::$instance;
		return ( $p->editor && $p->editor->is_edit_mode() ) || ( $p->preview && $p->preview->is_preview_mode() );
	}

	/** "Orange and Seminole Counties", "Palm Beach County (south)". */
	public static function county_label( array $counties ) {
		if ( ! $counties ) {
			return '';
		}
		$parts = array();
		$notes = array();
		foreach ( $counties as $c ) {
			if ( preg_match( '/^(.*?)\s*\((.+)\)$/', $c, $m ) ) {
				$parts[] = $m[1];
				$notes[] = $m[2];
			} else {
				$parts[] = $c;
			}
		}
		$last  = array_pop( $parts );
		$names = $parts ? implode( ', ', $parts ) . ' ' . __( 'and', 'fortify-offices' ) . ' ' . $last : $last;
		$label = $names . ' ' . ( count( $counties ) > 1 ? __( 'Counties', 'fortify-offices' ) : __( 'County', 'fortify-offices' ) );
		return $notes ? $label . ' (' . implode( ', ', $notes ) . ')' : $label;
	}

	/** Shared result card (finder list, address match). */
	public static function office_card( array $o, array $labels ) {
		$soon = 'coming_soon' === $o['status'];
		ob_start();
		?>
		<article class="fo-card<?php echo $soon ? ' is-soon' : ''; ?>">
			<div class="fo-card-head">
				<h3 class="fo-card-name"><?php echo esc_html( $o['name'] ); ?></h3>
				<span class="fo-badge <?php echo $soon ? 'is-soon' : 'is-live'; ?>"><?php echo esc_html( $soon ? $labels['soon'] : $labels['live'] ); ?></span>
			</div>
			<p class="fo-card-meta"><?php echo esc_html( implode( ' · ', array_filter( array( self::county_label( $o['counties'] ), $o['state_name'], $o['mls'] ) ) ) ); ?></p>
			<?php if ( ! $soon ) : ?>
				<a class="fo-card-link" href="<?php echo esc_url( $o['url'] ); ?>"><?php echo esc_html( $labels['visit'] ); ?></a>
			<?php endif; ?>
		</article>
		<?php
		return (string) ob_get_clean();
	}
}
