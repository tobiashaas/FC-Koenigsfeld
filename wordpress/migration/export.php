<?php
/**
 * Export der alten Bricks-Seite (nur lesend) als JSON auf stdout.
 *
 * Aufruf auf dem alten Server:  wp eval-file export.php > export.json
 * Enthält keine Passwörter; Adressen/Telefon von Vorständen werden NICHT exportiert
 * (sie werden auf der neuen Seite nicht gebraucht).
 */

global $wpdb;

$statuses = array( 'publish', 'draft', 'private', 'future' );

function fck_x_terms( int $id, string $tax ): array {
	$terms = wp_get_object_terms( $id, $tax );
	return is_wp_error( $terms ) ? array() : array_map( fn( $t ) => $t->slug, $terms );
}

function fck_x_base( WP_Post $p ): array {
	return array(
		'old_id'   => $p->ID,
		'type'     => $p->post_type,
		'status'   => $p->post_status,
		'title'    => $p->post_title,
		'slug'     => $p->post_name,
		'date'     => $p->post_date,
		'date_gmt' => $p->post_date_gmt,
		'modified' => $p->post_modified,
		'author'   => (int) $p->post_author,
		'content'  => $p->post_content,
		'excerpt'  => $p->post_excerpt,
		'order'    => (int) $p->menu_order,
		'parent'   => (int) $p->post_parent,
		'thumb'    => (int) get_post_thumbnail_id( $p ),
		'url'      => get_permalink( $p ),
	);
}

function fck_x_posts( string $type, array $statuses, callable $extra = null ): array {
	$out = array();
	foreach ( get_posts( array( 'post_type' => $type, 'post_status' => $statuses, 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $p ) {
		$row = fck_x_base( $p );
		if ( $extra ) {
			$row += $extra( $p );
		}
		$out[] = $row;
	}
	return $out;
}

$uploads = wp_upload_dir();

$data = array(
	'meta'    => array(
		'site'         => home_url(),
		'exported'     => gmdate( 'c' ),
		'uploads_dir'  => $uploads['basedir'],
		'uploads_url'  => $uploads['baseurl'],
		'permalinks'   => get_option( 'permalink_structure' ),
	),
	'users'   => array_map(
		fn( $u ) => array( 'old_id' => $u->ID, 'login' => $u->user_login, 'name' => $u->display_name, 'email' => $u->user_email ),
		get_users( array( 'has_published_posts' => array( 'post' ) ) )
	),
	'terms'   => array(),
	'media'   => array(),
);

foreach ( array( 'category', 'post_tag', 'playerposition', 'board_role', 'partner_type' ) as $tax ) {
	$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
	$data['terms'][ $tax ] = is_wp_error( $terms ) ? array() : array_map(
		fn( $t ) => array( 'old_id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'parent' => $t->parent, 'description' => $t->description, 'count' => $t->count ),
		$terms
	);
}

foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $a ) {
	$data['media'][] = array(
		'old_id'  => $a->ID,
		'file'    => get_post_meta( $a->ID, '_wp_attached_file', true ),
		'mime'    => $a->post_mime_type,
		'title'   => $a->post_title,
		'caption' => $a->post_excerpt,
		'desc'    => $a->post_content,
		'alt'     => get_post_meta( $a->ID, '_wp_attachment_image_alt', true ),
		'date'    => $a->post_date,
		'parent'  => (int) $a->post_parent,
		'url'     => wp_get_attachment_url( $a->ID ),
	);
}

$data['posts'] = fck_x_posts( 'post', $statuses, fn( $p ) => array(
	'categories' => fck_x_terms( $p->ID, 'category' ),
	'tags'       => fck_x_terms( $p->ID, 'post_tag' ),
	'sticky'     => is_sticky( $p->ID ),
	'seo_title'  => get_post_meta( $p->ID, '_seopress_titles_title', true ),
	'seo_desc'   => get_post_meta( $p->ID, '_seopress_titles_desc', true ),
) );

$data['pages'] = fck_x_posts( 'page', $statuses );

$data['mannschaften'] = fck_x_posts( 'mannschaften', $statuses, fn( $p ) => array(
	'fd_team_id'        => get_post_meta( $p->ID, 'fussball_de_team_id', true ),
	'team_category'     => get_post_meta( $p->ID, 'team_category', true ),
	'match_team_key'    => get_post_meta( $p->ID, 'match_team_key', true ),
	'spielplan_anzeigen'=> get_post_meta( $p->ID, 'spielplan_anzeigen', true ),
) );

$data['mitglieder'] = fck_x_posts( 'mitglieder', $statuses, fn( $p ) => array(
	'vorname'            => get_post_meta( $p->ID, 'vorname', true ),
	'nachname'           => get_post_meta( $p->ID, 'nachname', true ),
	'is_player'          => (bool) get_post_meta( $p->ID, 'is_player', true ),
	'player_photo'       => (int) get_post_meta( $p->ID, 'player_photo', true ),
	'is_board_member'    => (bool) get_post_meta( $p->ID, 'is_board_member', true ),
	'board_member_photo' => (int) get_post_meta( $p->ID, 'board_member_photo', true ),
	'board_email'        => get_post_meta( $p->ID, 'board_email', true ),
	'positions'          => fck_x_terms( $p->ID, 'playerposition' ),
	'board_roles'        => fck_x_terms( $p->ID, 'board_role' ),
) );

$data['sponsoren'] = fck_x_posts( 'sponsor', $statuses, fn( $p ) => array(
	'logo'    => (int) get_post_meta( $p->ID, 'partner_logo', true ),
	'website' => get_post_meta( $p->ID, 'website_url', true ),
	'stufen'  => fck_x_terms( $p->ID, 'partner_type' ),
) );

$data['vereine'] = fck_x_posts( 'vereine', $statuses, fn( $p ) => array(
	'platz_adresse' => get_post_meta( $p->ID, 'sports_ground_address', true ) ?: get_post_meta( $p->ID, 'vereineadresse_sportplatz', true ),
) );

foreach ( array( 'historie', 'foerderverein', 'rechtlich' ) as $type ) {
	$data[ $type ] = fck_x_posts( $type, $statuses );
}

// Mannschaft ↔ Spieler. Die Daten liegen unter der alten Relationship-ID.
$data['relationen'] = $wpdb->get_results(
	"SELECT `from` AS mannschaft, `to` AS mitglied, order_from, order_to FROM {$wpdb->prefix}mb_relationships WHERE type = 'mannschaft-spieler' ORDER BY ID",
	ARRAY_A
);

// Club-Einstellungen (ohne den manuellen Spielplan, der kommt künftig aus „Spiel“).
$settings = get_option( 'club-einstellungen' ) ?: get_option( 'club_einstellungen' );
$data['einstellungen'] = is_array( $settings ) ? $settings : array();

$data['menus'] = array();
foreach ( wp_get_nav_menus() as $menu ) {
	$data['menus'][ $menu->slug ] = array_map(
		fn( $i ) => array( 'title' => $i->title, 'url' => $i->url, 'parent' => (int) $i->menu_item_parent, 'id' => $i->ID, 'object' => $i->object, 'object_id' => (int) $i->object_id ),
		wp_get_nav_menu_items( $menu ) ?: array()
	);
}

echo wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
