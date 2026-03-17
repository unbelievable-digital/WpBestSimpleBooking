<?php
/**
 * Staff model class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Staff class
 */
class UNBSB_Staff {

	/**
	 * Database instance
	 *
	 * @var UNBSB_Database
	 */
	private $db;

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $table = 'staff';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new UNBSB_Database();
	}

	/**
	 * Create staff
	 *
	 * @param array $data Staff data.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );
		$staff_id  = $this->db->insert( $this->table, $sanitized );

		if ( $staff_id && ! empty( $data['services'] ) ) {
			$this->sync_services( $staff_id, $data['services'] );
		}

		// Create default working hours.
		if ( $staff_id ) {
			$this->create_default_working_hours( $staff_id );
		}

		return $staff_id;
	}

	/**
	 * Update staff
	 *
	 * @param int   $id   Staff ID.
	 * @param array $data Staff data.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );
		$result    = $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );

		if ( isset( $data['services'] ) ) {
			$this->sync_services( $id, $data['services'] );
		}

		return $result;
	}

	/**
	 * Delete staff
	 *
	 * @param int $id Staff ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		// Delete related data.
		$this->db->delete( 'staff_services', array( 'staff_id' => $id ) );
		$this->db->delete( 'working_hours', array( 'staff_id' => $id ) );
		$this->db->delete( 'breaks', array( 'staff_id' => $id ) );
		$this->db->delete( 'holidays', array( 'staff_id' => $id ) );

		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Get staff
	 *
	 * @param int $id Staff ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		$staff = $this->db->get_by_id( $this->table, $id );

		if ( $staff ) {
			$staff->services = $this->get_services( $id );
		}

		return $staff;
	}

	/**
	 * Get all staff
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public function get_all( $args = array() ) {
		$defaults = array(
			'where'   => array(),
			'orderby' => 'sort_order',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->db->get_all( $this->table, $args );
	}

	/**
	 * Get staff by WordPress user ID
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return object|null
	 */
	public function get_by_user_id( $user_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_staff';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d",
				absint( $user_id )
			)
		);
	}

	/**
	 * Get active staff
	 *
	 * @return array
	 */
	public function get_active() {
		return $this->get_all(
			array(
				'where' => array( 'status' => 'active' ),
			)
		);
	}

	/**
	 * Get staff by service
	 *
	 * @param int $service_id Service ID.
	 *
	 * @return array
	 */
	public function get_by_service( $service_id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		$sql = $wpdb->prepare(
			"SELECT st.*
			FROM {$prefix}staff st
			INNER JOIN {$prefix}staff_services ss ON st.id = ss.staff_id
			WHERE ss.service_id = %d AND st.status = 'active'
			ORDER BY st.sort_order ASC",
			$service_id
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get staff's services with custom pricing data.
	 *
	 * @param int  $staff_id Staff ID.
	 * @param bool $ids_only Return only service IDs (backward compatible).
	 *
	 * @return array Array of service IDs (ids_only=true) or objects with service_id, custom_price, custom_duration.
	 */
	public function get_services( $staff_id, $ids_only = true ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		if ( $ids_only ) {
			$sql = $wpdb->prepare(
				"SELECT service_id FROM {$prefix}staff_services WHERE staff_id = %d",
				$staff_id
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_col( $sql );
		}

		$sql = $wpdb->prepare(
			"SELECT service_id, custom_price, custom_duration FROM {$prefix}staff_services WHERE staff_id = %d",
			$staff_id
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get custom price/duration for a specific staff-service pair.
	 *
	 * @param int $staff_id   Staff ID.
	 * @param int $service_id Service ID.
	 *
	 * @return object|null Object with custom_price and custom_duration, or null.
	 */
	public function get_service_custom_data( $staff_id, $service_id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT custom_price, custom_duration FROM {$prefix}staff_services WHERE staff_id = %d AND service_id = %d",
				$staff_id,
				$service_id
			)
		);
	}

	/**
	 * Sync services
	 *
	 * Accepts two formats:
	 * - Simple: array( 1, 2, 3 ) — service IDs only (backward compatible).
	 * - Detailed: array( array( 'service_id' => 1, 'custom_price' => 50.00, 'custom_duration' => 45 ), ... )
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $services Service IDs or service data arrays.
	 */
	public function sync_services( $staff_id, $services ) {
		// Delete existing services.
		$this->db->delete( 'staff_services', array( 'staff_id' => $staff_id ) );

		// Add new services.
		foreach ( $services as $service ) {
			if ( is_array( $service ) ) {
				// Detailed format.
				$row = array(
					'staff_id'   => absint( $staff_id ),
					'service_id' => absint( $service['service_id'] ),
				);

				if ( isset( $service['custom_price'] ) && '' !== $service['custom_price'] ) {
					$row['custom_price'] = floatval( $service['custom_price'] );
				}

				if ( isset( $service['custom_duration'] ) && '' !== $service['custom_duration'] ) {
					$row['custom_duration'] = absint( $service['custom_duration'] );
				}

				$this->db->insert( 'staff_services', $row );
			} else {
				// Simple format (backward compatible).
				$this->db->insert(
					'staff_services',
					array(
						'staff_id'   => absint( $staff_id ),
						'service_id' => absint( $service ),
					)
				);
			}
		}
	}

	/**
	 * Get working hours
	 *
	 * @param int $staff_id Staff ID.
	 *
	 * @return array
	 */
	public function get_working_hours( $staff_id ) {
		return $this->db->get_all(
			'working_hours',
			array(
				'where'   => array( 'staff_id' => $staff_id ),
				'orderby' => 'day_of_week',
				'order'   => 'ASC',
			)
		);
	}

	/**
	 * Update working hours
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $hours    Working hours.
	 */
	public function update_working_hours( $staff_id, $hours ) {
		// Delete existing hours.
		$this->db->delete( 'working_hours', array( 'staff_id' => $staff_id ) );

		// Add new hours.
		foreach ( $hours as $hour ) {
			$this->db->insert(
				'working_hours',
				array(
					'staff_id'    => $staff_id,
					'day_of_week' => absint( $hour['day_of_week'] ),
					'start_time'  => sanitize_text_field( $hour['start_time'] ),
					'end_time'    => sanitize_text_field( $hour['end_time'] ),
					'is_working'  => isset( $hour['is_working'] ) ? absint( $hour['is_working'] ) : 1,
				)
			);
		}
	}

	/**
	 * Create default working hours
	 *
	 * @param int $staff_id Staff ID.
	 */
	private function create_default_working_hours( $staff_id ) {
		// Monday - Saturday 09:00 - 18:00.
		for ( $day = 1; $day <= 6; $day++ ) {
			$this->db->insert(
				'working_hours',
				array(
					'staff_id'    => $staff_id,
					'day_of_week' => $day,
					'start_time'  => '09:00:00',
					'end_time'    => '18:00:00',
					'is_working'  => 1,
				)
			);
		}

		// Sunday closed.
		$this->db->insert(
			'working_hours',
			array(
				'staff_id'    => $staff_id,
				'day_of_week' => 0,
				'start_time'  => '09:00:00',
				'end_time'    => '18:00:00',
				'is_working'  => 0,
			)
		);
	}

	/**
	 * Get breaks
	 *
	 * @param int $staff_id    Staff ID.
	 * @param int $day_of_week Day of the week.
	 *
	 * @return array
	 */
	public function get_breaks( $staff_id, $day_of_week = null ) {
		$args = array(
			'where' => array( 'staff_id' => $staff_id ),
		);

		if ( null !== $day_of_week ) {
			$args['where']['day_of_week'] = $day_of_week;
		}

		return $this->db->get_all( 'breaks', $args );
	}

	/**
	 * Add break
	 *
	 * @param int    $staff_id    Staff ID.
	 * @param int    $day_of_week Day of week.
	 * @param string $start_time  Start time.
	 * @param string $end_time    End time.
	 *
	 * @return int|false
	 */
	public function add_break( $staff_id, $day_of_week, $start_time, $end_time ) {
		return $this->db->insert(
			'breaks',
			array(
				'staff_id'    => absint( $staff_id ),
				'day_of_week' => absint( $day_of_week ),
				'start_time'  => sanitize_text_field( $start_time ),
				'end_time'    => sanitize_text_field( $end_time ),
			)
		);
	}

	/**
	 * Delete break
	 *
	 * @param int $break_id Break ID.
	 *
	 * @return int|false
	 */
	public function delete_break( $break_id ) {
		return $this->db->delete( 'breaks', array( 'id' => $break_id ) );
	}

	/**
	 * Delete all breaks for staff
	 *
	 * @param int $staff_id Staff ID.
	 *
	 * @return int|false
	 */
	public function delete_all_breaks( $staff_id ) {
		return $this->db->delete( 'breaks', array( 'staff_id' => $staff_id ) );
	}

	/**
	 * Update breaks (delete all and re-add)
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $breaks   Break data.
	 */
	public function update_breaks( $staff_id, $breaks ) {
		// Delete existing breaks.
		$this->delete_all_breaks( $staff_id );

		// Add new breaks.
		foreach ( $breaks as $break ) {
			if ( ! empty( $break['start_time'] ) && ! empty( $break['end_time'] ) ) {
				$this->add_break(
					$staff_id,
					$break['day_of_week'],
					$break['start_time'],
					$break['end_time']
				);
			}
		}
	}

	/**
	 * Get holidays/time-off
	 *
	 * @param int    $staff_id   Staff ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return array
	 */
	public function get_holidays( $staff_id, $start_date = null, $end_date = null ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';
		$sql    = "SELECT * FROM {$prefix}holidays WHERE staff_id = %d";
		$params = array( $staff_id );

		if ( $start_date ) {
			$sql     .= ' AND date >= %s';
			$params[] = $start_date;
		}

		if ( $end_date ) {
			$sql     .= ' AND date <= %s';
			$params[] = $end_date;
		}

		$sql .= ' ORDER BY date ASC';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Add holiday/time-off or extra open day
	 *
	 * @param int    $staff_id   Staff ID.
	 * @param string $date       Date (Y-m-d).
	 * @param string $reason     Reason.
	 * @param string $type       Type: 'off' or 'extra'.
	 * @param string $start_time Start time for extra day (H:i or null).
	 * @param string $end_time   End time for extra day (H:i or null).
	 *
	 * @return int|false
	 */
	public function add_holiday( $staff_id, $date, $reason = '', $type = 'off', $start_time = null, $end_time = null ) {
		// Check if entry already exists for the same date and type.
		global $wpdb;
		$prefix = $wpdb->prefix . 'unbsb_';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$prefix}holidays WHERE staff_id = %d AND date = %s AND type = %s",
				$staff_id,
				$date,
				$type
			)
		);

		if ( $exists ) {
			return false;
		}

		$data = array(
			'staff_id' => absint( $staff_id ),
			'date'     => sanitize_text_field( $date ),
			'reason'   => sanitize_text_field( $reason ),
			'type'     => in_array( $type, array( 'off', 'extra' ), true ) ? $type : 'off',
		);

		if ( 'extra' === $type && $start_time ) {
			$data['start_time'] = sanitize_text_field( $start_time );
		}
		if ( 'extra' === $type && $end_time ) {
			$data['end_time'] = sanitize_text_field( $end_time );
		}

		return $this->db->insert( 'holidays', $data );
	}

	/**
	 * Delete holiday/time-off
	 *
	 * @param int $holiday_id Holiday ID.
	 *
	 * @return int|false
	 */
	public function delete_holiday( $holiday_id ) {
		return $this->db->delete( 'holidays', array( 'id' => $holiday_id ) );
	}

	/**
	 * Delete holiday by date
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $date     Date (Y-m-d).
	 *
	 * @return int|false
	 */
	public function delete_holiday_by_date( $staff_id, $date ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'unbsb_';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete(
			$prefix . 'holidays',
			array(
				'staff_id' => $staff_id,
				'date'     => $date,
			),
			array( '%d', '%s' )
		);
	}

	/**
	 * Get staff count
	 *
	 * @return int
	 */
	public function count() {
		return $this->db->count( $this->table );
	}

	/**
	 * Calculate commission for a booking
	 *
	 * @param int   $staff_id      Staff ID.
	 * @param float $booking_price Booking price.
	 *
	 * @return float Commission amount.
	 */
	public function calculate_commission( $staff_id, $booking_price ) {
		$staff = $this->get( $staff_id );

		if ( ! $staff ) {
			return 0;
		}

		$salary_type       = isset( $staff->salary_type ) ? $staff->salary_type : 'percentage';
		$salary_percentage = isset( $staff->salary_percentage ) ? floatval( $staff->salary_percentage ) : 0;

		if ( 'fixed' === $salary_type ) {
			return 0; // Fixed salary has no per-booking commission.
		}

		// percentage or mix — calculate commission from booking price.
		return round( $booking_price * $salary_percentage / 100, 2 );
	}

	/**
	 * Record commission earning for a booking
	 *
	 * @param int   $staff_id   Staff ID.
	 * @param int   $booking_id Booking ID.
	 * @param float $amount     Commission amount.
	 *
	 * @return int|false
	 */
	public function record_commission( $staff_id, $booking_id, $amount ) {
		if ( $amount <= 0 ) {
			return false;
		}

		return $this->db->insert(
			'staff_earnings',
			array(
				'staff_id'   => absint( $staff_id ),
				'booking_id' => absint( $booking_id ),
				'amount'     => floatval( $amount ),
				'type'       => 'commission',
				'period'     => current_time( 'Y-m' ),
			)
		);
	}

	/**
	 * Record monthly fixed salary
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $period   Period in Y-m format.
	 *
	 * @return int|false
	 */
	public function record_salary( $staff_id, $period = null ) {
		$staff = $this->get( $staff_id );

		if ( ! $staff || 'percentage' === ( $staff->salary_type ?? 'percentage' ) ) {
			return false;
		}

		$salary_fixed = isset( $staff->salary_fixed ) ? floatval( $staff->salary_fixed ) : 0;

		if ( $salary_fixed <= 0 ) {
			return false;
		}

		if ( null === $period ) {
			$period = current_time( 'Y-m' );
		}

		// Check if salary already recorded for this period.
		global $wpdb;
		$table = $wpdb->prefix . 'unbsb_staff_earnings';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE staff_id = %d AND type = 'salary' AND period = %s",
				$staff_id,
				$period
			)
		);

		if ( $exists ) {
			return false; // Already recorded.
		}

		return $this->db->insert(
			'staff_earnings',
			array(
				'staff_id' => absint( $staff_id ),
				'amount'   => $salary_fixed,
				'type'     => 'salary',
				'period'   => $period,
			)
		);
	}

	/**
	 * Get staff earnings for a period
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $period   Period in Y-m format.
	 *
	 * @return array
	 */
	public function get_earnings( $staff_id, $period = null ) {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_staff_earnings';

		if ( null === $period ) {
			$period = current_time( 'Y-m' );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$commission_total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$table} WHERE staff_id = %d AND type = 'commission' AND period = %s",
				$staff_id,
				$period
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$salary_total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$table} WHERE staff_id = %d AND type = 'salary' AND period = %s",
				$staff_id,
				$period
			)
		);

		return array(
			'commission' => floatval( $commission_total ),
			'salary'     => floatval( $salary_total ),
			'total'      => floatval( $commission_total ) + floatval( $salary_total ),
			'period'     => $period,
		);
	}

	/**
	 * Record a payment to staff
	 *
	 * @param int    $staff_id       Staff ID.
	 * @param float  $amount         Payment amount.
	 * @param string $payment_date   Payment date (Y-m-d).
	 * @param string $payment_method Payment method.
	 * @param string $notes          Notes.
	 * @param int    $recorded_by    Admin user ID.
	 *
	 * @return int|false
	 */
	public function record_payment( $staff_id, $amount, $payment_date, $payment_method, $notes, $recorded_by ) {
		if ( $amount <= 0 ) {
			return false;
		}

		return $this->db->insert(
			'staff_payments',
			array(
				'staff_id'       => absint( $staff_id ),
				'amount'         => floatval( $amount ),
				'payment_date'   => sanitize_text_field( $payment_date ),
				'payment_method' => sanitize_text_field( $payment_method ),
				'notes'          => sanitize_textarea_field( $notes ),
				'recorded_by'    => absint( $recorded_by ),
			)
		);
	}

	/**
	 * Delete a payment record
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $staff_id   Staff ID (for ownership check).
	 *
	 * @return bool
	 */
	public function delete_payment( $payment_id, $staff_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'unbsb_staff_payments';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete(
			$table,
			array(
				'id'       => absint( $payment_id ),
				'staff_id' => absint( $staff_id ),
			),
			array( '%d', '%d' )
		);

		return (bool) $deleted;
	}

	/**
	 * Get staff payments
	 *
	 * @param int    $staff_id   Staff ID.
	 * @param string $date_from  Start date (Y-m-d). Optional.
	 * @param string $date_to    End date (Y-m-d). Optional.
	 *
	 * @return array
	 */
	public function get_payments( $staff_id, $date_from = null, $date_to = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'unbsb_staff_payments';

		$where = $wpdb->prepare( 'WHERE staff_id = %d', absint( $staff_id ) );

		if ( $date_from && $date_to ) {
			$where .= $wpdb->prepare( ' AND payment_date BETWEEN %s AND %s', $date_from, $date_to );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			"SELECT p.*, u.display_name AS recorded_by_name
			FROM {$table} p
			LEFT JOIN {$wpdb->users} u ON p.recorded_by = u.ID
			{$where}
			ORDER BY p.payment_date DESC, p.id DESC"
		);
	}

	/**
	 * Get earnings summary for a staff member
	 *
	 * @param int $staff_id Staff ID.
	 *
	 * @return array
	 */
	public function get_earnings_summary( $staff_id ) {
		global $wpdb;
		$earnings_table = $wpdb->prefix . 'unbsb_staff_earnings';
		$payments_table = $wpdb->prefix . 'unbsb_staff_payments';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_earnings = floatval( $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$earnings_table} WHERE staff_id = %d",
				absint( $staff_id )
			)
		) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_paid = floatval( $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$payments_table} WHERE staff_id = %d",
				absint( $staff_id )
			)
		) );

		$current_period = current_time( 'Y-m' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this_month_earnings = floatval( $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$earnings_table} WHERE staff_id = %d AND period = %s",
				absint( $staff_id ),
				$current_period
			)
		) );

		return array(
			'total_earnings'      => $total_earnings,
			'total_paid'          => $total_paid,
			'remaining_balance'   => $total_earnings - $total_paid,
			'this_month_earnings' => $this_month_earnings,
		);
	}

	/**
	 * Get detailed earnings records for a staff member
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $period   Period in Y-m format. Null for all.
	 *
	 * @return array
	 */
	public function get_earnings_detail( $staff_id, $period = null ) {
		global $wpdb;
		$table          = $wpdb->prefix . 'unbsb_staff_earnings';
		$bookings_table = $wpdb->prefix . 'unbsb_bookings';
		$services_table = $wpdb->prefix . 'unbsb_services';

		$where = $wpdb->prepare( 'WHERE e.staff_id = %d', absint( $staff_id ) );

		if ( $period ) {
			$where .= $wpdb->prepare( ' AND e.period = %s', $period );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			"SELECT e.*, b.booking_date, b.customer_name, b.price AS booking_price, s.name AS service_name
			FROM {$table} e
			LEFT JOIN {$bookings_table} b ON e.booking_id = b.id
			LEFT JOIN {$services_table} s ON b.service_id = s.id
			{$where}
			ORDER BY e.created_at DESC"
		);
	}

	/**
	 * Get performance metrics for a staff member
	 *
	 * @param int    $staff_id  Staff ID.
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to   End date (Y-m-d).
	 *
	 * @return array
	 */
	public function get_performance_metrics( $staff_id, $date_from, $date_to ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'unbsb_bookings';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) AS total_bookings,
					SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
					SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
					SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
					COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) AS total_revenue
				FROM {$bookings_table}
				WHERE staff_id = %d AND booking_date BETWEEN %s AND %s",
				absint( $staff_id ),
				$date_from,
				$date_to
			)
		);

		$denominator = intval( $stats->confirmed ) + intval( $stats->completed ) + intval( $stats->cancelled );
		$cancel_rate = $denominator > 0 ? round( ( intval( $stats->cancelled ) / $denominator ) * 100, 1 ) : 0;

		return array(
			'total_bookings' => intval( $stats->total_bookings ),
			'completed'      => intval( $stats->completed ),
			'cancelled'      => intval( $stats->cancelled ),
			'cancel_rate'    => $cancel_rate,
			'total_revenue'  => floatval( $stats->total_revenue ),
		);
	}

	/**
	 * Get top services for a staff member
	 *
	 * @param int    $staff_id  Staff ID.
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to   End date (Y-m-d).
	 * @param int    $limit     Number of results.
	 *
	 * @return array
	 */
	public function get_top_services( $staff_id, $date_from, $date_to, $limit = 5 ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'unbsb_bookings';
		$services_table = $wpdb->prefix . 'unbsb_services';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.name, COUNT(*) AS booking_count, COALESCE(SUM(b.price), 0) AS total_revenue
				FROM {$bookings_table} b
				INNER JOIN {$services_table} s ON b.service_id = s.id
				WHERE b.staff_id = %d AND b.booking_date BETWEEN %s AND %s AND b.status IN ('completed', 'confirmed')
				GROUP BY b.service_id, s.name
				ORDER BY booking_count DESC
				LIMIT %d",
				absint( $staff_id ),
				$date_from,
				$date_to,
				$limit
			)
		);
	}

	/**
	 * Get monthly trend for a staff member (last 6 months)
	 *
	 * @param int $staff_id Staff ID.
	 *
	 * @return array
	 */
	public function get_monthly_trend( $staff_id ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'unbsb_bookings';
		$earnings_table = $wpdb->prefix . 'unbsb_staff_earnings';

		$six_months_ago = wp_date( 'Y-m-01', strtotime( '-5 months' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$booking_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(booking_date, '%%Y-%%m') AS month,
					COUNT(*) AS bookings,
					COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) AS revenue
				FROM {$bookings_table}
				WHERE staff_id = %d AND booking_date >= %s
				GROUP BY month
				ORDER BY month ASC",
				absint( $staff_id ),
				$six_months_ago
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$earnings_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT period AS month, COALESCE(SUM(amount), 0) AS commission
				FROM {$earnings_table}
				WHERE staff_id = %d AND period >= %s
				GROUP BY period
				ORDER BY period ASC",
				absint( $staff_id ),
				wp_date( 'Y-m', strtotime( '-5 months' ) )
			)
		);

		$earnings_map = array();
		foreach ( $earnings_data as $row ) {
			$earnings_map[ $row->month ] = floatval( $row->commission );
		}

		$result = array();
		foreach ( $booking_data as $row ) {
			$result[] = array(
				'month'      => $row->month,
				'bookings'   => intval( $row->bookings ),
				'revenue'    => floatval( $row->revenue ),
				'commission' => isset( $earnings_map[ $row->month ] ) ? $earnings_map[ $row->month ] : 0,
			);
		}

		return $result;
	}

	/**
	 * Get all staff with remaining balance (single query, no N+1)
	 *
	 * @return array
	 */
	public function get_all_with_balance() {
		global $wpdb;
		$staff_table    = $wpdb->prefix . 'unbsb_staff';
		$earnings_table = $wpdb->prefix . 'unbsb_staff_earnings';
		$payments_table = $wpdb->prefix . 'unbsb_staff_payments';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			"SELECT s.*,
				COALESCE(e.total_earnings, 0) AS total_earnings,
				COALESCE(p.total_paid, 0) AS total_paid,
				COALESCE(e.total_earnings, 0) - COALESCE(p.total_paid, 0) AS remaining_balance
			FROM {$staff_table} s
			LEFT JOIN (
				SELECT staff_id, SUM(amount) AS total_earnings
				FROM {$earnings_table}
				GROUP BY staff_id
			) e ON s.id = e.staff_id
			LEFT JOIN (
				SELECT staff_id, SUM(amount) AS total_paid
				FROM {$payments_table}
				GROUP BY staff_id
			) p ON s.id = p.staff_id
			ORDER BY s.sort_order ASC, s.name ASC"
		);
	}

	/**
	 * Delete earnings for a booking (for reversals)
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return int|false
	 */
	public function delete_earnings_by_booking( $booking_id ) {
		return $this->db->delete( 'staff_earnings', array( 'booking_id' => $booking_id ) );
	}

	/**
	 * Sanitize data
	 *
	 * @param array $data Raw data.
	 *
	 * @return array
	 */
	private function sanitize_data( $data ) {
		$sanitized = array();

		if ( isset( $data['user_id'] ) ) {
			$sanitized['user_id'] = absint( $data['user_id'] ) ?: null;
		}

		if ( isset( $data['name'] ) ) {
			$sanitized['name'] = sanitize_text_field( $data['name'] );
		}

		if ( isset( $data['email'] ) ) {
			$sanitized['email'] = sanitize_email( $data['email'] );
		}

		if ( isset( $data['phone'] ) ) {
			$sanitized['phone'] = sanitize_text_field( $data['phone'] );
		}

		if ( isset( $data['bio'] ) ) {
			$sanitized['bio'] = sanitize_textarea_field( $data['bio'] );
		}

		if ( isset( $data['avatar_url'] ) ) {
			$sanitized['avatar_url'] = esc_url_raw( $data['avatar_url'] );
		}

		if ( isset( $data['status'] ) ) {
			$sanitized['status'] = in_array( $data['status'], array( 'active', 'inactive' ), true )
				? $data['status']
				: 'active';
		}

		if ( isset( $data['sort_order'] ) ) {
			$sanitized['sort_order'] = absint( $data['sort_order'] );
		}

		if ( isset( $data['salary_type'] ) ) {
			$sanitized['salary_type'] = in_array( $data['salary_type'], array( 'percentage', 'fixed', 'mix' ), true )
				? $data['salary_type']
				: 'percentage';
		}

		if ( isset( $data['salary_percentage'] ) ) {
			$sanitized['salary_percentage'] = max( 0, min( 100, floatval( $data['salary_percentage'] ) ) );
		}

		if ( isset( $data['salary_fixed'] ) ) {
			$sanitized['salary_fixed'] = max( 0, floatval( $data['salary_fixed'] ) );
		}

		return $sanitized;
	}
}
