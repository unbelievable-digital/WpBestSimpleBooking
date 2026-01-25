<?php
/**
 * Customer model sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer sınıfı
 */
class AG_Customer {

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
	private $table = 'customers';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new AG_Database();
	}

	/**
	 * Müşteri oluştur
	 *
	 * @param array $data Müşteri verileri.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->insert( $this->table, $sanitized );
	}

	/**
	 * Müşteri güncelle
	 *
	 * @param int   $id   Müşteri ID.
	 * @param array $data Müşteri verileri.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Müşteri sil
	 *
	 * @param int $id Müşteri ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Müşteri getir
	 *
	 * @param int $id Müşteri ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Email ile müşteri getir
	 *
	 * @param string $email Email adresi.
	 *
	 * @return object|null
	 */
	public function get_by_email( $email ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$prefix}customers WHERE email = %s",
			sanitize_email( $email )
		);

		return $wpdb->get_row( $sql );
	}

	/**
	 * Müşteri bul veya oluştur
	 *
	 * @param array $data Müşteri verileri.
	 *
	 * @return object|null
	 */
	public function find_or_create( $data ) {
		$customer = $this->get_by_email( $data['email'] );

		if ( $customer ) {
			// Mevcut müşteriyi güncelle
			$this->update( $customer->id, $data );
			return $this->get( $customer->id );
		}

		// Yeni müşteri oluştur
		$id = $this->create( $data );

		if ( $id ) {
			return $this->get( $id );
		}

		return null;
	}

	/**
	 * Tüm müşterileri getir
	 *
	 * @param array $args Argümanlar.
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
	 * Müşteri ara
	 *
	 * @param string $search Arama terimi.
	 *
	 * @return array
	 */
	public function search( $search ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';
		$search = '%' . $wpdb->esc_like( $search ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$prefix}customers
			WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s
			ORDER BY name ASC
			LIMIT 20",
			$search,
			$search,
			$search
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Müşterinin randevularını getir
	 *
	 * @param int $customer_id Müşteri ID.
	 *
	 * @return array
	 */
	public function get_bookings( $customer_id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = $wpdb->prepare(
			"SELECT b.*, s.name as service_name, st.name as staff_name
			FROM {$prefix}bookings b
			LEFT JOIN {$prefix}services s ON b.service_id = s.id
			LEFT JOIN {$prefix}staff st ON b.staff_id = st.id
			WHERE b.customer_id = %d
			ORDER BY b.booking_date DESC, b.start_time DESC",
			$customer_id
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Müşteri sayısını getir
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

		if ( isset( $data['notes'] ) ) {
			$sanitized['notes'] = sanitize_textarea_field( $data['notes'] );
		}

		return $sanitized;
	}
}
