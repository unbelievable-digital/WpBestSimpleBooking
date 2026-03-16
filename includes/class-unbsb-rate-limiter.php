<?php
/**
 * Rate Limiter - IP-based request limiting
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rate Limiter class
 */
class UNBSB_Rate_Limiter {

	/**
	 * Transient prefix
	 *
	 * @var string
	 */
	private static $prefix = 'unbsb_rl_';

	/**
	 * Default limits (requests per minute)
	 *
	 * @var array
	 */
	private static $limits = array(
		'booking_token'   => 10,  // Token endpoint - 10 req/min.
		'booking_create'  => 5,   // Booking creation - 5 req/min.
		'booking_cancel'  => 3,   // Cancel - 3 req/min.
		'slots'           => 30,  // Slots - 30 req/min.
		'general'         => 60,  // General - 60 req/min.
	);

	/**
	 * Check rate limit
	 *
	 * @param string $action Action type.
	 *
	 * @return bool|WP_Error True if allowed, WP_Error if rate limited.
	 */
	public static function check( $action = 'general' ) {
		$ip    = self::get_client_ip();
		$key   = self::$prefix . $action . '_' . md5( $ip );
		$limit = isset( self::$limits[ $action ] ) ? self::$limits[ $action ] : self::$limits['general'];

		// Get current count.
		$data = get_transient( $key );

		if ( false === $data ) {
			// First request, set count to 1.
			set_transient( $key, array( 'count' => 1, 'start' => time() ), MINUTE_IN_SECONDS );
			return true;
		}

		// Check if within time window.
		if ( $data['count'] >= $limit ) {
			// Log rate limit event.
			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_rate_limit( $action );
			}

			// Rate limited.
			$retry_after = MINUTE_IN_SECONDS - ( time() - $data['start'] );

			return new WP_Error(
				'rate_limit_exceeded',
				sprintf(
					/* translators: %d: seconds to wait */
					__( 'Too many requests. Please try again in %d seconds.', 'unbelievable-salon-booking' ),
					max( 1, $retry_after )
				),
				array(
					'status'      => 429,
					'retry_after' => max( 1, $retry_after ),
				)
			);
		}

		// Increment count.
		$data['count']++;
		set_transient( $key, $data, MINUTE_IN_SECONDS - ( time() - $data['start'] ) );

		return true;
	}

	/**
	 * REST API permission callback with rate limiting
	 *
	 * @param string $action Action type.
	 *
	 * @return bool|WP_Error
	 */
	public static function permission_callback( $action = 'general' ) {
		$check = self::check( $action );

		if ( is_wp_error( $check ) ) {
			return $check;
		}

		return true;
	}

	/**
	 * Get permission callback for booking token endpoints
	 *
	 * @return bool|WP_Error
	 */
	public static function token_permission() {
		return self::permission_callback( 'booking_token' );
	}

	/**
	 * Get permission callback for booking create endpoint
	 *
	 * @return bool|WP_Error
	 */
	public static function create_permission() {
		return self::permission_callback( 'booking_create' );
	}

	/**
	 * Get permission callback for cancel endpoint
	 *
	 * @return bool|WP_Error
	 */
	public static function cancel_permission() {
		return self::permission_callback( 'booking_cancel' );
	}

	/**
	 * Get permission callback for slots endpoint
	 *
	 * @return bool|WP_Error
	 */
	public static function slots_permission() {
		return self::permission_callback( 'slots' );
	}

	/**
	 * Get client IP address
	 *
	 * @return string
	 */
	private static function get_client_ip() {
		$ip = '';

		// Check various headers.
		$headers = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				// Handle comma-separated IPs (X-Forwarded-For).
				if ( strpos( $ip, ',' ) !== false ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				break;
			}
		}

		// Validate IP.
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}

		return '0.0.0.0';
	}

	/**
	 * Clear rate limit for an IP
	 *
	 * @param string $action Action type.
	 * @param string $ip     IP address (optional, defaults to current).
	 */
	public static function clear( $action, $ip = '' ) {
		if ( empty( $ip ) ) {
			$ip = self::get_client_ip();
		}
		$key = self::$prefix . $action . '_' . md5( $ip );
		delete_transient( $key );
	}

	/**
	 * Get remaining requests for current IP
	 *
	 * @param string $action Action type.
	 *
	 * @return int
	 */
	public static function get_remaining( $action = 'general' ) {
		$ip    = self::get_client_ip();
		$key   = self::$prefix . $action . '_' . md5( $ip );
		$limit = isset( self::$limits[ $action ] ) ? self::$limits[ $action ] : self::$limits['general'];
		$data  = get_transient( $key );

		if ( false === $data ) {
			return $limit;
		}

		return max( 0, $limit - $data['count'] );
	}
}
