<?php
/**
 * Booking Service Model
 *
 * Booking-service relationship model for multi-service support.
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UNBSB_Booking_Service class
 */
class UNBSB_Booking_Service {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'unbsb_booking_services';
	}

	/**
	 * Add service for booking
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $service    Service data (service_id, staff_id, price, duration, sort_order).
	 *
	 * @return int|false Insert ID or false.
	 */
	public function add( $booking_id, $service ) {
		global $wpdb;

		$data = array(
			'booking_id' => absint( $booking_id ),
			'service_id' => absint( $service['service_id'] ),
			'staff_id'   => isset( $service['staff_id'] ) ? absint( $service['staff_id'] ) : null,
			'price'      => floatval( $service['price'] ),
			'duration'   => absint( $service['duration'] ),
			'sort_order' => isset( $service['sort_order'] ) ? absint( $service['sort_order'] ) : 0,
		);

		$result = $wpdb->insert( $this->table, $data );

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Add multiple services for booking
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $services   Service list.
	 *
	 * @return bool
	 */
	public function add_multiple( $booking_id, $services ) {
		foreach ( $services as $index => $service ) {
			$service['sort_order'] = $index;
			$result                = $this->add( $booking_id, $service );
			if ( ! $result ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get services for a booking
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return array
	 */
	public function get_by_booking( $booking_id ) {
		global $wpdb;

		$services_table = $wpdb->prefix . 'unbsb_services';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT bs.*, s.name as service_name, s.color as service_color, s.description as service_description
				FROM {$this->table} bs
				LEFT JOIN {$services_table} s ON bs.service_id = s.id
				WHERE bs.booking_id = %d
				ORDER BY bs.sort_order ASC",
				$booking_id
			)
		);

		return $results ? $results : array();
	}

	/**
	 * Delete services for a booking
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return bool
	 */
	public function delete_by_booking( $booking_id ) {
		global $wpdb;

		return $wpdb->delete(
			$this->table,
			array( 'booking_id' => $booking_id ),
			array( '%d' )
		);
	}

	/**
	 * Calculate total price for booking
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return float
	 */
	public function get_total_price( $booking_id ) {
		global $wpdb;

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(price) FROM {$this->table} WHERE booking_id = %d",
				$booking_id
			)
		);

		return $total ? floatval( $total ) : 0;
	}

	/**
	 * Calculate total duration for booking
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return int Total duration in minutes.
	 */
	public function get_total_duration( $booking_id ) {
		global $wpdb;

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(duration) FROM {$this->table} WHERE booking_id = %d",
				$booking_id
			)
		);

		return $total ? absint( $total ) : 0;
	}

	/**
	 * Update booking services
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $services   New service list.
	 *
	 * @return bool
	 */
	public function update_services( $booking_id, $services ) {
		// First delete existing services.
		$this->delete_by_booking( $booking_id );

		// Add new services.
		return $this->add_multiple( $booking_id, $services );
	}

	/**
	 * Get service usage count
	 *
	 * @param int $service_id Service ID.
	 *
	 * @return int
	 */
	public function get_service_usage_count( $service_id ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE service_id = %d",
				$service_id
			)
		);

		return absint( $count );
	}
}
