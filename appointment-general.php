<?php
/**
 * Plugin Name: Appointment General
 * Plugin URI: https://example.com/appointment-general
 * Description: Berberler, güzellik salonları ve servis sağlayıcılar için profesyonel randevu sistemi.
 * Version: 1.0.0
 * Author: Developer
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: appointment-general
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 */

// Doğrudan erişimi engelle
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin sabitleri
define( 'AG_VERSION', '1.0.0' );
define( 'AG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Aktivasyon hook'u
 */
function ag_activate() {
	require_once AG_PLUGIN_DIR . 'includes/class-ag-activator.php';
	AG_Activator::activate();
}
register_activation_hook( __FILE__, 'ag_activate' );

/**
 * Deaktivasyon hook'u
 */
function ag_deactivate() {
	require_once AG_PLUGIN_DIR . 'includes/class-ag-deactivator.php';
	AG_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'ag_deactivate' );

/**
 * Core plugin sınıfını yükle
 */
require_once AG_PLUGIN_DIR . 'includes/class-ag-core.php';

/**
 * Plugin'i başlat
 */
function ag_run() {
	$plugin = new AG_Core();
	$plugin->run();
}
ag_run();
