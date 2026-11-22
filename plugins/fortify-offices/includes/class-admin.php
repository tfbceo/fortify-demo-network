<?php
/**
 * Editing screen for office records on the main site: one meta box with the
 * registry fields, and list columns for state, status and ZIP count.
 */

namespace FortifyOffices;

defined( 'ABSPATH' ) || exit;

class Admin {

	public static function init() {
		add_action( 'add_meta_boxes_' . Registry::POST_TYPE, array( __CLASS__, 'meta_box' ) );
		add_action( 'save_post_' . Registry::POST_TYPE, array( __CLASS__, 'save' ), 5, 2 );
		add_filter( 'manage_' . Registry::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . Registry::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
	}

	public static function meta_box() {
		add_meta_box( 'fortify-office-fields', __( 'Office registry fields', 'fortify-offices' ), array( __CLASS__, 'render' ), Registry::POST_TYPE, 'normal', 'high' );
	}

	public static function render( \WP_Post $post ) {
		wp_nonce_field( 'fortify_office_save', 'fortify_office_nonce' );
		echo '<p>' . esc_html__( 'The slug (below the title, or Quick Edit) is the office site path. Sample data in the demo: phones, addresses, brokers and license numbers are placeholders.', 'fortify-offices' ) . '</p>';
		echo '<table class="form-table" role="presentation">';
		foreach ( Registry::FIELDS as $key => $label ) {
			$value = get_post_meta( $post->ID, '_fo_' . $key, true );
			$id    = 'fo_' . $key;
			echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
			if ( 'status' === $key ) {
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
				foreach ( array( 'live' => 'Live', 'coming_soon' => 'Coming soon', 'hidden' => 'Hidden' ) as $v => $l ) {
					echo '<option value="' . esc_attr( $v ) . '"' . selected( $value ? $value : 'hidden', $v, false ) . '>' . esc_html( $l ) . '</option>';
				}
				echo '</select>';
			} elseif ( in_array( $key, array( 'counties', 'zips' ), true ) ) {
				$text = implode( 'counties' === $key ? "\n" : ', ', Registry::split_list( $value ) );
				echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" rows="' . ( 'zips' === $key ? 6 : 3 ) . '" class="large-text code">' . esc_textarea( $text ) . '</textarea>';
				if ( 'zips' === $key ) {
					/* translators: %d: number of ZIP codes */
					echo '<p class="description">' . esc_html( sprintf( __( '%d ZIP codes. A ZIP belongs to one office only.', 'fortify-offices' ), count( Registry::split_list( $value ) ) ) ) . '</p>';
				}
			} else {
				echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['fortify_office_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fortify_office_nonce'] ), 'fortify_office_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( array_keys( Registry::FIELDS ) as $key ) {
			$raw = isset( $_POST[ 'fo_' . $key ] ) ? wp_unslash( $_POST[ 'fo_' . $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
			switch ( $key ) {
				case 'counties':
					$value = array_map( 'sanitize_text_field', Registry::split_list( $raw ) );
					break;
				case 'zips':
					$value = array_values( array_unique( array_filter( Registry::split_list( $raw ), static function ( $z ) {
						return (bool) preg_match( '/^\d{5}$/', $z );
					} ) ) );
					sort( $value );
					break;
				case 'status':
					$value = in_array( $raw, Registry::STATUSES, true ) ? $raw : 'hidden';
					break;
				case 'blog_id':
					$value = absint( $raw );
					break;
				case 'state':
					$value = strtoupper( substr( sanitize_text_field( $raw ), 0, 2 ) );
					break;
				case 'lat':
				case 'lng':
					$value = is_numeric( $raw ) ? (string) (float) $raw : '';
					break;
				default:
					$value = sanitize_text_field( $raw );
			}
			update_post_meta( $post_id, '_fo_' . $key, $value );
		}
		Registry::flush();
	}

	public static function columns( $cols ) {
		$date = $cols['date'] ?? null;
		unset( $cols['date'] );
		$cols['fo_state']  = __( 'State', 'fortify-offices' );
		$cols['fo_status'] = __( 'Status', 'fortify-offices' );
		$cols['fo_zips']   = __( 'ZIPs', 'fortify-offices' );
		$cols['fo_site']   = __( 'Site', 'fortify-offices' );
		if ( $date ) {
			$cols['date'] = $date;
		}
		return $cols;
	}

	public static function column( $col, $post_id ) {
		switch ( $col ) {
			case 'fo_state':
				echo esc_html( get_post_meta( $post_id, '_fo_state', true ) );
				break;
			case 'fo_status':
				echo esc_html( str_replace( '_', ' ', (string) get_post_meta( $post_id, '_fo_status', true ) ) );
				break;
			case 'fo_zips':
				echo esc_html( (string) count( Registry::split_list( get_post_meta( $post_id, '_fo_zips', true ) ) ) );
				break;
			case 'fo_site':
				$blog = (int) get_post_meta( $post_id, '_fo_blog_id', true );
				echo $blog ? esc_html( '#' . $blog ) : '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'Not created yet', 'fortify-offices' ) . '</span>';
				break;
		}
	}
}
