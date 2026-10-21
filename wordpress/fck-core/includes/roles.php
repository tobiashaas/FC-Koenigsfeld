<?php
/**
 * Rechte und Rollen.
 *
 * - Spiel/Tabelle (`spiele`) und Person/Amt (`personen`) haben eigene Rechte.
 * - fck_trainer: darf nur, was zur eigenen Mannschaft gehört. „Eigene Mannschaft“ kommt aus
 *   Benutzer → Person (Feld user_id) → aktive Ämter (Trainer, Betreuer) → Mannschaft.
 * - fck_foerderverein: eigene Inhalte plus alles mit Organisation „Förderverein“, eigene Options-Seite.
 * - fck_sync: nur Spiele und Tabellen schreiben (Pipeline, mit Anwendungspasswort).
 */

defined( 'ABSPATH' ) || exit;

function fck_type_caps( string $plural, bool $others = true ): array {
	$caps = array( "edit_$plural", "publish_$plural", "edit_published_$plural", "delete_$plural", "delete_published_$plural", "read_private_$plural", "edit_private_$plural", "delete_private_$plural" );
	if ( $others ) {
		$caps[] = "edit_others_$plural";
		$caps[] = "delete_others_$plural";
	}
	return $caps;
}

function fck_setup_roles(): void {
	$all = array_merge( fck_type_caps( 'spiele' ), fck_type_caps( 'personen' ), array( 'fck_edit_verein' ) );

	foreach ( array( 'administrator', 'editor' ) as $name ) {
		$role = get_role( $name );
		if ( ! $role ) {
			continue;
		}
		foreach ( $all as $cap ) {
			$role->add_cap( $cap );
		}
		$role->add_cap( 'fck_edit_foerderverein' );
	}

	$trainer = array_fill_keys(
		array_merge(
			array( 'read', 'upload_files', 'edit_posts', 'delete_posts', 'publish_posts', 'edit_published_posts', 'fck_trainer' ),
			fck_type_caps( 'spiele', false ),
			fck_type_caps( 'personen', false )
		),
		true
	);
	remove_role( 'fck_trainer' );
	add_role( 'fck_trainer', 'Trainer:in', $trainer );

	remove_role( 'fck_foerderverein' );
	add_role( 'fck_foerderverein', 'Förderverein', array_fill_keys(
		array( 'read', 'upload_files', 'edit_posts', 'delete_posts', 'publish_posts', 'edit_published_posts', 'delete_published_posts', 'fck_edit_foerderverein', 'fck_foerderverein' ),
		true
	) );

	remove_role( 'fck_sync' );
	add_role( 'fck_sync', 'Abgleich fussball.de', array_fill_keys(
		array_merge( array( 'read', 'upload_files', 'fck_sync' ), fck_type_caps( 'spiele' ) ),
		true
	) );
}

add_action( 'init', function () {
	if ( get_option( 'fck_core_roles' ) !== FCK_CORE_VERSION ) {
		fck_setup_roles();
		update_option( 'fck_core_roles', FCK_CORE_VERSION, false );
	}
}, 21 );

/** Mannschafts-IDs, für die ein Benutzer als Trainer:in oder Betreuer:in aktiv ist. */
function fck_user_teams( int $user_id ): array {
	static $cache = array();
	if ( isset( $cache[ $user_id ] ) ) {
		return $cache[ $user_id ];
	}
	$persons = get_posts( array(
		'post_type'      => 'person',
		'post_status'    => 'any',
		'fields'         => 'ids',
		'posts_per_page' => 5,
		'meta_query'     => array( array( 'key' => 'user_id', 'value' => $user_id ) ),
	) );
	$teams = array();
	if ( $persons ) {
		$amts = get_posts( array(
			'post_type'      => 'amt',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array( 'key' => 'person_id', 'value' => $persons, 'compare' => 'IN' ),
				array( 'key' => 'gremium', 'value' => array( 'trainer', 'betreuer' ), 'compare' => 'IN' ),
				array( 'key' => 'fck_aktiv', 'value' => '1' ),
			),
		) );
		foreach ( $amts as $amt ) {
			$teams = array_merge( $teams, fck_meta_ids( $amt, 'team_id' ) );
		}
	}
	return $cache[ $user_id ] = array_values( array_unique( $teams ) );
}

/** Teams, zu denen ein Inhalt gehört. */
function fck_post_teams( WP_Post $post ): array {
	switch ( $post->post_type ) {
		case 'mannschaft':
			return array( $post->ID );
		case 'spiel':
		case 'amt':
		case 'frage':
			return fck_meta_ids( $post->ID, 'team_id' );
		case 'person':
			$teams = fck_meta_ids( $post->ID, 'mannschaften' );
			foreach ( get_posts( array( 'post_type' => 'amt', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'person_id', 'value' => $post->ID ) ) ) ) as $amt ) {
				$teams = array_merge( $teams, fck_meta_ids( $amt, 'team_id' ) );
			}
			return $teams;
		case 'post':
		case 'termin':
		case 'download':
			$teams = array();
			foreach ( wp_get_object_terms( $post->ID, 'team' ) as $term ) {
				$teams[] = (int) get_term_meta( $term->term_id, 'mannschaft_id', true );
			}
			return array_filter( $teams );
	}
	return array();
}

function fck_has_org( WP_Post $post, string $slug ): bool {
	return taxonomy_exists( 'organisation' ) && has_term( $slug, 'organisation', $post );
}

/**
 * Einzelrechte: Trainer:innen dürfen fremde Inhalte ihrer Mannschaft bearbeiten,
 * der Förderverein fremde Inhalte mit Organisation „Förderverein“.
 */
add_filter( 'map_meta_cap', function ( array $caps, string $cap, int $user_id, array $args ): array {
	if ( ! in_array( $cap, array( 'edit_post', 'delete_post', 'publish_post' ), true ) || empty( $args[0] ) ) {
		return $caps;
	}
	$post = get_post( (int) $args[0] );
	if ( ! $post ) {
		return $caps;
	}
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return $caps;
	}

	if ( in_array( 'fck_trainer', (array) $user->roles, true ) && 'delete_post' !== $cap ) {
		if ( in_array( $post->post_type, array( 'mannschaft', 'spiel', 'person', 'amt', 'post', 'termin', 'frage' ), true ) ) {
			if ( array_intersect( fck_post_teams( $post ), fck_user_teams( $user_id ) ) ) {
				return array( 'read' );
			}
		}
	}

	if ( in_array( 'fck_foerderverein', (array) $user->roles, true ) ) {
		if ( in_array( $post->post_type, array( 'post', 'termin', 'download', 'projekt' ), true ) && fck_has_org( $post, 'foerderverein' ) ) {
			return array( 'read' );
		}
	}

	return $caps;
}, 10, 4 );

/** Trainer:innen sehen in Mannschaften nur ihre eigenen. */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'mannschaft' !== $q->get( 'post_type' ) ) {
		return;
	}
	$user = wp_get_current_user();
	if ( in_array( 'fck_trainer', (array) $user->roles, true ) ) {
		$q->set( 'post__in', fck_user_teams( $user->ID ) ?: array( 0 ) );
	}
} );
