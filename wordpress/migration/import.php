<?php
/**
 * Import der alten Seite in die neue Struktur.
 *
 * Aufruf auf dem neuen Server (Medien vorher mit transfer.sh kopiert):
 *   wp eval-file import.php <import.json> [schritt,schritt] --user=<admin>
 * Schritte: users, media, terms, teams, gegner, personen, partner, posts, seiten, optionen, spiele, fertig
 *
 * Wiederholbar: Jedes Objekt trägt `_fck_old_id` + `_fck_old_type`. Ein zweiter Lauf aktualisiert
 * statt doppelt anzulegen. Schreibt redirects.csv neben die JSON-Datei.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

$fck_file  = $args[0] ?? '';
$fck_steps = isset( $args[1] ) ? explode( ',', $args[1] ) : array( 'users', 'media', 'terms', 'teams', 'gegner', 'personen', 'partner', 'posts', 'seiten', 'optionen', 'spiele', 'fertig' );
if ( ! is_readable( $fck_file ) ) {
	WP_CLI::error( "import.json nicht lesbar: $fck_file" );
}
$D = json_decode( file_get_contents( $fck_file ), true );

wp_defer_term_counting( true );
add_filter( 'wp_mail', fn( $m ) => array_merge( $m, array( 'to' => '' ) ) ); // keine Mails beim Anlegen

/* ------------------------------------------------------------------ Helfer */

function fck_imp_find( string $type, int $old_id, string $post_type = 'any' ): int {
	$ids = get_posts( array(
		'post_type'      => $post_type,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array( 'key' => '_fck_old_id', 'value' => $old_id ),
			array( 'key' => '_fck_old_type', 'value' => $type ),
		),
	) );
	return $ids ? (int) $ids[0] : 0;
}

/** Legt an oder aktualisiert. $meta wird komplett gesetzt (Mehrfachwerte als list). */
function fck_imp_upsert( string $type, int $old_id, array $post, array $meta = array() ): int {
	$id = fck_imp_find( $type, $old_id, $post['post_type'] );
	if ( $id ) {
		$post['ID'] = $id;
		wp_update_post( wp_slash( $post ), true );
	} else {
		$id = wp_insert_post( wp_slash( $post ), true );
		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( "$type $old_id: " . $id->get_error_message() );
			return 0;
		}
		update_post_meta( $id, '_fck_old_id', $old_id );
		update_post_meta( $id, '_fck_old_type', $type );
	}
	foreach ( $meta as $key => $value ) {
		delete_post_meta( $id, $key );
		if ( is_array( $value ) && array_is_list( $value ) && ! in_array( $key, array( 'leistungen', 'trainingszeiten', 'plaetze' ), true ) ) {
			foreach ( $value as $v ) {
				add_post_meta( $id, $key, $v );
			}
		} elseif ( null !== $value && '' !== $value ) {
			update_post_meta( $id, $key, $value );
		}
	}
	return $id;
}

function fck_imp_media( int $old ): int {
	static $map = null;
	if ( null === $map ) {
		global $wpdb;
		$map = array();
		foreach ( $wpdb->get_results( "SELECT p.post_id, p.meta_value FROM {$wpdb->postmeta} p JOIN {$wpdb->postmeta} t ON t.post_id = p.post_id AND t.meta_key = '_fck_old_type' AND t.meta_value = 'media' WHERE p.meta_key = '_fck_old_id'" ) as $r ) {
			$map[ (int) $r->meta_value ] = (int) $r->post_id;
		}
	}
	if ( -1 === $old ) {
		$map = null; // Cache leeren
		return 0;
	}
	return $map[ $old ] ?? 0;
}

function fck_imp_term( string $tax, string $name, array $args = array() ): int {
	$t = term_exists( $name, $tax );
	if ( ! $t ) {
		$t = wp_insert_term( $name, $tax, $args );
	}
	return is_wp_error( $t ) ? 0 : (int) $t['term_id'];
}

