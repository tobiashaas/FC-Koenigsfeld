<?php
/**
 * Schreibt die Inhaltstypen und Taxonomien aus content-types.php in Etch
 * (über Etchs eigene Routen etch-api/cms/post-type und etch-api/cms/taxonomy).
 *
 * Aufruf auf dem Server:  wp eval-file etch-content-types.php --user=<admin>
 * Wiederholbar: Vorhandenes wird aktualisiert statt doppelt angelegt.
 */

require_once __DIR__ . '/content-types.php';

function fck_etch_upsert( string $base, string $id, array $body ): string {
	$send = function ( string $method, string $route ) use ( $id, $body ) {
		$req = new WP_REST_Request( $method, $route );
		$req->set_header( 'content-type', 'application/json' );
		$req->set_body( wp_json_encode( array( 'id' => $id ) + $body ) );
		return rest_do_request( $req );
	};
	$res = $send( 'POST', "/etch-api/cms/$base" );
	if ( 409 === $res->get_status() ) {
		$res = $send( 'PUT', "/etch-api/cms/$base/$id" );
	}
	$data = $res->get_data();
	$msg  = is_array( $data ) ? ( $data['message'] ?? wp_json_encode( $data ) ) : (string) $data;
	return sprintf( '%-10s %-14s %d %s', $base, $id, $res->get_status(), $msg );
}

// WordPress-Argumente → Etchs camelCase-Format (TaxonomiesService::map_args_to_wp_args).
function fck_etch_taxonomy_args( array $wp ): array {
	$l = $wp['labels'] ?? array();
	$c = $wp['capabilities'] ?? array();
	return array_filter(
		array(
			'labels'            => array_filter( array(
				'pluralName'   => $l['name'] ?? null,
				'singularName' => $l['singular_name'] ?? null,
				'menuName'     => $l['menu_name'] ?? null,
				'allItems'     => $l['all_items'] ?? null,
				'editItem'     => $l['edit_item'] ?? null,
				'viewItem'     => $l['view_item'] ?? null,
				'addNewItem'   => $l['add_new_item'] ?? null,
			) ),
			'public'            => $wp['public'] ?? true,
			'hierarchical'      => $wp['hierarchical'] ?? false,
			'showUi'            => $wp['show_ui'] ?? true,
			'showInRest'        => $wp['show_in_rest'] ?? true,
			'showInQuickEdit'   => true,
			'showInAdminColumn' => $wp['show_admin_column'] ?? false,
			'capabilities'      => array_filter( array(
				'manageTerms' => $c['manage_terms'] ?? null,
				'editTerms'   => $c['edit_terms'] ?? null,
				'deleteTerms' => $c['delete_terms'] ?? null,
				'assignTerms' => $c['assign_terms'] ?? null,
			) ),
		),
		fn( $v ) => null !== $v && array() !== $v
	);
}

foreach ( fck_post_types() as $id => $args ) {
	WP_CLI::log( fck_etch_upsert( 'post-type', $id, array( 'args' => $args ) ) );
}
foreach ( fck_taxonomies() as $id => $def ) {
	WP_CLI::log( fck_etch_upsert( 'taxonomy', $id, array(
		'args'        => fck_etch_taxonomy_args( $def['args'] ),
		'objectTypes' => $def['object_types'],
	) ) );
}
flush_rewrite_rules();
WP_CLI::success( 'Etch-Inhaltstypen geschrieben.' );
