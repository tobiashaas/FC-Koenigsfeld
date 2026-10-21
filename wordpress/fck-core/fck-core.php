<?php
/**
 * Plugin Name: FCK Core
 * Description: Felder, Optionen und Logik für den FC Königsfeld 1954 e.V.
 * Version:     0.2.0
 * Author:      Tobias Haas
 * Requires PHP: 8.1
 * Requires Plugins: meta-box-aio
 * Text Domain: fck-core
 */

defined( 'ABSPATH' ) || exit;

define( 'FCK_CORE_VERSION', '0.2.0' );
define( 'FCK_CORE_DIR', __DIR__ );

require_once FCK_CORE_DIR . '/includes/content-types.php';
require_once FCK_CORE_DIR . '/includes/fields.php';
require_once FCK_CORE_DIR . '/includes/team-terms.php';

