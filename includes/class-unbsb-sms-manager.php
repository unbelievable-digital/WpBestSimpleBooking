<?php
/**
 * SMS Manager class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMS Manager
 */
class UNBSB_SMS_Manager {

	/**
	 * SMS Provider instance
	 *
	 * @var UNBSB_SMS_Provider
	 */
	private $provider;

	/**
	 * SMS Queue table name
	 *
	 * @var string
	 */
	private $queue_table;

	/**
	 * SMS Templates table name
	 *
	 * @var string
	 */
	private $templates_table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->queue_table     = $wpdb->prefix . 'unbsb_sms_queue';
		$this->templates_table = $wpdb->prefix . 'unbsb_sms_templates';

		$this->init_provider();
		$this->init_hooks();
	}

	/**
	 * Initialize SMS provider
	 */
	private function init_provider() {
		$provider = get_option( 'unbsb_sms_provider', 'netgsm' );

		// Load provider files.
		require_once UNBSB_PLUGIN_DIR . 'includes/sms/class-unbsb-sms-provider.php';

		switch ( $provider ) {
			case 'netgsm':
			default:
				require_once UNBSB_PLUGIN_DIR . 'includes/sms/class-unbsb-sms-netgsm.php';
				$this->provider = new UNBSB_SMS_NetGSM();
				break;
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Booking events.
		add_action( 'unbsb_after_booking_created', array( $this, 'on_booking_created' ), 20, 2 );
		add_action( 'unbsb_booking_status_changed', array( $this, 'on_booking_status_changed' ), 20, 3 );

		// Cron operations.
		add_action( 'unbsb_process_sms_queue', array( $this, 'process_queue' ) );
		add_action( 'unbsb_schedule_reminders', array( $this, 'schedule_reminders' ) );

		// Cron schedule.
		if ( ! wp_next_scheduled( 'unbsb_process_sms_queue' ) ) {
			wp_schedule_event( time(), 'every_minute', 'unbsb_process_sms_queue' );
		}

		if ( ! wp_next_scheduled( 'unbsb_schedule_reminders' ) ) {
			wp_schedule_event( time(), 'hourly', 'unbsb_schedule_reminders' );
		}

		// Custom cron interval.
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
	}

	/**
	 * Add custom cron interval
	 *
	 * @param array $schedules Existing schedules.
	 *
	 * @return array
	 */
	public function add_cron_interval( $schedules ) {
		$schedules['every_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Every Minute', 'unbelievable-salon-booking' ),
		);
		return $schedules;
	}

	/**
	 * Check if SMS is enabled
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return 'yes' === get_option( 'unbsb_sms_enabled', 'no' );
	}

	/**
	 * When booking is created
	 *
	 * @param int   $booking_id   Booking ID.
	 * @param array $booking_data Booking data.
	 */
	public function on_booking_created( $booking_id, $booking_data ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( 'yes' !== get_option( 'unbsb_sms_on_booking', 'yes' ) ) {
			return;
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking || empty( $booking->customer_phone ) ) {
			return;
		}

		$this->queue_sms( $booking_id, $booking->customer_phone, 'booking_created' );

		// Schedule reminder.
		$this->schedule_reminder_for_booking( $booking_id );
	}

	/**
	 * When booking status changes
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function on_booking_status_changed( $booking_id, $new_status, $old_status ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking || empty( $booking->customer_phone ) ) {
			return;
		}

		switch ( $new_status ) {
			case 'confirmed':
				if ( 'yes' === get_option( 'unbsb_sms_on_confirmation', 'yes' ) ) {
					$this->queue_sms( $booking_id, $booking->customer_phone, 'booking_confirmed' );
				}
				break;

			case 'cancelled':
				if ( 'yes' === get_option( 'unbsb_sms_on_cancellation', 'no' ) ) {
					$this->queue_sms( $booking_id, $booking->customer_phone, 'booking_cancelled' );
					// Delete reminder for cancelled booking.
					$this->cancel_reminder( $booking_id );
				}
				break;
		}
	}

	/**
	 * Add SMS to queue
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $phone      Phone number.
	 * @param string $type       Template type.
	 * @param string $scheduled  Scheduled send time (Y-m-d H:i:s).
	 *
	 * @return int|false Queue ID or false.
	 */
	public function queue_sms( $booking_id, $phone, $type, $scheduled = null ) {
		global $wpdb;

		$message = $this->render_template( $type, $booking_id );

		if ( empty( $message ) ) {
			return false;
		}

		$scheduled_at = $scheduled ? $scheduled : current_time( 'mysql' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$this->queue_table,
			array(
				'booking_id'   => $booking_id,
				'phone'        => $phone,
				'message'      => $message,
				'scheduled_at' => $scheduled_at,
				'status'       => 'pending',
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Process queue
	 */
	public function process_queue() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		global $wpdb;

		$now = current_time( 'mysql' );

		// Get SMS messages to send (max 10).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->queue_table}
				WHERE status = 'pending'
				AND scheduled_at <= %s
				AND attempts < 3
				ORDER BY scheduled_at ASC
				LIMIT 10",
				$now
			)
		);

		foreach ( $messages as $sms ) {
			$this->send_queued_sms( $sms );
		}
	}

	/**
	 * Send queued SMS
	 *
	 * @param object $sms SMS record.
	 */
	private function send_queued_sms( $sms ) {
		global $wpdb;

		// Increment attempt count.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$this->queue_table,
			array( 'attempts' => $sms->attempts + 1 ),
			array( 'id' => $sms->id ),
			array( '%d' ),
			array( '%d' )
		);

		// Send SMS.
		$result = $this->provider->send( $sms->phone, $sms->message );

		if ( $result['success'] ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$this->queue_table,
				array(
					'status'            => 'sent',
					'sent_at'           => current_time( 'mysql' ),
					'provider_response' => wp_json_encode( $result ),
				),
				array( 'id' => $sms->id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			$new_status = ( $sms->attempts + 1 ) >= 3 ? 'failed' : 'pending';

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$this->queue_table,
				array(
					'status'            => $new_status,
					'provider_response' => wp_json_encode( $result ),
				),
				array( 'id' => $sms->id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Schedule reminder
	 *
	 * @param int $booking_id Booking ID.
	 */
	public function schedule_reminder_for_booking( $booking_id ) {
		if ( 'yes' !== get_option( 'unbsb_sms_reminder_enabled', 'yes' ) ) {
			return;
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking || empty( $booking->customer_phone ) ) {
			return;
		}

		$hours = intval( get_option( 'unbsb_sms_reminder_hours', 24 ) );

		// Calculate booking time.
		$booking_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$booking_time     = strtotime( $booking_datetime );
		$reminder_time    = $booking_time - ( $hours * 3600 );

		// Schedule if not a past time.
		if ( $reminder_time > time() ) {
			$scheduled = gmdate( 'Y-m-d H:i:s', $reminder_time );
			$this->queue_sms( $booking_id, $booking->customer_phone, 'reminder', $scheduled );
		}
	}

	/**
	 * Schedule reminders for all tomorrow's bookings
	 */
	public function schedule_reminders() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( 'yes' !== get_option( 'unbsb_sms_reminder_enabled', 'yes' ) ) {
			return;
		}

		global $wpdb;

		$hours         = intval( get_option( 'unbsb_sms_reminder_hours', 24 ) );
		$reminder_date = gmdate( 'Y-m-d', strtotime( "+{$hours} hours" ) );

		$prefix = $wpdb->prefix . 'unbsb_';

		// Find bookings without reminders.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$bookings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.* FROM {$prefix}bookings b
				WHERE b.booking_date = %s
				AND b.status IN ('pending', 'confirmed')
				AND b.customer_phone IS NOT NULL
				AND b.customer_phone != ''
				AND NOT EXISTS (
					SELECT 1 FROM {$this->queue_table} q
					WHERE q.booking_id = b.id
					AND q.message LIKE %s
				)",
				$reminder_date,
				'%Reminder%'
			)
		);

		foreach ( $bookings as $booking ) {
			$this->schedule_reminder_for_booking( $booking->id );
		}
	}

	/**
	 * Cancel reminder
	 *
	 * @param int $booking_id Booking ID.
	 */
	public function cancel_reminder( $booking_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$this->queue_table,
			array( 'status' => 'cancelled' ),
			array(
				'booking_id' => $booking_id,
				'status'     => 'pending',
			),
			array( '%s' ),
			array( '%d', '%s' )
		);
	}

	/**
	 * Render template
	 *
	 * @param string $type       Template type.
	 * @param int    $booking_id Booking ID.
	 *
	 * @return string Rendered message.
	 */
	public function render_template( $type, $booking_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$template = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->templates_table} WHERE type = %s AND is_active = 1",
				$type
			)
		);

		if ( ! $template ) {
			return '';
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking ) {
			return '';
		}

		$placeholders = array(
			'{customer_name}'  => $booking->customer_name,
			'{customer_phone}' => $booking->customer_phone,
			'{service_name}'   => ! empty( $booking->services_list ) ? $booking->services_list : $booking->service_name,
			'{staff_name}'     => $booking->staff_name,
			'{booking_date}'   => date_i18n( get_option( 'unbsb_date_format', 'd.m.Y' ), strtotime( $booking->booking_date ) ),
			'{booking_time}'   => date_i18n( get_option( 'unbsb_time_format', 'H:i' ), strtotime( $booking->start_time ) ),
			'{price}'          => $booking->price . ' ' . get_option( 'unbsb_currency_symbol', '₺' ),
			'{company_name}'   => get_option( 'unbsb_company_name', get_bloginfo( 'name' ) ),
			'{company_phone}'  => get_option( 'unbsb_company_phone', '' ),
		);

		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $template->message );
	}

	/**
	 * Get booking details
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return object|null
	 */
	private function get_booking_details( $booking_id ) {
		$booking_model = new UNBSB_Booking();
		return $booking_model->get_with_details( $booking_id );
	}

	/**
	 * Get SMS templates
	 *
	 * @return array
	 */
	public function get_templates() {
		global $wpdb;

		$safe_table = esc_sql( $this->templates_table );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM `{$safe_table}` ORDER BY id ASC" );
	}

	/**
	 * Update template
	 *
	 * @param int    $id      Template ID.
	 * @param string $message New message.
	 * @param bool   $active  Whether active.
	 *
	 * @return bool
	 */
	public function update_template( $id, $message, $active = true ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (bool) $wpdb->update(
			$this->templates_table,
			array(
				'message'   => sanitize_textarea_field( $message ),
				'is_active' => $active ? 1 : 0,
			),
			array( 'id' => $id ),
			array( '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Get queue statistics
	 *
	 * @return array
	 */
	public function get_queue_stats() {
		global $wpdb;

		$safe_table = esc_sql( $this->queue_table );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$stats = $wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM `{$safe_table}` GROUP BY status"
		);

		$result = array(
			'pending'   => 0,
			'sent'      => 0,
			'failed'    => 0,
			'cancelled' => 0,
		);

		foreach ( $stats as $stat ) {
			$result[ $stat->status ] = intval( $stat->count );
		}

		return $result;
	}

	/**
	 * Get provider balance
	 *
	 * @return array
	 */
	public function get_balance() {
		return $this->provider->get_balance();
	}

	/**
	 * Send test SMS
	 *
	 * @param string $phone Phone number.
	 *
	 * @return array
	 */
	public function send_test( $phone ) {
		if ( ! $this->provider instanceof UNBSB_SMS_NetGSM ) {
			return array(
				'success' => false,
				'message' => __( 'Provider not found.', 'unbelievable-salon-booking' ),
			);
		}

		return $this->provider->send_test( $phone );
	}
}
