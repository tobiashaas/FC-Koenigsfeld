<?php
/**
 * Inhaltstypen und Taxonomien registriert Etch (Content Hub).
 * Definitionen: wordpress/setup/content-types.php, übertragen mit setup/etch-content-types.php.
 * Hier nur noch die festen Begriffe.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Feste Begriffe einmal anlegen. Weitere Altersklassen können jederzeit ergänzt werden.
 */
function fck_seed_terms(): void {
	$terms = array(
		'altersklasse'  => array( 'Herren', 'Ü35', 'A-Junioren', 'B-Junioren', 'C-Junioren', 'D-Junioren', 'E-Junioren', 'F-Junioren', 'Bambini', 'Mädchen', 'Schiedsrichter' ),
		'spielposition' => array( 'Tor', 'Abwehr', 'Mittelfeld', 'Sturm' ),
	);
	foreach ( $terms as $taxonomy => $names ) {
		foreach ( $names as $order => $name ) {
			if ( ! term_exists( $name, $taxonomy ) ) {
				$term = wp_insert_term( $name, $taxonomy );
				if ( ! is_wp_error( $term ) ) {
					update_term_meta( $term['term_id'], 'sortierung', $order );
				}
			}
		}
	}
}

add_action( 'init', function () {
	if ( get_option( 'fck_core_seeded' ) !== FCK_CORE_VERSION ) {
		fck_seed_terms();
		update_option( 'fck_core_seeded', FCK_CORE_VERSION, false );
	}
}, 20 );
