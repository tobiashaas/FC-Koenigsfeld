<?php
/**
 * Rechenwerte und Automatik. Etch kennt kein „jetzt“ und kann nicht rechnen,
 * darum stehen die Ergebnisse als Felder (`fck_*`) an den Inhalten.
 *
 * Spiel:      fck_ausgang (s/u/n), fck_tag (Y-m-d), Titel, Slug fd-<id>, Saison, ursprünglicher Termin
 * Mannschaft: fck_naechstes_spiel_id, fck_letztes_spiel_id, fck_form, fck_jahrgaenge
 * Amt:        fck_aktiv, Titel
 * Partner:    fck_stufe_rang, Status „abgelaufen“
 * Download:   fck_ersetzt_durch
 * Beitrag:    Mannschaft aus dem verknüpften Spiel, Anpinnen bis
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ Spiel */

/** Alten Anstoß merken, bevor er sich ändert (Verlegung). */
add_filter( 'update_post_metadata', function ( $check, $object_id, $meta_key, $meta_value ) {
	if ( 'anstoss' === $meta_key && 'spiel' === get_post_type( $object_id ) ) {
		$old = get_post_meta( $object_id, 'anstoss', true );
		if ( $old && $old !== $meta_value && ! get_post_meta( $object_id, 'anstoss_urspruenglich', true ) ) {
			update_post_meta( $object_id, 'anstoss_urspruenglich', $old );
		}
	}
	return $check;
}, 10, 4 );

function fck_spiel_ausgang( int $id ): string {
	$status = get_post_meta( $id, 'status', true );
	$we     = get_post_meta( $id, 'wertung_eigen', true );
	$wg     = get_post_meta( $id, 'wertung_gegner', true );
	if ( '' !== $we && '' !== $wg ) {
		$e = (int) $we;
		$g = (int) $wg;
	} elseif ( 'beendet' === $status ) {
		$te = get_post_meta( $id, 'tore_eigen', true );
		$tg = get_post_meta( $id, 'tore_gegner', true );
		if ( '' === $te || '' === $tg ) {
			return '';
		}
		$e = (int) $te;
		$g = (int) $tg;
		if ( $e === $g && 'ie' === get_post_meta( $id, 'zusatz', true ) ) {
			$e = (int) get_post_meta( $id, 'ie_eigen', true );
			$g = (int) get_post_meta( $id, 'ie_gegner', true );
		}
	} else {
		return '';
	}
	return $e > $g ? 's' : ( $e < $g ? 'n' : 'u' );
}

function fck_spiel_refresh( int $id ): void {
	$post = get_post( $id );
	if ( ! $post || 'spiel' !== $post->post_type ) {
		return;
	}
	fck_guard( "spiel-$id", function () use ( $post, $id ) {
		$anstoss = (string) get_post_meta( $id, 'anstoss', true );
		update_post_meta( $id, 'fck_tag', substr( $anstoss, 0, 10 ) );
		update_post_meta( $id, 'fck_ausgang', fck_spiel_ausgang( $id ) );

		// Saison aus dem Anstoß, wenn keine gesetzt ist.
		if ( $anstoss && ! wp_get_object_terms( $id, 'saison', array( 'fields' => 'ids' ) ) ) {
			$saison = fck_saison_aus_datum( $anstoss );
			$term   = term_exists( $saison, 'saison' ) ?: wp_insert_term( $saison, 'saison', array( 'slug' => sanitize_title( str_replace( '/', '-', $saison ) ) ) );
			if ( ! is_wp_error( $term ) ) {
				wp_set_object_terms( $id, (int) $term['term_id'], 'saison' );
			}
		}

		// Titel „1. Herren – SV Deggenhausertal“ und Slug fd-<id> für eindeutige Pipeline-Treffer.
		$team   = (int) get_post_meta( $id, 'team_id', true );
		$kurz   = $team ? ( get_post_meta( $team, 'kurzname', true ) ?: get_the_title( $team ) ) : '';
		$art    = get_post_meta( $id, 'art', true );
		$gegner = (int) get_post_meta( $id, 'gegner_id', true );
		$gname  = in_array( $art, array( 'turnier', 'spielfest' ), true )
			? (string) get_post_meta( $id, 'turnier_name', true )
			: ( $gegner ? get_the_title( $gegner ) : (string) get_post_meta( $id, 'gegner', true ) );
		$title  = trim( $kurz . ( $gname ? ' – ' . $gname : '' ), ' –' );
		$update = array( 'ID' => $id );
		if ( $title && $title !== $post->post_title ) {
			$update['post_title'] = $title;
		}
		$fd = trim( (string) get_post_meta( $id, 'fd_spiel_id', true ) );
		if ( $fd && 'fd-' . sanitize_title( $fd ) !== $post->post_name ) {
			$update['post_name'] = 'fd-' . sanitize_title( $fd );
		}
		if ( count( $update ) > 1 ) {
			wp_update_post( $update );
		}

		if ( $team ) {
			fck_team_refresh( $team );
		}
	} );
}
add_action( 'rwmb_spiel-daten_after_save_post', 'fck_spiel_refresh' );
add_action( 'rwmb_spiel-ergebnis_after_save_post', 'fck_spiel_refresh' );

