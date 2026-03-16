<?php
/**
 * Security Logger - Log security events
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Logger class
 */
class UNBSB_Security_Logger {

	/**
	 * Log table
	 *
	 * @var string
	 */
	private static $table_name;

	/**
	 * Log levels
	 */
	const LEVEL_INFO    = 'info';
	const LEVEL_WARNING = 'warning';
	const LEVEL_ERROR   = 'error';
	const LEVEL_CRITICAL = 'critical';

	/**
	 * Event types
	 */
	const EVENT_RATE_LIMIT     = 'rate_limit';
	const EVENT_INVALID_TOKEN  = 'invalid_token';
	const EVENT_SPAM_DETECTED  = 'spam_detected';
	const EVENT_INVALID_INPUT  = 'invalid_input';
	const EVENT_AUTH_FAILURE   = 'auth_failure';
	const EVENT_SUSPICIOUS     = 'suspicious';
	const EVENT_BOOKING_CREATE = 'booking_create';
	const EVENT_BOOKING_CANCEL = 'booking_cancel';

	/**
	 * Initialize
	 */
	public static function init() {
		global $wpdb;
		self::$table_name = $wpdb->prefix . 'unbsb_security_logs';
	}

	/**
	 * Log a security event
	 *
	 * @param string $event_type Event type constant.
	 * @param string $message    Log message.
	 * @param array  $context    Additional context data.
	 * @param string $level      Log level.
	 *
	 * @return bool|int
	 */
	public static function log( $event_type, $message, $context = array(), $level = self::LEVEL_INFO ) {
		global $wpdb;

		self::init();

		// Check if logging is enabled.
		if ( 'yes' !== get_option( 'unbsb_security_logging_enabled', 'yes' ) ) {
			return false;
		}

		$ip         = self::get_client_ip();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$user_id    = get_current_user_id();
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		$data = array(
			'event_type'  => sanitize_key( $event_type ),
			'level'       => sanitize_key( $level ),
			'message'     => sanitize_text_field( $message ),
			'ip_address'  => sanitize_text_field( $ip ),
			'user_agent'  => mb_substr( $user_agent, 0, 500 ),
			'user_id'     => $user_id,
			'request_uri' => mb_substr( $request_uri, 0, 500 ),
			'context'     => wp_json_encode( $context ),
			'created_at'  => current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			self::$table_name,
			$data,
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		// Auto-cleanup old logs (keep 30 days).
		self::maybe_cleanup();

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Log rate limit event
	 *
	 * @param string $action Action that was rate limited.
	 */
	public static function log_rate_limit( $action ) {
		self::log(
			self::EVENT_RATE_LIMIT,
			sprintf( 'Rate limit exceeded for action: %s', $action ),
			array( 'action' => $action ),
			self::LEVEL_WARNING
		);
	}

	/**
	 * Log invalid token attempt
	 *
	 * @param string $token Attempted token (masked).
	 */
	public static function log_invalid_token( $token ) {
		$masked = mb_substr( $token, 0, 4 ) . '****' . mb_substr( $token, -4 );
		self::log(
			self::EVENT_INVALID_TOKEN,
			'Invalid booking token attempted',
			array( 'token_masked' => $masked ),
			self::LEVEL_WARNING
		);
	}

	/**
	 * Log spam detection
	 *
	 * @param string $reason Spam detection reason.
	 */
	public static function log_spam( $reason ) {
		self::log(
			self::EVENT_SPAM_DETECTED,
			'Spam detected: ' . $reason,
			array( 'reason' => $reason ),
			self::LEVEL_WARNING
		);
	}

	/**
	 * Log invalid input
	 *
	 * @param string $field  Field name.
	 * @param string $reason Reason.
	 */
	public static function log_invalid_input( $field, $reason ) {
		self::log(
			self::EVENT_INVALID_INPUT,
			sprintf( 'Invalid input for field %s: %s', $field, $reason ),
			array(
				'field'  => $field,
				'reason' => $reason,
			),
			self::LEVEL_INFO
		);
	}

	/**
	 * Log suspicious activity
	 *
	 * @param string $description Description.
	 * @param array  $context     Context.
	 */
	public static function log_suspicious( $description, $context = array() ) {
		self::log(
			self::EVENT_SUSPICIOUS,
			$description,
			$context,
			self::LEVEL_ERROR
		);
	}

	/**
	 * Log successful booking creation
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $data       Booking data (sanitized).
	 */
	public static function log_booking_created( $booking_id, $data = array() ) {
		self::log(
			self::EVENT_BOOKING_CREATE,
			sprintf( 'Booking #%d created', $booking_id ),
			array(
				'booking_id' => $booking_id,
				'email'      => isset( $data['customer_email'] ) ? md5( $data['customer_email'] ) : '',
			),
			self::LEVEL_INFO
		);
	}

	/**
	 * Log booking cancellation
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $reason     Cancellation reason.
	 */
	public static function log_booking_cancelled( $booking_id, $reason = '' ) {
		self::log(
			self::EVENT_BOOKING_CANCEL,
			sprintf( 'Booking #%d cancelled', $booking_id ),
			array(
				'booking_id' => $booking_id,
				'reason'     => $reason,
			),
			self::LEVEL_INFO
		);
	}

	/**
	 * Get logs
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public static function get_logs( $args = array() ) {
		global $wpdb;

		self::init();

		$defaults = array(
			'limit'      => 50,
			'offset'     => 0,
			'event_type' => '',
			'level'      => '',
			'ip_address' => '',
			'date_from'  => '',
			'date_to'    => '',
			'order'      => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $args['event_type'] ) ) {
			$where[]  = 'event_type = %s';
			$values[] = $args['event_type'];
		}

		if ( ! empty( $args['level'] ) ) {
			$where[]  = 'level = %s';
			$values[] = $args['level'];
		}

		if ( ! empty( $args['ip_address'] ) ) {
			$where[]  = 'ip_address = %s';
			$values[] = $args['ip_address'];
		}

		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = $args['date_from'] . ' 00:00:00';
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = $args['date_to'] . ' 23:59:59';
		}

		$order     = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';
		$where_sql = implode( ' AND ', $where );

		$values[] = absint( $args['limit'] );
		$values[] = absint( $args['offset'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM " . self::$table_name . " WHERE {$where_sql} ORDER BY created_at {$order} LIMIT %d OFFSET %d",
				$values
			)
		);

		return $results ? $results : array();
	}

	/**
	 * Get log count
	 *
	 * @param array $args Query arguments.
	 *
	 * @return int
	 */
	public static function get_count( $args = array() ) {
		global $wpdb;

		self::init();

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $args['event_type'] ) ) {
			$where[]  = 'event_type = %s';
			$values[] = $args['event_type'];
		}

		if ( ! empty( $args['level'] ) ) {
			$where[]  = 'level = %s';
			$values[] = $args['level'];
		}

		$where_sql = implode( ' AND ', $where );

		if ( empty( $values ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::$table_name . " WHERE {$where_sql}" );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM " . self::$table_name . " WHERE {$where_sql}",
				$values
			)
		);
	}

	/**
	 * Get statistics
	 *
	 * @param int $days Number of days to look back.
	 *
	 * @return array
	 */
	public static function get_stats( $days = 7 ) {
		global $wpdb;

		self::init();

		$date_from = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$stats = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_type, level, COUNT(*) as count
				FROM " . self::$table_name . "
				WHERE created_at >= %s
				GROUP BY event_type, level",
				$date_from . ' 00:00:00'
			)
		);

		$result = array(
			'total'       => 0,
			'by_type'     => array(),
			'by_level'    => array(),
			'rate_limits' => 0,
			'warnings'    => 0,
			'errors'      => 0,
		);

		foreach ( $stats as $stat ) {
			$result['total'] += $stat->count;

			if ( ! isset( $result['by_type'][ $stat->event_type ] ) ) {
				$result['by_type'][ $stat->event_type ] = 0;
			}
			$result['by_type'][ $stat->event_type ] += $stat->count;

			if ( ! isset( $result['by_level'][ $stat->level ] ) ) {
				$result['by_level'][ $stat->level ] = 0;
			}
			$result['by_level'][ $stat->level ] += $stat->count;

			if ( self::EVENT_RATE_LIMIT === $stat->event_type ) {
				$result['rate_limits'] += $stat->count;
			}

			if ( self::LEVEL_WARNING === $stat->level ) {
				$result['warnings'] += $stat->count;
			}

			if ( self::LEVEL_ERROR === $stat->level || self::LEVEL_CRITICAL === $stat->level ) {
				$result['errors'] += $stat->count;
			}
		}

		return $result;
	}

	/**
	 * Delete old logs
	 *
	 * @param int $days Keep logs for this many days.
	 *
	 * @return int Number of deleted rows.
	 */
	public static function cleanup( $days = 30 ) {
		global $wpdb;

		self::init();

		$date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . self::$table_name . " WHERE created_at < %s",
				$date
			)
		);

		return $deleted ? $deleted : 0;
	}

	/**
	 * Maybe run cleanup (once per day)
	 */
	private static function maybe_cleanup() {
		$last_cleanup = get_option( 'unbsb_security_log_last_cleanup', 0 );

		// Run cleanup once per day.
		if ( time() - $last_cleanup > DAY_IN_SECONDS ) {
			self::cleanup();
			update_option( 'unbsb_security_log_last_cleanup', time() );
		}
	}

	/**
	 * Create table
	 */
	public static function create_table() {
		global $wpdb;

		self::init();

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_type VARCHAR(50) NOT NULL,
			level VARCHAR(20) NOT NULL DEFAULT 'info',
			message VARCHAR(500) NOT NULL,
			ip_address VARCHAR(45) NOT NULL,
			user_agent VARCHAR(500) DEFAULT '',
			user_id BIGINT(20) UNSIGNED DEFAULT 0,
			request_uri VARCHAR(500) DEFAULT '',
			context LONGTEXT,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY event_type (event_type),
			KEY level (level),
			KEY ip_address (ip_address),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Get client IP address
	 *
	 * @return string
	 */
	private static function get_client_ip() {
		$headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				if ( strpos( $ip, ',' ) !== false ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Get event type label
	 *
	 * @param string $type Event type.
	 *
	 * @return string
	 */
	public static function get_event_label( $type ) {
		$labels = array(
			self::EVENT_RATE_LIMIT     => __( 'Rate Limit', 'unbelievable-salon-booking' ),
			self::EVENT_INVALID_TOKEN  => __( 'Invalid Token', 'unbelievable-salon-booking' ),
			self::EVENT_SPAM_DETECTED  => __( 'Spam Detected', 'unbelievable-salon-booking' ),
			self::EVENT_INVALID_INPUT  => __( 'Invalid Input', 'unbelievable-salon-booking' ),
			self::EVENT_AUTH_FAILURE   => __( 'Auth Failure', 'unbelievable-salon-booking' ),
			self::EVENT_SUSPICIOUS     => __( 'Suspicious', 'unbelievable-salon-booking' ),
			self::EVENT_BOOKING_CREATE => __( 'Booking Created', 'unbelievable-salon-booking' ),
			self::EVENT_BOOKING_CANCEL => __( 'Booking Cancelled', 'unbelievable-salon-booking' ),
		);

		return isset( $labels[ $type ] ) ? $labels[ $type ] : $type;
	}

	/**
	 * Get level label
	 *
	 * @param string $level Level.
	 *
	 * @return string
	 */
	public static function get_level_label( $level ) {
		$labels = array(
			self::LEVEL_INFO     => __( 'Info', 'unbelievable-salon-booking' ),
			self::LEVEL_WARNING  => __( 'Warning', 'unbelievable-salon-booking' ),
			self::LEVEL_ERROR    => __( 'Error', 'unbelievable-salon-booking' ),
			self::LEVEL_CRITICAL => __( 'Critical', 'unbelievable-salon-booking' ),
		);

		return isset( $labels[ $level ] ) ? $labels[ $level ] : $level;
	}
}
