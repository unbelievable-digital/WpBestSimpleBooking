<?php
/**
 * Booking Service Model
 *
 * Çoklu hizmet desteği için booking-service ilişki modeli.
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AG_Booking_Service sınıfı
 */
class AG_Booking_Service {

	/**
	 * Tablo adı
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'ag_booking_services';
	}

	/**
	 * Booking için servis ekle
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $service    Servis verisi (service_id, staff_id, price, duration, sort_order).
	 *
	 * @return int|false Insert ID veya false.
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
	 * Booking için çoklu servis ekle
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $services   Servis listesi.
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
	 * Booking'e ait servisleri getir
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return array
	 */
	public function get_by_booking( $booking_id ) {
		global $wpdb;

		$services_table = $wpdb->prefix . 'ag_services';

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
	 * Booking'e ait servisleri sil
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
	 * Booking için toplam fiyatı hesapla
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
	 * Booking için toplam süreyi hesapla
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return int Dakika cinsinden toplam süre.
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
	 * Booking servislerini güncelle
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $services   Yeni servis listesi.
	 *
	 * @return bool
	 */
	public function update_services( $booking_id, $services ) {
		// Önce mevcut servisleri sil.
		$this->delete_by_booking( $booking_id );

		// Yeni servisleri ekle.
		return $this->add_multiple( $booking_id, $services );
	}

	/**
	 * Servis kullanım sayısını getir
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
