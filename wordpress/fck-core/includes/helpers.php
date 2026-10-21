<?php
/**
 * Kleine Helfer, die mehrere Module brauchen.
 */

defined( 'ABSPATH' ) || exit;

/** Wert von der Options-Seite „Verein“. */
function fck_option( string $key, $default = '' ) {
	$opts = get_option( 'verein', array() );
	return is_array( $opts ) && isset( $opts[ $key ] ) && '' !== $opts[ $key ] ? $opts[ $key ] : $default;
}

/** Jetzt als Ortszeit im Speicherformat der Felder. */
function fck_now(): string {
	return current_time( 'mysql' );
}

function fck_today(): string {
	return current_time( 'Y-m-d' );
}

/** Saison zu einem Datum: ab Juli zählt die neue Saison („2026/27“). */
function fck_saison_aus_datum( string $date ): string {
	$y = (int) substr( $date, 0, 4 );
	$m = (int) substr( $date, 5, 2 );
	$start = $m >= 7 ? $y : $y - 1;
	return sprintf( '%d/%02d', $start, ( $start + 1 ) % 100 );
}

function fck_meta_ids( int $post_id, string $key ): array {
	return array_values( array_filter( array_map( 'intval', (array) get_post_meta( $post_id, $key, false ) ) ) );
}

/** Seiten-Cache leeren (Nginx Helper) und Objekt-Cache für Abfragen. */
function fck_purge_cache(): void {
	do_action( 'rt_nginx_helper_purge_all' );
	if ( function_exists( 'wp_cache_flush_group' ) ) {
		wp_cache_flush_group( 'posts' );
	}
}

/** Verhindert Endlosschleifen, wenn ein Speichern weitere Speicherungen auslöst. */
function fck_guard( string $key, callable $fn ): void {
	static $running = array();
	if ( ! empty( $running[ $key ] ) ) {
		return;
	}
	$running[ $key ] = true;
	try {
		$fn();
	} finally {
		unset( $running[ $key ] );
	}
}
