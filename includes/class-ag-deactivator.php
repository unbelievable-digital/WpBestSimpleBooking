<?php
/**
 * Plugin deaktivasyon sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator sınıfı
 */
class AG_Deactivator {

	/**
	 * Deaktivasyon işlemleri
	 */
	public static function deactivate() {
		// Scheduled events'leri temizle
		wp_clear_scheduled_hook( 'ag_send_reminder_emails' );
		wp_clear_scheduled_hook( 'ag_cleanup_expired_bookings' );

		// Rewrite rules flush
		flush_rewrite_rules();
	}
}
