<?php
/**
 * Plugin Name: Unbelievable Salon Booking
 * Description: Beautiful and easy to use salon booking plugin for WordPress
 * Version: 1.8.0
 * Author: zgrkaralar
 * Author URI: https://unbelievable.digital
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: unbelievable-salon-booking
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 *
 * @package Unbelievable_Salon_Booking
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'UNBSB_VERSION', '1.8.0' );
define( 'UNBSB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UNBSB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UNBSB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Activation hook
 */
function unbsb_activate() {
	require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-activator.php';
	UNBSB_Activator::activate();
}
register_activation_hook( __FILE__, 'unbsb_activate' );

/**
 * Deactivation hook
 */
function unbsb_deactivate() {
	require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-deactivator.php';
	UNBSB_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'unbsb_deactivate' );

/**
 * Self-hosted update checker.
 */
require_once UNBSB_PLUGIN_DIR . 'wp-update-sdk/class-wp-update-checker.php';
new WP_Update_Checker(
	'https://wp-base.unbelievable.digital/',
	__FILE__,
	'unbelievable-salon-booking'
);

/**
 * Load core plugin class.
 */
require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-core.php';

/**
 * Run the plugin
 */
function unbsb_run() {
	$plugin = new UNBSB_Core();
	$plugin->run();
}
unbsb_run();