function fck_imp_name( string $s ): string {
	return html_entity_decode( $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

// eval-file führt die Datei in einer Funktion aus: gemeinsame Werte deshalb über $GLOBALS.
$GLOBALS['fck_redirects'] = array();
function fck_imp_redirect( string $from, string $to ): void {
	$fck_redirects = &$GLOBALS['fck_redirects'];
	$from = wp_parse_url( $from, PHP_URL_PATH ) ?: $from;
	$to   = wp_parse_url( $to, PHP_URL_PATH ) ?: $to;
	if ( $from && $to && untrailingslashit( $from ) !== untrailingslashit( $to ) ) {
		$fck_redirects[ $from ] = $to;
	}
}

/** Mannschaft (neu) zu alter Mannschafts-ID. */
function fck_imp_team( int $old ): int {
	return $old ? fck_imp_find( 'mannschaft', $old, 'mannschaft' ) : 0;
}

$own_club = array_map( 'intval', $D['own_club_ids'] );
$used     = array_flip( $D['used_media'] );
$GLOBALS['fck_urls'] = array(
	'old_base' => rtrim( $D['meta']['uploads_url'], '/' ),
	'new_base' => rtrim( wp_upload_dir()['baseurl'], '/' ),
	'old_home' => rtrim( $D['meta']['site'], '/' ),
	'new_home' => rtrim( home_url(), '/' ),
);

/* ------------------------------------------------------------------ users */

$fck_users = array();
if ( in_array( 'users', $fck_steps, true ) ) {
	foreach ( $D['users'] as $u ) {
		$existing = get_user_by( 'email', $u['email'] ) ?: get_user_by( 'login', $u['login'] );
		if ( ! $existing ) {
			$id = wp_insert_user( array(
				'user_login'   => $u['login'],
				'user_email'   => $u['email'],
				'display_name' => $u['name'],
				'user_pass'    => wp_generate_password( 32 ),
				'role'         => 'author',
			) );
			$existing = is_wp_error( $id ) ? null : get_user_by( 'id', $id );
		}
		if ( $existing ) {
			update_user_meta( $existing->ID, '_fck_old_id', $u['old_id'] );
		}
	}
	WP_CLI::log( 'users: ' . count( $D['users'] ) );
}
foreach ( get_users( array( 'meta_key' => '_fck_old_id' ) ) as $u ) {
	$fck_users[ (int) get_user_meta( $u->ID, '_fck_old_id', true ) ] = $u->ID;
}

/* ------------------------------------------------------------------ media */

if ( in_array( 'media', $fck_steps, true ) ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$dir = wp_upload_dir()['basedir'];
	$n = 0; $missing = 0;
	foreach ( $D['media'] as $m ) {
		if ( ! isset( $used[ $m['old_id'] ] ) ) {
			continue;
		}
		$path = "$dir/{$m['file']}";
		if ( ! file_exists( $path ) ) {
			$missing++;
			WP_CLI::warning( "Datei fehlt: {$m['file']}" );
			continue;
		}
		$id = fck_imp_find( 'media', $m['old_id'], 'attachment' );
		if ( ! $id ) {
			// EXIF/GPS aus JPEG-Originalen entfernen, Farbprofil behalten.
			if ( 'image/jpeg' === $m['mime'] && class_exists( 'Imagick' ) ) {
				try {
					$im = new Imagick( $path );
					$profiles = $im->getImageProfiles( 'icc', true );
					$im->stripImage();
					if ( ! empty( $profiles['icc'] ) ) {
						$im->profileImage( 'icc', $profiles['icc'] );
					}
					$im->writeImage( $path );
					$im->clear();
				} catch ( Exception $e ) {
					WP_CLI::warning( "EXIF {$m['file']}: " . $e->getMessage() );
				}
			}
			$id = wp_insert_attachment( array(
				'post_title'     => fck_imp_name( $m['title'] ),
				'post_excerpt'   => $m['caption'],
				'post_content'   => $m['desc'],
				'post_mime_type' => $m['mime'],
				'post_date'      => $m['date'],
				'post_status'    => 'inherit',
			), $path, 0, true );
			if ( is_wp_error( $id ) ) {
				WP_CLI::warning( "Medium {$m['old_id']}: " . $id->get_error_message() );
				continue;
			}
			update_post_meta( $id, '_fck_old_id', $m['old_id'] );
			update_post_meta( $id, '_fck_old_type', 'media' );
			if ( str_starts_with( $m['mime'], 'image/' ) && 'image/svg+xml' !== $m['mime'] ) {
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
			}
		}
		if ( $m['alt'] ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $m['alt'] );
		}
		$n++;
		if ( 0 === $n % 25 ) {
			WP_CLI::log( "  media $n …" );
		}
	}
	fck_imp_media( -1 );
	WP_CLI::log( "media: $n, fehlend $missing" );
}

/* ------------------------------------------------------------------ terms */

$fck_cat = array();
{ // Kategorien braucht auch der Schritt posts
	foreach ( array( 'Spielbericht', 'Jugend', 'Mädchen', 'Verein', 'Förderverein', 'Turniere' ) as $name ) {
		$fck_cat[ $name ] = fck_imp_term( 'category', $name );
	}
	if ( in_array( 'terms', $fck_steps, true ) ) {
		foreach ( $D['terms']['post_tag'] as $t ) {
			fck_imp_term( 'post_tag', fck_imp_name( $t['name'] ), array( 'slug' => $t['slug'] ) );
		}
		// „Uncategorized“ umbenennen statt löschen (Standardkategorie).
		$default = (int) get_option( 'default_category' );
		update_option( 'default_category', $fck_cat['Verein'] );
		if ( $default && $default !== $fck_cat['Verein'] && ( $t = get_term( $default, 'category' ) ) && 0 === (int) $t->count ) {
			wp_delete_term( $default, 'category' );
		}
		WP_CLI::log( 'terms: Kategorien ' . count( $fck_cat ) . ', Schlagwörter ' . count( $D['terms']['post_tag'] ) );
	}
}

/* ------------------------------------------------------------------ teams */

$fck_team_meta = array(
	// alte ID => [Kurzname, Altersklasse, SG-Name, Sortierung]
	681  => array( '1. Mannschaft', 'Herren', '', 1 ),
	682  => array( '2. Mannschaft', 'Herren', 'SG Gem. B.K.N.', 2 ),
	1994 => array( '3. Mannschaft', 'Herren', 'SG Gem. B.K.N.', 3 ),
	2028 => array( 'AH', 'Ü35', '', 4 ),
	683  => array( 'A-Junioren', 'A-Junioren', '', 10 ),
	684  => array( 'B-Junioren', 'B-Junioren', '', 11 ),
	2064 => array( 'C-Junioren', 'C-Junioren', 'SG Gemeinde Königsfeld', 12 ),
	687  => array( 'C-Junioren', 'C-Junioren', '', 12 ),
	2018 => array( 'D-Junioren', 'D-Junioren', '', 13 ),
	2068 => array( 'E1', 'E-Junioren', 'SG Gemeinde Königsfeld', 14 ),
	2070 => array( 'E2', 'E-Junioren', 'SG Gemeinde Königsfeld', 15 ),
);
if ( in_array( 'teams', $fck_steps, true ) ) {
	$n = 0;
	foreach ( $D['mannschaften'] as $t ) {
		if ( ! isset( $fck_team_meta[ $t['old_id'] ] ) ) {
			continue; // Schiedsrichter → Amt
		}
		[ $kurz, $ak, $sg, $order ] = $fck_team_meta[ $t['old_id'] ];
		$id = fck_imp_upsert( 'mannschaft', $t['old_id'], array(
			'post_type'    => 'mannschaft',
			'post_status'  => $t['status'],
			'post_title'   => fck_imp_name( $t['title'] ),
			'post_name'    => $t['slug'],
			'post_content' => $t['content'],
			'menu_order'   => $order,
		), array(
			'kurzname'   => $kurz,
			'fd_team_id' => $t['fd_team_id'],
			'sg'         => $sg ? 1 : 0,
			'sg_name'    => $sg,
			'geschlecht' => in_array( $ak, array( 'Herren', 'Ü35' ), true ) ? 'herren' : 'gemischt',
		) );
		if ( $id ) {
			wp_set_object_terms( $id, $ak, 'altersklasse' );
			if ( $t['thumb'] && ( $m = fck_imp_media( $t['thumb'] ) ) ) {
				set_post_thumbnail( $id, $m );
			}
			fck_sync_team_term( $id );
			fck_team_refresh( $id );
			$n++;
		}
	}
	WP_CLI::log( "teams: $n" );
}

/* ------------------------------------------------------------------ gegner */

if ( in_array( 'gegner', $fck_steps, true ) ) {
	$n = 0;
	foreach ( $D['vereine'] as $v ) {
		if ( in_array( (int) $v['old_id'], $own_club, true ) ) {
			continue;
		}
		$name = fck_imp_name( $v['title'] );
		$id   = fck_imp_upsert( 'gegner', $v['old_id'], array(
			'post_type'   => 'gegner',
			'post_status' => 'publish',
			'post_title'  => $name,
			'post_name'   => $v['slug'],
		), array(
			'fd_namen'      => array( $name ),
			'platz_adresse' => $v['platz_adresse'],
		) );
		if ( $id && $v['thumb'] && ( $m = fck_imp_media( $v['thumb'] ) ) ) {
			set_post_thumbnail( $id, $m );
		}
		fck_imp_redirect( $v['url'], '/' );
		$n += $id ? 1 : 0;
	}
	WP_CLI::log( "gegner: $n" );
}

/* ------------------------------------------------------------------ personen + ämter */

$fck_positions = array( 'torwart' => 'Tor', 'verteidiger' => 'Abwehr', 'mittelfeld' => 'Mittelfeld', 'stuermer' => 'Sturm' );
$fck_board     = array(
	// Slug => [Gremium, Funktion, Sortierung]
	'praesident'               => array( 'vorstand', 'Präsident', 1 ),
	'1-vorsitzender'           => array( 'vorstand', '1. Vorsitzender', 2 ),
	'2-vorsitzender'           => array( 'vorstand', '2. Vorsitzende', 3 ),
	'1-kassierer'              => array( 'vorstand', 'Kassierer', 4 ),
	'schriftfuehrer'           => array( 'vorstand', 'Schriftführer', 5 ),
	'jugendleiter'             => array( 'jugendleitung', 'Jugendleiterin', 6 ),
	'spielausschuss'           => array( 'spielausschuss', 'Spielausschuss', 7 ),
	'gebaeude-und-platzpflege' => array( 'vorstand', 'Gebäude- und Platzpflege', 8 ),
	'beisitzer'                => array( 'vorstand', 'Beisitzer', 9 ),
	'mitgliederverwaltung'     => array( 'vorstand', 'Mitgliederverwaltung', 10 ),
	'webmaster'                => array( 'vorstand', 'Webmaster', 11 ),
	'vize-praesident'          => array( 'vorstand', 'Vize-Präsident', 2 ),
);
if ( in_array( 'personen', $fck_steps, true ) ) {
	$rel = array();
	foreach ( $D['relationen'] as $r ) {
		$rel[ (int) $r['mitglied'] ][] = (int) $r['mannschaft'];
	}
	$n = 0; $a = 0;
	foreach ( $D['mitglieder'] as $m ) {
		$is_trainer = in_array( 'trainer', $m['positions'], true );
		$teams_old  = $rel[ $m['old_id'] ] ?? array();
		$ref        = in_array( 688, $teams_old, true );
		$teams_old  = array_values( array_diff( $teams_old, array( 688 ) ) );
		$teams      = array_values( array_filter( array_map( 'fck_imp_team', $teams_old ) ) );
		$plays      = $m['is_player'] && ! $is_trainer && $teams;
		$email      = $m['board_email'];
		$func_email = $email && str_ends_with( strtolower( $email ), '@fc-koenigsfeld.de' );

		$id = fck_imp_upsert( 'person', $m['old_id'], array(
			'post_type'   => 'person',
			'post_status' => $m['status'],
			'post_title'  => trim( "{$m['vorname']} {$m['nachname']}" ) ?: fck_imp_name( $m['title'] ),
			'post_name'   => $m['slug'],
		), array(
			'vorname'      => $m['vorname'],
			'nachname'     => $m['nachname'],
			'spieler'      => $plays ? 1 : 0,
			'mannschaften' => $plays ? $teams : array(),
			'aktiv'        => 1,
			'email'        => $func_email ? '' : $email,
		) );
		if ( ! $id ) {
			continue;
		}
		$n++;
		$photo = $m['player_photo'] ?: $m['board_member_photo'];
		if ( $photo && ( $mid = fck_imp_media( $photo ) ) ) {
			set_post_thumbnail( $id, $mid );
		}
		$pos = array_values( array_filter( array_map( fn( $p ) => $fck_positions[ $p ] ?? null, $m['positions'] ) ) );
		wp_set_object_terms( $id, $pos, 'spielposition' );
		fck_imp_redirect( $m['url'], get_permalink( $id ) );

		// Ämter: Vorstand, Trainer:in, Schiedsrichter:in. Alte ID + Kennung macht sie wiederholbar.
		$amts = array();
		foreach ( $m['board_roles'] as $role ) {
			if ( isset( $fck_board[ $role ] ) ) {
				$amts[ "board-$role" ] = array( $fck_board[ $role ][0], $fck_board[ $role ][1], 0, $fck_board[ $role ][2] );
			}
		}
		if ( $m['is_board_member'] && ! $m['board_roles'] ) {
			$amts['board'] = array( 'vorstand', 'Beisitzer', 0, 9 );
		}
		if ( $is_trainer ) {
			foreach ( $teams as $t ) {
				$amts[ "trainer-$t" ] = array( 'trainer', 'Trainer', $t, 20 );
			}
		}
		if ( $ref ) {
			$amts['schiedsrichter'] = array( 'schiedsrichter', 'Schiedsrichter', 0, 30 );
		}
		foreach ( $amts as $key => [ $gremium, $funktion, $team, $order ] ) {
			$amt = fck_imp_upsert( 'amt', crc32( $m['old_id'] . $key ), array(
				'post_type'   => 'amt',
				'post_status' => 'publish',
				'post_title'  => $funktion,
				'menu_order'  => $order,
			), array(
				'person_id'       => $id,
				'gremium'         => $gremium,
				'funktion'        => $funktion,
				'team_id'         => $team ?: '',
				'funktions_email' => $func_email && 'vorstand' === $gremium ? $email : '',
			) );
			if ( $amt ) {
				wp_set_object_terms( $amt, 'fck', 'organisation' );
				fck_amt_refresh( $amt );
				$a++;
			}
		}
	}
	WP_CLI::log( "personen: $n, ämter: $a" );
}

/* ------------------------------------------------------------------ partner */

$fck_partner_map = array(
	// alter Stufen-Slug => [Stufe, Leistung, Mannschaft (alte ID)]
	'sportpark'                  => array( 'haupt', 'sportpark', 0 ),
	'trikotsponsor-1-mannschaft' => array( 'exklusiv', 'trikot', 681 ),
	'trikotsponsor-sg'           => array( 'exklusiv', 'trikot', 682 ),
	'exclusiv'                   => array( 'exklusiv', '', 0 ),
	'premium'                    => array( 'premium', '', 0 ),
	'praesentationsanzuege'      => array( 'ausstattung', 'praesentationsanzug', 0 ),
	'bandensponsor-plus'         => array( 'bande_plus', 'bande_plus', 0 ),
	'bandensponsor'              => array( 'bande', 'bande', 0 ),
	'basis'                      => array( 'basis', '', 0 ),
);
if ( in_array( 'partner', $fck_steps, true ) ) {
	$ranks = array_keys( FCK_STUFEN );
	$n = 0; $haupt = 0;
	foreach ( $D['sponsoren'] as $s ) {
		$stufe = 'basis';
		$leist = array();
		foreach ( $s['stufen'] as $slug ) {
			if ( ! isset( $fck_partner_map[ $slug ] ) ) {
				continue;
			}
			[ $st, $l, $team ] = $fck_partner_map[ $slug ];
			if ( array_search( $st, $ranks, true ) < array_search( $stufe, $ranks, true ) ) {
				$stufe = $st;
			}
			if ( $l ) {
				$row = array( 'art' => $l );
				if ( $team && ( $t = fck_imp_team( $team ) ) ) {
					$row['team_id'] = $t;
					if ( 682 === $team && ( $t3 = fck_imp_team( 1994 ) ) ) {
						$leist[] = array( 'art' => 'trikot', 'team_id' => $t3 );
					}
				}
				$leist[] = $row;
			}
		}
		$id = fck_imp_upsert( 'partner', $s['old_id'], array(
			'post_type'   => 'partner',
			'post_status' => 'publish',
			'post_title'  => fck_imp_name( $s['title'] ),
			'post_name'   => $s['slug'],
			'menu_order'  => $s['order'],
		), array(
			'stufe'       => $stufe,
			'website'     => $s['website'],
			'leistungen'  => $leist,
			'unbefristet' => 1,
		) );
		if ( ! $id ) {
			continue;
		}
		if ( $s['logo'] && ( $m = fck_imp_media( $s['logo'] ) ) ) {
			update_post_meta( $id, 'logo', $m );
		}
		fck_partner_refresh( $id );
		fck_imp_redirect( $s['url'], '/partner/' );
		if ( 'haupt' === $stufe ) {
			$haupt = $id;
		}
		$n++;
	}
	if ( $haupt ) {
		$opts = get_option( 'verein', array() ) ?: array();
		$opts['hauptpartner_id'] = $haupt;
		update_option( 'verein', $opts );
	}
	WP_CLI::log( "partner: $n" . ( $haupt ? ', Hauptpartner gesetzt' : '' ) );
}

/* ------------------------------------------------------------------ posts */

function fck_imp_content( string $html ): string {
	[ 'old_base' => $old_base, 'new_base' => $new_base, 'old_home' => $old_home, 'new_home' => $new_home ] = $GLOBALS['fck_urls'];
	// Block-Attribute mit Medien-IDs.
	$html = preg_replace_callback( '/<!-- wp:(image|file|video|cover|gallery|media-text) (\{.*?\}) (\/?)-->/', function ( $m ) {
		$attrs = json_decode( $m[2], true );
		if ( ! is_array( $attrs ) ) {
			return $m[0];
		}
		foreach ( array( 'id', 'mediaId' ) as $k ) {
			if ( isset( $attrs[ $k ] ) && ( $n = fck_imp_media( (int) $attrs[ $k ] ) ) ) {
				$attrs[ $k ] = $n;
			}
		}
		if ( isset( $attrs['ids'] ) && is_array( $attrs['ids'] ) ) {
			$attrs['ids'] = array_values( array_filter( array_map( fn( $i ) => fck_imp_media( (int) $i ), $attrs['ids'] ) ) );
		}
		return "<!-- wp:{$m[1]} " . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . " {$m[3]}-->";
	}, $html );
	$html = preg_replace_callback( '/wp-image-(\d+)/', fn( $m ) => 'wp-image-' . ( fck_imp_media( (int) $m[1] ) ?: $m[1] ), $html );
	$html = preg_replace_callback( '/data-id="(\d+)"/', fn( $m ) => 'data-id="' . ( fck_imp_media( (int) $m[1] ) ?: $m[1] ) . '"', $html );
	$www  = str_replace( '://', '://www.', $old_base );
	$html = str_replace( array( $old_base, $www, str_replace( 'https://', 'http://', $www ), str_replace( 'https://', 'http://', $old_base ) ), $new_base, $html );
	$old_home_www = str_replace( '://', '://www.', $old_home );
	$html = str_replace( array( $old_home_www . '/', str_replace( 'https://', 'http://', $old_home_www ) . '/' ), $new_home . '/', $html );
	$html = str_replace( array( $old_home . '/', str_replace( 'https://', 'http://', $old_home ) . '/' ), $new_home . '/', $html );
	return $html;
}

$fck_cat_map = array(
	'spielberichte'   => array( 'Spielbericht' ),
	'nachwuchs'       => array( 'Jugend' ),
	'foerderverein'   => array( 'Förderverein' ),
	'veranstaltungen' => array( 'Verein' ),
	'allgemein'       => array( 'Verein' ),
	'aktive'          => array(),
	'ah'              => array(),
	'kunstrasen'      => array( 'Verein' ),
	'neubau'          => array( 'Verein' ),
	'weihnachten'     => array( 'Verein' ),
	'bezirkspokal'    => array( 'Spielbericht' ),
	'verbandspokal'   => array( 'Spielbericht' ),
	'gemeindepokal'   => array( 'Spielbericht' ),
);
// Themen bleiben als Schlagwort erhalten.
$fck_cat_tags = array( 'kunstrasen' => 'Kunstrasen', 'neubau' => 'Neubau', 'weihnachten' => 'Weihnachten', 'bezirkspokal' => 'Bezirkspokal', 'verbandspokal' => 'Verbandspokal', 'gemeindepokal' => 'Gemeindepokal', 'veranstaltungen' => 'Veranstaltung' );

if ( in_array( 'posts', $fck_steps, true ) ) {
	$ah_term = (int) get_post_meta( fck_imp_team( 2028 ), 'team_term_id', true );
	$n = 0;
	foreach ( $D['posts'] as $p ) {
		$cats = array();
		$tags = array_map( fn( $s ) => ( get_term_by( 'slug', $s, 'post_tag' ) ?: null )?->term_id, $p['tags'] );
		foreach ( $p['categories'] as $c ) {
			foreach ( $fck_cat_map[ $c ] ?? array() as $name ) {
				$cats[] = $fck_cat[ $name ];
			}
			if ( isset( $fck_cat_tags[ $c ] ) ) {
				$tags[] = fck_imp_term( 'post_tag', $fck_cat_tags[ $c ] );
			}
		}
		if ( ! $cats ) {
			$cats[] = in_array( 'aktive', $p['categories'], true ) || in_array( 'ah', $p['categories'], true ) ? $fck_cat['Spielbericht'] : $fck_cat['Verein'];
		}
		$id = fck_imp_upsert( 'post', $p['old_id'], array(
			'post_type'     => 'post',
			'post_status'   => $p['status'],
			'post_title'    => fck_imp_name( $p['title'] ),
			'post_name'     => $p['slug'],
			'post_date'     => $p['date'],
			'post_date_gmt' => $p['date_gmt'],
			'post_content'  => fck_imp_content( $p['content'] ),
			'post_excerpt'  => $p['excerpt'],
			'post_author'   => $fck_users[ $p['author'] ] ?? get_current_user_id(),
		), array(
			'_fck_seo_title' => $p['seo_title'],
			'_fck_seo_desc'  => $p['seo_desc'],
		) );
		if ( ! $id ) {
			continue;
		}
		wp_set_post_categories( $id, array_unique( array_filter( $cats ) ) );
		wp_set_object_terms( $id, array_values( array_unique( array_filter( $tags ) ) ), 'post_tag' );
		$org = in_array( 'foerderverein', $p['categories'], true ) ? array( 'foerderverein' ) : array( 'fck' );
		wp_set_object_terms( $id, $org, 'organisation' );
		if ( $ah_term && in_array( 'ah', $p['categories'], true ) ) {
			wp_set_object_terms( $id, array( $ah_term ), 'team', true );
		}
		if ( $p['thumb'] && ( $m = fck_imp_media( $p['thumb'] ) ) ) {
			set_post_thumbnail( $id, $m );
		} else {
			delete_post_thumbnail( $id );
		}
		$p['sticky'] ? stick_post( $id ) : unstick_post( $id );
		$n++;
	}
	WP_CLI::log( "posts: $n" );
}

/* ------------------------------------------------------------------ seiten, historie */

if ( in_array( 'seiten', $fck_steps, true ) ) {
	$n = 0;
	foreach ( $D['rechtlich'] as $p ) {
		fck_imp_upsert( 'rechtlich', $p['old_id'], array(
			'post_type' => 'page', 'post_status' => 'publish', 'post_title' => fck_imp_name( $p['title'] ), 'post_name' => $p['slug'], 'post_content' => fck_imp_content( $p['content'] ),
		) ) && $n++;
	}
	$parent = fck_imp_upsert( 'foerderverein-seite', 1, array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Förderverein', 'post_name' => 'foerderverein',
	) );
	foreach ( $D['foerderverein'] as $p ) {
		fck_imp_upsert( 'foerderverein', $p['old_id'], array(
			'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $parent, 'post_title' => fck_imp_name( $p['title'] ), 'post_name' => $p['slug'], 'post_content' => fck_imp_content( $p['content'] ),
		) ) && $n++;
	}
	// Alte Bricks-Seiten mit eigenem Text als Entwurf zum Nachschlagen.
	foreach ( $D['pages'] as $p ) {
		if ( strlen( trim( wp_strip_all_tags( $p['content'] ) ) ) > 50 ) {
			fck_imp_upsert( 'seite', $p['old_id'], array(
				'post_type' => 'page', 'post_status' => 'draft', 'post_title' => fck_imp_name( $p['title'] ) . ' (alt)', 'post_name' => $p['slug'] . '-alt', 'post_content' => fck_imp_content( $p['content'] ),
			) ) && $n++;
		}
	}
	foreach ( $D['historie'] as $i => $p ) {
		preg_match( '/(19|20)\d{2}/', $p['title'], $y );
		$id = fck_imp_upsert( 'historie', $p['old_id'], array(
			'post_type' => 'historie', 'post_status' => 'publish', 'post_title' => fck_imp_name( $p['title'] ), 'post_name' => $p['slug'], 'post_content' => fck_imp_content( $p['content'] ), 'menu_order' => $i,
		), array(
			'jahr' => $y[0] ?? ( str_contains( $p['title'], '60er' ) ? 1960 : 1954 ),
			'art'  => 'meilenstein',
		) );
		if ( $id && $p['thumb'] && ( $m = fck_imp_media( $p['thumb'] ) ) ) {
			set_post_thumbnail( $id, $m );
		}
		fck_imp_redirect( $p['url'], '/verein/' );
		$n += $id ? 1 : 0;
	}
	WP_CLI::log( "seiten + historie: $n" );
}

