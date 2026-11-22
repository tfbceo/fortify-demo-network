<?php
/**
 * Office Profile: office-owned content stored on the office's own site (the
 * `fortify_office_profile` option): market intro, team members and local
 * reviews. Edited on the sitelet's "Office Profile" screen by its local
 * editors (capability `edit_pages` by default); not available on site 1.
 * Team photos are stored as URLs: office sites pick images from the main
 * site's media library (Sitelets blocks uploads there), so attachment IDs
 * would not resolve on the office site.
 */

namespace FortifyOffices;

defined( 'ABSPATH' ) || exit;

class Profile {

	const OPTION = 'fortify_office_profile';
	const PAGE   = 'fortify-office-profile';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_fortify_office_profile_save', array( __CLASS__, 'save' ) );
	}

	/** Who may edit the profile on an office site. */
	public static function cap() {
		return apply_filters( 'fortify_office_profile_cap', 'edit_pages' );
	}

	/** Profile of the office site $blog_id (default current), normalized. */
	public static function get( $blog_id = null ) {
		$blog_id = $blog_id ? (int) $blog_id : get_current_blog_id();
		$raw     = get_blog_option( $blog_id, self::OPTION, array() );
		return self::normalize( is_array( $raw ) ? $raw : array() );
	}

	public static function normalize( array $p ) {
		$p = wp_parse_args( $p, array( 'intro' => '', 'team' => array(), 'reviews' => array() ) );
		$p['team']    = array_values( array_filter( array_map( static function ( $m ) {
			$m = wp_parse_args( (array) $m, array( 'name' => '', 'title' => '', 'bio' => '', 'photo' => '', 'sample' => false ) );
			$m['photo'] = is_string( $m['photo'] ) ? $m['photo'] : '';
			return '' === trim( $m['name'] ) ? null : $m;
		}, (array) $p['team'] ) ) );
		$p['reviews'] = array_values( array_filter( array_map( static function ( $r ) {
			$r = wp_parse_args( (array) $r, array( 'name' => '', 'location' => '', 'rating' => 5, 'text' => '', 'sample' => false ) );
			$r['rating'] = max( 0, min( 5, (int) $r['rating'] ) );
			return '' === trim( $r['text'] ) ? null : $r;
		}, (array) $p['reviews'] ) ) );
		return $p;
	}

	/**
	 * Sample profile for an office: used in the editor on site 1 (with
	 * "Preview with office") and to seed new office sites in phase 6.
	 * Everything is marked sample.
	 */
	public static function sample( ?array $office = null ) {
		$name = $office ? $office['name'] : __( 'your', 'fortify-offices' );
		$area = $office && $office['counties'] ? implode( ' and ', array_map( static fn( $c ) => preg_replace( '/\s*\(.*\)$/', '', $c ), $office['counties'] ) ) : __( 'the area', 'fortify-offices' );
		return array(
			'intro'   => '<p>' . sprintf(
				/* translators: 1: office name, 2: counties */
				esc_html__( 'Sample market intro for the %1$s office. The local team replaces this with a short description of the market: what homes sell for, how quickly they move, and what sellers in %2$s should know before listing.', 'fortify-offices' ),
				$name,
				$area
			) . '</p>',
			'team'    => array(
				array( 'name' => 'Sample Broker (demo)', 'title' => __( 'Broker of record', 'fortify-offices' ), 'bio' => __( 'Sample bio. The broker reviews every listing from this office before it goes to the MLS.', 'fortify-offices' ), 'photo' => '', 'sample' => true ),
				array( 'name' => 'Sample Agent A', 'title' => __( 'Listing coordinator', 'fortify-offices' ), 'bio' => __( 'Sample bio. Helps sellers prepare photos, disclosures and showing instructions.', 'fortify-offices' ), 'photo' => '', 'sample' => true ),
				array( 'name' => 'Sample Agent B', 'title' => __( 'Seller support', 'fortify-offices' ), 'bio' => __( 'Sample bio. Answers seller questions from listing to closing.', 'fortify-offices' ), 'photo' => '', 'sample' => true ),
			),
			'reviews' => array(
				array( 'name' => 'Sample Seller 1', 'location' => $office ? $office['city'] : '', 'rating' => 5, 'text' => __( 'Sample review text. A real review from a seller of this office goes here.', 'fortify-offices' ), 'sample' => true ),
				array( 'name' => 'Sample Seller 2', 'location' => $office ? $office['city'] : '', 'rating' => 5, 'text' => __( 'Sample review text. Reviews are added by the office on its Office Profile screen.', 'fortify-offices' ), 'sample' => true ),
				array( 'name' => 'Sample Seller 3', 'location' => $office ? $office['city'] : '', 'rating' => 4, 'text' => __( 'Sample review text. Each review shows the reviewer name, location and rating.', 'fortify-offices' ), 'sample' => true ),
			),
		);
	}

	/* ------------------------------------------------------------------
	 * Admin screen (office sites only)
	 * ---------------------------------------------------------------- */

	public static function menu() {
		if ( is_main_site() ) {
			return;
		}
		$hook = add_menu_page(
			__( 'Office Profile', 'fortify-offices' ),
			__( 'Office Profile', 'fortify-offices' ),
			self::cap(),
			self::PAGE,
			array( __CLASS__, 'render' ),
			'dashicons-id-alt',
			3
		);
		add_action( 'admin_print_scripts-' . $hook, static function () {
			wp_enqueue_media();
			wp_enqueue_script( 'fortify-office-profile', plugins_url( 'assets/profile-admin.js', FORTIFY_OFFICES_FILE ), array( 'jquery' ), Frontend::asset_version( 'assets/profile-admin.js' ), true );
		} );
	}

	public static function render() {
		if ( is_main_site() || ! current_user_can( self::cap() ) ) {
			wp_die( esc_html__( 'You cannot edit this office profile.', 'fortify-offices' ) );
		}
		$p      = self::get();
		$office = fortify_office_for_site();
		?>
		<div class="wrap fo-profile">
			<h1><?php esc_html_e( 'Office Profile', 'fortify-offices' ); ?></h1>
			<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Profile saved.', 'fortify-offices' ); ?></p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Your office’s own content. It appears on your office pages through the Market Intro, Team List and Local Reviews blocks.', 'fortify-offices' ); ?></p>

			<?php if ( $office ) : ?>
				<div class="fo-profile-registry">
					<h2><?php esc_html_e( 'Office details (managed by Fortify)', 'fortify-offices' ); ?></h2>
					<p><?php echo esc_html( $office['name'] . ' · ' . $office['phone'] . ' · ' . $office['address'] ); ?><br>
					<?php echo esc_html( $office['hours'] . ' · ' . $office['mls'] ); ?></p>
					<p class="description"><?php esc_html_e( 'To change these, contact Fortify. They come from the office registry.', 'fortify-offices' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'This site is not linked to an office in the registry yet, so registry details are not shown.', 'fortify-offices' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fortify_office_profile_save">
				<?php wp_nonce_field( 'fortify_office_profile_save' ); ?>

				<h2><?php esc_html_e( 'Market intro', 'fortify-offices' ); ?></h2>
				<?php
				wp_editor( $p['intro'], 'fo_intro', array(
					'textarea_name' => 'intro',
					'media_buttons' => false,
					'teeny'         => true,
					'textarea_rows' => 6,
				) );
				?>

				<h2><?php esc_html_e( 'Team', 'fortify-offices' ); ?></h2>
				<div class="fo-rows" data-fo-rows="team">
					<?php
					foreach ( $p['team'] as $i => $m ) {
						self::team_row( $i, $m );
					}
					?>
				</div>
				<script type="text/html" id="tmpl-fo-team-row"><?php self::team_row( '__i__', array( 'name' => '', 'title' => '', 'bio' => '', 'photo' => '', 'sample' => false ) ); ?></script>
				<p><button type="button" class="button" data-fo-add="team"><?php esc_html_e( 'Add team member', 'fortify-offices' ); ?></button></p>

				<h2><?php esc_html_e( 'Local reviews', 'fortify-offices' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Only add reviews you have permission to publish. Mark demo content as sample: it is labelled on the site.', 'fortify-offices' ); ?></p>
				<div class="fo-rows" data-fo-rows="reviews">
					<?php
					foreach ( $p['reviews'] as $i => $r ) {
						self::review_row( $i, $r );
					}
					?>
				</div>
				<script type="text/html" id="tmpl-fo-reviews-row"><?php self::review_row( '__i__', array( 'name' => '', 'location' => '', 'rating' => 5, 'text' => '', 'sample' => false ) ); ?></script>
				<p><button type="button" class="button" data-fo-add="reviews"><?php esc_html_e( 'Add review', 'fortify-offices' ); ?></button></p>

				<?php submit_button( __( 'Save profile', 'fortify-offices' ) ); ?>
			</form>
		</div>
		<style>
			.fo-profile-registry { background: #fff; border: 1px solid #dcdcde; padding: 4px 16px; margin: 16px 0; max-width: 900px; }
			.fo-row { background: #fff; border: 1px solid #dcdcde; padding: 12px 16px; margin: 0 0 12px; max-width: 900px; display: grid; grid-template-columns: 120px 1fr; gap: 8px 16px; align-items: start; }
			.fo-row label { font-weight: 600; padding-top: 6px; }
			.fo-row input[type=text], .fo-row textarea, .fo-row select { width: 100%; }
			.fo-row .fo-row-actions { grid-column: 2; display: flex; gap: 12px; align-items: center; }
			.fo-photo { display: flex; gap: 10px; align-items: center; }
			.fo-photo img { width: 56px; height: 56px; object-fit: cover; border-radius: 50%; }
		</style>
		<?php
	}

	private static function team_row( $i, array $m ) {
		$n = 'team[' . $i . ']';
		?>
		<div class="fo-row">
			<label><?php esc_html_e( 'Name', 'fortify-offices' ); ?></label><input type="text" name="<?php echo esc_attr( $n ); ?>[name]" value="<?php echo esc_attr( $m['name'] ); ?>">
			<label><?php esc_html_e( 'Title', 'fortify-offices' ); ?></label><input type="text" name="<?php echo esc_attr( $n ); ?>[title]" value="<?php echo esc_attr( $m['title'] ); ?>">
			<label><?php esc_html_e( 'Bio', 'fortify-offices' ); ?></label><textarea rows="3" name="<?php echo esc_attr( $n ); ?>[bio]"><?php echo esc_textarea( $m['bio'] ); ?></textarea>
			<label><?php esc_html_e( 'Photo', 'fortify-offices' ); ?></label>
			<div class="fo-photo">
				<input type="hidden" name="<?php echo esc_attr( $n ); ?>[photo]" value="<?php echo esc_attr( $m['photo'] ); ?>" data-fo-photo-id>
				<span data-fo-photo-preview><?php echo $m['photo'] ? '<img src="' . esc_url( $m['photo'] ) . '" alt="">' : ''; ?></span>
				<button type="button" class="button" data-fo-photo-pick><?php esc_html_e( 'Choose photo', 'fortify-offices' ); ?></button>
				<button type="button" class="button-link" data-fo-photo-clear><?php esc_html_e( 'Remove photo', 'fortify-offices' ); ?></button>
			</div>
			<div class="fo-row-actions">
				<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[sample]" value="1"<?php checked( ! empty( $m['sample'] ) ); ?>> <?php esc_html_e( 'Sample (demo content)', 'fortify-offices' ); ?></label>
				<button type="button" class="button-link button-link-delete" data-fo-remove><?php esc_html_e( 'Remove', 'fortify-offices' ); ?></button>
			</div>
		</div>
		<?php
	}

	private static function review_row( $i, array $r ) {
		$n = 'reviews[' . $i . ']';
		?>
		<div class="fo-row">
			<label><?php esc_html_e( 'Reviewer', 'fortify-offices' ); ?></label><input type="text" name="<?php echo esc_attr( $n ); ?>[name]" value="<?php echo esc_attr( $r['name'] ); ?>">
			<label><?php esc_html_e( 'Location', 'fortify-offices' ); ?></label><input type="text" name="<?php echo esc_attr( $n ); ?>[location]" value="<?php echo esc_attr( $r['location'] ); ?>">
			<label><?php esc_html_e( 'Rating', 'fortify-offices' ); ?></label>
			<select name="<?php echo esc_attr( $n ); ?>[rating]">
				<?php for ( $s = 5; $s >= 0; $s-- ) : ?>
					<option value="<?php echo (int) $s; ?>"<?php selected( (int) $r['rating'], $s ); ?>><?php echo $s ? esc_html( str_repeat( '★', $s ) ) : esc_html__( 'No rating', 'fortify-offices' ); ?></option>
				<?php endfor; ?>
			</select>
			<label><?php esc_html_e( 'Review', 'fortify-offices' ); ?></label><textarea rows="3" name="<?php echo esc_attr( $n ); ?>[text]"><?php echo esc_textarea( $r['text'] ); ?></textarea>
			<div class="fo-row-actions">
				<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[sample]" value="1"<?php checked( ! empty( $r['sample'] ) ); ?>> <?php esc_html_e( 'Sample (demo content)', 'fortify-offices' ); ?></label>
				<button type="button" class="button-link button-link-delete" data-fo-remove><?php esc_html_e( 'Remove', 'fortify-offices' ); ?></button>
			</div>
		</div>
		<?php
	}

	public static function save() {
		if ( is_main_site() || ! current_user_can( self::cap() ) ) {
			wp_die( esc_html__( 'You cannot edit this office profile.', 'fortify-offices' ), 403 );
		}
		check_admin_referer( 'fortify_office_profile_save' );
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field is sanitized in sanitize().
		self::update( array(
			'intro'   => isset( $_POST['intro'] ) ? wp_unslash( $_POST['intro'] ) : '',
			'team'    => isset( $_POST['team'] ) ? wp_unslash( (array) $_POST['team'] ) : array(),
			'reviews' => isset( $_POST['reviews'] ) ? wp_unslash( (array) $_POST['reviews'] ) : array(),
		) );
		// phpcs:enable
		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE, 'updated' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/** Sanitize and store a profile on the current site. */
	public static function update( array $p ) {
		$clean = array(
			'intro'   => wp_kses_post( (string) ( $p['intro'] ?? '' ) ),
			'team'    => array(),
			'reviews' => array(),
		);
		foreach ( (array) ( $p['team'] ?? array() ) as $m ) {
			$clean['team'][] = array(
				'name'   => sanitize_text_field( $m['name'] ?? '' ),
				'title'  => sanitize_text_field( $m['title'] ?? '' ),
				'bio'    => sanitize_textarea_field( $m['bio'] ?? '' ),
				'photo'  => esc_url_raw( (string) ( $m['photo'] ?? '' ) ),
				'sample' => ! empty( $m['sample'] ),
			);
		}
		foreach ( (array) ( $p['reviews'] ?? array() ) as $r ) {
			$clean['reviews'][] = array(
				'name'     => sanitize_text_field( $r['name'] ?? '' ),
				'location' => sanitize_text_field( $r['location'] ?? '' ),
				'rating'   => max( 0, min( 5, (int) ( $r['rating'] ?? 5 ) ) ),
				'text'     => sanitize_textarea_field( $r['text'] ?? '' ),
				'sample'   => ! empty( $r['sample'] ),
			);
		}
		update_option( self::OPTION, self::normalize( $clean ), false );
	}
}
