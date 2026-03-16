<?php
/**
 * Internationalization class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * I18n class
 */
class UNBSB_I18n {

	/**
	 * Load text domain
	 *
	 * Note: Since WordPress 4.6, plugins hosted on WordPress.org
	 * don't need to call load_plugin_textdomain().
	 * Translations are loaded automatically.
	 */
	public function load_plugin_textdomain() {
		// WordPress.org hosted plugins don't need this since WP 4.6
		// Translations are loaded automatically.
	}
}
