<?php
/**
 * Hält die Taxonomie `team` deckungsgleich mit den Mannschaften.
 *
 * Jede veröffentlichte Mannschaft hat genau einen Begriff mit gleichem Namen und Slug.
 * Die Begriff-ID liegt in der Mannschaft als `team_term_id`, die Mannschafts-ID im Begriff
 * als `mannschaft_id`. So kommt eine Teamseite an ihre Beiträge und ein Beitrag an seine Teamseite.
 */

defined( 'ABSPATH' ) || exit;

function fck_sync_team_term( int $post_id ): void {
	$post = get_post( $post_id );
	if ( ! $post || 'mannschaft' !== $post->post_type || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$term_id = (int) get_post_meta( $post_id, 'team_term_id', true );

	if ( 'publish' !== $post->post_status ) {
		return; // Entwürfe bekommen noch keinen Begriff; Begriffe werden nie automatisch gelöscht.
	}

	$args = array( 'slug' => $post->post_name, 'name' => $post->post_title );

	if ( $term_id && get_term( $term_id, 'team' ) ) {
		wp_update_term( $term_id, 'team', $args );
	} else {
		$existing = get_term_by( 'slug', $post->post_name, 'team' );
		$result   = $existing ? array( 'term_id' => $existing->term_id ) : wp_insert_term( $post->post_title, 'team', array( 'slug' => $post->post_name ) );
		if ( is_wp_error( $result ) ) {
			return;
		}
		$term_id = (int) $result['term_id'];
		update_post_meta( $post_id, 'team_term_id', $term_id );
	}

	update_term_meta( $term_id, 'mannschaft_id', $post_id );
	update_term_meta( $term_id, 'sortierung', (int) $post->menu_order );
}
add_action( 'save_post_mannschaft', 'fck_sync_team_term', 20 );
