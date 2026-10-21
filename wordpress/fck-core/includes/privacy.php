<?php
/**
 * Datenschutz für Personen.
 *
 * - „Profil ausblenden“ setzt die Person auf privat. Private Inhalte fallen aus Loops, Suche,
 *   REST und Sitemap, ohne dass jedes Template daran denken muss.
 * - Verweise auf die Person (Ansprechperson, Probetraining, Sponsoring) werden entfernt,
 *   weil ein Verweis per ID auch private Einträge auflösen würde.
 * - Kontaktfelder sind in fields.php `hide_from_rest`; hier zusätzlich aus Etch-Daten entfernt,
 *   solange „Kontakt zeigen“ aus ist.
 */

defined( 'ABSPATH' ) || exit;

function fck_person_refs_entfernen( int $person_id ): void {
	foreach ( array( 'ansprechperson_id', 'probetraining_kontakt_id', 'vermietung_kontakt_id' ) as $key ) {
		$posts = get_posts( array( 'post_type' => 'any', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => $key, 'value' => $person_id ) ) ) );
		foreach ( $posts as $id ) {
			delete_post_meta( $id, $key, $person_id );
		}
	}
	$opts = get_option( 'verein', array() );
	if ( is_array( $opts ) && (int) ( $opts['sponsoring_person_id'] ?? 0 ) === $person_id ) {
		unset( $opts['sponsoring_person_id'] );
		update_option( 'verein', $opts );
	}
}

function fck_person_sichtbarkeit( int $post_id ): void {
	$post = get_post( $post_id );
	if ( ! $post || 'person' !== $post->post_type ) {
		return;
	}
	fck_guard( "person-$post_id", function () use ( $post ) {
		$hide = (bool) get_post_meta( $post->ID, 'profil_ausblenden', true );
		if ( $hide && 'private' !== $post->post_status ) {
			update_post_meta( $post->ID, 'fck_war_status', $post->post_status );
			wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'private' ) );
			fck_person_refs_entfernen( $post->ID );
		} elseif ( ! $hide && 'private' === $post->post_status && get_post_meta( $post->ID, 'fck_war_status', true ) ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_status' => get_post_meta( $post->ID, 'fck_war_status', true ) ) );
			delete_post_meta( $post->ID, 'fck_war_status' );
		}
		// Titel aus dem Namen, damit Listen und Loops sortierbar sind.
		$vor  = trim( (string) get_post_meta( $post->ID, 'vorname', true ) );
		$nach = trim( (string) get_post_meta( $post->ID, 'nachname', true ) );
		if ( ( $vor || $nach ) && trim( "$vor $nach" ) !== $post->post_title ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_title' => trim( "$vor $nach" ) ) );
			// Ämter tragen den Namen im Titel.
			foreach ( get_posts( array( 'post_type' => 'amt', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'person_id', 'value' => $post->ID ) ) ) ) as $amt ) {
				fck_amt_refresh( $amt );
			}
		}
	} );
}
add_action( 'rwmb_person-daten_after_save_post', 'fck_person_sichtbarkeit' );

/** Kontaktdaten nicht an Etch ausliefern, wenn sie nicht öffentlich sein sollen. */
add_filter( 'etch/dynamic_data/post', function ( $data, $post_id = 0 ) {
	if ( ! is_array( $data ) || ! $post_id || 'person' !== get_post_type( $post_id ) ) {
		return $data;
	}
	if ( ! get_post_meta( $post_id, 'kontakt_oeffentlich', true ) && isset( $data['metabox'] ) && is_array( $data['metabox'] ) ) {
		unset( $data['metabox']['telefon'], $data['metabox']['email'] );
	}
	if ( isset( $data['metabox'] ) && is_array( $data['metabox'] ) ) {
		unset( $data['metabox']['user_id'], $data['metabox']['profil_ausblenden'], $data['metabox']['kontakt_oeffentlich'] );
	}
	return $data;
}, 10, 2 );

/** Sitemap: keine Personen, Spiele, Ämter und internen Typen. */
add_filter( 'wp_sitemaps_post_types', function ( array $types ): array {
	foreach ( array( 'person', 'spiel', 'tabelle', 'amt', 'gegner', 'partner', 'frage', 'download', 'historie' ) as $t ) {
		unset( $types[ $t ] );
	}
	return $types;
} );
add_filter( 'wp_sitemaps_taxonomies', function ( array $tax ): array {
	foreach ( array( 'altersklasse', 'spielposition', 'saison', 'wettbewerb', 'organisation', 'terminart' ) as $t ) {
		unset( $tax[ $t ] );
	}
	return $tax;
} );
