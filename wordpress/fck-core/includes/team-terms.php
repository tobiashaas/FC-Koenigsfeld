<?php
/**
 * Hält die Taxonomie `team` deckungsgleich mit den Mannschaften.
 *
 * Jede veröffentlichte Mannschaft hat genau einen Begriff mit gleichem Namen und Slug.
 * Die Begriff-ID liegt in der Mannschaft als `team_term_id`, die Mannschafts-ID im Begriff
 * als `mannschaft_id`. So kommt eine Teamseite an ihre Beiträge und ein Beitrag an seine Teamseite.
 *
 * - Ein Begriff, der schon einer anderen Mannschaft gehört, wird nie übernommen.
 * - Ändert sich der Slug, merkt sich der Begriff den alten und leitet /team/<alt>/ weiter.
 * - Wird eine Mannschaft gelöscht, verschwindet ihr Begriff, wenn nichts mehr daran hängt.
 * - Fehler stehen an der Mannschaft in `fck_sync_fehler`.
 */

defined( 'ABSPATH' ) || exit;

function fck_sync_team_term( int $post_id ): void {
	$post = get_post( $post_id );
	if ( ! $post || 'mannschaft' !== $post->post_type || wp_is_post_revision( $post_id ) || 'publish' !== $post->post_status ) {
		return;
	}

	$term_id = (int) get_post_meta( $post_id, 'team_term_id', true );
	$term    = $term_id ? get_term( $term_id, 'team' ) : null;

	if ( $term && ! is_wp_error( $term ) ) {
		if ( $term->slug !== $post->post_name ) {
			$alt = (array) get_term_meta( $term_id, 'alte_slugs', true );
			$alt[] = $term->slug;
			update_term_meta( $term_id, 'alte_slugs', array_values( array_unique( array_filter( $alt ) ) ) );
		}
		$result = wp_update_term( $term_id, 'team', array( 'slug' => $post->post_name, 'name' => $post->post_title ) );
	} else {
		$existing = get_term_by( 'slug', $post->post_name, 'team' );
		if ( $existing && (int) get_term_meta( $existing->term_id, 'mannschaft_id', true ) && (int) get_term_meta( $existing->term_id, 'mannschaft_id', true ) !== $post_id ) {
			$result = new WP_Error( 'fck_team_slug', 'Der Begriff „' . $existing->name . '“ gehört schon zu einer anderen Mannschaft. Bitte Titelform (Slug) ändern.' );
		} else {
			$result = $existing ? array( 'term_id' => $existing->term_id ) : wp_insert_term( $post->post_title, 'team', array( 'slug' => $post->post_name ) );
		}
	}

	if ( is_wp_error( $result ) ) {
		update_post_meta( $post_id, 'fck_sync_fehler', 'Team-Begriff: ' . $result->get_error_message() );
		return;
	}

	$term_id = (int) $result['term_id'];
	update_post_meta( $post_id, 'team_term_id', $term_id );
	update_term_meta( $term_id, 'mannschaft_id', $post_id );
	update_term_meta( $term_id, 'sortierung', (int) $post->menu_order );
}
add_action( 'save_post_mannschaft', 'fck_sync_team_term', 20 );

add_action( 'before_delete_post', function ( int $post_id ) {
	if ( 'mannschaft' !== get_post_type( $post_id ) ) {
		return;
	}
	$term_id = (int) get_post_meta( $post_id, 'team_term_id', true );
	$term    = $term_id ? get_term( $term_id, 'team' ) : null;
	if ( $term && ! is_wp_error( $term ) ) {
		if ( 0 === (int) $term->count ) {
			wp_delete_term( $term_id, 'team' );
		} else {
			delete_term_meta( $term_id, 'mannschaft_id' );
		}
	}
} );

/** /team/<alter-slug>/ → neuer Slug (301). */
add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( ! preg_match( '#^team/([^/]+)#', $path, $m ) ) {
		return;
	}
	$terms = get_terms( array(
		'taxonomy'   => 'team',
		'hide_empty' => false,
		'meta_query' => array( array( 'key' => 'alte_slugs', 'value' => '"' . sanitize_title( $m[1] ) . '"', 'compare' => 'LIKE' ) ),
	) );
	if ( $terms && ! is_wp_error( $terms ) ) {
		wp_safe_redirect( get_term_link( $terms[0] ), 301 );
		exit;
	}
} );
