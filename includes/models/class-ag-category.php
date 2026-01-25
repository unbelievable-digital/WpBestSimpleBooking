<?php
/**
 * Category model sinifi
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category sinifi
 */
class AG_Category {

	/**
	 * Database instance
	 *
	 * @var AG_Database
	 */
	private $db;

	/**
	 * Tablo adi
	 *
	 * @var string
	 */
	private $table = 'categories';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new AG_Database();
	}

	/**
	 * Kategori olustur
	 *
	 * @param array $data Kategori verileri.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->insert( $this->table, $sanitized );
	}

	/**
	 * Kategori guncelle
	 *
	 * @param int   $id   Kategori ID.
	 * @param array $data Kategori verileri.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Kategori sil
	 *
	 * @param int $id Kategori ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		// Once bu kategorideki servislerin category_id'sini NULL yap.
		global $wpdb;
		$prefix = $wpdb->prefix . 'ag_';

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
	 * Kategori getir
	 *
	 * @param int $id Kategori ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Tum kategorileri getir
	 *
	 * @param array $args Argumanlar.
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
	 * Aktif kategorileri getir
	 *
	 * @return array
	 */
	public function get_active() {
		return $this->get_all( array(
			'where' => array( 'status' => 'active' ),
		) );
	}

	/**
	 * Kategorileri servis sayilari ile getir
	 *
	 * @return array
	 */
	public function get_with_service_count() {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = "SELECT c.*, COUNT(s.id) as service_count
			FROM {$prefix}categories c
			LEFT JOIN {$prefix}services s ON c.id = s.category_id
			GROUP BY c.id
			ORDER BY c.sort_order ASC, c.name ASC";

		return $wpdb->get_results( $sql );
	}

	/**
	 * Kategori sayisini getir
	 *
	 * @return int
	 */
	public function count() {
		return $this->db->count( $this->table );
	}

	/**
	 * Veriyi sanitize et
	 *
	 * @param array $data Ham veri.
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
