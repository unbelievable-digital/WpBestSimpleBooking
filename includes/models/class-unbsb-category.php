<?php
/**
 * Category model class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category class
 */
class UNBSB_Category {

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
	private $table = 'categories';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new UNBSB_Database();
	}

	/**
	 * Create category
	 *
	 * @param array $data Category data.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->insert( $this->table, $sanitized );
	}

	/**
	 * Update category
	 *
	 * @param int   $id   Category ID.
	 * @param array $data Category data.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Delete category
	 *
	 * @param int $id Category ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		// First set category_id to NULL for services in this category.
		global $wpdb;
		$prefix = $wpdb->prefix . 'unbsb_';

		$wpdb->update(
			$prefix . 'services',
			array( 'category_id' => null ),
			array( 'category_id' => $id ),
			array( '%d' ),
			array( '%d' )
		);

		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Get category
	 *
	 * @param int $id Category ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Get all categories
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
	 * Get active categories
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
	 * Get categories with service counts
	 *
	 * @return array
	 */
	public function get_with_service_count() {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		$sql = "SELECT c.*, COUNT(s.id) as service_count
			FROM {$prefix}categories c
			LEFT JOIN {$prefix}services s ON c.id = s.category_id
			GROUP BY c.id
			ORDER BY c.sort_order ASC, c.name ASC";

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get category count
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

		if ( isset( $data['name'] ) ) {
			$sanitized['name'] = sanitize_text_field( $data['name'] );
		}

		if ( isset( $data['description'] ) ) {
			$sanitized['description'] = sanitize_textarea_field( $data['description'] );
		}

		if ( isset( $data['color'] ) ) {
			$sanitized['color'] = sanitize_hex_color( $data['color'] );
		}

		if ( isset( $data['icon'] ) ) {
			$sanitized['icon'] = sanitize_text_field( $data['icon'] );
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
