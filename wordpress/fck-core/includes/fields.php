<?php
/**
 * Meta-Box-Felder.
 *
 * Regeln:
 * - Verknüpfungen sind `post`-Felder. Ein Wert = eine Meta-Zeile, mehrere Werte = je eine Zeile.
 *   So filtern Etch-Loops mit `meta_query` (z. B. Spiele mit `team_id = $team`).
 * - Listen, die nur angezeigt werden (Trainingszeiten, Tabellenzeilen), sind geklonte Gruppen.
 *   Etch: {#loop this.metabox.trainingszeiten as zeit}.
 * - Zeitpunkte sind Ortszeit (Europe/Berlin) als `Y-m-d H:i:s`, Daten als `Y-m-d`.
 *   Beides sortiert als Text richtig; „jetzt“ rechnet nur fck-core (compute.php).
 * - Felder mit `fck_` vorne schreibt fck-core selbst (Rechenwerte), sie sind schreibgeschützt.
 * - Kontaktdaten sind nie in REST (`hide_from_rest`).
 * - Optionen: {options.metabox.verein.<feld>}, {options.metabox.foerderverein.<feld>}.
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
			'query_args' => array( 'post_status' => array( 'publish', 'private', 'draft' ), 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC' ),
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

function fck_datetime( string $id, string $name, array $extra = array() ): array {
	return array_merge(
		array(
			'id'          => $id,
			'name'        => $name,
			'type'        => 'datetime',
			'save_format' => 'Y-m-d H:i:s',
			'js_options'  => array( 'stepMinute' => 15, 'dateFormat' => 'dd.mm.yy', 'timeFormat' => 'HH:mm', 'showButtonPanel' => true ),
		),
		$extra
	);
}

function fck_date( string $id, string $name, array $extra = array() ): array {
	return array_merge(
		array(
			'id'          => $id,
			'name'        => $name,
			'type'        => 'date',
			'save_format' => 'Y-m-d',
			'js_options'  => array( 'dateFormat' => 'dd.mm.yy' ),
		),
		$extra
	);
}

function fck_readonly( string $id, string $name, string $type = 'text', array $extra = array() ): array {
	return array_merge(
		array( 'id' => $id, 'name' => $name, 'type' => $type, 'readonly' => true, 'desc' => 'Wird automatisch gesetzt.' ),
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

const FCK_SPIEL_ART = array(
	'liga'      => 'Liga',
	'pokal'     => 'Pokal',
	'test'      => 'Testspiel',
	'turnier'   => 'Turnier',
	'spielfest' => 'Spielfest',
);

const FCK_LEISTUNGEN = array(
	'sportpark'          => 'Namensgeber Sportpark',
	'trikot'             => 'Trikot',
	'praesentationsanzug'=> 'Präsentationsanzüge',
	'ausstattung'        => 'Ausstattung',
	'bande_plus'         => 'Bande Plus',
	'bande'              => 'Bande',
	'startseite_band'    => 'Startseiten-Band',
	'turnier'            => 'Turnier',
	'jugend'             => 'Jugendförderung',
);

const FCK_STUFEN = array(
	'haupt'       => 'Hauptpartner',
	'exklusiv'    => 'Exklusiv',
	'premium'     => 'Premium',
	'ausstattung' => 'Ausstattung',
	'bande_plus'  => 'Bande Plus',
	'bande'       => 'Bande',
	'basis'       => 'Basis',
);

const FCK_BEREICHE = array(
	'eltern'        => 'Eltern',
	'jugend'        => 'Jugend',
	'kinderschutz'  => 'Kinderschutz',
	'trainer'       => 'Trainer:innen',
	'mitglied'      => 'Mitglied werden',
	'partner'       => 'Partner',
	'verein'        => 'Verein',
	'foerderverein' => 'Förderverein',
);

const FCK_GREMIEN = array(
	'vorstand'       => 'Vorstand',
	'jugendleitung'  => 'Jugendleitung',
	'spielausschuss' => 'Spielausschuss',
	'trainer'        => 'Trainer:in',
	'betreuer'       => 'Betreuer:in',
	'kinderschutz'   => 'Kinderschutz / Vertrauensperson',
	'schiedsrichter' => 'Schiedsrichter:in',
	'sponsoring'     => 'Sponsoring',
	'ehrenmitglied'  => 'Ehrenmitglied',
	'helfer'         => 'Helfer:in / Ehrenamt',
);

add_filter( 'mb_settings_pages', function ( array $pages ): array {
	$pages[] = array(
		'id'          => 'verein',
		'option_name' => 'verein',
		'menu_title'  => 'Verein',
		'page_title'  => 'Verein',
		'icon_url'    => 'dashicons-shield',
		'position'    => 19,
		'capability'  => 'fck_edit_verein',
		'columns'     => 1,
		'tabs'        => array(
			'kontakt'  => 'Kontakt',
			'saison'   => 'Saison & Training',
			'beitraege'=> 'Mitgliedschaft',
			'partner'  => 'Partner',
			'zahlen'   => 'Zahlen',
		),
	);
	$pages[] = array(
		'id'          => 'foerderverein',
		'option_name' => 'foerderverein',
		'menu_title'  => 'Förderverein',
		'page_title'  => 'Förderverein',
		'parent'      => 'verein',
		'capability'  => 'fck_edit_foerderverein',
		'columns'     => 1,
	);
	return $pages;
} );

add_filter( 'rwmb_meta_boxes', function ( array $boxes ): array {

	/* ---------------------------------------------------------------- Optionen: Verein */

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
			array( 'id' => 'vereinsregister', 'name' => 'Vereinsregister', 'type' => 'text' ),
			array( 'id' => 'instagram', 'name' => 'Instagram', 'type' => 'url' ),
			array( 'id' => 'instagram_handle', 'name' => 'Instagram-Name', 'type' => 'text', 'placeholder' => '@fckoenigsfeld' ),
			array( 'id' => 'facebook', 'name' => 'Facebook', 'type' => 'url' ),
			array( 'id' => 'youtube', 'name' => 'YouTube', 'type' => 'url' ),
			array( 'id' => 'logo', 'name' => 'Vereinswappen', 'type' => 'single_image' ),
			fck_post_field( 'aufmacher_id', 'Aufmacher Startseite', 'post', false, array( 'desc' => 'Leer = neuester Beitrag.', 'query_args' => array( 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC' ) ) ),
		),
	);
	$boxes[] = array(
		'id'             => 'verein-saison',
		'title'          => 'Saison & Training',
		'settings_pages' => 'verein',
		'tab'            => 'saison',
		'fields'         => array(
			array( 'id' => 'saison', 'name' => 'Aktuelle Saison', 'type' => 'text', 'placeholder' => '2026/27', 'desc' => 'Neue Spiele und Tabellen bekommen diese Saison.' ),
			fck_date( 'saison_start', 'Saisonstart', array( 'desc' => 'Daraus werden die Jahrgänge der Jugendteams berechnet.' ) ),
			fck_date( 'winterpause_von', 'Winterpause von', array( 'columns' => 6 ) ),
			fck_date( 'winterpause_bis', 'Winterpause bis', array( 'columns' => 6 ) ),
			fck_date( 'sommerpause_von', 'Sommerpause von', array( 'columns' => 6 ) ),
			fck_date( 'sommerpause_bis', 'Sommerpause bis', array( 'columns' => 6 ) ),
			fck_date( 'hallensaison_von', 'Hallensaison von', array( 'columns' => 6 ) ),
			fck_date( 'hallensaison_bis', 'Hallensaison bis', array( 'columns' => 6 ) ),
		),
	);
	$boxes[] = array(
		'id'             => 'verein-beitraege',
		'title'          => 'Mitgliedschaft',
		'settings_pages' => 'verein',
		'tab'            => 'beitraege',
		'fields'         => array(
			fck_date( 'beitraege_gueltig_ab', 'Beiträge gültig ab' ),
			array(
				'id'         => 'beitraege',
				'name'       => 'Beiträge',
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'add_button' => 'Beitrag hinzufügen',
				'fields'     => array(
					array( 'id' => 'bezeichnung', 'name' => 'Bezeichnung', 'type' => 'text', 'columns' => 4, 'placeholder' => 'Jugend, Erwachsene, Familie …' ),
					array( 'id' => 'betrag', 'name' => 'Betrag (€)', 'type' => 'number', 'min' => 0, 'step' => 'any', 'columns' => 2 ),
					array( 'id' => 'intervall', 'name' => 'Intervall', 'type' => 'select', 'columns' => 2, 'std' => 'jahr', 'options' => array( 'jahr' => 'pro Jahr', 'halbjahr' => 'pro Halbjahr', 'monat' => 'pro Monat', 'einmalig' => 'einmalig' ) ),
					array( 'id' => 'hinweis', 'name' => 'Hinweis', 'type' => 'text', 'columns' => 4 ),
				),
			),
			fck_post_field( 'antrag_download_id', 'Mitgliedsantrag', 'download' ),
		),
	);
	$boxes[] = array(
		'id'             => 'verein-partner',
		'title'          => 'Partner',
		'settings_pages' => 'verein',
		'tab'            => 'partner',
		'fields'         => array(
			fck_post_field( 'hauptpartner_id', 'Hauptpartner (Startseiten-Band)', 'partner' ),
			fck_post_field( 'sponsoring_person_id', 'Ansprechperson Sponsoring', 'person' ),
			fck_post_field( 'sponsorenmappe_id', 'Sponsorenmappe', 'download' ),
			array(
				'id'         => 'pakete',
				'name'       => 'Pakete',
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'add_button' => 'Paket hinzufügen',
				'fields'     => array(
					array( 'id' => 'name', 'name' => 'Name', 'type' => 'text', 'columns' => 3 ),
					array( 'id' => 'ab_preis', 'name' => 'ab (€/Jahr)', 'type' => 'number', 'min' => 0, 'columns' => 2 ),
					array( 'id' => 'leistungen', 'name' => 'Leistungen', 'type' => 'textarea', 'rows' => 2, 'columns' => 5 ),
					fck_switch( 'verfuegbar', 'Frei', array( 'std' => 1, 'columns' => 2 ) ),
				),
			),
			array( 'id' => 'reichweite_zuschauer', 'name' => 'Zuschauer pro Saison', 'type' => 'number', 'min' => 0, 'columns' => 4 ),
			array( 'id' => 'reichweite_instagram', 'name' => 'Instagram-Follower', 'type' => 'number', 'min' => 0, 'columns' => 4 ),
			array( 'id' => 'reichweite_website', 'name' => 'Website-Besuche pro Monat', 'type' => 'number', 'min' => 0, 'columns' => 4 ),
		),
	);
	$boxes[] = array(
		'id'             => 'verein-zahlen',
		'title'          => 'Zahlen',
		'settings_pages' => 'verein',
		'tab'            => 'zahlen',
		'fields'         => array(
			array( 'id' => 'gruendungsjahr', 'name' => 'Gründungsjahr', 'type' => 'number', 'std' => 1954 ),
			array( 'id' => 'anzahl_mitglieder', 'name' => 'Mitglieder', 'type' => 'number', 'min' => 0 ),
			array( 'id' => 'anzahl_plaetze', 'name' => 'Plätze', 'type' => 'number', 'min' => 0 ),
		),
	);

	/* ---------------------------------------------------------------- Optionen: Förderverein */

	$boxes[] = array(
		'id'             => 'foerderverein-daten',
		'title'          => 'Förderverein',
		'settings_pages' => 'foerderverein',
		'fields'         => array(
			array( 'id' => 'name', 'name' => 'Name', 'type' => 'text' ),
			array( 'id' => 'zweck', 'name' => 'Zweck', 'type' => 'textarea', 'rows' => 4 ),
			array( 'id' => 'adresse', 'name' => 'Anschrift', 'type' => 'textarea', 'rows' => 3 ),
			array( 'id' => 'email', 'name' => 'E-Mail', 'type' => 'email' ),
			array( 'id' => 'vereinsregister', 'name' => 'Vereinsregister', 'type' => 'text' ),
			array( 'type' => 'heading', 'name' => 'Spenden' ),
			array( 'id' => 'kontoinhaber', 'name' => 'Kontoinhaber', 'type' => 'text' ),
			array( 'id' => 'iban', 'name' => 'IBAN', 'type' => 'text', 'columns' => 8 ),
			array( 'id' => 'bic', 'name' => 'BIC', 'type' => 'text', 'columns' => 4 ),
			array( 'id' => 'spendenquittung_hinweis', 'name' => 'Hinweis Spendenquittung', 'type' => 'textarea', 'rows' => 2 ),
			fck_date( 'freistellungsbescheid_datum', 'Freistellungsbescheid vom' ),
			array( 'type' => 'heading', 'name' => 'Mitgliedschaft' ),
			array(
				'id'         => 'beitraege',
				'name'       => 'Beiträge',
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'add_button' => 'Beitrag hinzufügen',
				'fields'     => array(
					array( 'id' => 'bezeichnung', 'name' => 'Bezeichnung', 'type' => 'text', 'columns' => 5 ),
					array( 'id' => 'betrag', 'name' => 'Betrag (€)', 'type' => 'number', 'min' => 0, 'step' => 'any', 'columns' => 3 ),
					array( 'id' => 'intervall', 'name' => 'Intervall', 'type' => 'select', 'columns' => 4, 'std' => 'jahr', 'options' => array( 'jahr' => 'pro Jahr', 'einmalig' => 'einmalig' ) ),
				),
			),
			fck_post_field( 'antrag_download_id', 'Beitrittserklärung', 'download' ),
		),
	);

	/* ---------------------------------------------------------------- Altersklasse (Term-Meta) */

	$boxes[] = array(
		'id'         => 'altersklasse-daten',
		'title'      => 'Altersklasse',
		'taxonomies' => 'altersklasse',
		'fields'     => array(
			fck_switch( 'jugend', 'Jugend' ),
			array( 'id' => 'u_von', 'name' => 'U von', 'type' => 'number', 'min' => 5, 'max' => 23, 'desc' => 'z. B. E-Junioren: U10' ),
			array( 'id' => 'u_bis', 'name' => 'U bis', 'type' => 'number', 'min' => 5, 'max' => 23, 'desc' => 'z. B. E-Junioren: U11' ),
			array( 'id' => 'sortierung', 'name' => 'Sortierung', 'type' => 'number' ),
		),
	);
	$boxes[] = array(
		'id'         => 'wettbewerb-daten',
		'title'      => 'Wettbewerb',
		'taxonomies' => 'wettbewerb',
		'fields'     => array(
			array( 'id' => 'art', 'name' => 'Art', 'type' => 'select', 'options' => FCK_SPIEL_ART ),
		),
	);

	/* ---------------------------------------------------------------- Mannschaft */

	$boxes[] = array(
		'id'         => 'mannschaft-daten',
		'title'      => 'Mannschaft',
		'post_types' => 'mannschaft',
		'fields'     => array(
			array( 'id' => 'kurzname', 'name' => 'Kurzname', 'type' => 'text', 'desc' => 'Für Spielkarten und Navigation, z. B. „1. Herren“, „E2“.' ),
			array( 'id' => 'geschlecht', 'name' => 'Team', 'type' => 'select', 'std' => 'gemischt', 'options' => array( 'gemischt' => 'Jungen / gemischt', 'maedchen' => 'Mädchen / Frauen', 'herren' => 'Herren' ) ),
			array( 'id' => 'liga', 'name' => 'Liga / Staffel', 'type' => 'text' ),
			array( 'id' => 'jahrgaenge_abweichend', 'name' => 'Jahrgänge (nur bei Abweichung)', 'type' => 'text', 'placeholder' => '2015/2016', 'desc' => 'Sonst aus Altersklasse und Saisonstart berechnet.' ),
			fck_readonly( 'fck_jahrgaenge', 'Jahrgänge (berechnet)' ),
			fck_switch( 'auf_startseite', 'Auf der Startseite zeigen' ),
			fck_post_field( 'heimspielstaette_id', 'Heimplatz', 'spielstaette' ),
			fck_post_field( 'ansprechperson_id', 'Ansprechperson', 'person', false, array( 'desc' => 'Wen Eltern und Interessierte zuerst anrufen. Trainer:innen kommen aus den Ämtern.' ) ),
			array( 'id' => 'kontakt_email', 'name' => 'Team-E-Mail', 'type' => 'email', 'desc' => 'z. B. e-jugend@… Hat Vorrang vor persönlichen Adressen.', 'hide_from_rest' => true ),
			array( 'type' => 'heading', 'name' => 'Spielgemeinschaft' ),
			fck_switch( 'sg', 'Spielgemeinschaft' ),
			array( 'id' => 'sg_name', 'name' => 'Name der SG', 'type' => 'text', 'placeholder' => 'SG Gemeinde Königsfeld', 'visible' => array( 'sg', true ) ),
			array( 'id' => 'sg_partner', 'name' => 'Partnervereine', 'type' => 'text', 'visible' => array( 'sg', true ) ),
			fck_switch( 'sg_federfuehrend', 'FCK ist federführend', array( 'visible' => array( 'sg', true ) ) ),
			array( 'type' => 'heading', 'name' => 'Probetraining' ),
			fck_switch( 'probetraining', 'Probetraining möglich', array( 'std' => 1 ) ),
			fck_post_field( 'probetraining_kontakt_id', 'Kontakt Probetraining', 'person', false, array( 'desc' => 'Leer = Ansprechperson.', 'visible' => array( 'probetraining', true ) ) ),
			array( 'id' => 'hinweis_eltern', 'name' => 'Hinweis für Eltern', 'type' => 'textarea', 'rows' => 3, 'placeholder' => 'Was mitbringen, Fahrten, Einteilung E1/E2 …' ),
			array( 'type' => 'heading', 'name' => 'Tabellenzonen' ),
			array( 'id' => 'zone_aufstieg', 'name' => 'Aufstiegsplätze', 'type' => 'number', 'min' => 0, 'std' => 1, 'columns' => 4 ),
			array( 'id' => 'zone_relegation', 'name' => 'Relegationsplätze', 'type' => 'number', 'min' => 0, 'std' => 0, 'columns' => 4 ),
			array( 'id' => 'zone_abstieg', 'name' => 'Abstiegsplätze', 'type' => 'number', 'min' => 0, 'std' => 2, 'columns' => 4 ),
		),
	);
	$boxes[] = array(
		'id'         => 'mannschaft-training',
		'title'      => 'Training',
		'post_types' => 'mannschaft',
		'fields'     => array(
			array(
				'id'         => 'trainingszeiten',
				'name'       => 'Trainingszeiten',
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'add_button' => 'Trainingszeit hinzufügen',
				'fields'     => array(
					array( 'id' => 'tag', 'name' => 'Tag', 'type' => 'select', 'options' => FCK_WOCHENTAGE, 'columns' => 2 ),
					array( 'id' => 'von', 'name' => 'Von', 'type' => 'time', 'columns' => 2, 'js_options' => array( 'stepMinute' => 15 ) ),
					array( 'id' => 'bis', 'name' => 'Bis', 'type' => 'time', 'columns' => 2, 'js_options' => array( 'stepMinute' => 15 ) ),
					array( 'id' => 'zeitraum', 'name' => 'Wann', 'type' => 'select', 'columns' => 2, 'std' => 'ganzjaehrig', 'options' => array( 'ganzjaehrig' => 'Ganzjährig', 'feld' => 'Feldsaison', 'halle' => 'Hallensaison' ) ),
					fck_post_field( 'spielstaette_id', 'Ort', 'spielstaette', false, array( 'columns' => 4 ) ),
					array( 'id' => 'hinweis', 'name' => 'Hinweis', 'type' => 'text' ),
				),
			),
			array(
				'id'         => 'ausfaelle',
				'name'       => 'Training fällt aus',
				'type'       => 'group',
				'clone'      => true,
				'add_button' => 'Ausfall eintragen',
				'desc'       => 'Vergangene Tage verschwinden automatisch.',
				'fields'     => array(
					fck_date( 'datum', 'Datum', array( 'columns' => 4 ) ),
					array( 'id' => 'grund', 'name' => 'Grund', 'type' => 'text', 'columns' => 8, 'placeholder' => 'Platz gesperrt, Ferien …' ),
				),
			),
		),
	);
	$boxes[] = array(
		'id'         => 'mannschaft-sync',
		'title'      => 'fussball.de',
		'post_types' => 'mannschaft',
		'context'    => 'side',
		'fields'     => array(
			array( 'id' => 'fd_team_id', 'name' => 'Team-ID', 'type' => 'text', 'desc' => 'Leer, wenn das Team nicht im Ligabetrieb spielt.' ),
			array( 'id' => 'fd_team_name', 'name' => 'Name bei fussball.de', 'type' => 'text', 'desc' => 'Damit die eigene Zeile in der Tabelle erkannt wird.' ),
			array( 'id' => 'fd_saison', 'name' => 'Saison der Team-ID', 'type' => 'text', 'placeholder' => '2026/27' ),
			fck_readonly( 'fck_sync_letzter_lauf', 'Letzter Abgleich' ),
			fck_readonly( 'fck_sync_fehler', 'Fehler' ),
			fck_post_field( 'fck_naechstes_spiel_id', 'Nächstes Spiel', 'spiel', false, array( 'readonly' => true, 'disabled' => true ) ),
			fck_post_field( 'fck_letztes_spiel_id', 'Letztes Spiel', 'spiel', false, array( 'readonly' => true, 'disabled' => true ) ),
			fck_readonly( 'fck_form', 'Form (letzte 5)' ),
		),
	);

	/* ---------------------------------------------------------------- Spiel */

	$boxes[] = array(
		'id'         => 'spiel-daten',
		'title'      => 'Spiel',
		'post_types' => 'spiel',
		'fields'     => array(
			fck_post_field( 'team_id', 'Mannschaft', 'mannschaft', false, array( 'required' => true ) ),
			array( 'id' => 'art', 'name' => 'Art', 'type' => 'select', 'std' => 'liga', 'options' => FCK_SPIEL_ART ),
			fck_datetime( 'anstoss', 'Anstoß / Beginn', array( 'required' => true, 'columns' => 6 ) ),
			fck_datetime( 'ende', 'Ende', array( 'columns' => 6, 'visible' => array( 'art', 'in', array( 'turnier', 'spielfest' ) ) ) ),
			array( 'id' => 'turnier_name', 'name' => 'Turniername', 'type' => 'text', 'visible' => array( 'art', 'in', array( 'turnier', 'spielfest' ) ) ),
			array( 'id' => 'spieltag', 'name' => 'Spieltag', 'type' => 'number', 'min' => 0, 'visible' => array( 'art', 'liga' ) ),
			fck_switch( 'heim', 'Heimspiel', array( 'std' => 1 ) ),
			fck_post_field( 'gegner_id', 'Gegner', 'gegner', false, array( 'hidden' => array( 'art', 'in', array( 'turnier', 'spielfest' ) ) ) ),
			array( 'id' => 'gegner', 'name' => 'Gegner (Text)', 'type' => 'text', 'desc' => 'Nur wenn der Gegner noch nicht angelegt ist.', 'hidden' => array( 'art', 'in', array( 'turnier', 'spielfest' ) ) ),
			fck_post_field( 'spielstaette_id', 'Spielstätte', 'spielstaette' ),
			array( 'id' => 'platz', 'name' => 'Platz', 'type' => 'text', 'placeholder' => 'Kunstrasen, Platz 2 …' ),
			array( 'id' => 'ort_auswaerts', 'name' => 'Ort (auswärts)', 'type' => 'text', 'desc' => 'Leer = Platzadresse des Gegners.' ),
		),
	);
	$boxes[] = array(
		'id'         => 'spiel-ergebnis',
		'title'      => 'Ergebnis',
		'post_types' => 'spiel',
		'hidden'     => array( 'art', 'in', array( 'turnier', 'spielfest' ) ),
		'fields'     => array(
			array(
				'id'      => 'status',
				'name'    => 'Status',
				'type'    => 'select',
				'std'     => 'geplant',
				'options' => array(
					'geplant'     => 'Geplant',
					'beendet'     => 'Beendet',
					'verlegt'     => 'Verlegt (neuer Termin offen)',
					'abgesagt'    => 'Abgesagt',
					'abgebrochen' => 'Abgebrochen',
					'nichtantritt'=> 'Nichtantritt',
				),
			),
			array( 'id' => 'status_hinweis', 'name' => 'Hinweis zum Status', 'type' => 'text', 'placeholder' => 'Platz unbespielbar, Gewitter …' ),
			array( 'id' => 'tore_eigen', 'name' => 'Tore FCK', 'type' => 'number', 'min' => 0, 'columns' => 3 ),
			array( 'id' => 'tore_gegner', 'name' => 'Tore Gegner', 'type' => 'number', 'min' => 0, 'columns' => 3 ),
			array( 'id' => 'ht_eigen', 'name' => 'Halbzeit FCK', 'type' => 'number', 'min' => 0, 'columns' => 3 ),
			array( 'id' => 'ht_gegner', 'name' => 'Halbzeit Gegner', 'type' => 'number', 'min' => 0, 'columns' => 3 ),
			array(
				'id'      => 'zusatz',
				'name'    => 'Entscheidung',
				'type'    => 'select',
				'options' => array( '' => 'Regulär', 'nv' => 'nach Verlängerung', 'ie' => 'im Elfmeterschießen' ),
			),
			array( 'id' => 'ie_eigen', 'name' => 'Elfmeter FCK', 'type' => 'number', 'min' => 0, 'columns' => 6, 'visible' => array( 'zusatz', 'ie' ) ),
			array( 'id' => 'ie_gegner', 'name' => 'Elfmeter Gegner', 'type' => 'number', 'min' => 0, 'columns' => 6, 'visible' => array( 'zusatz', 'ie' ) ),
			array( 'id' => 'abbruch_minute', 'name' => 'Abbruch in Minute', 'type' => 'number', 'min' => 1, 'max' => 130, 'visible' => array( 'status', 'abgebrochen' ) ),
			array( 'id' => 'wertung_eigen', 'name' => 'Wertung FCK', 'type' => 'number', 'min' => 0, 'columns' => 6, 'desc' => 'Nur bei Sportgerichtsurteil.' ),
			array( 'id' => 'wertung_gegner', 'name' => 'Wertung Gegner', 'type' => 'number', 'min' => 0, 'columns' => 6 ),
		),
	);
	$boxes[] = array(
		'id'         => 'spiel-quelle',
		'title'      => 'Herkunft',
		'post_types' => 'spiel',
		'context'    => 'side',
		'fields'     => array(
			array( 'id' => 'quelle', 'name' => 'Quelle', 'type' => 'select', 'std' => 'manuell', 'options' => array( 'manuell' => 'Von Hand', 'fussballde' => 'fussball.de' ) ),
			array( 'id' => 'fd_spiel_id', 'name' => 'fussball.de Spiel-ID', 'type' => 'text' ),
			fck_switch( 'manuell_gesperrt', 'Korrektur schützen', array( 'desc' => 'Der Abgleich überschreibt dann Ergebnis und Status nicht.' ) ),
			fck_switch( 'nachholspiel', 'Nachholspiel' ),
			fck_readonly( 'anstoss_urspruenglich', 'Ursprünglicher Termin' ),
			fck_readonly( 'fck_ausgang', 'Ausgang (s/u/n)' ),
			fck_readonly( 'fck_tag', 'Spieltag (Datum)' ),
		),
	);

	/* ---------------------------------------------------------------- Gegner */

	$boxes[] = array(
		'id'         => 'gegner-daten',
		'title'      => 'Gegner',
		'post_types' => 'gegner',
		'fields'     => array(
			array( 'id' => 'kurzname', 'name' => 'Kurzname', 'type' => 'text', 'placeholder' => 'SV Deggenhausertal → Deggenhausertal' ),
			array( 'id' => 'fd_namen', 'name' => 'Namen bei fussball.de', 'type' => 'text', 'clone' => true, 'clone_as_multiple' => true, 'add_button' => 'Weitere Schreibweise', 'desc' => 'Alle Schreibweisen, auch SG-Namen. Der Abgleich ordnet darüber zu.' ),
			array( 'id' => 'platz_adresse', 'name' => 'Platzadresse', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'lat', 'name' => 'Breitengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
			array( 'id' => 'lng', 'name' => 'Längengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
		),
	);

	/* ---------------------------------------------------------------- Tabelle */

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
					fck_post_field( 'gegner_id', 'Gegner', 'gegner', false, array( 'columns' => 2 ) ),
					array( 'id' => 'spiele', 'name' => 'Sp', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'siege', 'name' => 'S', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'unentschieden', 'name' => 'U', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'niederlagen', 'name' => 'N', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'tore', 'name' => 'Tore', 'type' => 'text', 'columns' => 1 ),
					array( 'id' => 'diff', 'name' => 'Diff', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'punkte', 'name' => 'Pkt', 'type' => 'number', 'columns' => 1 ),
					array( 'id' => 'eigenes_team', 'name' => 'FCK', 'type' => 'checkbox', 'columns' => 1 ),
					array( 'id' => 'zone', 'name' => 'Zone', 'type' => 'select', 'columns' => 1, 'options' => array( '' => '–', 'aufstieg' => 'Aufstieg', 'relegation' => 'Relegation', 'abstieg' => 'Abstieg' ) ),
				),
			),
		),
	);

	/* ---------------------------------------------------------------- Person */

	$boxes[] = array(
		'id'         => 'person-daten',
		'title'      => 'Person',
		'post_types' => 'person',
		'fields'     => array(
			array( 'id' => 'vorname', 'name' => 'Vorname', 'type' => 'text', 'columns' => 6 ),
			array( 'id' => 'nachname', 'name' => 'Nachname', 'type' => 'text', 'columns' => 6 ),
			fck_switch( 'spieler', 'Spielt aktiv' ),
			fck_post_field( 'mannschaften', 'Spielt in', 'mannschaft', true, array( 'visible' => array( 'spieler', true ), 'desc' => 'Nur Spieler:innen. Trainer, Vorstand usw. stehen unter „Ämter“.' ) ),
			fck_switch( 'aktiv', 'Aktives Mitglied', array( 'std' => 1, 'desc' => 'Aus = ehemalig. Bleibt in Historie und alten Berichten verknüpft.' ) ),
			array( 'id' => 'user_id', 'name' => 'Login', 'type' => 'user', 'field_type' => 'select_advanced', 'desc' => 'Verknüpft die Person mit einem WordPress-Benutzer (für Trainer-Rechte).', 'hide_from_rest' => true ),
			array( 'type' => 'heading', 'name' => 'Kontakt' ),
			array( 'id' => 'telefon', 'name' => 'Telefon', 'type' => 'text', 'columns' => 6, 'hide_from_rest' => true ),
			array( 'id' => 'email', 'name' => 'E-Mail', 'type' => 'email', 'columns' => 6, 'hide_from_rest' => true ),
			fck_switch( 'kontakt_oeffentlich', 'Kontakt auf der Website zeigen', array( 'desc' => 'Standard aus. Dann erscheint die Funktions- oder Team-Adresse.', 'hide_from_rest' => true ) ),
			array( 'type' => 'heading', 'name' => 'Sichtbarkeit' ),
			fck_switch( 'profil_ausblenden', 'Profil ausblenden', array( 'desc' => 'Bei Widerruf: Die Person wird privat und erscheint nirgends mehr auf der Website.', 'hide_from_rest' => true ) ),
		),
	);
	$boxes[] = array(
		'id'         => 'person-spielerprofil',
		'title'      => 'Spielerprofil',
		'post_types' => 'person',
		'visible'    => array( 'spieler', true ),
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

	/* ---------------------------------------------------------------- Amt */

	$boxes[] = array(
		'id'         => 'amt-daten',
		'title'      => 'Amt',
		'post_types' => 'amt',
		'fields'     => array(
			fck_post_field( 'person_id', 'Person', 'person', false, array( 'required' => true ) ),
			array( 'id' => 'gremium', 'name' => 'Bereich', 'type' => 'select', 'required' => true, 'options' => FCK_GREMIEN ),
			array( 'id' => 'funktion', 'name' => 'Funktion', 'type' => 'text', 'placeholder' => '1. Vorsitz, Kassier, Cheftrainer, Co-Trainer …' ),
			fck_post_field( 'team_id', 'Mannschaft', 'mannschaft', false, array( 'visible' => array( 'gremium', 'in', array( 'trainer', 'betreuer', 'jugendleitung' ) ) ) ),
			fck_date( 'von', 'Seit', array( 'columns' => 6 ) ),
			fck_date( 'bis', 'Bis', array( 'columns' => 6, 'desc' => 'Leer = aktuell im Amt.' ) ),
			array( 'id' => 'funktions_email', 'name' => 'Funktions-E-Mail', 'type' => 'email', 'placeholder' => 'vorstand@…' ),
			fck_readonly( 'fck_aktiv', 'Aktuell im Amt', 'text' ),
		),
	);

	/* ---------------------------------------------------------------- Spielstätte */

	$boxes[] = array(
		'id'         => 'spielstaette-daten',
		'title'      => 'Spielstätte',
		'post_types' => 'spielstaette',
		'fields'     => array(
			array( 'id' => 'name_offiziell', 'name' => 'Offizieller Name', 'type' => 'text', 'placeholder' => 'HSS Sportpark Königsfeld' ),
			array( 'id' => 'name_kurz', 'name' => 'Kurzname', 'type' => 'text', 'placeholder' => 'Sportpark' ),
			fck_post_field( 'namensgeber_id', 'Namensgeber', 'partner' ),
			array( 'id' => 'adresse', 'name' => 'Adresse', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'lat', 'name' => 'Breitengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
			array( 'id' => 'lng', 'name' => 'Längengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
			array(
				'id'         => 'plaetze',
				'name'       => 'Plätze',
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'add_button' => 'Platz hinzufügen',
				'fields'     => array(
					array( 'id' => 'name', 'name' => 'Name', 'type' => 'text', 'columns' => 6 ),
					array( 'id' => 'belag', 'name' => 'Belag', 'type' => 'select', 'columns' => 6, 'options' => array( 'rasen' => 'Rasen', 'kunstrasen' => 'Kunstrasen', 'halle' => 'Halle', 'hartplatz' => 'Hartplatz' ) ),
				),
			),
			array( 'type' => 'heading', 'name' => 'Für Gäste' ),
			array( 'id' => 'parken', 'name' => 'Parken', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'parken_lat', 'name' => 'Parkplatz Breitengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
			array( 'id' => 'parken_lng', 'name' => 'Parkplatz Längengrad', 'type' => 'number', 'step' => 'any', 'columns' => 6 ),
			array( 'id' => 'oepnv', 'name' => 'Bus & Bahn', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'kabinen', 'name' => 'Kabinen', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'barrierefrei', 'name' => 'Barrierefreiheit', 'type' => 'textarea', 'rows' => 2 ),
			array( 'id' => 'telefon_spieltag', 'name' => 'Telefon am Spieltag', 'type' => 'text' ),
			array( 'type' => 'heading', 'name' => 'Vereinsheim' ),
			array(
				'id'         => 'oeffnungszeiten',
				'name'       => 'Öffnungszeiten',
				'type'       => 'group',
				'clone'      => true,
				'add_button' => 'Zeit hinzufügen',
				'fields'     => array(
					array( 'id' => 'tag', 'name' => 'Tag', 'type' => 'select', 'options' => FCK_WOCHENTAGE, 'columns' => 4 ),
					array( 'id' => 'von', 'name' => 'Von', 'type' => 'time', 'columns' => 4 ),
					array( 'id' => 'bis', 'name' => 'Bis', 'type' => 'time', 'columns' => 4 ),
				),
			),
			fck_post_field( 'vermietung_kontakt_id', 'Kontakt Vermietung', 'person' ),
		),
	);

	/* ---------------------------------------------------------------- Partner */

	$boxes[] = array(
		'id'         => 'partner-daten',
		'title'      => 'Partner',
		'post_types' => 'partner',
		'fields'     => array(
			array( 'id' => 'stufe', 'name' => 'Stufe', 'type' => 'select', 'required' => true, 'options' => FCK_STUFEN, 'desc' => 'Bestimmt die Reihenfolge auf der Partner-Wand.' ),
			array( 'id' => 'website', 'name' => 'Website', 'type' => 'url' ),
			array( 'id' => 'ort', 'name' => 'Ort', 'type' => 'text' ),
			array(
				'id'         => 'leistungen',
				'name'       => 'Leistungen',
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'add_button' => 'Leistung hinzufügen',
				'fields'     => array(
					array( 'id' => 'art', 'name' => 'Art', 'type' => 'select', 'options' => FCK_LEISTUNGEN, 'columns' => 4 ),
					fck_post_field( 'team_id', 'Mannschaft', 'mannschaft', false, array( 'columns' => 4 ) ),
					array( 'id' => 'text', 'name' => 'Text', 'type' => 'text', 'columns' => 4, 'placeholder' => 'Trikot SG B.K.N.' ),
				),
			),
			fck_date( 'laufzeit_von', 'Laufzeit von', array( 'columns' => 4 ) ),
			fck_date( 'laufzeit_bis', 'Laufzeit bis', array( 'columns' => 4, 'hidden' => array( 'unbefristet', true ) ) ),
			fck_switch( 'unbefristet', 'Unbefristet', array( 'columns' => 4 ) ),
			fck_readonly( 'fck_stufe_rang', 'Rang', 'number' ),
		),
	);
	$boxes[] = array(
		'id'         => 'partner-logos',
		'title'      => 'Logos',
		'post_types' => 'partner',
		'fields'     => array(
			array( 'id' => 'logo', 'name' => 'Logo (Original)', 'type' => 'single_image', 'desc' => 'Am besten SVG.' ),
			array( 'id' => 'logo_hell', 'name' => 'Logo hell (für dunklen Grund)', 'type' => 'single_image', 'desc' => 'Leer = Original wird weiß gefärbt.' ),
			array( 'id' => 'logo_dunkel', 'name' => 'Logo dunkel (für hellen Grund)', 'type' => 'single_image' ),
		),
	);
	$boxes[] = array(
		'id'         => 'partner-auftritt',
		'title'      => 'Auftritt (Hauptpartner)',
		'post_types' => 'partner',
		'visible'    => array( 'stufe', 'in', array( 'haupt', 'exklusiv' ) ),
		'fields'     => array(
			array( 'id' => 'claim', 'name' => 'Claim', 'type' => 'text' ),
			array( 'id' => 'beschreibung', 'name' => 'Beschreibung', 'type' => 'textarea', 'rows' => 3 ),
			array( 'id' => 'band_foto', 'name' => 'Foto für das Band', 'type' => 'single_image' ),
			array(
				'id'         => 'links',
				'name'       => 'Links',
				'type'       => 'group',
				'clone'      => true,
				'max_clone'  => 3,
				'add_button' => 'Link hinzufügen',
				'fields'     => array(
					array( 'id' => 'label', 'name' => 'Text', 'type' => 'text', 'columns' => 4, 'placeholder' => 'Jobs bei HSS' ),
					array( 'id' => 'url', 'name' => 'URL', 'type' => 'url', 'columns' => 8 ),
				),
			),
			array( 'id' => 'utm_kampagne', 'name' => 'UTM-Kampagne', 'type' => 'text', 'placeholder' => 'fck-website' ),
		),
	);
	$boxes[] = array(
		'id'         => 'partner-intern',
		'title'      => 'Intern',
		'post_types' => 'partner',
		'context'    => 'side',
		'fields'     => array(
			array( 'id' => 'kontakt_name', 'name' => 'Ansprechperson', 'type' => 'text', 'hide_from_rest' => true ),
			array( 'id' => 'kontakt_email', 'name' => 'E-Mail', 'type' => 'email', 'hide_from_rest' => true ),
		),
	);

	/* ---------------------------------------------------------------- Termin */

	$boxes[] = array(
		'id'         => 'termin-daten',
		'title'      => 'Termin',
		'post_types' => 'termin',
		'fields'     => array(
			fck_datetime( 'start', 'Beginn', array( 'required' => true, 'columns' => 6 ) ),
			fck_datetime( 'ende', 'Ende', array( 'columns' => 6 ) ),
			fck_switch( 'ganztaegig', 'Ganztägig' ),
			array( 'id' => 'status', 'name' => 'Status', 'type' => 'select', 'std' => 'geplant', 'options' => array( 'geplant' => 'Geplant', 'abgesagt' => 'Abgesagt', 'verschoben' => 'Verschoben' ) ),
			fck_post_field( 'spielstaette_id', 'Ort (Spielstätte)', 'spielstaette' ),
			array( 'id' => 'ort', 'name' => 'Ort (frei)', 'type' => 'text', 'desc' => 'Nur wenn der Ort keine Spielstätte ist.' ),
			array( 'id' => 'anmeldung_url', 'name' => 'Anmeldung', 'type' => 'url' ),
			fck_post_field( 'bericht_id', 'Nachbericht', 'post', false, array( 'query_args' => array( 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC' ) ) ),
			array( 'id' => 'galerie', 'name' => 'Galerie', 'type' => 'image_advanced' ),
		),
	);

	/* ---------------------------------------------------------------- Projekt */

	$boxes[] = array(
		'id'         => 'projekt-daten',
		'title'      => 'Projekt',
		'post_types' => 'projekt',
		'fields'     => array(
			array( 'id' => 'status', 'name' => 'Status', 'type' => 'select', 'std' => 'laeuft', 'options' => array( 'geplant' => 'Geplant', 'laeuft' => 'Läuft', 'abgeschlossen' => 'Abgeschlossen' ) ),
			array( 'id' => 'ziel_betrag', 'name' => 'Ziel (€)', 'type' => 'number', 'min' => 0, 'columns' => 6 ),
			array( 'id' => 'stand_betrag', 'name' => 'Stand (€)', 'type' => 'number', 'min' => 0, 'columns' => 6 ),
			fck_date( 'zeitraum_von', 'Von', array( 'columns' => 6 ) ),
			fck_date( 'zeitraum_bis', 'Bis', array( 'columns' => 6 ) ),
			fck_switch( 'spenden_aufruf', 'Spendenaufruf zeigen' ),
			array( 'id' => 'galerie', 'name' => 'Galerie', 'type' => 'image_advanced' ),
		),
	);

	/* ---------------------------------------------------------------- Frage, Download, Historie */

	$boxes[] = array(
		'id'         => 'frage-daten',
		'title'      => 'Frage',
		'post_types' => 'frage',
		'context'    => 'side',
		'fields'     => array(
			array( 'id' => 'bereich', 'name' => 'Bereich', 'type' => 'select', 'options' => FCK_BEREICHE ),
			fck_post_field( 'team_id', 'Nur für Mannschaft', 'mannschaft' ),
		),
	);
	$boxes[] = array(
		'id'         => 'download-daten',
		'title'      => 'Download',
		'post_types' => 'download',
		'fields'     => array(
			array( 'id' => 'datei', 'name' => 'Datei', 'type' => 'file_advanced', 'max_file_uploads' => 1, 'max_status' => false ),
			array( 'id' => 'bereich', 'name' => 'Bereich', 'type' => 'select', 'options' => FCK_BEREICHE ),
			fck_date( 'stand', 'Stand' ),
			fck_post_field( 'ersetzt_id', 'Ersetzt', 'download', false, array( 'desc' => 'Ältere Fassung, die nicht mehr angezeigt wird.' ) ),
			fck_readonly( 'fck_ersetzt_durch', 'Ersetzt durch (ID)' ),
		),
	);
	$boxes[] = array(
		'id'         => 'historie-daten',
		'title'      => 'Historie',
		'post_types' => 'historie',
		'fields'     => array(
			array( 'id' => 'jahr', 'name' => 'Jahr', 'type' => 'number', 'min' => 1900, 'max' => 2100, 'columns' => 4 ),
			fck_date( 'datum', 'Datum (optional)', array( 'columns' => 4 ) ),
			array( 'id' => 'art', 'name' => 'Art', 'type' => 'select', 'columns' => 4, 'options' => array( 'meilenstein' => 'Meilenstein', 'erfolg' => 'Erfolg', 'ehrung' => 'Ehrung', 'jubilaeum' => 'Jubiläum' ) ),
			fck_post_field( 'person_ids', 'Personen', 'person', true ),
			fck_post_field( 'team_id', 'Mannschaft', 'mannschaft' ),
			array( 'id' => 'galerie', 'name' => 'Galerie', 'type' => 'image_advanced' ),
		),
	);

	/* ---------------------------------------------------------------- Beitrag */

	$boxes[] = array(
		'id'         => 'beitrag-extra',
		'title'      => 'Beitrag',
		'post_types' => 'post',
		'context'    => 'side',
		'fields'     => array(
			fck_post_field( 'spiel_id', 'Spielbericht zu', 'spiel', true, array(
				'desc'       => 'Setzt die Mannschaft automatisch. Mehrere Spiele bei Turnieren.',
				'ajax'       => true,
				'query_args' => array( 'post_status' => 'any', 'posts_per_page' => 30, 'meta_key' => 'anstoss', 'orderby' => 'meta_value', 'order' => 'DESC' ),
			) ),
			array( 'id' => 'autor_name', 'name' => 'Autor:in (Anzeige)', 'type' => 'text', 'desc' => 'Überschreibt den Benutzer, z. B. „Text: Trainerteam E2“.' ),
			fck_datetime( 'angepinnt_bis', 'Angepinnt bis', array( 'desc' => 'Danach automatisch nicht mehr oben.' ) ),
			array( 'id' => 'instagram_url', 'name' => 'Instagram-Beitrag', 'type' => 'url' ),
		),
	);
	$boxes[] = array(
		'id'         => 'beitrag-galerie',
		'title'      => 'Galerie',
		'post_types' => 'post',
		'fields'     => array(
			array( 'id' => 'galerie', 'name' => 'Bilder', 'type' => 'image_advanced' ),
		),
	);

	/* ---------------------------------------------------------------- Medien */

	$boxes[] = array(
		'id'         => 'medium-extra',
		'title'      => 'Bildnachweis',
		'post_types' => 'attachment',
		'media_modal'=> true,
		'fields'     => array(
			array( 'id' => 'fotograf', 'name' => 'Foto', 'type' => 'text' ),
		),
	);

	return $boxes;
} );
