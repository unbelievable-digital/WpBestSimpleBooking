<?php
/**
 * Booking model sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking sınıfı
 */
class AG_Booking {

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
	private $table = 'bookings';

	/**
	 * Geçerli durumlar
	 *
	 * @var array
	 */
	private $valid_statuses = array( 'pending', 'confirmed', 'cancelled', 'completed', 'no_show' );

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new AG_Database();
	}

	/**
	 * Randevu oluştur
	 *
	 * @param array $data Randevu verileri.
	 *
	 * @return int|WP_Error
	 */
	public function create( $data ) {
		// Çoklu hizmet kontrolü
		$is_multi_service = ! empty( $data['service_ids'] );
		$service_ids      = array();

		if ( $is_multi_service ) {
			// JSON string ise decode et
			if ( is_string( $data['service_ids'] ) ) {
				$service_ids = json_decode( $data['service_ids'], true );
			} else {
				$service_ids = $data['service_ids'];
			}

			// Geçerli array kontrolü
			if ( ! is_array( $service_ids ) || empty( $service_ids ) ) {
				return new WP_Error(
					'invalid_services',
					__( 'Geçersiz hizmet listesi.', 'appointment-general' )
				);
			}

			// İlk hizmeti ana service_id olarak ata (geriye uyumluluk)
			$data['service_id'] = $service_ids[0];
		}

		// Zorunlu alanları kontrol et
		$required = array( 'service_id', 'staff_id', 'customer_name', 'customer_email', 'booking_date', 'start_time' );

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error(
					'missing_field',
					sprintf( __( '%s alanı zorunludur.', 'appointment-general' ), $field )
				);
			}
		}

		// Çakışma kontrolü
		if ( $this->has_conflict( $data ) ) {
			return new WP_Error(
				'booking_conflict',
				__( 'Bu saat diliminde başka bir randevu mevcut.', 'appointment-general' )
			);
		}

		$sanitized = $this->sanitize_data( $data );

		// Token oluştur
		$sanitized['token'] = $this->generate_token();

		// Çoklu hizmet modunda süre ve fiyat hesapla
		$service_model = new AG_Service();

		if ( $is_multi_service ) {
			$total_duration = 0;
			$total_price    = 0;
			$services_data  = array();

			foreach ( $service_ids as $index => $sid ) {
				$service = $service_model->get( absint( $sid ) );
				if ( $service ) {
					$total_duration += $service->duration;
					$total_price    += floatval( $service->price );

					$services_data[] = array(
						'service_id' => absint( $sid ),
						'staff_id'   => $sanitized['staff_id'],
						'price'      => floatval( $service->price ),
						'duration'   => intval( $service->duration ),
						'sort_order' => $index,
					);

					// Son hizmetin buffer_after'ını ekle
					if ( $index === count( $service_ids ) - 1 ) {
						$total_duration += $service->buffer_after;
					}
				}
			}

			$sanitized['total_duration'] = $total_duration;
			$sanitized['price']          = $total_price;

			// Bitiş saatini hesapla
			$start                     = strtotime( $sanitized['start_time'] );
			$sanitized['end_time']     = gmdate( 'H:i:s', $start + ( $total_duration * 60 ) );
		} else {
			// Tekli hizmet - mevcut mantık
			if ( empty( $sanitized['end_time'] ) ) {
				$service = $service_model->get( $sanitized['service_id'] );

				if ( $service ) {
					$start    = strtotime( $sanitized['start_time'] );
					$duration = $service->duration + $service->buffer_after;
					$sanitized['end_time'] = gmdate( 'H:i:s', $start + ( $duration * 60 ) );

					if ( empty( $sanitized['price'] ) ) {
						$sanitized['price'] = $service->price;
					}

					$sanitized['total_duration'] = $duration;
				}
			}
		}

		// Müşteriyi oluştur veya getir
		$customer_model = new AG_Customer();
		$customer       = $customer_model->find_or_create( array(
			'name'  => $sanitized['customer_name'],
			'email' => $sanitized['customer_email'],
			'phone' => $sanitized['customer_phone'] ?? '',
		) );

		if ( $customer ) {
			$sanitized['customer_id'] = $customer->id;
		}

		$booking_id = $this->db->insert( $this->table, $sanitized );

		if ( $booking_id ) {
			// Çoklu hizmet ise booking_services tablosuna kaydet
			if ( $is_multi_service && ! empty( $services_data ) ) {
				$booking_service = new AG_Booking_Service();
				$booking_service->add_multiple( $booking_id, $services_data );
			}

			do_action( 'ag_after_booking_created', $booking_id, $sanitized );
		}

		return $booking_id;
	}

	/**
	 * Randevu güncelle
	 *
	 * @param int   $id   Randevu ID.
	 * @param array $data Randevu verileri.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Randevu durumunu güncelle
	 *
	 * @param int    $id     Randevu ID.
	 * @param string $status Yeni durum.
	 *
	 * @return bool
	 */
	public function update_status( $id, $status ) {
		if ( ! in_array( $status, $this->valid_statuses, true ) ) {
			return false;
		}

		$booking    = $this->get( $id );
		$old_status = $booking ? $booking->status : '';

		$result = $this->db->update(
			$this->table,
			array( 'status' => $status ),
			array( 'id' => $id )
		);

		if ( $result ) {
			do_action( 'ag_booking_status_changed', $id, $status, $old_status );
		}

		return (bool) $result;
	}

	/**
	 * Randevu sil
	 *
	 * @param int $id Randevu ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Randevu getir
	 *
	 * @param int $id Randevu ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Token ile randevu getir
	 *
	 * @param string $token Token.
	 *
	 * @return object|null
	 */
	public function get_by_token( $token ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$prefix}bookings WHERE token = %s",
			sanitize_text_field( $token )
		);

		return $wpdb->get_row( $sql );
	}

	/**
	 * Detaylı randevu getir
	 *
	 * @param int $id Randevu ID.
	 *
	 * @return object|null
	 */
	public function get_with_details( $id ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = $wpdb->prepare(
			"SELECT b.*,
				s.name as service_name, s.duration as service_duration, s.color as service_color,
				st.name as staff_name, st.email as staff_email, st.phone as staff_phone
			FROM {$prefix}bookings b
			LEFT JOIN {$prefix}services s ON b.service_id = s.id
			LEFT JOIN {$prefix}staff st ON b.staff_id = st.id
			WHERE b.id = %d",
			$id
		);

		$booking = $wpdb->get_row( $sql );

		if ( $booking ) {
			// Çoklu hizmet listesini getir
			$booking_service = new AG_Booking_Service();
			$services        = $booking_service->get_by_booking( $id );

			if ( ! empty( $services ) ) {
				$booking->services = $services;

				// Hizmet isimlerini de ekle
				$service_names = array();
				foreach ( $services as $svc ) {
					if ( ! empty( $svc->service_name ) ) {
						$service_names[] = $svc->service_name;
					}
				}
				$booking->services_list = implode( ', ', $service_names );
			} else {
				// Tekli hizmet için geriye uyumluluk
				$booking->services      = array();
				$booking->services_list = $booking->service_name;
			}
		}

		return $booking;
	}

	/**
	 * Randevuları getir
	 *
	 * @param array $args Argümanlar.
	 *
	 * @return array
	 */
	public function get_all( $args = array() ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$defaults = array(
			'staff_id'   => 0,
			'service_id' => 0,
			'status'     => '',
			'date_from'  => '',
			'date_to'    => '',
			'orderby'    => 'booking_date',
			'order'      => 'ASC',
			'limit'      => 0,
			'offset'     => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "SELECT b.*,
				s.name as service_name, s.color as service_color,
				st.name as staff_name
			FROM {$prefix}bookings b
			LEFT JOIN {$prefix}services s ON b.service_id = s.id
			LEFT JOIN {$prefix}staff st ON b.staff_id = st.id
			WHERE 1=1";

		$prepare_args = array();

		if ( $args['staff_id'] ) {
			$sql           .= ' AND b.staff_id = %d';
			$prepare_args[] = $args['staff_id'];
		}

		if ( $args['service_id'] ) {
			$sql           .= ' AND b.service_id = %d';
			$prepare_args[] = $args['service_id'];
		}

		if ( $args['status'] ) {
			$sql           .= ' AND b.status = %s';
			$prepare_args[] = $args['status'];
		}

		if ( $args['date_from'] ) {
			$sql           .= ' AND b.booking_date >= %s';
			$prepare_args[] = $args['date_from'];
		}

		if ( $args['date_to'] ) {
			$sql           .= ' AND b.booking_date <= %s';
			$prepare_args[] = $args['date_to'];
		}

		$sql .= sprintf(
			' ORDER BY b.%s %s',
			esc_sql( $args['orderby'] ),
			esc_sql( $args['order'] )
		);

		if ( $args['limit'] > 0 ) {
			$sql           .= ' LIMIT %d OFFSET %d';
			$prepare_args[] = $args['limit'];
			$prepare_args[] = $args['offset'];
		}

		if ( ! empty( $prepare_args ) ) {
			$sql = $wpdb->prepare( $sql, $prepare_args );
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Tarihe göre randevuları getir
	 *
	 * @param string $date     Tarih (Y-m-d).
	 * @param int    $staff_id Personel ID.
	 *
	 * @return array
	 */
	public function get_by_date( $date, $staff_id = 0 ) {
		$args = array(
			'date_from' => $date,
			'date_to'   => $date,
			'orderby'   => 'start_time',
		);

		if ( $staff_id ) {
			$args['staff_id'] = $staff_id;
		}

		return $this->get_all( $args );
	}

	/**
	 * Personel ve tarih aralığına göre randevuları getir
	 *
	 * @param int    $staff_id   Personel ID.
	 * @param string $date_from  Başlangıç tarihi.
	 * @param string $date_to    Bitiş tarihi.
	 *
	 * @return array
	 */
	public function get_by_staff_and_date_range( $staff_id, $date_from, $date_to ) {
		return $this->get_all( array(
			'staff_id'  => $staff_id,
			'date_from' => $date_from,
			'date_to'   => $date_to,
			'orderby'   => 'booking_date',
		) );
	}

	/**
	 * Randevu çakışması kontrol et
	 *
	 * @param array $data       Randevu verileri.
	 * @param int   $exclude_id Hariç tutulacak randevu ID.
	 *
	 * @return bool
	 */
	public function has_conflict( $data, $exclude_id = 0 ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		// Bitiş saatini hesapla
		$end_time = $data['end_time'] ?? '';

		if ( empty( $end_time ) ) {
			// Önce total_duration kontrol et (çoklu hizmet)
			if ( ! empty( $data['total_duration'] ) ) {
				$start    = strtotime( $data['start_time'] );
				$end_time = gmdate( 'H:i:s', $start + ( absint( $data['total_duration'] ) * 60 ) );
			} else {
				// Tekli hizmet için mevcut mantık
				$service_model = new AG_Service();
				$service       = $service_model->get( $data['service_id'] );

				if ( $service ) {
					$start    = strtotime( $data['start_time'] );
					$duration = $service->duration + $service->buffer_after;
					$end_time = gmdate( 'H:i:s', $start + ( $duration * 60 ) );
				}
			}
		}

		$sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$prefix}bookings
			WHERE staff_id = %d
			AND booking_date = %s
			AND status NOT IN ('cancelled')
			AND (
				(start_time <= %s AND end_time > %s)
				OR (start_time < %s AND end_time >= %s)
				OR (start_time >= %s AND end_time <= %s)
			)",
			$data['staff_id'],
			$data['booking_date'],
			$data['start_time'],
			$data['start_time'],
			$end_time,
			$end_time,
			$data['start_time'],
			$end_time
		);

		if ( $exclude_id ) {
			$sql .= $wpdb->prepare( ' AND id != %d', $exclude_id );
		}

		return (int) $wpdb->get_var( $sql ) > 0;
	}

	/**
	 * Randevu sayısını getir
	 *
	 * @param string $status Durum filtresi.
	 *
	 * @return int
	 */
	public function count( $status = '' ) {
		$where = array();

		if ( $status ) {
			$where['status'] = $status;
		}

		return $this->db->count( $this->table, $where );
	}

	/**
	 * Bugünkü randevu sayısını getir
	 *
	 * @return int
	 */
	public function count_today() {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';
		$today  = current_time( 'Y-m-d' );

		$sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$prefix}bookings WHERE booking_date = %s AND status NOT IN ('cancelled')",
			$today
		);

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Token oluştur
	 *
	 * @return string
	 */
	private function generate_token() {
		return wp_generate_password( 32, false );
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

		if ( isset( $data['service_id'] ) ) {
			$sanitized['service_id'] = absint( $data['service_id'] );
		}

		if ( isset( $data['staff_id'] ) ) {
			$sanitized['staff_id'] = absint( $data['staff_id'] );
		}

		if ( isset( $data['customer_id'] ) ) {
			$sanitized['customer_id'] = absint( $data['customer_id'] );
		}

		if ( isset( $data['customer_name'] ) ) {
			$sanitized['customer_name'] = sanitize_text_field( $data['customer_name'] );
		}

		if ( isset( $data['customer_email'] ) ) {
			$sanitized['customer_email'] = sanitize_email( $data['customer_email'] );
		}

		if ( isset( $data['customer_phone'] ) ) {
			$sanitized['customer_phone'] = sanitize_text_field( $data['customer_phone'] );
		}

		if ( isset( $data['booking_date'] ) ) {
			$sanitized['booking_date'] = sanitize_text_field( $data['booking_date'] );
		}

		if ( isset( $data['start_time'] ) ) {
			$sanitized['start_time'] = sanitize_text_field( $data['start_time'] );
		}

		if ( isset( $data['end_time'] ) ) {
			$sanitized['end_time'] = sanitize_text_field( $data['end_time'] );
		}

		if ( isset( $data['price'] ) ) {
			$sanitized['price'] = floatval( $data['price'] );
		}

		if ( isset( $data['status'] ) ) {
			$sanitized['status'] = in_array( $data['status'], $this->valid_statuses, true )
				? $data['status']
				: 'pending';
		}

		if ( isset( $data['notes'] ) ) {
			$sanitized['notes'] = sanitize_textarea_field( $data['notes'] );
		}

		if ( isset( $data['internal_notes'] ) ) {
			$sanitized['internal_notes'] = sanitize_textarea_field( $data['internal_notes'] );
		}

		if ( isset( $data['total_duration'] ) ) {
			$sanitized['total_duration'] = absint( $data['total_duration'] );
		}

		return $sanitized;
	}
}
