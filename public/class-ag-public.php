<?php
/**
 * Public sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public sınıfı
 */
class AG_Public {

	/**
	 * Plugin versiyonu
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructor
	 *
	 * @param string $version Plugin versiyonu.
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Public CSS yükle
	 */
	public function enqueue_styles() {
		wp_register_style(
			'ag-public',
			AG_PLUGIN_URL . 'public/css/ag-public.css',
			array(),
			$this->version
		);
	}

	/**
	 * Public JS yükle
	 */
	public function enqueue_scripts() {
		wp_register_script(
			'ag-public',
			AG_PLUGIN_URL . 'public/js/ag-public.js',
			array(),
			$this->version,
			true
		);

		wp_localize_script( 'ag-public', 'agPublic', array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'restUrl'  => rest_url( 'ag/v1/' ),
			'nonce'    => wp_create_nonce( 'ag_public_nonce' ),
			'strings'  => array(
				'select_service'  => __( 'Hizmet seçin', 'appointment-general' ),
				'select_staff'    => __( 'Personel seçin', 'appointment-general' ),
				'select_date'     => __( 'Tarih seçin', 'appointment-general' ),
				'select_time'     => __( 'Saat seçin', 'appointment-general' ),
				'loading'         => __( 'Yükleniyor...', 'appointment-general' ),
				'no_slots'        => __( 'Bu tarihte müsait saat bulunmuyor.', 'appointment-general' ),
				'no_services'     => __( 'Bu personel için hizmet bulunmuyor.', 'appointment-general' ),
				'no_staff'        => __( 'Personel bulunmuyor.', 'appointment-general' ),
				'booking_success' => __( 'Randevunuz başarıyla oluşturuldu!', 'appointment-general' ),
				'booking_error'   => __( 'Bir hata oluştu. Lütfen tekrar deneyin.', 'appointment-general' ),
				'required_fields' => __( 'Lütfen tüm zorunlu alanları doldurun.', 'appointment-general' ),
			),
			'currency' => array(
				'symbol'   => get_option( 'ag_currency_symbol', '₺' ),
				'position' => get_option( 'ag_currency_position', 'after' ),
			),
			'dateFormat' => get_option( 'ag_date_format', 'd.m.Y' ),
			'timeFormat' => get_option( 'ag_time_format', 'H:i' ),
		) );
	}

	/**
	 * Shortcode'ları kaydet
	 */
	public function register_shortcodes() {
		add_shortcode( 'ag_booking_form', array( $this, 'booking_form_shortcode' ) );
		add_shortcode( 'ag_services', array( $this, 'services_shortcode' ) );
		add_shortcode( 'ag_staff_list', array( $this, 'staff_list_shortcode' ) );
		add_shortcode( 'ag_manage_booking', array( $this, 'manage_booking_shortcode' ) );
	}

	/**
	 * Booking form shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function booking_form_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'service'  => 0,
			'staff'    => 0,
			'category' => 0,
		), $atts );

		// Stilleri ve scriptleri yükle
		wp_enqueue_style( 'ag-public' );
		wp_enqueue_script( 'ag-public' );

		// Servisleri, kategorileri ve personeli getir
		$service_model  = new AG_Service();
		$category_model = new AG_Category();
		$staff_model    = new AG_Staff();

		$services   = $service_model->get_with_categories();
		$categories = $category_model->get_active();
		$staff      = $staff_model->get_active();

		ob_start();
		include AG_PLUGIN_DIR . 'public/partials/booking-form.php';
		return ob_get_clean();
	}

	/**
	 * Services shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function services_shortcode( $atts ) {
		$atts = shortcode_atts( array(), $atts );

		wp_enqueue_style( 'ag-public' );

		$service_model = new AG_Service();
		$services      = $service_model->get_active();

		ob_start();
		include AG_PLUGIN_DIR . 'public/partials/services-list.php';
		return ob_get_clean();
	}

	/**
	 * Staff list shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function staff_list_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'show_services' => 'no',
		), $atts );

		wp_enqueue_style( 'ag-public' );

		$staff_model   = new AG_Staff();
		$service_model = new AG_Service();

		$staff = $staff_model->get_active();

		ob_start();
		include AG_PLUGIN_DIR . 'public/partials/staff-list.php';
		return ob_get_clean();
	}

	/**
	 * Manage booking shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function manage_booking_shortcode( $atts ) {
		$atts = shortcode_atts( array(), $atts );

		// Token URL'den alınır.
		wp_enqueue_style( 'ag-public' );
		wp_enqueue_script( 'ag-public' );

		ob_start();
		include AG_PLUGIN_DIR . 'public/partials/booking-manage.php';
		return ob_get_clean();
	}

	/**
	 * AJAX: Müsait slotları getir
	 */
	public function ajax_get_available_slots() {
		check_ajax_referer( 'ag_public_nonce', 'nonce' );

		$staff_id       = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$service_id     = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$date           = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$total_duration = isset( $_POST['total_duration'] ) ? absint( $_POST['total_duration'] ) : 0;

		if ( ! $staff_id || ! $service_id || ! $date ) {
			wp_send_json_error( __( 'Eksik parametreler.', 'appointment-general' ) );
		}

		$calendar = new AG_Calendar();

		// Çoklu hizmet için total_duration kullan
		if ( $total_duration > 0 ) {
			$slots = $calendar->get_available_slots_by_duration( $staff_id, $date, $total_duration );
		} else {
			$slots = $calendar->get_available_slots( $staff_id, $service_id, $date );
		}

		wp_send_json_success( $slots );
	}

	/**
	 * AJAX: Randevu oluştur
	 */
	public function ajax_create_booking() {
		check_ajax_referer( 'ag_public_nonce', 'nonce' );

		// Honeypot kontrolü
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_error( __( 'Spam tespit edildi.', 'appointment-general' ) );
		}

		$data = array(
			'service_id'     => isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0,
			'staff_id'       => isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0,
			'booking_date'   => isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '',
			'start_time'     => isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '',
			'customer_name'  => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
			'customer_email' => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
			'customer_phone' => isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '',
			'notes'          => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		);

		// Çoklu hizmet desteği
		if ( ! empty( $_POST['service_ids'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$service_ids = wp_unslash( $_POST['service_ids'] );

			// JSON string ise decode et
			if ( is_string( $service_ids ) ) {
				$decoded = json_decode( $service_ids, true );
				if ( is_array( $decoded ) ) {
					$service_ids = array_map( 'absint', $decoded );
				}
			} elseif ( is_array( $service_ids ) ) {
				$service_ids = array_map( 'absint', $service_ids );
			}

			if ( ! empty( $service_ids ) ) {
				$data['service_ids'] = $service_ids;
				$data['service_id']  = $service_ids[0]; // İlk hizmet ana olarak
			}
		}

		// Toplam süre (çoklu hizmet için)
		if ( ! empty( $_POST['total_duration'] ) ) {
			$data['total_duration'] = absint( $_POST['total_duration'] );
		}

		$booking_model = new AG_Booking();
		$result        = $booking_model->create( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		$booking = $booking_model->get_with_details( $result );

		wp_send_json_success( array(
			'message' => __( 'Randevunuz başarıyla oluşturuldu!', 'appointment-general' ),
			'booking' => $booking,
		) );
	}

	/**
	 * AJAX: Servise göre personel getir
	 */
	public function ajax_get_staff_for_service() {
		check_ajax_referer( 'ag_public_nonce', 'nonce' );

		$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;

		if ( ! $service_id ) {
			wp_send_json_error( __( 'Servis ID gerekli.', 'appointment-general' ) );
		}

		$staff_model = new AG_Staff();
		$staff       = $staff_model->get_by_service( $service_id );

		wp_send_json_success( $staff );
	}

	/**
	 * AJAX: Tüm personeli getir
	 */
	public function ajax_get_all_staff() {
		check_ajax_referer( 'ag_public_nonce', 'nonce' );

		$staff_model = new AG_Staff();
		$staff       = $staff_model->get_active();

		wp_send_json_success( $staff );
	}

	/**
	 * AJAX: Personele göre servisleri getir
	 */
	public function ajax_get_services_for_staff() {
		check_ajax_referer( 'ag_public_nonce', 'nonce' );

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Personel ID gerekli.', 'appointment-general' ) );
		}

		$service_model = new AG_Service();
		$services      = $service_model->get_by_staff( $staff_id );

		wp_send_json_success( $services );
	}
}
