<?php
/**
 * Inhaltstypen und Taxonomien registriert Etch (Content Hub).
 * Definitionen: wordpress/setup/content-types.php, übertragen mit setup/etch-content-types.php.
 * Hier nur: eigener Status für abgelaufene Partner und die festen Begriffe.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_status( 'abgelaufen', array(
		'label'                     => 'Abgelaufen',
		'public'                    => false,
		'internal'                  => false,
		'protected'                 => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: Anzahl */
		'label_count'               => _n_noop( 'Abgelaufen <span class="count">(%s)</span>', 'Abgelaufen <span class="count">(%s)</span>' ),
	) );
}, 6 );

function fck_seed_terms(): void {
	// Altersklassen: Name => [jugend, U von, U bis].
	$altersklassen = array(
		'Herren'     => array( 0, null, null ),
		'Ü35'        => array( 0, null, null ),
		'A-Junioren' => array( 1, 18, 19 ),
		'B-Junioren' => array( 1, 16, 17 ),
		'C-Junioren' => array( 1, 14, 15 ),
		'D-Junioren' => array( 1, 12, 13 ),
		'E-Junioren' => array( 1, 10, 11 ),
		'F-Junioren' => array( 1, 8, 9 ),
		'Bambini'    => array( 1, 6, 7 ),
	);
	$order = 0;
	foreach ( $altersklassen as $name => $meta ) {
		$term = term_exists( $name, 'altersklasse' ) ?: wp_insert_term( $name, 'altersklasse' );
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$id = (int) $term['term_id'];
		update_term_meta( $id, 'jugend', $meta[0] );
		update_term_meta( $id, 'sortierung', $order++ );
		if ( $meta[1] ) {
			update_term_meta( $id, 'u_von', $meta[1] );
			update_term_meta( $id, 'u_bis', $meta[2] );
		}
	}
	// Mädchen ist jetzt das Feld „Team“ an der Mannschaft, Schiedsrichter ein Amt.
	foreach ( array( 'Mädchen', 'Schiedsrichter' ) as $name ) {
		$t = get_term_by( 'name', $name, 'altersklasse' );
		if ( $t && 0 === (int) $t->count ) {
			wp_delete_term( $t->term_id, 'altersklasse' );
		}
	}

	$simple = array(
		'spielposition' => array( 'Tor', 'Abwehr', 'Mittelfeld', 'Sturm' ),
		'organisation'  => array( 'fck' => 'FC Königsfeld', 'foerderverein' => 'Förderverein' ),
		'terminart'     => array( 'Turnier', 'Fest', 'Versammlung', 'Jugend', 'Förderverein' ),
	);
	foreach ( $simple as $taxonomy => $names ) {
		$i = 0;
		foreach ( $names as $slug => $name ) {
			if ( ! term_exists( $name, $taxonomy ) ) {
				$args = is_string( $slug ) ? array( 'slug' => $slug ) : array();
				$term = wp_insert_term( $name, $taxonomy, $args );
				if ( ! is_wp_error( $term ) ) {
					update_term_meta( $term['term_id'], 'sortierung', $i );
				}
			}
			$i++;
		}
	}

	$saison = fck_option( 'saison' ) ?: fck_saison_aus_datum( current_time( 'Y-m-d' ) );
	if ( ! term_exists( $saison, 'saison' ) ) {
		wp_insert_term( $saison, 'saison', array( 'slug' => sanitize_title( str_replace( '/', '-', $saison ) ) ) );
	}
}

add_action( 'init', function () {
	if ( get_option( 'fck_core_seeded' ) !== FCK_CORE_VERSION && taxonomy_exists( 'altersklasse' ) ) {
		fck_seed_terms();
		update_option( 'fck_core_seeded', FCK_CORE_VERSION, false );
	}
}, 20 );
