<?php
/**
 * Meta-Box-Felder.
 *
 * Regeln:
 * - Verknüpfungen sind `post`-Felder. Ein Wert = eine Meta-Zeile, mehrere Werte = je eine Zeile.
 *   So filtern Etch-Loops mit `meta_query` (z. B. Spiele mit `team_id = $team`).
 * - Listen, die nur angezeigt werden (Trainingszeiten, Tabellenzeilen), sind geklonte Gruppen.
 *   Etch: {#loop this.metabox.trainingszeiten as zeit}.
 * - Zeitpunkte als Unix-Zeitstempel, damit nach `meta_value_num` sortiert werden kann.
 * - Optionen: {options.metabox.verein.<feld>}.
 */

defined( 'ABSPATH' ) || exit;

function fck_post_field( string $id, string $name, string $post_type, bool $multiple = false, array $extra = array() ): array {
	return array_merge(
		array(
			'id'         => $id,
			'name'       => $name,
			'type'       => 'post',
			'post_type'  => $post_type,
			'field_type' => 'select_advanced',
			'multiple'   => $multiple,
			'query_args' => array( 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC' ),
		),
		$extra
	);
}

function fck_switch( string $id, string $name, array $extra = array() ): array {
	return array_merge(
		array( 'id' => $id, 'name' => $name, 'type' => 'switch', 'style' => 'rounded', 'on_label' => 'Ja', 'off_label' => 'Nein' ),
		$extra
	);
}

function fck_timestamp( string $id, string $name, array $extra = array() ): array {
	return array_merge(
		array(
			'id'         => $id,
			'name'       => $name,
			'type'       => 'datetime',
			'timestamp'  => true,
			'js_options' => array( 'stepMinute' => 15, 'dateFormat' => 'dd.mm.yy', 'timeFormat' => 'HH:mm' ),
		),
		$extra
	);
}

const FCK_WOCHENTAGE = array(
	'1' => 'Montag',
	'2' => 'Dienstag',
	'3' => 'Mittwoch',
	'4' => 'Donnerstag',
	'5' => 'Freitag',
	'6' => 'Samstag',
	'7' => 'Sonntag',
);

add_filter( 'mb_settings_pages', function ( array $pages ): array {
	$pages[] = array(
		'id'          => 'verein',
		'option_name' => 'verein',
		'menu_title'  => 'Verein',
		'page_title'  => 'Verein',
		'icon_url'    => 'dashicons-shield',
		'position'    => 19,
		'columns'     => 1,
		'tabs'        => array(
			'kontakt' => 'Kontakt',
			'saison'  => 'Saison',
			'zahlen'  => 'Zahlen & Beiträge',
		),
	);
	return $pages;
} );

add_filter( 'rwmb_meta_boxes', function ( array $boxes ): array {

	// Verein (Options-Seite).
	$boxes[] = array(
		'id'             => 'verein-kontakt',
		'title'          => 'Kontakt',
		'settings_pages' => 'verein',
		'tab'            => 'kontakt',
		'fields'         => array(
			array( 'id' => 'name', 'name' => 'Vereinsname', 'type' => 'text', 'std' => 'FC Königsfeld 1954 e.V.' ),
			array( 'id' => 'adresse', 'name' => 'Postanschrift', 'type' => 'textarea', 'rows' => 3 ),
			array( 'id' => 'email', 'name' => 'E-Mail', 'type' => 'email' ),
			array( 'id' => 'vereinsheim_telefon', 'name' => 'Telefon Vereinsheim', 'type' => 'text' ),
			fck_post_field( 'vereinsheim_id', 'Vereinsheim / Hauptplatz', 'spielstaette' ),
			array( 'id' => 'instagram', 'name' => 'Instagram', 'type' => 'url' ),
			array( 'id' => 'instagram_handle', 'name' => 'Instagram-Name', 'type' => 'text', 'placeholder' => '@fckoenigsfeld' ),
			array( 'id' => 'facebook', 'name' => 'Facebook', 'type' => 'url' ),
			array( 'id' => 'foerderverein_url', 'name' => 'Förderverein (Seite)', 'type' => 'url' ),
		),
	);
	$boxes[] = array(
		'id'             => 'verein-saison',
		'title'          => 'Saison',
		'settings_pages' => 'verein',
		'tab'            => 'saison',
		'fields'         => array(
			array( 'id' => 'saison', 'name' => 'Aktuelle Saison', 'type' => 'text', 'placeholder' => '2026/27' ),
			array( 'id' => 'winterpause_von', 'name' => 'Winterpause von', 'type' => 'date', 'timestamp' => true ),
			array( 'id' => 'winterpause_bis', 'name' => 'Winterpause bis', 'type' => 'date', 'timestamp' => true ),
			array( 'id' => 'sommerpause_von', 'name' => 'Sommerpause von', 'type' => 'date', 'timestamp' => true ),
			array( 'id' => 'sommerpause_bis', 'name' => 'Sommerpause bis', 'type' => 'date', 'timestamp' => true ),
		),
	);
	$boxes[] = array(
		'id'             => 'verein-zahlen',
		'title'          => 'Zahlen & Beiträge',
		'settings_pages' => 'verein',
		'tab'            => 'zahlen',
		'fields'         => array(
			array( 'id' => 'gruendungsjahr', 'name' => 'Gründungsjahr', 'type' => 'number', 'std' => 1954 ),
			array( 'id' => 'anzahl_mitglieder', 'name' => 'Mitglieder', 'type' => 'number', 'min' => 0 ),
			array( 'id' => 'anzahl_plaetze', 'name' => 'Plätze', 'type' => 'number', 'min' => 0 ),
			array( 'id' => 'beitrag_jugend', 'name' => 'Beitrag Jugend (€/Jahr)', 'type' => 'number', 'min' => 0, 'step' => 'any' ),
			array( 'id' => 'beitrag_erwachsene', 'name' => 'Beitrag Erwachsene (€/Jahr)', 'type' => 'number', 'min' => 0, 'step' => 'any' ),
			array( 'id' => 'beitrag_familie', 'name' => 'Beitrag Familie (€/Jahr)', 'type' => 'number', 'min' => 0, 'step' => 'any' ),
			array( 'id' => 'beitrag_passiv', 'name' => 'Beitrag passiv (€/Jahr)', 'type' => 'number', 'min' => 0, 'step' => 'any' ),
		),
	);

	// Mannschaft.
	$boxes[] = array(
		'id'         => 'mannschaft-daten',
		'title'      => 'Mannschaft',
		'post_types' => 'mannschaft',
		'fields'     => array(
			array( 'id' => 'kurzname', 'name' => 'Kurzname', 'type' => 'text', 'desc' => 'Für Spielkarten und Navigation, z. B. „1. Herren“.' ),
			array( 'id' => 'jahrgaenge', 'name' => 'Jahrgänge', 'type' => 'text', 'placeholder' => '2012/2013' ),
			array( 'id' => 'liga', 'name' => 'Liga / Staffel', 'type' => 'text' ),
			array( 'id' => 'fd_team_id', 'name' => 'fussball.de Team-ID', 'type' => 'text', 'desc' => 'Leer lassen, wenn das Team nicht im Ligabetrieb spielt. Dann werden Spiele von Hand gepflegt.' ),
			fck_switch( 'auf_startseite', 'Auf der Startseite zeigen' ),
			fck_post_field( 'heimspielstaette_id', 'Heimplatz', 'spielstaette' ),
			fck_post_field( 'ansprechperson_id', 'Ansprechperson', 'person' ),
			fck_post_field( 'trikotsponsor_id', 'Trikotsponsor', 'partner' ),
			array( 'type' => 'heading', 'name' => 'Tabellenzonen' ),
			array( 'id' => 'zone_aufstieg', 'name' => 'Aufstiegsplätze', 'type' => 'number', 'min' => 0, 'std' => 1 ),
			array( 'id' => 'zone_relegation', 'name' => 'Relegationsplätze', 'type' => 'number', 'min' => 0, 'std' => 0 ),
			array( 'id' => 'zone_abstieg', 'name' => 'Abstiegsplätze', 'type' => 'number', 'min' => 0, 'std' => 2 ),
		),
	);
	$boxes[] = array(
		'id'         => 'mannschaft-training',
		'title'      => 'Trainingszeiten',
		'post_types' => 'mannschaft',
		'fields'     => array(
			array(
				'id'         => 'trainingszeiten',
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'add_button' => 'Trainingszeit hinzufügen',
				'fields'     => array(
					array( 'id' => 'tag', 'name' => 'Tag', 'type' => 'select', 'options' => FCK_WOCHENTAGE, 'columns' => 3 ),
					array( 'id' => 'von', 'name' => 'Von', 'type' => 'time', 'columns' => 2, 'js_options' => array( 'stepMinute' => 15 ) ),
					array( 'id' => 'bis', 'name' => 'Bis', 'type' => 'time', 'columns' => 2, 'js_options' => array( 'stepMinute' => 15 ) ),
					fck_post_field( 'spielstaette_id', 'Ort', 'spielstaette', false, array( 'columns' => 5 ) ),
				),
			),
		),
	);

	// Spiel.
	$boxes[] = array(
		'id'         => 'spiel-daten',
		'title'      => 'Spiel',
		'post_types' => 'spiel',
		'fields'     => array(
			fck_post_field( 'team_id', 'Mannschaft', 'mannschaft', false, array( 'required' => true ) ),
			fck_timestamp( 'anstoss', 'Anstoß', array( 'required' => true ) ),
			array( 'id' => 'wettbewerb', 'name' => 'Wettbewerb', 'type' => 'text', 'placeholder' => 'Kreisliga A, Bezirkspokal, Testspiel' ),
			array( 'id' => 'spieltag', 'name' => 'Spieltag', 'type' => 'number', 'min' => 0 ),
			fck_switch( 'heim', 'Heimspiel', array( 'std' => 1 ) ),
			array( 'id' => 'gegner', 'name' => 'Gegner', 'type' => 'text', 'required' => true ),
			array( 'id' => 'gegner_wappen', 'name' => 'Gegner-Wappen', 'type' => 'single_image' ),
			fck_post_field( 'spielstaette_id', 'Spielstätte', 'spielstaette' ),
			array(
				'id'      => 'status',
				'name'    => 'Status',
				'type'    => 'select',
				'std'     => 'geplant',
				'options' => array( 'geplant' => 'Geplant', 'beendet' => 'Beendet', 'verlegt' => 'Verlegt', 'abgesagt' => 'Abgesagt' ),
			),
			array( 'id' => 'tore_eigen', 'name' => 'Tore FCK', 'type' => 'number', 'min' => 0 ),
			array( 'id' => 'tore_gegner', 'name' => 'Tore Gegner', 'type' => 'number', 'min' => 0 ),
			array(
				'id'      => 'zusatz',
				'name'    => 'Zusatz',
				'type'    => 'select',
				'options' => array( '' => '–', 'nv' => 'n. V.', 'ie' => 'i. E.', 'wertung' => 'Wertung' ),
			),
			array(
				'id'       => 'quelle',
				'name'     => 'Quelle',
				'type'     => 'select',
				'std'      => 'manuell',
				'options'  => array( 'manuell' => 'Von Hand', 'fussballde' => 'fussball.de' ),
				'readonly' => false,
			),
			array( 'id' => 'fd_spiel_id', 'name' => 'fussball.de Spiel-ID', 'type' => 'text' ),
		),
	);

	// Tabelle (eine pro Mannschaft, schreibt die Pipeline).
	$boxes[] = array(
		'id'         => 'tabelle-daten',
		'title'      => 'Tabelle',
		'post_types' => 'tabelle',
		'fields'     => array(
			fck_post_field( 'team_id', 'Mannschaft', 'mannschaft', false, array( 'required' => true ) ),
			array( 'id' => 'liga', 'name' => 'Liga / Staffel', 'type' => 'text' ),
			array(
				'id'     => 'zeilen',
				'name'   => 'Zeilen',
				'type'   => 'group',
				'clone'  => true,
				'fields' => array(
					array( 'id' => 'platz', 'name' => 'Pl.', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'verein', 'name' => 'Verein', 'type' => 'text', 'columns' => 3 ),
					array( 'id' => 'wappen', 'name' => 'Wappen', 'type' => 'single_image', 'columns' => 1 ),
					array( 'id' => 'spiele', 'name' => 'Sp', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'siege', 'name' => 'S', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'unentschieden', 'name' => 'U', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'niederlagen', 'name' => 'N', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'tore', 'name' => 'Tore', 'type' => 'text', 'columns' => 1 ),
					array( 'id' => 'diff', 'name' => 'Diff', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'punkte', 'name' => 'Pkt', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'eigenes_team', 'name' => 'FCK', 'type' => 'checkbox', 'columns' => 1 ),
				),
			),
		),
	);

	// Person.
	$boxes[] = array(
		'id'         => 'person-daten',
		'title'      => 'Person',
		'post_types' => 'person',
		'fields'     => array(
			array( 'id' => 'vorname', 'name' => 'Vorname', 'type' => 'text', 'columns' => 6 ),
			array( 'id' => 'nachname', 'name' => 'Nachname', 'type' => 'text', 'columns' => 6 ),
			array(
				'id'      => 'rollen',
				'name'    => 'Rollen',
				'type'    => 'checkbox_list',
				'inline'  => true,
				'options' => array(
					'spieler'       => 'Spieler:in',
					'trainer'       => 'Trainer:in',
					'betreuer'      => 'Betreuer:in',
					'jugendleitung' => 'Jugendleitung',
					'vorstand'      => 'Vorstand',
					'kinderschutz'  => 'Kinderschutz / Vertrauensperson',
					'schiedsrichter'=> 'Schiedsrichter:in',
				),
			),
			array( 'id' => 'funktion', 'name' => 'Funktion', 'type' => 'text', 'placeholder' => '1. Vorsitzender, Kassier, Jugendleiter …' ),
			fck_post_field( 'mannschaften', 'Mannschaften', 'mannschaft', true ),
			array( 'id' => 'telefon', 'name' => 'Telefon', 'type' => 'text', 'columns' => 6 ),
			array( 'id' => 'email', 'name' => 'E-Mail', 'type' => 'email', 'columns' => 6 ),
			fck_switch( 'profil_ausblenden', 'Profil ausblenden', array( 'desc' => 'Bei Widerruf: Person erscheint nirgends mehr auf der Website.' ) ),
		),
	);
	$boxes[] = array(
		'id'         => 'person-spielerprofil',
		'title'      => 'Spielerprofil',
		'post_types' => 'person',
		'fields'     => array(
			array( 'id' => 'rueckennummer', 'name' => 'Rückennummer', 'type' => 'number', 'min' => 1, 'max' => 99, 'columns' => 4 ),
			array( 'id' => 'im_verein_seit', 'name' => 'Im Verein seit', 'type' => 'number', 'min' => 1954, 'max' => 2100, 'columns' => 4 ),
			array(
				'id'      => 'starker_fuss',
				'name'    => 'Starker Fuß',
				'type'    => 'select',
				'columns' => 4,
				'options' => array( '' => '–', 'rechts' => 'Rechts', 'links' => 'Links', 'beide' => 'Beidfüßig' ),
			),
			array( 'id' => 'lieblingsmoment', 'name' => 'Lieblingsmoment', 'type' => 'textarea', 'rows' => 3 ),
			array( 'id' => 'saison_einsaetze', 'name' => 'Einsätze (Saison)', 'type' => 'number', 'min' => 0, 'columns' => 4 ),
			array( 'id' => 'saison_tore', 'name' => 'Tore (Saison)', 'type' => 'number', 'min' => 0, 'columns' => 4 ),
			array( 'id' => 'saison_vorlagen', 'name' => 'Vorlagen (Saison)', 'type' => 'number', 'min' => 0, 'columns' => 4 ),
		),
	);

	// Spielstätte.
	$boxes[] = array(
		'id'         => 'spielstaette-daten',
		'title'      => 'Spielstätte',
		'post_types' => 'spielstaette',
		'fields'     => array(
			array( 'id' => 'adresse', 'name' => 'Adresse', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'lat', 'name' => 'Breitengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
			array( 'id' => 'lng', 'name' => 'Längengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
			array(
				'id'      => 'belag',
				'name'    => 'Belag',
				'type'    => 'select',
				'options' => array( 'rasen' => 'Rasen', 'kunstrasen' => 'Kunstrasen', 'halle' => 'Halle', 'hartplatz' => 'Hartplatz' ),
			),
			array( 'id' => 'parken', 'name' => 'Parken', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'hinweise_gaeste', 'name' => 'Hinweise für Gäste', 'type' => 'textarea', 'rows' => 3 ),
			array( 'id' => 'telefon_spieltag', 'name' => 'Telefon am Spieltag', 'type' => 'text' ),
		),
	);

	// Partner.
	$boxes[] = array(
		'id'         => 'partner-daten',
		'title'      => 'Partner',
		'post_types' => 'partner',
		'fields'     => array(
			array( 'id' => 'logo', 'name' => 'Logo', 'type' => 'single_image', 'desc' => 'Am besten als SVG oder PNG mit transparentem Hintergrund.' ),
			array(
				'id'       => 'stufe',
				'name'     => 'Stufe',
				'type'     => 'select',
				'required' => true,
				'options'  => array(
					'haupt'       => 'Hauptpartner',
					'exklusiv'    => 'Exklusiv',
					'premium'     => 'Premium',
					'ausstattung' => 'Ausstattung',
					'bande_plus'  => 'Bande Plus',
					'bande'       => 'Bande',
					'basis'       => 'Basis',
				),
			),
			array( 'id' => 'rolle', 'name' => 'Rolle', 'type' => 'text', 'placeholder' => 'Trikot SG B.K.N., Präsentationsanzüge …' ),
			array( 'id' => 'website', 'name' => 'Website', 'type' => 'url' ),
			array( 'id' => 'ort', 'name' => 'Ort', 'type' => 'text' ),
			array( 'id' => 'laufzeit_bis', 'name' => 'Laufzeit bis', 'type' => 'date', 'timestamp' => true ),
			fck_post_field( 'team_id', 'Mannschaft (z. B. Trikot)', 'mannschaft' ),
		),
	);

	// Termin.
	$boxes[] = array(
		'id'         => 'termin-daten',
		'title'      => 'Termin',
		'post_types' => 'termin',
		'fields'     => array(
			fck_timestamp( 'start', 'Beginn', array( 'required' => true ) ),
			fck_timestamp( 'ende', 'Ende' ),
			fck_switch( 'ganztaegig', 'Ganztägig' ),
			fck_post_field( 'spielstaette_id', 'Ort (Spielstätte)', 'spielstaette' ),
			array( 'id' => 'ort', 'name' => 'Ort (frei)', 'type' => 'text', 'desc' => 'Nur wenn der Ort keine Spielstätte ist.' ),
			array(
				'id'      => 'veranstalter',
				'name'    => 'Veranstalter',
				'type'    => 'select',
				'std'     => 'verein',
				'options' => array( 'verein' => 'FC Königsfeld', 'foerderverein' => 'Förderverein' ),
			),
		),
	);

	// Frage und Download.
	$bereiche = array( 'eltern' => 'Eltern', 'mitglied' => 'Mitglied werden', 'partner' => 'Partner', 'verein' => 'Verein' );
	$boxes[] = array(
		'id'         => 'frage-daten',
		'title'      => 'Frage',
		'post_types' => 'frage',
		'context'    => 'side',
		'fields'     => array(
			array( 'id' => 'bereich', 'name' => 'Bereich', 'type' => 'select', 'options' => $bereiche ),
		),
	);
	$boxes[] = array(
		'id'         => 'download-daten',
		'title'      => 'Download',
		'post_types' => 'download',
		'fields'     => array(
			array( 'id' => 'datei', 'name' => 'Datei', 'type' => 'file_advanced', 'max_file_uploads' => 1, 'max_status' => false ),
			array( 'id' => 'bereich', 'name' => 'Bereich', 'type' => 'select', 'options' => $bereiche ),
		),
	);

	// Historie.
	$boxes[] = array(
		'id'         => 'historie-daten',
		'title'      => 'Historie',
		'post_types' => 'historie',
		'context'    => 'side',
		'fields'     => array(
			array( 'id' => 'jahr', 'name' => 'Jahr', 'type' => 'number', 'min' => 1900, 'max' => 2100 ),
		),
	);

	// Beitrag → Spiel (macht den Beitrag zum Spielbericht).
	$boxes[] = array(
		'id'         => 'beitrag-spiel',
		'title'      => 'Spielbericht',
		'post_types' => 'post',
		'context'    => 'side',
		'fields'     => array(
			fck_post_field( 'spiel_id', 'Zu Spiel', 'spiel', false, array( 'query_args' => array( 'post_status' => 'any', 'posts_per_page' => 200, 'meta_key' => 'anstoss', 'orderby' => 'meta_value_num', 'order' => 'DESC' ) ) ),
		),
	);

	return $boxes;
} );
