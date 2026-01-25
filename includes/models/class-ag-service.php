<?php
/**
 * Service model sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service sınıfı
 */
class AG_Service {

	/**
	 * Database instance
	 *
	 * @var AG_Database
	 */
	private $db;

	/**
	 * Tablo adı
	 *
	 * @var string
	 */
	private $table = 'services';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new AG_Database();
	}

	/**
	 * Servis oluştur
	 *
	 * @param array $data Servis verileri.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->insert( $this->table, $sanitized );
	}

	/**
	 * Servis güncelle
	 *
	 * @param int   $id   Servis ID.
	 * @param array $data Servis verileri.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Servis sil
	 *
	 * @param int $id Servis ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		// Önce staff_services ilişkilerini sil
		$this->db->delete( 'staff_services', array( 'service_id' => $id ) );

		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Servis getir
	 *
	 * @param int $id Servis ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Tüm servisleri getir
	 *
	 * @param array $args Argümanlar.
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
	 * Aktif servisleri getir
	 *
	 * @return array
	 */
	public function get_active() {
		return $this->get_all( array(
			'where' => array( 'status' => 'active' ),
		) );
	}

	/**
	 * Kategoriye gore servisleri getir
	 *
	 * @param int $category_id Kategori ID.
	 *
	 * @return array
	 */
	public function get_by_category( $category_id ) {
		return $this->get_all( array(
			'where' => array( 'category_id' => $category_id ),
		) );
	}

	/**
	 * Kategorileriyle birlikte servisleri getir
	 *
	 * @return array
	 */
	public function get_with_categories() {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = "SELECT s.*, c.name as category_name, c.color as category_color
			FROM {$prefix}services s
			LEFT JOIN {$prefix}categories c ON s.category_id = c.id
			ORDER BY c.sort_order ASC, s.sort_order ASC";

		return $wpdb->get_results( $sql );
	}

	/**
	 * Kategorilere gore gruplu servisleri getir
	 *
	 * @param bool $only_active Sadece aktif servisler.
	 *
	 * @return array
	 */
	public function get_grouped_by_category( $only_active = true ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$where = $only_active ? "WHERE s.status = 'active'" : '';

		$sql = "SELECT s.*, c.id as category_id, c.name as category_name, c.color as category_color, c.sort_order as category_sort_order
			FROM {$prefix}services s
			LEFT JOIN {$prefix}categories c ON s.category_id = c.id
			{$where}
			ORDER BY c.sort_order ASC, c.name ASC, s.sort_order ASC, s.name ASC";

		$services = $wpdb->get_results( $sql );

		// Kategorilere gore grupla.
		$grouped = array();

		foreach ( $services as $service ) {
			$cat_id = $service->category_id ? $service->category_id : 0;

			if ( ! isset( $grouped[ $cat_id ] ) ) {
				$grouped[ $cat_id ] = array(
					'category_id'    => $cat_id,
					'category_name'  => $service->category_name ? $service->category_name : __( 'Kategorisiz', 'appointment-general' ),
					'category_color' => $service->category_color ? $service->category_color : '#6b7280',
					'services'       => array(),
				);
			}

			$grouped[ $cat_id ]['services'][] = $service;
		}

		return array_values( $grouped );
	}

	/**
	 * Personele göre servisleri getir
	 *
	 * @param int $staff_id Personel ID.
	 *
	 * @return array
	 */
	public function get_by_staff( $staff_id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

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
	 * Servis sayısını getir
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
