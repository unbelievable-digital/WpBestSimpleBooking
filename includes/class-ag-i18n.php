<?php
/**
 * Internationalization sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * i18n sınıfı
 */
class AG_i18n {

	/**
	 * Text domain yükle
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'appointment-general',
			false,
			dirname( AG_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
