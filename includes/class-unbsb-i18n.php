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
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'unbelievable-salon-booking',
			false,
			dirname( UNBSB_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