/* ------------------------------------------------------------------ optionen */

if ( in_array( 'optionen', $fck_steps, true ) ) {
	$e    = $D['einstellungen'];
	$opts = get_option( 'verein', array() ) ?: array();
	$addr = $e['official_address']['official_address_text'] ?? '';
	$opts = array_merge( $opts, array_filter( array(
		'adresse'             => $addr,
		'email'               => $e['official_address']['email_adresse'] ?? '',
		'vereinsheim_telefon' => $e['clubhouse']['telefon_nice'] ?? '',
		'facebook'            => $e['social_links']['facebook'] ?? '',
		'instagram'           => $e['social_links']['instagram'] ?? '',
		'youtube'             => $e['social_links']['youtube'] ?? '',
		'instagram_handle'    => '@fckoenigsfeld1954',
		'saison'              => $opts['saison'] ?? fck_saison_aus_datum( current_time( 'Y-m-d' ) ),
		'gruendungsjahr'      => 1954,
	) ) );
	if ( ! empty( $e['logo_group']['logo'] ) && ( $m = fck_imp_media( (int) $e['logo_group']['logo'] ) ) ) {
		$opts['logo'] = $m;
	}
	// Vereinsheim als Spielstätte.
	if ( ! empty( $e['clubhouse']['adresse_clubhouse'] ) ) {
		$lines = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $e['clubhouse']['adresse_clubhouse'] ) ) ) );
		$heim  = fck_imp_upsert( 'vereinsheim', 1, array( 'post_type' => 'spielstaette', 'post_status' => 'publish', 'post_title' => $lines[0] ?? 'Vereinsheim' ), array(
			'adresse'          => implode( "\n", array_slice( $lines, 1 ) ),
			'telefon_spieltag' => $e['clubhouse']['telefon_nice'] ?? '',
		) );
		$opts['vereinsheim_id'] = $heim;
	}
	update_option( 'verein', $opts );
	WP_CLI::log( 'optionen: ' . count( $opts ) . ' Werte' );
}