/** Spiel per Pipeline-ID finden (Slug hat einen Index). */
function fck_spiel_by_fd_id( string $fd_id ): int {
	$found = get_posts( array( 'post_type' => 'spiel', 'name' => 'fd-' . sanitize_title( $fd_id ), 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1 ) );
	return $found ? (int) $found[0] : 0;
}

/* ------------------------------------------------------------------ Mannschaft */

function fck_team_spiele( int $team, array $extra ): array {
	return get_posts( array_merge( array(
		'post_type'      => 'spiel',
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'posts_per_page' => 1,
		'meta_key'       => 'anstoss',
		'orderby'        => 'meta_value',
	), $extra, array(
		'meta_query' => array_merge( array( array( 'key' => 'team_id', 'value' => $team ) ), $extra['meta_query'] ?? array() ),
	) ) );
}

function fck_jahrgaenge( int $team ): string {
	if ( $manual = trim( (string) get_post_meta( $team, 'jahrgaenge_abweichend', true ) ) ) {
		return $manual;
	}
	$terms = wp_get_object_terms( $team, 'altersklasse' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$u_von = (int) get_term_meta( $terms[0]->term_id, 'u_von', true );
	$u_bis = (int) get_term_meta( $terms[0]->term_id, 'u_bis', true );
	if ( ! $u_von || ! $u_bis ) {
		return '';
	}
	$start = fck_option( 'saison_start' ) ?: ( substr( fck_saison_aus_datum( fck_today() ), 0, 4 ) . '-07-01' );
	$ref   = (int) substr( $start, 0, 4 ) + 1; // Saison 2026/27 → Stichtag 2027
	return ( $ref - $u_bis ) . '/' . ( $ref - $u_von );
}

function fck_team_refresh( int $team ): void {
	if ( 'mannschaft' !== get_post_type( $team ) ) {
		return;
	}
	$now = fck_now();

	$next = fck_team_spiele( $team, array(
		'order'      => 'ASC',
		'meta_query' => array(
			array( 'key' => 'anstoss', 'value' => $now, 'compare' => '>=' ),
			array( 'key' => 'status', 'value' => array( 'geplant' ), 'compare' => 'IN' ),
		),
	) );
	$last = fck_team_spiele( $team, array(
		'order'      => 'DESC',
		'meta_query' => array( array( 'key' => 'fck_ausgang', 'value' => array( 's', 'u', 'n' ), 'compare' => 'IN' ) ),
	) );
	$form = fck_team_spiele( $team, array(
		'order'          => 'DESC',
		'posts_per_page' => 5,
		'meta_query'     => array(
			array( 'key' => 'fck_ausgang', 'value' => array( 's', 'u', 'n' ), 'compare' => 'IN' ),
			array( 'key' => 'art', 'value' => array( 'liga', 'pokal' ), 'compare' => 'IN' ),
		),
		'tax_query'      => ( $s = fck_option( 'saison' ) ) ? array( array( 'taxonomy' => 'saison', 'field' => 'name', 'terms' => $s ) ) : array(),
	) );

	update_post_meta( $team, 'fck_naechstes_spiel_id', $next ? (int) $next[0] : '' );
	update_post_meta( $team, 'fck_letztes_spiel_id', $last ? (int) $last[0] : '' );
	update_post_meta( $team, 'fck_form', strtoupper( implode( '', array_map( fn( $id ) => get_post_meta( $id, 'fck_ausgang', true ), array_reverse( $form ) ) ) ) );
	update_post_meta( $team, 'fck_jahrgaenge', fck_jahrgaenge( $team ) );
}
add_action( 'rwmb_mannschaft-daten_after_save_post', 'fck_team_refresh' );

function fck_refresh_all_teams(): void {
	foreach ( get_posts( array( 'post_type' => 'mannschaft', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1 ) ) as $team ) {
		fck_team_refresh( $team );
	}
}

/** Saison oder Saisonstart geändert → Jahrgänge und Form neu. */
add_action( 'rwmb_verein-saison_after_save_post', 'fck_refresh_all_teams' );

/* ------------------------------------------------------------------ Amt */

function fck_amt_refresh( int $id ): void {
	if ( 'amt' !== get_post_type( $id ) ) {
		return;
	}
	fck_guard( "amt-$id", function () use ( $id ) {
		$person = (int) get_post_meta( $id, 'person_id', true );
		$bis    = (string) get_post_meta( $id, 'bis', true );
		$von    = (string) get_post_meta( $id, 'von', true );
		$aktiv  = $person && 'publish' === get_post_status( $person )
			&& ( ! $bis || $bis >= fck_today() )
			&& ( ! $von || $von <= fck_today() );
		update_post_meta( $id, 'fck_aktiv', $aktiv ? '1' : '0' );

		$gremien = FCK_GREMIEN;
		$funk    = get_post_meta( $id, 'funktion', true ) ?: ( $gremien[ get_post_meta( $id, 'gremium', true ) ] ?? '' );
		$team    = (int) get_post_meta( $id, 'team_id', true );
		$title   = trim( ( $person ? get_the_title( $person ) : '' ) . ' – ' . $funk . ( $team ? ' ' . ( get_post_meta( $team, 'kurzname', true ) ?: get_the_title( $team ) ) : '' ), ' –' );
		if ( $title !== get_the_title( $id ) ) {
			wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
		}
	} );
}
add_action( 'rwmb_amt-daten_after_save_post', 'fck_amt_refresh' );

/* Person privat/öffentlich → ihre Ämter neu bewerten. */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( 'person' === $post->post_type && $new !== $old ) {
		foreach ( get_posts( array( 'post_type' => 'amt', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => 'person_id', 'value' => $post->ID ) ) ) ) as $amt ) {
			fck_amt_refresh( $amt );
		}
	}
}, 10, 3 );

