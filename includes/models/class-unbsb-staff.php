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
	 * Add holiday/time-off
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $date     Date (Y-m-d).
	 * @param string $reason   Reason.
	 *
	 * @return int|false
	 */
	public function add_holiday( $staff_id, $date, $reason = '' ) {
		// Check if time-off already exists for the same date.
		global $wpdb;
		$prefix = $wpdb->prefix . 'unbsb_';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$prefix}holidays WHERE staff_id = %d AND date = %s",
				$staff_id,
				$date
			)
		);

		if ( $exists ) {
			return false; // Already marked as time-off.
		}

		return $this->db->insert(
			'holidays',
			array(
				'staff_id' => absint( $staff_id ),
				'date'     => sanitize_text_field( $date ),
				'reason'   => sanitize_text_field( $reason ),
			)
		);
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

		return $sanitized;
	}
}
