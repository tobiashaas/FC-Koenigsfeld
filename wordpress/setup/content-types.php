<?php
/**
 * Definitionen der Inhaltstypen und Taxonomien (Quelle der Wahrheit im Git).
 * Registriert werden sie von Etch (Content Hub); etch-content-types.php schreibt sie dorthin.
 */

defined( 'ABSPATH' ) || exit;

function fck_labels( string $singular, string $plural, string $new = 'Neue' ): array {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'all_items'          => "Alle $plural",
		'add_new'            => 'Hinzufügen',
		'add_new_item'       => "$new $singular",
		'edit_item'          => "$singular bearbeiten",
		'new_item'           => "$new $singular",
		'view_item'          => "$singular ansehen",
		'search_items'       => "$plural durchsuchen",
		'not_found'          => "Keine $plural gefunden",
		'not_found_in_trash' => "Keine $plural im Papierkorb",
	);
}

function fck_post_types(): array {
	$public = array(
		'public'       => true,
		'has_archive'  => false,
		'show_in_rest' => true,
	);
	$internal = array(
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_rest'        => true,
		'rewrite'             => false,
	);

	return array(
		'mannschaft'   => $public + array(
			'labels'        => fck_labels( 'Mannschaft', 'Mannschaften' ),
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 20,
			'rewrite'       => array( 'slug' => 'mannschaften', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
		),
		'spiel'        => $public + array(
			'labels'        => fck_labels( 'Spiel', 'Spiele', 'Neues' ),
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 21,
			'rewrite'       => array( 'slug' => 'spiele', 'with_front' => false ),
			'supports'      => array( 'title' ),
		),
		'tabelle'      => $internal + array(
			'labels'       => fck_labels( 'Tabelle', 'Tabellen' ),
			'show_in_menu' => 'edit.php?post_type=spiel',
			'supports'     => array( 'title' ),
		),
		'person'       => $public + array(
			'labels'        => fck_labels( 'Person', 'Personen' ),
			'menu_icon'     => 'dashicons-id',
			'menu_position' => 22,
			'rewrite'       => array( 'slug' => 'personen', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		),
		'spielstaette' => $public + array(
			'labels'        => fck_labels( 'Spielstätte', 'Spielstätten' ),
			'menu_icon'     => 'dashicons-location',
			'menu_position' => 23,
			'rewrite'       => array( 'slug' => 'spielstaetten', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
		),
		'partner'      => $internal + array(
			'labels'        => fck_labels( 'Partner', 'Partner', 'Neuer' ),
			'menu_icon'     => 'dashicons-heart',
			'menu_position' => 24,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		),
		'termin'       => $public + array(
			'labels'        => fck_labels( 'Termin', 'Termine', 'Neuer' ),
			'menu_icon'     => 'dashicons-clock',
			'menu_position' => 25,
			'rewrite'       => array( 'slug' => 'termine', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		),
		'frage'        => $internal + array(
			'labels'        => fck_labels( 'Frage', 'Fragen' ),
			'menu_icon'     => 'dashicons-editor-help',
			'menu_position' => 26,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
		),
		'download'     => $internal + array(
			'labels'        => fck_labels( 'Download', 'Downloads', 'Neuer' ),
			'menu_icon'     => 'dashicons-media-document',
			'menu_position' => 27,
			'supports'      => array( 'title', 'page-attributes' ),
		),
		'historie'     => $internal + array(
			'labels'        => fck_labels( 'Historie-Eintrag', 'Historie', 'Neuer' ),
			'menu_icon'     => 'dashicons-backup',
			'menu_position' => 28,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
		),
	);
}

function fck_taxonomies(): array {
	return array(
		'team'          => array(
			'object_types' => array( 'post', 'termin', 'download' ),
			'args'         => array(
				'labels'            => fck_labels( 'Mannschaft', 'Mannschaften' ),
				'public'            => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'team', 'with_front' => false ),
				// Begriffe entstehen aus den Mannschaften (team-terms.php), nicht von Hand.
				'capabilities'      => array(
					'manage_terms' => 'manage_options',
					'edit_terms'   => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				),
			),
		),
		'altersklasse'  => array(
			'object_types' => array( 'mannschaft' ),
			'args'         => array(
				'labels'            => fck_labels( 'Altersklasse', 'Altersklassen' ),
				'public'            => false,
				'show_ui'           => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => false,
			),
		),
		'spielposition' => array(
			'object_types' => array( 'person' ),
			'args'         => array(
				'labels'            => fck_labels( 'Position', 'Positionen' ),
				'public'            => false,
				'show_ui'           => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => false,
			),
		),
	);
}

