<?php
/**
 * Calendar sınıfı - Slot hesaplama ve takvim işlemleri
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calendar sınıfı
 */
class AG_Calendar {

	/**
	 * Staff model
	 *
	 * @var AG_Staff
	 */
	private $staff_model;

	/**
	 * Booking model
	 *
	 * @var AG_Booking
	 */
	private $booking_model;

	/**
	 * Service model
	 *
	 * @var AG_Service
	 */
	private $service_model;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->staff_model   = new AG_Staff();
		$this->booking_model = new AG_Booking();
		$this->service_model = new AG_Service();
	}

	/**
	 * Müsait slotları getir
	 *
	 * @param int    $staff_id           Personel ID.
	 * @param int    $service_id         Servis ID.
	 * @param string $date               Tarih (Y-m-d).
	 * @param int    $exclude_booking_id Hariç tutulacak randevu ID (opsiyonel).
	 *
	 * @return array
	 */
	public function get_available_slots( $staff_id, $service_id, $date, $exclude_booking_id = 0 ) {
		// Servisi getir
		$service = $this->service_model->get( $service_id );

		if ( ! $service ) {
			return array();
		}

		// Geçmiş tarih kontrolü
		$today = current_time( 'Y-m-d' );
		if ( $date < $today ) {
			return array();
		}

		// Tatil kontrolü
		if ( $this->is_holiday( $staff_id, $date ) ) {
			return array();
		}

		// Haftanın gününü al
		$day_of_week = (int) gmdate( 'w', strtotime( $date ) );

		// Çalışma saatlerini getir
		$working_hours = $this->get_working_hours_for_day( $staff_id, $day_of_week );

		if ( empty( $working_hours ) || ! $working_hours->is_working ) {
			return array();
		}

		// Mevcut randevuları getir
		$bookings = $this->booking_model->get_by_date( $date, $staff_id );

		// Hariç tutulacak randevuyu filtrele.
		if ( $exclude_booking_id > 0 ) {
			$bookings = array_filter( $bookings, function( $booking ) use ( $exclude_booking_id ) {
				return (int) $booking->id !== (int) $exclude_booking_id;
			} );
		}

		// Molaları getir
		$breaks = $this->staff_model->get_breaks( $staff_id, $day_of_week );

		// Slotları hesapla
		$slots = $this->calculate_slots(
			$working_hours->start_time,
			$working_hours->end_time,
			$service->duration + $service->buffer_before + $service->buffer_after,
			$bookings,
			$breaks,
			$date
		);

		return apply_filters( 'ag_filter_available_slots', $slots, $date, $staff_id, $service_id );
	}

	/**
	 * Belirli bir süreye göre müsait slotları getir (çoklu hizmet için)
	 *
	 * @param int    $staff_id           Personel ID.
	 * @param string $date               Tarih (Y-m-d).
	 * @param int    $total_duration     Toplam süre (dakika).
	 * @param int    $exclude_booking_id Hariç tutulacak randevu ID (opsiyonel).
	 *
	 * @return array
	 */
	public function get_available_slots_by_duration( $staff_id, $date, $total_duration, $exclude_booking_id = 0 ) {
		// Geçmiş tarih kontrolü
		$today = current_time( 'Y-m-d' );
		if ( $date < $today ) {
			return array();
		}

		// Tatil kontrolü
		if ( $this->is_holiday( $staff_id, $date ) ) {
			return array();
		}

		// Haftanın gününü al
		$day_of_week = (int) gmdate( 'w', strtotime( $date ) );

		// Çalışma saatlerini getir
		$working_hours = $this->get_working_hours_for_day( $staff_id, $day_of_week );

		if ( empty( $working_hours ) || ! $working_hours->is_working ) {
			return array();
		}

		// Mevcut randevuları getir
		$bookings = $this->booking_model->get_by_date( $date, $staff_id );

		// Hariç tutulacak randevuyu filtrele
		if ( $exclude_booking_id > 0 ) {
			$bookings = array_filter( $bookings, function( $booking ) use ( $exclude_booking_id ) {
				return (int) $booking->id !== (int) $exclude_booking_id;
			} );
		}

		// Molaları getir
		$breaks = $this->staff_model->get_breaks( $staff_id, $day_of_week );

		// Slotları hesapla
		$slots = $this->calculate_slots(
			$working_hours->start_time,
			$working_hours->end_time,
			$total_duration,
			$bookings,
			$breaks,
			$date
		);

		return apply_filters( 'ag_filter_available_slots_by_duration', $slots, $date, $staff_id, $total_duration );
	}

	/**
	 * Slotları hesapla
	 *
	 * @param string $start_time Başlangıç saati.
	 * @param string $end_time   Bitiş saati.
	 * @param int    $duration   Süre (dakika).
	 * @param array  $bookings   Mevcut randevular.
	 * @param array  $breaks     Molalar.
	 * @param string $date       Tarih.
	 *
	 * @return array
	 */
	private function calculate_slots( $start_time, $end_time, $duration, $bookings, $breaks, $date ) {
		$slots    = array();
		$interval = (int) get_option( 'ag_time_slot_interval', 30 );

		$start = strtotime( $date . ' ' . $start_time );
		$end   = strtotime( $date . ' ' . $end_time );

		// Lead time kontrolü (minimum önceden randevu süresi)
		$lead_time   = (int) get_option( 'ag_booking_lead_time', 60 );
		$current     = current_time( 'timestamp' );
		$min_booking = $current + ( $lead_time * 60 );

		while ( $start + ( $duration * 60 ) <= $end ) {
			$slot_start = gmdate( 'H:i', $start );
			$slot_end   = gmdate( 'H:i', $start + ( $duration * 60 ) );

			// Geçmiş saat kontrolü
			if ( $start >= $min_booking ) {
				// Çakışma kontrolü
				if ( ! $this->is_slot_booked( $slot_start, $slot_end, $bookings ) &&
					 ! $this->is_slot_in_break( $slot_start, $slot_end, $breaks ) ) {
					$slots[] = array(
						'start'     => $slot_start,
						'end'       => $slot_end,
						'available' => true,
					);
				}
			}

			$start += $interval * 60;
		}

		return $slots;
	}

	/**
	 * Slot dolu mu kontrol et
	 *
	 * @param string $slot_start Slot başlangıç.
	 * @param string $slot_end   Slot bitiş.
	 * @param array  $bookings   Mevcut randevular.
	 *
	 * @return bool
	 */
	private function is_slot_booked( $slot_start, $slot_end, $bookings ) {
		foreach ( $bookings as $booking ) {
			if ( 'cancelled' === $booking->status ) {
				continue;
			}

			$booking_start = substr( $booking->start_time, 0, 5 );
			$booking_end   = substr( $booking->end_time, 0, 5 );

			// Çakışma kontrolü
			if ( $slot_start < $booking_end && $slot_end > $booking_start ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Slot mola zamanında mı kontrol et
	 *
	 * @param string $slot_start Slot başlangıç.
	 * @param string $slot_end   Slot bitiş.
	 * @param array  $breaks     Molalar.
	 *
	 * @return bool
	 */
	private function is_slot_in_break( $slot_start, $slot_end, $breaks ) {
		foreach ( $breaks as $break ) {
			$break_start = substr( $break->start_time, 0, 5 );
			$break_end   = substr( $break->end_time, 0, 5 );

			if ( $slot_start < $break_end && $slot_end > $break_start ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Günün çalışma saatlerini getir
	 *
	 * @param int $staff_id    Personel ID.
	 * @param int $day_of_week Haftanın günü.
	 *
	 * @return object|null
	 */
	private function get_working_hours_for_day( $staff_id, $day_of_week ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$prefix}working_hours WHERE staff_id = %d AND day_of_week = %d",
			$staff_id,
			$day_of_week
		);

		return $wpdb->get_row( $sql );
	}

	/**
	 * Tatil günü mü kontrol et
	 *
	 * @param int    $staff_id Personel ID.
	 * @param string $date     Tarih.
	 *
	 * @return bool
	 */
	public function is_holiday( $staff_id, $date ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'ag_';

		// Personele özel veya genel tatil kontrolü
		$sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$prefix}holidays
			WHERE date = %s AND (staff_id = %d OR staff_id IS NULL)",
			$date,
			$staff_id
		);

		return (int) $wpdb->get_var( $sql ) > 0;
	}

	/**
	 * Müsait günleri getir
	 *
	 * @param int    $staff_id   Personel ID.
	 * @param int    $service_id Servis ID.
	 * @param string $month      Ay (Y-m).
	 *
	 * @return array
	 */
	public function get_available_days( $staff_id, $service_id, $month ) {
		$days       = array();
		$year_month = explode( '-', $month );
		$year       = (int) $year_month[0];
		$month_num  = (int) $year_month[1];

		$days_in_month = cal_days_in_month( CAL_GREGORIAN, $month_num, $year );
		$today         = current_time( 'Y-m-d' );
		$max_future    = (int) get_option( 'ag_booking_future_days', 30 );
		$max_date      = gmdate( 'Y-m-d', strtotime( "+{$max_future} days" ) );

		for ( $day = 1; $day <= $days_in_month; $day++ ) {
			$date = sprintf( '%04d-%02d-%02d', $year, $month_num, $day );

			// Geçmiş ve çok ileri tarih kontrolü
			if ( $date < $today || $date > $max_date ) {
				$days[ $date ] = false;
				continue;
			}

			// Müsait slot var mı kontrol et
			$slots = $this->get_available_slots( $staff_id, $service_id, $date );
			$days[ $date ] = ! empty( $slots );
		}

		return $days;
	}

	/**
	 * Takvim verilerini getir (admin için)
	 *
	 * @param string $start_date Başlangıç tarihi.
	 * @param string $end_date   Bitiş tarihi.
	 * @param int    $staff_id   Personel ID (opsiyonel).
	 *
	 * @return array
	 */
	public function get_calendar_events( $start_date, $end_date, $staff_id = 0 ) {
		$args = array(
			'date_from' => $start_date,
			'date_to'   => $end_date,
		);

		if ( $staff_id ) {
			$args['staff_id'] = $staff_id;
		}

		$bookings = $this->booking_model->get_all( $args );
		$events   = array();

		foreach ( $bookings as $booking ) {
			$events[] = array(
				'id'              => $booking->id,
				'title'           => $booking->customer_name . ' - ' . $booking->service_name,
				'start'           => $booking->booking_date . 'T' . $booking->start_time,
				'end'             => $booking->booking_date . 'T' . $booking->end_time,
				'backgroundColor' => $this->get_status_color( $booking->status ),
				'borderColor'     => $booking->service_color ?? '#3788d8',
				'extendedProps'   => array(
					'status'         => $booking->status,
					'customer_name'  => $booking->customer_name,
					'customer_email' => $booking->customer_email,
					'customer_phone' => $booking->customer_phone,
					'service_name'   => $booking->service_name,
					'staff_name'     => $booking->staff_name,
					'price'          => $booking->price,
				),
			);
		}

		return $events;
	}

	/**
	 * Durum rengini getir
	 *
	 * @param string $status Durum.
	 *
	 * @return string
	 */
	private function get_status_color( $status ) {
		$colors = array(
			'pending'   => '#f59e0b',
			'confirmed' => '#10b981',
			'cancelled' => '#ef4444',
			'completed' => '#6b7280',
			'no_show'   => '#8b5cf6',
		);

		return $colors[ $status ] ?? '#3788d8';
	}

	/**
	 * Belirli bir slot müsait mi kontrol et
	 *
	 * @param int    $staff_id           Personel ID.
	 * @param int    $service_id         Servis ID.
	 * @param string $date               Tarih (Y-m-d).
	 * @param string $time               Saat (H:i).
	 * @param int    $exclude_booking_id Hariç tutulacak randevu ID (opsiyonel).
	 *
	 * @return bool
	 */
	public function is_slot_available( $staff_id, $service_id, $date, $time, $exclude_booking_id = 0 ) {
		$available_slots = $this->get_available_slots( $staff_id, $service_id, $date, $exclude_booking_id );

		// H:i formatına normalize et.
		$time_normalized = substr( $time, 0, 5 );

		foreach ( $available_slots as $slot ) {
			if ( $slot['start'] === $time_normalized ) {
				return true;
			}
		}

		return false;
	}
}
