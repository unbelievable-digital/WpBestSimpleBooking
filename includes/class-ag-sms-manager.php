<?php
/**
 * SMS Manager sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMS Manager
 */
class AG_SMS_Manager {

	/**
	 * SMS Provider instance
	 *
	 * @var AG_SMS_Provider
	 */
	private $provider;

	/**
	 * SMS Queue tablo adı
	 *
	 * @var string
	 */
	private $queue_table;

	/**
	 * SMS Templates tablo adı
	 *
	 * @var string
	 */
	private $templates_table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->queue_table     = $wpdb->prefix . 'ag_sms_queue';
		$this->templates_table = $wpdb->prefix . 'ag_sms_templates';

		$this->init_provider();
		$this->init_hooks();
	}

	/**
	 * SMS provider'ı başlat
	 */
	private function init_provider() {
		$provider = get_option( 'ag_sms_provider', 'netgsm' );

		// Provider dosyalarını yükle.
		require_once AG_PLUGIN_DIR . 'includes/sms/class-ag-sms-provider.php';

		switch ( $provider ) {
			case 'netgsm':
			default:
				require_once AG_PLUGIN_DIR . 'includes/sms/class-ag-sms-netgsm.php';
				$this->provider = new AG_SMS_NetGSM();
				break;
		}
	}

	/**
	 * Hook'ları başlat
	 */
	private function init_hooks() {
		// Booking eventleri.
		add_action( 'ag_after_booking_created', array( $this, 'on_booking_created' ), 20, 2 );
		add_action( 'ag_booking_status_changed', array( $this, 'on_booking_status_changed' ), 20, 3 );

		// Cron işlemleri.
		add_action( 'ag_process_sms_queue', array( $this, 'process_queue' ) );
		add_action( 'ag_schedule_reminders', array( $this, 'schedule_reminders' ) );

		// Cron schedule.
		if ( ! wp_next_scheduled( 'ag_process_sms_queue' ) ) {
			wp_schedule_event( time(), 'every_minute', 'ag_process_sms_queue' );
		}

		if ( ! wp_next_scheduled( 'ag_schedule_reminders' ) ) {
			wp_schedule_event( time(), 'hourly', 'ag_schedule_reminders' );
		}

		// Custom cron interval.
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
	}

	/**
	 * Custom cron interval ekle
	 *
	 * @param array $schedules Mevcut schedule'lar.
	 *
	 * @return array
	 */
	public function add_cron_interval( $schedules ) {
		$schedules['every_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Her Dakika', 'appointment-general' ),
		);
		return $schedules;
	}

	/**
	 * SMS aktif mi kontrol et
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return 'yes' === get_option( 'ag_sms_enabled', 'no' );
	}

	/**
	 * Randevu oluşturulduğunda
	 *
	 * @param int   $booking_id   Randevu ID.
	 * @param array $booking_data Randevu verileri.
	 */
	public function on_booking_created( $booking_id, $booking_data ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( 'yes' !== get_option( 'ag_sms_on_booking', 'yes' ) ) {
			return;
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking || empty( $booking->customer_phone ) ) {
			return;
		}

		$this->queue_sms( $booking_id, $booking->customer_phone, 'booking_created' );

		// Hatırlatma planla.
		$this->schedule_reminder_for_booking( $booking_id );
	}

	/**
	 * Randevu durumu değiştiğinde
	 *
	 * @param int    $booking_id Randevu ID.
	 * @param string $new_status Yeni durum.
	 * @param string $old_status Eski durum.
	 */
	public function on_booking_status_changed( $booking_id, $new_status, $old_status ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking || empty( $booking->customer_phone ) ) {
			return;
		}

		switch ( $new_status ) {
			case 'confirmed':
				if ( 'yes' === get_option( 'ag_sms_on_confirmation', 'yes' ) ) {
					$this->queue_sms( $booking_id, $booking->customer_phone, 'booking_confirmed' );
				}
				break;

			case 'cancelled':
				if ( 'yes' === get_option( 'ag_sms_on_cancellation', 'no' ) ) {
					$this->queue_sms( $booking_id, $booking->customer_phone, 'booking_cancelled' );
					// İptal edilen randevu için hatırlatmayı sil.
					$this->cancel_reminder( $booking_id );
				}
				break;
		}
	}

	/**
	 * SMS'i kuyruğa ekle
	 *
	 * @param int    $booking_id Randevu ID.
	 * @param string $phone      Telefon numarası.
	 * @param string $type       Şablon tipi.
	 * @param string $scheduled  Zamanlanmış gönderim (Y-m-d H:i:s).
	 *
	 * @return int|false Kuyruk ID veya false.
	 */
	public function queue_sms( $booking_id, $phone, $type, $scheduled = null ) {
		global $wpdb;

		$message = $this->render_template( $type, $booking_id );

		if ( empty( $message ) ) {
			return false;
		}

		$scheduled_at = $scheduled ? $scheduled : current_time( 'mysql' );

		$result = $wpdb->insert(
			$this->queue_table,
			array(
				'booking_id'   => $booking_id,
				'phone'        => $phone,
				'message'      => $message,
				'scheduled_at' => $scheduled_at,
				'status'       => 'pending',
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Kuyruğu işle
	 */
	public function process_queue() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		global $wpdb;

		$now = current_time( 'mysql' );

		// Gönderilecek SMS'leri al (max 10).
		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->queue_table}
				WHERE status = 'pending'
				AND scheduled_at <= %s
				AND attempts < 3
				ORDER BY scheduled_at ASC
				LIMIT 10",
				$now
			)
		);

		foreach ( $messages as $sms ) {
			$this->send_queued_sms( $sms );
		}
	}

	/**
	 * Kuyruktaki SMS'i gönder
	 *
	 * @param object $sms SMS kaydı.
	 */
	private function send_queued_sms( $sms ) {
		global $wpdb;

		// Deneme sayısını artır.
		$wpdb->update(
			$this->queue_table,
			array( 'attempts' => $sms->attempts + 1 ),
			array( 'id' => $sms->id ),
			array( '%d' ),
			array( '%d' )
		);

		// SMS gönder.
		$result = $this->provider->send( $sms->phone, $sms->message );

		if ( $result['success'] ) {
			$wpdb->update(
				$this->queue_table,
				array(
					'status'            => 'sent',
					'sent_at'           => current_time( 'mysql' ),
					'provider_response' => wp_json_encode( $result ),
				),
				array( 'id' => $sms->id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			$new_status = ( $sms->attempts + 1 ) >= 3 ? 'failed' : 'pending';

			$wpdb->update(
				$this->queue_table,
				array(
					'status'            => $new_status,
					'provider_response' => wp_json_encode( $result ),
				),
				array( 'id' => $sms->id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Hatırlatma planla
	 *
	 * @param int $booking_id Randevu ID.
	 */
	public function schedule_reminder_for_booking( $booking_id ) {
		if ( 'yes' !== get_option( 'ag_sms_reminder_enabled', 'yes' ) ) {
			return;
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking || empty( $booking->customer_phone ) ) {
			return;
		}

		$hours = intval( get_option( 'ag_sms_reminder_hours', 24 ) );

		// Randevu zamanını hesapla.
		$booking_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$booking_time     = strtotime( $booking_datetime );
		$reminder_time    = $booking_time - ( $hours * 3600 );

		// Geçmiş bir zaman değilse planla.
		if ( $reminder_time > time() ) {
			$scheduled = gmdate( 'Y-m-d H:i:s', $reminder_time );
			$this->queue_sms( $booking_id, $booking->customer_phone, 'reminder', $scheduled );
		}
	}

	/**
	 * Tüm yarınki randevular için hatırlatma planla
	 */
	public function schedule_reminders() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( 'yes' !== get_option( 'ag_sms_reminder_enabled', 'yes' ) ) {
			return;
		}

		global $wpdb;

		$hours         = intval( get_option( 'ag_sms_reminder_hours', 24 ) );
		$reminder_date = gmdate( 'Y-m-d', strtotime( "+{$hours} hours" ) );

		$prefix = $wpdb->prefix . 'ag_';

		// Hatırlatması olmayan randevuları bul.
		$bookings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.* FROM {$prefix}bookings b
				WHERE b.booking_date = %s
				AND b.status IN ('pending', 'confirmed')
				AND b.customer_phone IS NOT NULL
				AND b.customer_phone != ''
				AND NOT EXISTS (
					SELECT 1 FROM {$this->queue_table} q
					WHERE q.booking_id = b.id
					AND q.message LIKE %s
				)",
				$reminder_date,
				'%Hatirlatma%'
			)
		);

		foreach ( $bookings as $booking ) {
			$this->schedule_reminder_for_booking( $booking->id );
		}
	}

	/**
	 * Hatırlatmayı iptal et
	 *
	 * @param int $booking_id Randevu ID.
	 */
	public function cancel_reminder( $booking_id ) {
		global $wpdb;

		$wpdb->update(
			$this->queue_table,
			array( 'status' => 'cancelled' ),
			array(
				'booking_id' => $booking_id,
				'status'     => 'pending',
			),
			array( '%s' ),
			array( '%d', '%s' )
		);
	}

	/**
	 * Şablonu render et
	 *
	 * @param string $type       Şablon tipi.
	 * @param int    $booking_id Randevu ID.
	 *
	 * @return string Render edilmiş mesaj.
	 */
	public function render_template( $type, $booking_id ) {
		global $wpdb;

		$template = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->templates_table} WHERE type = %s AND is_active = 1",
				$type
			)
		);

		if ( ! $template ) {
			return '';
		}

		$booking = $this->get_booking_details( $booking_id );
		if ( ! $booking ) {
			return '';
		}

		$placeholders = array(
			'{customer_name}'  => $booking->customer_name,
			'{customer_phone}' => $booking->customer_phone,
			'{service_name}'   => ! empty( $booking->services_list ) ? $booking->services_list : $booking->service_name,
			'{staff_name}'     => $booking->staff_name,
			'{booking_date}'   => date_i18n( get_option( 'ag_date_format', 'd.m.Y' ), strtotime( $booking->booking_date ) ),
			'{booking_time}'   => date_i18n( get_option( 'ag_time_format', 'H:i' ), strtotime( $booking->start_time ) ),
			'{price}'          => $booking->price . ' ' . get_option( 'ag_currency_symbol', '₺' ),
			'{company_name}'   => get_option( 'ag_company_name', get_bloginfo( 'name' ) ),
			'{company_phone}'  => get_option( 'ag_company_phone', '' ),
		);

		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $template->message );
	}

	/**
	 * Randevu detaylarını getir
	 *
	 * @param int $booking_id Randevu ID.
	 *
	 * @return object|null
	 */
	private function get_booking_details( $booking_id ) {
		$booking_model = new AG_Booking();
		return $booking_model->get_with_details( $booking_id );
	}

	/**
	 * SMS şablonlarını getir
	 *
	 * @return array
	 */
	public function get_templates() {
		global $wpdb;

		return $wpdb->get_results( "SELECT * FROM {$this->templates_table} ORDER BY id ASC" );
	}

	/**
	 * Şablon güncelle
	 *
	 * @param int    $id      Şablon ID.
	 * @param string $message Yeni mesaj.
	 * @param bool   $active  Aktif mi.
	 *
	 * @return bool
	 */
	public function update_template( $id, $message, $active = true ) {
		global $wpdb;

		return (bool) $wpdb->update(
			$this->templates_table,
			array(
				'message'   => sanitize_textarea_field( $message ),
				'is_active' => $active ? 1 : 0,
			),
			array( 'id' => $id ),
			array( '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Kuyruk istatistiklerini getir
	 *
	 * @return array
	 */
	public function get_queue_stats() {
		global $wpdb;

		$stats = $wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$this->queue_table} GROUP BY status"
		);

		$result = array(
			'pending'   => 0,
			'sent'      => 0,
			'failed'    => 0,
			'cancelled' => 0,
		);

		foreach ( $stats as $stat ) {
			$result[ $stat->status ] = intval( $stat->count );
		}

		return $result;
	}

	/**
	 * Provider bakiyesini getir
	 *
	 * @return array
	 */
	public function get_balance() {
		return $this->provider->get_balance();
	}

	/**
	 * Test SMS gönder
	 *
	 * @param string $phone Telefon numarası.
	 *
	 * @return array
	 */
	public function send_test( $phone ) {
		if ( ! $this->provider instanceof AG_SMS_NetGSM ) {
			return array(
				'success' => false,
				'message' => __( 'Provider bulunamadı.', 'appointment-general' ),
			);
		}

		return $this->provider->send_test( $phone );
	}
}
