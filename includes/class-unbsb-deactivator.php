<?php
/**
 * Plugin deactivation class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator class
 */
class UNBSB_Deactivator {

	/**
	 * Deactivation operations
	 */
	public static function deactivate() {
		// Clear scheduled events.
		wp_clear_scheduled_hook( 'unbsb_send_reminder_emails' );
		wp_clear_scheduled_hook( 'unbsb_cleanup_expired_bookings' );

		// Rewrite rules flush.
		flush_rewrite_rules();
	}
}
