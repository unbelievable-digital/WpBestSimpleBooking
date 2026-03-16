<?php
/**
 * Service model class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service class
 */
class UNBSB_Service {

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
	private $table = 'services';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new UNBSB_Database();
	}

	/**
	 * Create service
	 *
	 * @param array $data Service data.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->insert( $this->table, $sanitized );
	}

	/**
	 * Update service
	 *
	 * @param int   $id   Service ID.
	 * @param array $data Service data.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Delete service
	 *
	 * @param int $id Service ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		// First delete staff_services relationships
		$this->db->delete( 'staff_services', array( 'service_id' => $id ) );

		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Get service
	 *
	 * @param int $id Service ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Get all services
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
	 * Get active services
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
	 * Get services by category
	 *
	 * @param int $category_id Category ID.
	 *
	 * @return array
	 */
	public function get_by_category( $category_id ) {
		return $this->get_all(
			array(
				'where' => array( 'category_id' => $category_id ),
			)
		);
	}

	/**
	 * Get services with categories
	 *
	 * @return array
	 */
	public function get_with_categories() {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		$sql = "SELECT s.*, c.name as category_name, c.color as category_color
			FROM {$prefix}services s
			LEFT JOIN {$prefix}categories c ON s.category_id = c.id
			ORDER BY c.sort_order ASC, s.sort_order ASC";

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get services grouped by category
	 *
	 * @param bool $only_active Active services only.
	 *
	 * @return array
	 */
	public function get_grouped_by_category( $only_active = true ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		$where = $only_active ? "WHERE s.status = 'active'" : '';

		$sql = "SELECT s.*, c.id as category_id, c.name as category_name, c.color as category_color, c.sort_order as category_sort_order
			FROM {$prefix}services s
			LEFT JOIN {$prefix}categories c ON s.category_id = c.id
			{$where}
			ORDER BY c.sort_order ASC, c.name ASC, s.sort_order ASC, s.name ASC";

		$services = $wpdb->get_results( $sql );

		// Group by categories.
		$grouped = array();

		foreach ( $services as $service ) {
			$cat_id = $service->category_id ? $service->category_id : 0;

			if ( ! isset( $grouped[ $cat_id ] ) ) {
				$grouped[ $cat_id ] = array(
					'category_id'    => $cat_id,
					'category_name'  => $service->category_name ? $service->category_name : __( 'Uncategorized', 'unbelievable-salon-booking' ),
					'category_color' => $service->category_color ? $service->category_color : '#6b7280',
					'services'       => array(),
				);
			}

			$grouped[ $cat_id ]['services'][] = $service;
		}

		return array_values( $grouped );
	}

	/**
	 * Get services by staff
	 *
	 * @param int $staff_id Staff ID.
	 *
	 * @return array
	 */
	public function get_by_staff( $staff_id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		$sql = $wpdb->prepare(
			"SELECT s.*, ss.custom_price, ss.custom_duration
			FROM {$prefix}services s
			INNER JOIN {$prefix}staff_services ss ON s.id = ss.service_id
			WHERE ss.staff_id = %d AND s.status = 'active'
			ORDER BY s.sort_order ASC",
			$staff_id
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get service count
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

		if ( isset( $data['category_id'] ) ) {
			$sanitized['category_id'] = $data['category_id'] ? absint( $data['category_id'] ) : null;
		}

		if ( isset( $data['name'] ) ) {
			$sanitized['name'] = sanitize_text_field( $data['name'] );
		}

		if ( isset( $data['description'] ) ) {
			$sanitized['description'] = sanitize_textarea_field( $data['description'] );
		}

		if ( isset( $data['duration'] ) ) {
			$sanitized['duration'] = absint( $data['duration'] );
		}

		if ( isset( $data['price'] ) ) {
			$sanitized['price'] = floatval( $data['price'] );
		}

		if ( array_key_exists( 'discounted_price', $data ) ) {
			$sanitized['discounted_price'] = ( '' !== $data['discounted_price'] && null !== $data['discounted_price'] )
				? floatval( $data['discounted_price'] )
				: null;
		}

		if ( isset( $data['buffer_before'] ) ) {
			$sanitized['buffer_before'] = absint( $data['buffer_before'] );
		}

		if ( isset( $data['buffer_after'] ) ) {
			$sanitized['buffer_after'] = absint( $data['buffer_after'] );
		}

		if ( isset( $data['color'] ) ) {
			$sanitized['color'] = sanitize_hex_color( $data['color'] );
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