/* ------------------------------------------------------------------ Partner */

function fck_partner_refresh( int $id ): void {
	if ( 'partner' !== get_post_type( $id ) ) {
		return;
	}
	fck_guard( "partner-$id", function () use ( $id ) {
		$rang = array_search( get_post_meta( $id, 'stufe', true ), array_keys( FCK_STUFEN ), true );
		update_post_meta( $id, 'fck_stufe_rang', false === $rang ? 99 : $rang );

		$bis        = (string) get_post_meta( $id, 'laufzeit_bis', true );
		$unbefristet = (bool) get_post_meta( $id, 'unbefristet', true );
		$abgelaufen = ! $unbefristet && $bis && $bis < fck_today();
		$status     = get_post_status( $id );
		if ( $abgelaufen && 'publish' === $status ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'abgelaufen' ) );
		} elseif ( ! $abgelaufen && 'abgelaufen' === $status ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
	} );
}
add_action( 'rwmb_partner-daten_after_save_post', 'fck_partner_refresh' );

/* ------------------------------------------------------------------ Download */

add_action( 'rwmb_download-daten_after_save_post', function ( int $id ) {
	$old = (int) get_post_meta( $id, 'ersetzt_id', true );
	if ( $old && $old !== $id ) {
		update_post_meta( $old, 'fck_ersetzt_durch', $id );
	}
} );

/* ------------------------------------------------------------------ Beitrag */

add_action( 'rwmb_beitrag-extra_after_save_post', function ( int $id ) {
	$terms = array();
	foreach ( fck_meta_ids( $id, 'spiel_id' ) as $spiel ) {
		$team = (int) get_post_meta( $spiel, 'team_id', true );
		if ( $team && ( $term = (int) get_post_meta( $team, 'team_term_id', true ) ) ) {
			$terms[] = $term;
		}
	}
	if ( $terms ) {
		wp_set_object_terms( $id, array_unique( $terms ), 'team', true );
	}
	if ( get_post_meta( $id, 'angepinnt_bis', true ) ) {
		stick_post( $id );
	}
} );

/* ------------------------------------------------------------------ Cron */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'fck_stuendlich' ) ) {
		wp_schedule_event( time() + 300, 'hourly', 'fck_stuendlich' );
	}
	if ( ! wp_next_scheduled( 'fck_taeglich' ) ) {
		$ts = ( new DateTimeImmutable( 'tomorrow 04:00', wp_timezone() ) )->getTimestamp();
		wp_schedule_event( $ts, 'daily', 'fck_taeglich' );
	}
} );

/* Stündlich: nächstes/letztes Spiel und Form, weil „jetzt“ weiterläuft. */
add_action( 'fck_stuendlich', function () {
	fck_refresh_all_teams();
	fck_purge_cache();
} );

/* Täglich: Ämter, Partner-Laufzeiten, Anpinnen, vergangene Trainingsausfälle. */
add_action( 'fck_taeglich', function () {
	foreach ( get_posts( array( 'post_type' => 'amt', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1 ) ) as $id ) {
		fck_amt_refresh( $id );
	}
	foreach ( get_posts( array( 'post_type' => 'partner', 'post_status' => array( 'publish', 'abgelaufen' ), 'fields' => 'ids', 'posts_per_page' => -1 ) ) as $id ) {
		fck_partner_refresh( $id );
	}
	foreach ( (array) get_option( 'sticky_posts', array() ) as $id ) {
		$bis = (string) get_post_meta( $id, 'angepinnt_bis', true );
		if ( $bis && $bis < fck_now() ) {
			unstick_post( $id );
		}
	}
	foreach ( get_posts( array( 'post_type' => 'mannschaft', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1 ) ) as $team ) {
		$rows = get_post_meta( $team, 'ausfaelle', true );
		if ( is_array( $rows ) ) {
			$keep = array_values( array_filter( $rows, fn( $r ) => ! empty( $r['datum'] ) && $r['datum'] >= fck_today() ) );
			if ( count( $keep ) !== count( $rows ) ) {
				update_post_meta( $team, 'ausfaelle', $keep );
			}
		}
	}
	fck_purge_cache();
} );
