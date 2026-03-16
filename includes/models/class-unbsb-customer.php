<?php
/**
 * Customer model class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer class
 */
class UNBSB_Customer {

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
	private $table = 'customers';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new UNBSB_Database();
	}

	/**
	 * Create customer
	 *
	 * @param array $data Customer data.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->insert( $this->table, $sanitized );
	}

	/**
	 * Update customer
	 *
	 * @param int   $id   Customer ID.
	 * @param array $data Customer data.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Delete customer
	 *
	 * @param int $id Customer ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Get customer
	 *
	 * @param int $id Customer ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Get customer by email
	 *
	 * @param string $email Email address.
	 *
	 * @return object|null
	 */
	public function get_by_email( $email ) {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_customers';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $table . ' WHERE email = %s',
				sanitize_email( $email )
			)
		);
	}

	/**
	 * Find or create customer
	 *
	 * @param array $data Customer data.
	 *
	 * @return object|null
	 */
	public function find_or_create( $data ) {
		$customer = $this->get_by_email( $data['email'] );

		if ( $customer ) {
			// Link to WP user if not already linked.
			if ( empty( $customer->user_id ) ) {
				$wp_user = get_user_by( 'email', $data['email'] );
				if ( $wp_user ) {
					$data['user_id'] = $wp_user->ID;
				}
			}

			// Update existing customer.
			$this->update( $customer->id, $data );
			return $this->get( $customer->id );
		}

		// Check if a WP user exists with this email and link it.
		$wp_user = get_user_by( 'email', $data['email'] );
		if ( $wp_user ) {
			$data['user_id'] = $wp_user->ID;
		}

		// Create new customer.
		$id = $this->create( $data );

		if ( $id ) {
			return $this->get( $id );
		}

		return null;
	}

	/**
	 * Get customer by WP user ID
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return object|null
	 */
	public function get_by_user_id( $user_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_customers';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $table . ' WHERE user_id = %d',
				absint( $user_id )
			)
		);
	}

	/**
	 * Link customer to a WordPress user
	 *
	 * @param int $customer_id Customer ID.
	 * @param int $user_id     WordPress user ID.
	 *
	 * @return int|false
	 */
	public function link_user( $customer_id, $user_id ) {
		return $this->update( $customer_id, array( 'user_id' => $user_id ) );
	}

	/**
	 * Get all customers
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public function get_all( $args = array() ) {
		$defaults = array(
			'where'   => array(),
			'orderby' => 'name',
			'order'   => 'ASC',
			'limit'   => 0,
			'offset'  => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->db->get_all( $this->table, $args );
	}

	/**
	 * Search customers
	 *
	 * @param string $search Search term.
	 *
	 * @return array
	 */
	public function search( $search ) {
		global $wpdb;

		$table       = $wpdb->prefix . 'unbsb_customers';
		$search_term = '%' . $wpdb->esc_like( $search ) . '%';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $table . '
				WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s
				ORDER BY name ASC
				LIMIT 20',
				$search_term,
				$search_term,
				$search_term
			)
		);
	}

	/**
	 * Get customer's bookings
	 *
	 * @param int $customer_id Customer ID.
	 *
	 * @return array
	 */
	public function get_bookings( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'unbsb_bookings';
		$services_table = $wpdb->prefix . 'unbsb_services';
		$staff_table    = $wpdb->prefix . 'unbsb_staff';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT b.*, s.name as service_name, st.name as staff_name
				FROM ' . $bookings_table . ' b
				LEFT JOIN ' . $services_table . ' s ON b.service_id = s.id
				LEFT JOIN ' . $staff_table . ' st ON b.staff_id = st.id
				WHERE b.customer_id = %d
				ORDER BY b.booking_date DESC, b.start_time DESC',
				$customer_id
			)
		);
	}

	/**
	 * Get customer count
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
			$user_id_value        = absint( $data['user_id'] );
			$sanitized['user_id'] = $user_id_value > 0 ? $user_id_value : null;
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

		if ( isset( $data['notes'] ) ) {
			$sanitized['notes'] = sanitize_textarea_field( $data['notes'] );
		}

		return $sanitized;
	}
}
