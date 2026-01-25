<?php
/**
 * Staff model sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Staff sınıfı
 */
class AG_Staff {

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
	private $table = 'staff';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new AG_Database();
	}

	/**
	 * Personel oluştur
	 *
	 * @param array $data Personel verileri.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );
		$staff_id  = $this->db->insert( $this->table, $sanitized );

		if ( $staff_id && ! empty( $data['services'] ) ) {
			$this->sync_services( $staff_id, $data['services'] );
		}

		// Varsayılan çalışma saatlerini oluştur
		if ( $staff_id ) {
			$this->create_default_working_hours( $staff_id );
		}

		return $staff_id;
	}

	/**
	 * Personel güncelle
	 *
	 * @param int   $id   Personel ID.
	 * @param array $data Personel verileri.
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
	 * Personel sil
	 *
	 * @param int $id Personel ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		// İlişkili verileri sil
		$this->db->delete( 'staff_services', array( 'staff_id' => $id ) );
		$this->db->delete( 'working_hours', array( 'staff_id' => $id ) );
		$this->db->delete( 'breaks', array( 'staff_id' => $id ) );
		$this->db->delete( 'holidays', array( 'staff_id' => $id ) );

		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Personel getir
	 *
	 * @param int $id Personel ID.
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
	 * Tüm personeli getir
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
	 * Aktif personeli getir
	 *
	 * @return array
	 */
	public function get_active() {
		return $this->get_all( array(
			'where' => array( 'status' => 'active' ),
		) );
	}

	/**
	 * Servise göre personeli getir
	 *
	 * @param int $service_id Servis ID.
	 *
	 * @return array
	 */
	public function get_by_service( $service_id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = $wpdb->prepare(
			"SELECT st.*
			FROM {$prefix}staff st
			INNER JOIN {$prefix}staff_services ss ON st.id = ss.staff_id
			WHERE ss.service_id = %d AND st.status = 'active'
			ORDER BY st.sort_order ASC",
			$service_id
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Personelin servislerini getir
	 *
	 * @param int $staff_id Personel ID.
	 *
	 * @return array
	 */
	public function get_services( $staff_id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = $wpdb->prepare(
			"SELECT service_id FROM {$prefix}staff_services WHERE staff_id = %d",
			$staff_id
		);

		return $wpdb->get_col( $sql );
	}

	/**
	 * Servisleri senkronize et
	 *
	 * @param int   $staff_id Personel ID.
	 * @param array $services Servis ID'leri.
	 */
	public function sync_services( $staff_id, $services ) {
		// Mevcut servisleri sil
		$this->db->delete( 'staff_services', array( 'staff_id' => $staff_id ) );

		// Yeni servisleri ekle
		foreach ( $services as $service_id ) {
			$this->db->insert( 'staff_services', array(
				'staff_id'   => $staff_id,
				'service_id' => absint( $service_id ),
			) );
		}
	}

	/**
	 * Çalışma saatlerini getir
	 *
	 * @param int $staff_id Personel ID.
	 *
	 * @return array
	 */
	public function get_working_hours( $staff_id ) {
		return $this->db->get_all( 'working_hours', array(
			'where'   => array( 'staff_id' => $staff_id ),
			'orderby' => 'day_of_week',
			'order'   => 'ASC',
		) );
	}

	/**
	 * Çalışma saatlerini güncelle
	 *
	 * @param int   $staff_id Personel ID.
	 * @param array $hours    Çalışma saatleri.
	 */
	public function update_working_hours( $staff_id, $hours ) {
		// Mevcut saatleri sil
		$this->db->delete( 'working_hours', array( 'staff_id' => $staff_id ) );

		// Yeni saatleri ekle
		foreach ( $hours as $hour ) {
			$this->db->insert( 'working_hours', array(
				'staff_id'    => $staff_id,
				'day_of_week' => absint( $hour['day_of_week'] ),
				'start_time'  => sanitize_text_field( $hour['start_time'] ),
				'end_time'    => sanitize_text_field( $hour['end_time'] ),
				'is_working'  => isset( $hour['is_working'] ) ? absint( $hour['is_working'] ) : 1,
			) );
		}
	}

	/**
	 * Varsayılan çalışma saatlerini oluştur
	 *
	 * @param int $staff_id Personel ID.
	 */
	private function create_default_working_hours( $staff_id ) {
		// Pazartesi - Cumartesi 09:00 - 18:00
		for ( $day = 1; $day <= 6; $day++ ) {
			$this->db->insert( 'working_hours', array(
				'staff_id'    => $staff_id,
				'day_of_week' => $day,
				'start_time'  => '09:00:00',
				'end_time'    => '18:00:00',
				'is_working'  => 1,
			) );
		}

		// Pazar kapalı
		$this->db->insert( 'working_hours', array(
			'staff_id'    => $staff_id,
			'day_of_week' => 0,
			'start_time'  => '09:00:00',
			'end_time'    => '18:00:00',
			'is_working'  => 0,
		) );
	}

	/**
	 * Molalar getir
	 *
	 * @param int $staff_id    Personel ID.
	 * @param int $day_of_week Haftanın günü.
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
	 * Mola ekle
	 *
	 * @param int    $staff_id    Personel ID.
	 * @param int    $day_of_week Haftanın günü.
	 * @param string $start_time  Başlangıç saati.
	 * @param string $end_time    Bitiş saati.
	 *
	 * @return int|false
	 */
	public function add_break( $staff_id, $day_of_week, $start_time, $end_time ) {
		return $this->db->insert( 'breaks', array(
			'staff_id'    => absint( $staff_id ),
			'day_of_week' => absint( $day_of_week ),
			'start_time'  => sanitize_text_field( $start_time ),
			'end_time'    => sanitize_text_field( $end_time ),
		) );
	}

	/**
	 * Mola sil
	 *
	 * @param int $break_id Mola ID.
	 *
	 * @return int|false
	 */
	public function delete_break( $break_id ) {
		return $this->db->delete( 'breaks', array( 'id' => $break_id ) );
	}

	/**
	 * Personelin tüm molalarını sil
	 *
	 * @param int $staff_id Personel ID.
	 *
	 * @return int|false
	 */
	public function delete_all_breaks( $staff_id ) {
		return $this->db->delete( 'breaks', array( 'staff_id' => $staff_id ) );
	}

	/**
	 * Molaları güncelle (tümünü sil ve yeniden ekle)
	 *
	 * @param int   $staff_id Personel ID.
	 * @param array $breaks   Mola verileri.
	 */
	public function update_breaks( $staff_id, $breaks ) {
		// Mevcut molaları sil.
		$this->delete_all_breaks( $staff_id );

		// Yeni molaları ekle.
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
	 * Tatilleri/izinleri getir
	 *
	 * @param int    $staff_id   Personel ID.
	 * @param string $start_date Başlangıç tarihi (Y-m-d).
	 * @param string $end_date   Bitiş tarihi (Y-m-d).
	 *
	 * @return array
	 */
	public function get_holidays( $staff_id, $start_date = null, $end_date = null ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';
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

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Tatil/izin ekle
	 *
	 * @param int    $staff_id Personel ID.
	 * @param string $date     Tarih (Y-m-d).
	 * @param string $reason   Sebep.
	 *
	 * @return int|false
	 */
	public function add_holiday( $staff_id, $date, $reason = '' ) {
		// Aynı tarihte zaten izin var mı kontrol et.
		global $wpdb;
		$prefix = $wpdb->prefix . 'ag_';

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$prefix}holidays WHERE staff_id = %d AND date = %s",
				$staff_id,
				$date
			)
		);

		if ( $exists ) {
			return false; // Zaten izinli.
		}

		return $this->db->insert( 'holidays', array(
			'staff_id' => absint( $staff_id ),
			'date'     => sanitize_text_field( $date ),
			'reason'   => sanitize_text_field( $reason ),
		) );
	}

	/**
	 * Tatil/izin sil
	 *
	 * @param int $holiday_id Tatil ID.
	 *
	 * @return int|false
	 */
	public function delete_holiday( $holiday_id ) {
		return $this->db->delete( 'holidays', array( 'id' => $holiday_id ) );
	}

	/**
	 * Tarihe göre tatil sil
	 *
	 * @param int    $staff_id Personel ID.
	 * @param string $date     Tarih (Y-m-d).
	 *
	 * @return int|false
	 */
	public function delete_holiday_by_date( $staff_id, $date ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'ag_';

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
	 * Personel sayısını getir
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
