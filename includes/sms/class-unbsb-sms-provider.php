<?php
/**
 * Abstract SMS Provider class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract SMS Provider
 */
abstract class UNBSB_SMS_Provider {

	/**
	 * Provider name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * API URL
	 *
	 * @var string
	 */
	protected $api_url = '';

	/**
	 * Send SMS
	 *
	 * @param string $phone   Phone number.
	 * @param string $message Message.
	 *
	 * @return array Success status and message.
	 */
	abstract public function send( $phone, $message );

	/**
	 * Query balance
	 *
	 * @return array Balance information or error.
	 */
	abstract public function get_balance();

	/**
	 * Validate credentials
	 *
	 * @return bool
	 */
	abstract public function validate_credentials();

	/**
	 * Get provider name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Format phone number
	 *
	 * @param string $phone Phone number.
	 *
	 * @return string Formatted number.
	 */
	protected function format_phone( $phone ) {
		// Get digits only.
		$phone = preg_replace( '/[^0-9]/', '', $phone );

		// Add +90 for Turkey.
		if ( strlen( $phone ) === 10 && substr( $phone, 0, 1 ) === '5' ) {
			$phone = '90' . $phone;
		} elseif ( strlen( $phone ) === 11 && substr( $phone, 0, 1 ) === '0' ) {
			$phone = '9' . $phone;
		}

		return $phone;
	}

	/**
	 * Sanitize Turkish characters from message (for SMS compatibility)
	 *
	 * @param string $message Message.
	 *
	 * @return string Sanitized message.
	 */
	protected function sanitize_message( $message ) {
		$turkish = array( 'ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç' );
		$latin   = array( 'i', 'g', 'u', 's', 'o', 'c', 'I', 'G', 'U', 'S', 'O', 'C' );

		return str_replace( $turkish, $latin, $message );
	}

	/**
	 * Log error
	 *
	 * @param string $message Error message.
	 * @param mixed  $data    Additional data.
	 */
	protected function log_error( $message, $data = null ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled.
			error_log( sprintf( '[UNBSB SMS - %s] %s: %s', $this->name, $message, wp_json_encode( $data ) ) );
		}
	}

	/**
	 * Create success response
	 *
	 * @param string $message_id Message ID.
	 * @param string $message    Message.
	 *
	 * @return array
	 */
	protected function success_response( $message_id = '', $message = '' ) {
		return array(
			'success'    => true,
			'message_id' => $message_id,
			'message'    => $message,
		);
	}

	/**
	 * Create error response
	 *
	 * @param string $error_code Error code.
	 * @param string $message    Error message.
	 *
	 * @return array
	 */
	protected function error_response( $error_code = '', $message = '' ) {
		return array(
			'success'    => false,
			'error_code' => $error_code,
			'message'    => $message,
		);
	}
}
