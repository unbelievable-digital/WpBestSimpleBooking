<?php
/**
 * Booking Manager sınıfı - İptal ve yeniden planlama işlemleri
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Manager sınıfı
 */
class AG_Booking_Manager {

	/**
	 * Booking model instance
	 *
	 * @var AG_Booking
	 */
	private $booking_model;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->booking_model = new AG_Booking();
	}

	/**
	 * Token ile randevu getir
	 *
	 * @param string $token Randevu token.
	 *
	 * @return object|false Randevu objesi veya false.
	 */
	public function get_booking_by_token( $token ) {
		if ( empty( $token ) ) {
			return false;
		}

		return $this->booking_model->get_by_token( $token );
	}

	/**
	 * Randevu iptal edilebilir mi kontrol et
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return array ['can_cancel' => bool, 'reason' => string]
	 */
	public function can_cancel( $booking ) {
		// İptal özelliği aktif mi?
		if ( 'yes' !== get_option( 'ag_allow_cancel', 'yes' ) ) {
			return array(
				'can_cancel' => false,
				'reason'     => __( 'Randevu iptali devre dışı bırakılmış.', 'appointment-general' ),
			);
		}

		// Randevu zaten iptal edilmiş mi?
		if ( 'cancelled' === $booking->status ) {
			return array(
				'can_cancel' => false,
				'reason'     => __( 'Bu randevu zaten iptal edilmiş.', 'appointment-general' ),
			);
		}

		// Randevu tamamlanmış mı?
		if ( 'completed' === $booking->status ) {
			return array(
				'can_cancel' => false,
				'reason'     => __( 'Tamamlanmış randevular iptal edilemez.', 'appointment-general' ),
			);
		}

		// Son iptal saati geçmiş mi?
		$deadline_hours = absint( get_option( 'ag_cancel_deadline_hours', 24 ) );
		$deadline_check = $this->check_deadline( $booking, $deadline_hours );

		if ( ! $deadline_check['passed'] ) {
			return array(
				'can_cancel' => false,
				'reason'     => sprintf(
					/* translators: %d: Number of hours */
					__( 'Randevuya %d saatten az kaldığı için iptal edilemez.', 'appointment-general' ),
					$deadline_hours
				),
			);
		}

		return array(
			'can_cancel' => true,
			'reason'     => '',
		);
	}

	/**
	 * Randevu yeniden planlanabilir mi kontrol et
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return array ['can_reschedule' => bool, 'reason' => string]
	 */
	public function can_reschedule( $booking ) {
		// Yeniden planlama özelliği aktif mi?
		if ( 'yes' !== get_option( 'ag_allow_reschedule', 'yes' ) ) {
			return array(
				'can_reschedule' => false,
				'reason'         => __( 'Randevu değiştirme devre dışı bırakılmış.', 'appointment-general' ),
			);
		}

		// Randevu iptal edilmiş mi?
		if ( 'cancelled' === $booking->status ) {
			return array(
				'can_reschedule' => false,
				'reason'         => __( 'İptal edilmiş randevular değiştirilemez.', 'appointment-general' ),
			);
		}

		// Randevu tamamlanmış mı?
		if ( 'completed' === $booking->status ) {
			return array(
				'can_reschedule' => false,
				'reason'         => __( 'Tamamlanmış randevular değiştirilemez.', 'appointment-general' ),
			);
		}

		// Maksimum yeniden planlama sayısına ulaşılmış mı?
		$max_reschedules    = absint( get_option( 'ag_max_reschedules', 2 ) );
		$reschedule_count   = isset( $booking->reschedule_count ) ? absint( $booking->reschedule_count ) : 0;

		if ( $reschedule_count >= $max_reschedules ) {
			return array(
				'can_reschedule' => false,
				'reason'         => sprintf(
					/* translators: %d: Maximum number of reschedules */
					__( 'Randevu en fazla %d kez değiştirilebilir.', 'appointment-general' ),
					$max_reschedules
				),
			);
		}

		// Son değiştirme saati geçmiş mi?
		$deadline_hours = absint( get_option( 'ag_reschedule_deadline_hours', 24 ) );
		$deadline_check = $this->check_deadline( $booking, $deadline_hours );

		if ( ! $deadline_check['passed'] ) {
			return array(
				'can_reschedule' => false,
				'reason'         => sprintf(
					/* translators: %d: Number of hours */
					__( 'Randevuya %d saatten az kaldığı için değiştirilemez.', 'appointment-general' ),
					$deadline_hours
				),
			);
		}

		return array(
			'can_reschedule' => true,
			'reason'         => '',
			'remaining'      => $max_reschedules - $reschedule_count,
		);
	}

	/**
	 * Deadline kontrolü
	 *
	 * @param object $booking       Randevu objesi.
	 * @param int    $deadline_hours Saat cinsinden deadline.
	 *
	 * @return array ['passed' => bool, 'hours_left' => int]
	 */
	private function check_deadline( $booking, $deadline_hours ) {
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		try {
			$tz              = new DateTimeZone( $timezone );
			$booking_datetime = new DateTime( $booking->booking_date . ' ' . $booking->start_time, $tz );
			$now             = new DateTime( 'now', $tz );

			$diff       = $booking_datetime->getTimestamp() - $now->getTimestamp();
			$hours_left = floor( $diff / 3600 );

			return array(
				'passed'     => $hours_left >= $deadline_hours,
				'hours_left' => max( 0, $hours_left ),
			);
		} catch ( Exception $e ) {
			// Hata durumunda izin ver.
			return array(
				'passed'     => true,
				'hours_left' => 999,
			);
		}
	}

	/**
	 * Randevuyu iptal et
	 *
	 * @param string $token  Randevu token.
	 * @param string $reason İptal nedeni (opsiyonel).
	 *
	 * @return array ['success' => bool, 'message' => string]
	 */
	public function cancel_booking( $token, $reason = '' ) {
		$booking = $this->get_booking_by_token( $token );

		if ( ! $booking ) {
			return array(
				'success' => false,
				'message' => __( 'Randevu bulunamadı.', 'appointment-general' ),
			);
		}

		$can_cancel = $this->can_cancel( $booking );

		if ( ! $can_cancel['can_cancel'] ) {
			return array(
				'success' => false,
				'message' => $can_cancel['reason'],
			);
		}

		// İptal nedenini internal notes'a ekle.
		if ( ! empty( $reason ) ) {
			$internal_notes = $booking->internal_notes ?? '';
			$internal_notes .= "\n" . sprintf(
				/* translators: 1: Date/time, 2: Cancellation reason */
				__( '[%1$s] Müşteri tarafından iptal edildi. Neden: %2$s', 'appointment-general' ),
				current_time( 'mysql' ),
				sanitize_text_field( $reason )
			);

			$this->booking_model->update( $booking->id, array(
				'internal_notes' => $internal_notes,
			) );
		}

		// Durumu güncelle.
		$result = $this->booking_model->update_status( $booking->id, 'cancelled' );

		if ( $result ) {
			/**
			 * Randevu müşteri tarafından iptal edildiğinde tetiklenir.
			 *
			 * @param int    $booking_id Randevu ID.
			 * @param object $booking    Randevu objesi.
			 * @param string $reason     İptal nedeni.
			 */
			do_action( 'ag_booking_cancelled_by_customer', $booking->id, $booking, $reason );

			return array(
				'success' => true,
				'message' => __( 'Randevunuz başarıyla iptal edildi.', 'appointment-general' ),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'Randevu iptal edilirken bir hata oluştu.', 'appointment-general' ),
		);
	}

	/**
	 * Randevuyu yeniden planla
	 *
	 * @param string $token        Randevu token.
	 * @param string $new_date     Yeni tarih (Y-m-d).
	 * @param string $new_time     Yeni saat (H:i).
	 * @param int    $new_staff_id Yeni personel ID (opsiyonel).
	 *
	 * @return array ['success' => bool, 'message' => string, 'booking' => object|null]
	 */
	public function reschedule_booking( $token, $new_date, $new_time, $new_staff_id = null ) {
		$booking = $this->get_booking_by_token( $token );

		if ( ! $booking ) {
			return array(
				'success' => false,
				'message' => __( 'Randevu bulunamadı.', 'appointment-general' ),
				'booking' => null,
			);
		}

		$can_reschedule = $this->can_reschedule( $booking );

		if ( ! $can_reschedule['can_reschedule'] ) {
			return array(
				'success' => false,
				'message' => $can_reschedule['reason'],
				'booking' => null,
			);
		}

		// Tarih/saat validasyonu.
		if ( ! $this->validate_date( $new_date ) ) {
			return array(
				'success' => false,
				'message' => __( 'Geçersiz tarih formatı.', 'appointment-general' ),
				'booking' => null,
			);
		}

		if ( ! $this->validate_time( $new_time ) ) {
			return array(
				'success' => false,
				'message' => __( 'Geçersiz saat formatı.', 'appointment-general' ),
				'booking' => null,
			);
		}

		// Personel değiştirilmemişse mevcut personeli kullan.
		$staff_id = $new_staff_id ? absint( $new_staff_id ) : $booking->staff_id;

		// Yeni slot müsait mi kontrol et.
		$calendar = new AG_Calendar();
		$is_available = $calendar->is_slot_available(
			$staff_id,
			$booking->service_id,
			$new_date,
			$new_time,
			$booking->id // Mevcut randevuyu hariç tut.
		);

		if ( ! $is_available ) {
			return array(
				'success' => false,
				'message' => __( 'Seçilen tarih ve saat müsait değil.', 'appointment-general' ),
				'booking' => null,
			);
		}

		// Servis süresini hesapla.
		$service_model = new AG_Service();
		$service       = $service_model->get( $booking->service_id );

		if ( ! $service ) {
			return array(
				'success' => false,
				'message' => __( 'Hizmet bilgisi bulunamadı.', 'appointment-general' ),
				'booking' => null,
			);
		}

		$duration     = $service->duration + ( $service->buffer_before ?? 0 ) + ( $service->buffer_after ?? 0 );
		$start_time   = new DateTime( $new_time );
		$end_time     = clone $start_time;
		$end_time->add( new DateInterval( 'PT' . $duration . 'M' ) );

		// Eski bilgileri kaydet.
		$old_date = $booking->booking_date;
		$old_time = $booking->start_time;

		// Randevuyu güncelle.
		$reschedule_count = isset( $booking->reschedule_count ) ? absint( $booking->reschedule_count ) + 1 : 1;
		$original_id      = $booking->original_booking_id ?? $booking->id;

		$update_data = array(
			'staff_id'            => $staff_id,
			'booking_date'        => $new_date,
			'start_time'          => $new_time,
			'end_time'            => $end_time->format( 'H:i:s' ),
			'reschedule_count'    => $reschedule_count,
			'original_booking_id' => $original_id,
		);

		// Internal notes güncelle.
		$internal_notes = $booking->internal_notes ?? '';
		$internal_notes .= "\n" . sprintf(
			/* translators: 1: Date/time, 2: Old date, 3: Old time, 4: New date, 5: New time */
			__( '[%1$s] Yeniden planlandı: %2$s %3$s -> %4$s %5$s', 'appointment-general' ),
			current_time( 'mysql' ),
			$old_date,
			$old_time,
			$new_date,
			$new_time
		);
		$update_data['internal_notes'] = $internal_notes;

		$result = $this->booking_model->update( $booking->id, $update_data );

		if ( $result ) {
			// Güncel randevu bilgisini al.
			$updated_booking = $this->booking_model->get_with_details( $booking->id );

			/**
			 * Randevu müşteri tarafından yeniden planlandığında tetiklenir.
			 *
			 * @param int    $booking_id      Randevu ID.
			 * @param object $updated_booking Güncel randevu objesi.
			 * @param string $old_date        Eski tarih.
			 * @param string $old_time        Eski saat.
			 */
			do_action( 'ag_booking_rescheduled_by_customer', $booking->id, $updated_booking, $old_date, $old_time );

			return array(
				'success' => true,
				'message' => __( 'Randevunuz başarıyla güncellendi.', 'appointment-general' ),
				'booking' => $updated_booking,
			);
		}

		return array(
			'success' => false,
			'message' => __( 'Randevu güncellenirken bir hata oluştu.', 'appointment-general' ),
			'booking' => null,
		);
	}

	/**
	 * Tarih validasyonu
	 *
	 * @param string $date Tarih (Y-m-d).
	 *
	 * @return bool
	 */
	private function validate_date( $date ) {
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Saat validasyonu
	 *
	 * @param string $time Saat (H:i veya H:i:s).
	 *
	 * @return bool
	 */
	private function validate_time( $time ) {
		// H:i formatı.
		$t = DateTime::createFromFormat( 'H:i', $time );
		if ( $t && $t->format( 'H:i' ) === $time ) {
			return true;
		}

		// H:i:s formatı.
		$t = DateTime::createFromFormat( 'H:i:s', $time );
		return $t && $t->format( 'H:i:s' ) === $time;
	}

	/**
	 * Randevu yönetim URL'si oluştur
	 *
	 * @param string $token Randevu token.
	 *
	 * @return string URL.
	 */
	public function get_manage_url( $token ) {
		// Shortcode sayfası veya özel sayfa.
		$page_id = get_option( 'ag_manage_booking_page', 0 );

		if ( $page_id ) {
			$url = get_permalink( $page_id );
		} else {
			// Varsayılan olarak home URL kullan.
			$url = home_url( '/' );
		}

		return add_query_arg( array(
			'ag_action' => 'manage_booking',
			'token'     => sanitize_text_field( $token ),
		), $url );
	}

	/**
	 * Mevcut slotları getir (yeniden planlama için)
	 *
	 * @param object $booking Randevu objesi.
	 * @param string $date    Tarih (Y-m-d).
	 *
	 * @return array Müsait slotlar.
	 */
	public function get_available_slots_for_reschedule( $booking, $date ) {
		$calendar = new AG_Calendar();

		// Mevcut randevuyu hariç tutarak slotları getir.
		$slots = $calendar->get_available_slots(
			$booking->staff_id,
			$booking->service_id,
			$date,
			$booking->id
		);

		return $slots;
	}
}