/* ------------------------------------------------------------------ spiele (manueller Spielplan) */

if ( in_array( 'spiele', $fck_steps, true ) ) {
	$team_map = array( 'erste' => 681, 'zweite' => 682, 'dritte' => 1994, 'ah' => 2028 );
	$n = 0;
	foreach ( (array) ( $D['einstellungen']['club_matchdays'] ?? array() ) as $i => $s ) {
		$team = fck_imp_team( $team_map[ $s['team'] ] ?? 0 );
		if ( ! $team ) {
			continue;
		}
		$home   = (int) $s['home_team'];
		$heim   = in_array( $home, $own_club, true );
		$gegner = fck_imp_find( 'gegner', $heim ? (int) $s['away_team'] : $home, 'gegner' );
		$dt     = DateTimeImmutable::createFromFormat( 'd-m-Y H:i', $s['match_datetime']['formatted'] ?? '', wp_timezone() );
		$has    = isset( $s['home_score'], $s['away_score'] ) && '' !== $s['home_score'];
		$league = trim( (string) ( $s['league'] ?? '' ) );
		$art    = stripos( $league, 'freundschaft' ) !== false ? 'test' : ( stripos( $league, 'pokal' ) !== false ? 'pokal' : 'liga' );
		$id     = fck_imp_upsert( 'spiel', crc32( 'match' . $i . ( $s['match_datetime']['timestamp'] ?? '' ) ), array( 'post_type' => 'spiel', 'post_status' => 'publish', 'post_title' => 'Spiel' ), array(
			'team_id'     => $team,
			'art'         => $art,
			'anstoss'     => $dt ? $dt->format( 'Y-m-d H:i:s' ) : '',
			'heim'        => $heim ? 1 : 0,
			'gegner_id'   => $gegner ?: '',
			'status'      => $has ? 'beendet' : 'geplant',
			'tore_eigen'  => $has ? (int) ( $heim ? $s['home_score'] : $s['away_score'] ) : '',
			'tore_gegner' => $has ? (int) ( $heim ? $s['away_score'] : $s['home_score'] ) : '',
			'quelle'      => 'manuell',
		) );
		if ( $id ) {
			if ( $league ) {
				$w = fck_imp_term( 'wettbewerb', $league );
				update_term_meta( $w, 'art', $art );
				wp_set_object_terms( $id, $w, 'wettbewerb' );
			}
			fck_spiel_refresh( $id );
			$n++;
		}
	}
	WP_CLI::log( "spiele (manuell): $n" );
}

/* ------------------------------------------------------------------ fertig */

if ( in_array( 'fertig', $fck_steps, true ) ) {
	wp_defer_term_counting( false );
	update_option( 'permalink_structure', $D['meta']['permalinks'] );
	flush_rewrite_rules();
	fck_refresh_all_teams();
	fck_purge_cache();
}

if ( $GLOBALS['fck_redirects'] ) {
	$csv = dirname( $fck_file ) . '/redirects.csv';
	$fh  = fopen( $csv, 'w' );
	foreach ( $GLOBALS['fck_redirects'] as $from => $to ) {
		fputcsv( $fh, array( $from, $to ) );
	}
	fclose( $fh );
	WP_CLI::log( 'Weiterleitungen: ' . count( $GLOBALS['fck_redirects'] ) . " → $csv" );
}
WP_CLI::success( 'Import fertig: ' . implode( ', ', $fck_steps ) );
