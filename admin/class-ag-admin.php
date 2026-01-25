<?php
/**
 * Admin sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin sınıfı
 */
class AG_Admin {

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
	 * Admin menüsünü ekle
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Randevular', 'appointment-general' ),
			__( 'Randevular', 'appointment-general' ),
			'manage_options',
			'appointment-general',
			array( $this, 'render_dashboard' ),
			'dashicons-calendar-alt',
			30
		);

		add_submenu_page(
			'appointment-general',
			__( 'Kontrol Paneli', 'appointment-general' ),
			__( 'Kontrol Paneli', 'appointment-general' ),
			'manage_options',
			'appointment-general',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Randevular', 'appointment-general' ),
			__( 'Randevular', 'appointment-general' ),
			'manage_options',
			'ag-bookings',
			array( $this, 'render_bookings' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Takvim', 'appointment-general' ),
			__( 'Takvim', 'appointment-general' ),
			'manage_options',
			'ag-calendar',
			array( $this, 'render_calendar' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Kategoriler', 'appointment-general' ),
			__( 'Kategoriler', 'appointment-general' ),
			'manage_options',
			'ag-categories',
			array( $this, 'render_categories' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Hizmetler', 'appointment-general' ),
			__( 'Hizmetler', 'appointment-general' ),
			'manage_options',
			'ag-services',
			array( $this, 'render_services' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Personel', 'appointment-general' ),
			__( 'Personel', 'appointment-general' ),
			'manage_options',
			'ag-staff',
			array( $this, 'render_staff' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Çalışma Takvimi', 'appointment-general' ),
			__( 'Çalışma Takvimi', 'appointment-general' ),
			'manage_options',
			'ag-staff-schedule',
			array( $this, 'render_staff_schedule' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Müşteriler', 'appointment-general' ),
			__( 'Müşteriler', 'appointment-general' ),
			'manage_options',
			'ag-customers',
			array( $this, 'render_customers' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'Ayarlar', 'appointment-general' ),
			__( 'Ayarlar', 'appointment-general' ),
			'manage_options',
			'ag-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'appointment-general',
			__( 'E-posta Şablonları', 'appointment-general' ),
			__( 'E-posta Şablonları', 'appointment-general' ),
			'manage_options',
			'ag-email-templates',
			array( $this, 'render_email_templates' )
		);
	}

	/**
	 * Admin CSS yükle
	 *
	 * @param string $hook Sayfa hook'u.
	 */
	public function enqueue_styles( $hook ) {
		if ( strpos( $hook, 'appointment-general' ) === false && strpos( $hook, 'ag-' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'ag-admin',
			AG_PLUGIN_URL . 'admin/css/ag-admin.css',
			array(),
			$this->version
		);
	}

	/**
	 * Admin JS yükle
	 *
	 * @param string $hook Sayfa hook'u.
	 */
	public function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'appointment-general' ) === false && strpos( $hook, 'ag-' ) === false ) {
			return;
		}

		wp_enqueue_script(
			'ag-admin',
			AG_PLUGIN_URL . 'admin/js/ag-admin.js',
			array(),
			$this->version,
			true
		);

		wp_localize_script( 'ag-admin', 'agAdmin', array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'restUrl'   => rest_url( 'ag/v1/' ),
			'nonce'     => wp_create_nonce( 'ag_admin_nonce' ),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
			'strings'   => array(
				'confirm_delete' => __( 'Silmek istediğinize emin misiniz?', 'appointment-general' ),
				'saving'         => __( 'Kaydediliyor...', 'appointment-general' ),
				'saved'          => __( 'Kaydedildi!', 'appointment-general' ),
				'error'          => __( 'Bir hata oluştu.', 'appointment-general' ),
				'loading'        => __( 'Yükleniyor...', 'appointment-general' ),
			),
			'currency'  => array(
				'symbol'   => get_option( 'ag_currency_symbol', '₺' ),
				'position' => get_option( 'ag_currency_position', 'after' ),
			),
		) );
	}

	/**
	 * Dashboard sayfası
	 */
	public function render_dashboard() {
		$booking_model  = new AG_Booking();
		$service_model  = new AG_Service();
		$staff_model    = new AG_Staff();
		$customer_model = new AG_Customer();

		$stats = array(
			'total_bookings'   => $booking_model->count(),
			'pending_bookings' => $booking_model->count( 'pending' ),
			'today_bookings'   => $booking_model->count_today(),
			'total_services'   => $service_model->count(),
			'total_staff'      => $staff_model->count(),
			'total_customers'  => $customer_model->count(),
		);

		// Son randevular
		$recent_bookings = $booking_model->get_all( array(
			'orderby' => 'created_at',
			'order'   => 'DESC',
			'limit'   => 10,
		) );

		include AG_PLUGIN_DIR . 'admin/partials/admin-dashboard.php';
	}

	/**
	 * Randevular sayfası
	 */
	public function render_bookings() {
		$booking_model  = new AG_Booking();
		$staff_model    = new AG_Staff();
		$service_model  = new AG_Service();

		$staff    = $staff_model->get_active();
		$services = $service_model->get_active();

		// Filtreler
		$status    = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$staff_id  = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';

		$args = array(
			'orderby' => 'booking_date',
			'order'   => 'DESC',
		);

		if ( $status ) {
			$args['status'] = $status;
		}
		if ( $staff_id ) {
			$args['staff_id'] = $staff_id;
		}
		if ( $date_from ) {
			$args['date_from'] = $date_from;
		}
		if ( $date_to ) {
			$args['date_to'] = $date_to;
		}

		$bookings = $booking_model->get_all( $args );

		include AG_PLUGIN_DIR . 'admin/partials/admin-bookings.php';
	}

	/**
	 * Takvim sayfası
	 */
	public function render_calendar() {
		$staff_model = new AG_Staff();
		$staff       = $staff_model->get_active();

		include AG_PLUGIN_DIR . 'admin/partials/admin-calendar.php';
	}

	/**
	 * Kategoriler sayfası
	 */
	public function render_categories() {
		$category_model = new AG_Category();
		$categories     = $category_model->get_with_service_count();

		include AG_PLUGIN_DIR . 'admin/partials/admin-categories.php';
	}

	/**
	 * Hizmetler sayfası
	 */
	public function render_services() {
		$service_model  = new AG_Service();
		$category_model = new AG_Category();
		$services       = $service_model->get_with_categories();
		$categories     = $category_model->get_active();

		include AG_PLUGIN_DIR . 'admin/partials/admin-services.php';
	}

	/**
	 * Personel sayfası
	 */
	public function render_staff() {
		$staff_model   = new AG_Staff();
		$service_model = new AG_Service();

		$staff    = $staff_model->get_all();
		$services = $service_model->get_active();

		include AG_PLUGIN_DIR . 'admin/partials/admin-staff.php';
	}

	/**
	 * Müşteriler sayfası
	 */
	public function render_customers() {
		$customer_model = new AG_Customer();
		$customers      = $customer_model->get_all( array( 'limit' => 50 ) );

		include AG_PLUGIN_DIR . 'admin/partials/admin-customers.php';
	}

	/**
	 * Ayarlar sayfası
	 */
	public function render_settings() {
		include AG_PLUGIN_DIR . 'admin/partials/admin-settings.php';
	}

	/**
	 * E-posta Şablonları sayfası
	 */
	public function render_email_templates() {
		include AG_PLUGIN_DIR . 'admin/partials/admin-email-templates.php';
	}

	/**
	 * Çalışma Takvimi sayfası
	 */
	public function render_staff_schedule() {
		include AG_PLUGIN_DIR . 'admin/partials/admin-staff-schedule.php';
	}

	/**
	 * AJAX: Kategori kaydet
	 */
	public function ajax_save_category() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'color'       => isset( $_POST['color'] ) ? sanitize_hex_color( wp_unslash( $_POST['color'] ) ) : '#3788d8',
			'icon'        => isset( $_POST['icon'] ) ? sanitize_text_field( wp_unslash( $_POST['icon'] ) ) : '',
			'status'      => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
			'sort_order'  => isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0,
		);

		$category_model = new AG_Category();

		if ( $id ) {
			$result = $category_model->update( $id, $data );
		} else {
			$result = $category_model->create( $data );
		}

		if ( $result !== false ) {
			wp_send_json_success( array(
				'message' => __( 'Kategori kaydedildi.', 'appointment-general' ),
				'id'      => $id ? $id : $result,
			) );
		} else {
			wp_send_json_error( __( 'Kaydetme hatası.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Kategori sil
	 */
	public function ajax_delete_category() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Geçersiz ID.', 'appointment-general' ) );
		}

		$category_model = new AG_Category();
		$result         = $category_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Kategori silindi.', 'appointment-general' ) );
		} else {
			wp_send_json_error( __( 'Silme hatası.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Servis kaydet
	 */
	public function ajax_save_service() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'category_id'   => isset( $_POST['category_id'] ) && '' !== $_POST['category_id'] ? absint( $_POST['category_id'] ) : null,
			'name'          => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'description'   => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'duration'      => isset( $_POST['duration'] ) ? absint( $_POST['duration'] ) : 30,
			'price'         => isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : 0,
			'buffer_before' => isset( $_POST['buffer_before'] ) ? absint( $_POST['buffer_before'] ) : 0,
			'buffer_after'  => isset( $_POST['buffer_after'] ) ? absint( $_POST['buffer_after'] ) : 0,
			'color'         => isset( $_POST['color'] ) ? sanitize_hex_color( wp_unslash( $_POST['color'] ) ) : '#3788d8',
			'status'        => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		);

		$service_model = new AG_Service();

		if ( $id ) {
			$result = $service_model->update( $id, $data );
		} else {
			$result = $service_model->create( $data );
		}

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Hizmet kaydedildi.', 'appointment-general' ),
				'id'      => $id ? $id : $result,
			) );
		} else {
			wp_send_json_error( __( 'Kaydetme hatası.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Servis sil
	 */
	public function ajax_delete_service() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Geçersiz ID.', 'appointment-general' ) );
		}

		$service_model = new AG_Service();
		$result        = $service_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Hizmet silindi.', 'appointment-general' ) );
		} else {
			wp_send_json_error( __( 'Silme hatası.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Personel kaydet
	 */
	public function ajax_save_staff() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'name'     => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'email'    => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone'    => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'bio'      => isset( $_POST['bio'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bio'] ) ) : '',
			'status'   => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
			'services' => isset( $_POST['services'] ) ? array_map( 'absint', (array) $_POST['services'] ) : array(),
		);

		$staff_model = new AG_Staff();

		if ( $id ) {
			$result = $staff_model->update( $id, $data );
		} else {
			$result = $staff_model->create( $data );
		}

		if ( $result !== false ) {
			wp_send_json_success( array(
				'message' => __( 'Personel kaydedildi.', 'appointment-general' ),
				'id'      => $id ? $id : $result,
			) );
		} else {
			wp_send_json_error( __( 'Kaydetme hatası.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Personel sil
	 */
	public function ajax_delete_staff() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Geçersiz ID.', 'appointment-general' ) );
		}

		$staff_model = new AG_Staff();
		$result      = $staff_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Personel silindi.', 'appointment-general' ) );
		} else {
			wp_send_json_error( __( 'Silme hatası.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Randevu durumu güncelle
	 */
	public function ajax_update_booking_status() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';

		if ( ! $id || ! $status ) {
			wp_send_json_error( __( 'Geçersiz parametreler.', 'appointment-general' ) );
		}

		$booking_model = new AG_Booking();
		$result        = $booking_model->update_status( $id, $status );

		if ( $result ) {
			wp_send_json_success( __( 'Durum güncellendi.', 'appointment-general' ) );
		} else {
			wp_send_json_error( __( 'Güncelleme hatası.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Randevuları getir
	 */
	public function ajax_get_bookings() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$start    = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
		$end      = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : '';
		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		$calendar = new AG_Calendar();
		$events   = $calendar->get_calendar_events( $start, $end, $staff_id );

		wp_send_json_success( $events );
	}

	/**
	 * AJAX: Randevu oluştur
	 */
	public function ajax_create_booking() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$data = array(
			'service_id'     => isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0,
			'staff_id'       => isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0,
			'customer_name'  => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
			'customer_email' => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
			'customer_phone' => isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '',
			'booking_date'   => isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '',
			'start_time'     => isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '',
			'status'         => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending',
			'notes'          => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
			'internal_notes' => isset( $_POST['internal_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['internal_notes'] ) ) : '',
		);

		// Zorunlu alan kontrolü.
		if ( empty( $data['service_id'] ) || empty( $data['staff_id'] ) || empty( $data['customer_name'] ) ||
			empty( $data['customer_email'] ) || empty( $data['booking_date'] ) || empty( $data['start_time'] ) ) {
			wp_send_json_error( __( 'Lütfen tüm zorunlu alanları doldurun.', 'appointment-general' ) );
		}

		// E-posta doğrulama.
		if ( ! is_email( $data['customer_email'] ) ) {
			wp_send_json_error( __( 'Geçersiz e-posta adresi.', 'appointment-general' ) );
		}

		// Tarih formatı kontrolü.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data['booking_date'] ) ) {
			wp_send_json_error( __( 'Geçersiz tarih formatı.', 'appointment-general' ) );
		}

		$booking_model = new AG_Booking();
		$result        = $booking_model->create( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( $result ) {
			wp_send_json_success( array(
				'message'    => __( 'Randevu başarıyla oluşturuldu.', 'appointment-general' ),
				'booking_id' => $result,
			) );
		} else {
			wp_send_json_error( __( 'Randevu oluşturulurken bir hata oluştu.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: Ayarları kaydet
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$settings = array(
			'ag_time_slot_interval',
			'ag_booking_lead_time',
			'ag_booking_future_days',
			'ag_booking_flow_mode',
			'ag_currency',
			'ag_currency_symbol',
			'ag_currency_position',
			'ag_date_format',
			'ag_time_format',
			'ag_admin_email',
			'ag_email_from_name',
			'ag_email_from_address',
			'ag_company_name',
			'ag_company_phone',
			'ag_company_address',
			// İptal/değiştirme ayarları.
			'ag_cancel_deadline_hours',
			'ag_reschedule_deadline_hours',
			'ag_max_reschedules',
			// SMS ayarları.
			'ag_sms_provider',
			'ag_sms_netgsm_username',
			'ag_sms_netgsm_password',
			'ag_sms_netgsm_sender',
			'ag_sms_reminder_hours',
			// E-posta ayarları.
			'ag_email_reminder_hours',
			'ag_email_logo_url',
			'ag_email_primary_color',
		);

		foreach ( $settings as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				update_option( $key, $value );
			}
		}

		// Checkbox ayarları (işaretlenmezse POST'ta gelmez).
		$checkbox_settings = array(
			'ag_enable_ics',
			'ag_allow_cancel',
			'ag_allow_reschedule',
			'ag_enable_multi_service',
			'ag_sms_enabled',
			'ag_sms_reminder_enabled',
			'ag_sms_on_booking',
			'ag_sms_on_confirmation',
			'ag_sms_on_cancellation',
			'ag_email_reminder_enabled',
		);

		foreach ( $checkbox_settings as $key ) {
			$value = isset( $_POST[ $key ] ) ? 'yes' : 'no';
			update_option( $key, $value );
		}

		wp_send_json_success( __( 'Ayarlar kaydedildi.', 'appointment-general' ) );
	}

	/**
	 * AJAX: Çalışma saatlerini kaydet
	 */
	public function ajax_save_working_hours() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$hours    = isset( $_POST['hours'] ) ? $_POST['hours'] : array();

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Geçersiz personel.', 'appointment-general' ) );
		}

		$staff_model = new AG_Staff();
		$staff_model->update_working_hours( $staff_id, $hours );

		wp_send_json_success( __( 'Çalışma saatleri kaydedildi.', 'appointment-general' ) );
	}

	/**
	 * AJAX: SMS Test gönder
	 */
	public function ajax_sms_send_test() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

		if ( empty( $phone ) ) {
			wp_send_json_error( __( 'Telefon numarası gerekli.', 'appointment-general' ) );
		}

		$sms_manager = new AG_SMS_Manager();
		$result      = $sms_manager->send_test( $phone );

		if ( $result['success'] ) {
			wp_send_json_success( $result['message'] );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * AJAX: SMS Bakiye sorgula
	 */
	public function ajax_sms_get_balance() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$sms_manager = new AG_SMS_Manager();
		$result      = $sms_manager->get_balance();

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * AJAX: SMS Şablonlarını kaydet
	 */
	public function ajax_save_sms_templates() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$templates_raw = isset( $_POST['templates'] ) ? wp_unslash( $_POST['templates'] ) : '';
		$templates     = json_decode( $templates_raw, true );

		if ( empty( $templates ) || ! is_array( $templates ) ) {
			wp_send_json_error( __( 'Geçersiz şablon verisi.', 'appointment-general' ) );
		}

		$sms_manager = new AG_SMS_Manager();

		foreach ( $templates as $template ) {
			if ( ! empty( $template['id'] ) && isset( $template['message'] ) ) {
				$active = isset( $template['is_active'] ) && $template['is_active'];
				$sms_manager->update_template(
					absint( $template['id'] ),
					$template['message'],
					$active
				);
			}
		}

		wp_send_json_success( __( 'SMS şablonları kaydedildi.', 'appointment-general' ) );
	}

	/**
	 * AJAX: E-posta şablonlarını kaydet
	 */
	public function ajax_save_email_templates() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$templates_raw = isset( $_POST['templates'] ) ? wp_unslash( $_POST['templates'] ) : '';
		$templates     = json_decode( $templates_raw, true );

		if ( empty( $templates ) || ! is_array( $templates ) ) {
			wp_send_json_error( __( 'Geçersiz şablon verisi.', 'appointment-general' ) );
		}

		$notification = new AG_Notification();

		foreach ( $templates as $template ) {
			if ( ! empty( $template['id'] ) && isset( $template['subject'] ) && isset( $template['content'] ) ) {
				$active = isset( $template['is_active'] ) && $template['is_active'];
				$notification->update_template(
					absint( $template['id'] ),
					$template['subject'],
					$template['content'],
					$active
				);
			}
		}

		wp_send_json_success( __( 'E-posta şablonları kaydedildi.', 'appointment-general' ) );
	}

	/**
	 * AJAX: Test e-postası gönder
	 */
	public function ajax_email_send_test() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$email         = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$template_type = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( __( 'Geçerli bir e-posta adresi girin.', 'appointment-general' ) );
		}

		if ( empty( $template_type ) ) {
			wp_send_json_error( __( 'Şablon tipi gerekli.', 'appointment-general' ) );
		}

		$notification = new AG_Notification();
		$result       = $notification->send_test_email( $email, $template_type );

		if ( $result['success'] ) {
			wp_send_json_success( $result['message'] );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * AJAX: E-posta önizleme
	 */
	public function ajax_email_preview() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$template_type = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';

		if ( empty( $template_type ) ) {
			wp_send_json_error( __( 'Şablon tipi gerekli.', 'appointment-general' ) );
		}

		$notification = new AG_Notification();
		$template     = $notification->get_template( $template_type );

		if ( ! $template ) {
			wp_send_json_error( __( 'Şablon bulunamadı.', 'appointment-general' ) );
		}

		// Preview için test verisi oluştur (AG_Notification::send_test_email benzeri)
		$test_booking = (object) array(
			'id'               => 999,
			'customer_name'    => 'Test Müşteri',
			'customer_email'   => 'test@example.com',
			'customer_phone'   => '0555 555 55 55',
			'service_name'     => 'Test Hizmeti',
			'services_list'    => 'Saç Kesimi, Sakal Tıraşı',
			'staff_name'       => 'Ahmet Usta',
			'booking_date'     => gmdate( 'Y-m-d', strtotime( '+1 day' ) ),
			'start_time'       => '14:00:00',
			'price'            => '150.00',
			'total_duration'   => 60,
			'status'           => 'pending',
			'token'            => 'preview-token-123',
		);

		// Preview HTML oluşturmak için reflection kullan
		$reflection = new ReflectionClass( $notification );

		// parse_placeholders metodunu çağır
		$parse_method = $reflection->getMethod( 'parse_placeholders' );
		$parse_method->setAccessible( true );
		$content = $parse_method->invoke( $notification, $template->content, $test_booking );
		$content = str_replace( '{calendar_links}', '', $content );

		// wrap_email_content metodunu çağır
		$wrap_method = $reflection->getMethod( 'wrap_email_content' );
		$wrap_method->setAccessible( true );
		$html = $wrap_method->invoke( $notification, $content, $template_type );

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * AJAX: E-posta ayarlarını kaydet
	 */
	public function ajax_save_email_settings() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$settings = array(
			'ag_email_logo_url',
			'ag_email_primary_color',
			'ag_email_reminder_hours',
		);

		foreach ( $settings as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				update_option( $key, $value );
			}
		}

		// Checkbox
		$reminder_enabled = isset( $_POST['ag_email_reminder_enabled'] ) && 'yes' === $_POST['ag_email_reminder_enabled'] ? 'yes' : 'no';
		update_option( 'ag_email_reminder_enabled', $reminder_enabled );

		wp_send_json_success( __( 'E-posta ayarları kaydedildi.', 'appointment-general' ) );
	}

	/**
	 * AJAX: Personel takvim verilerini getir
	 */
	public function ajax_get_staff_schedule() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Geçersiz personel.', 'appointment-general' ) );
		}

		$staff_model = new AG_Staff();

		// Çalışma saatleri.
		$working_hours = $staff_model->get_working_hours( $staff_id );

		// Molalar.
		$breaks = $staff_model->get_breaks( $staff_id );

		// Tatiller (önümüzdeki 1 yıl).
		$start_date = gmdate( 'Y-m-d' );
		$end_date   = gmdate( 'Y-m-d', strtotime( '+1 year' ) );
		$holidays   = $staff_model->get_holidays( $staff_id, $start_date, $end_date );

		wp_send_json_success( array(
			'working_hours' => $working_hours,
			'breaks'        => $breaks,
			'holidays'      => $holidays,
		) );
	}

	/**
	 * AJAX: Personel programını kaydet (çalışma saatleri + molalar)
	 */
	public function ajax_save_staff_schedule() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Geçersiz personel.', 'appointment-general' ) );
		}

		// Çalışma saatlerini al.
		$hours_raw = isset( $_POST['working_hours'] ) ? wp_unslash( $_POST['working_hours'] ) : '';
		$hours     = json_decode( $hours_raw, true );

		// Molaları al.
		$breaks_raw = isset( $_POST['breaks'] ) ? wp_unslash( $_POST['breaks'] ) : '';
		$breaks     = json_decode( $breaks_raw, true );

		$staff_model = new AG_Staff();

		// Çalışma saatlerini kaydet.
		if ( is_array( $hours ) ) {
			$staff_model->update_working_hours( $staff_id, $hours );
		}

		// Molaları kaydet.
		if ( is_array( $breaks ) ) {
			$staff_model->update_breaks( $staff_id, $breaks );
		}

		wp_send_json_success( __( 'Program kaydedildi.', 'appointment-general' ) );
	}

	/**
	 * AJAX: İzin/tatil ekle
	 */
	public function ajax_add_staff_holiday() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$date     = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$reason   = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( ! $staff_id || empty( $date ) ) {
			wp_send_json_error( __( 'Geçersiz veri.', 'appointment-general' ) );
		}

		// Tarih formatı kontrolü.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			wp_send_json_error( __( 'Geçersiz tarih formatı.', 'appointment-general' ) );
		}

		$staff_model = new AG_Staff();
		$result      = $staff_model->add_holiday( $staff_id, $date, $reason );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'İzin eklendi.', 'appointment-general' ),
				'id'      => $result,
			) );
		} else {
			wp_send_json_error( __( 'Bu tarihte zaten izin var.', 'appointment-general' ) );
		}
	}

	/**
	 * AJAX: İzin/tatil sil
	 */
	public function ajax_delete_staff_holiday() {
		check_ajax_referer( 'ag_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'appointment-general' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$date     = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

		if ( ! $staff_id || empty( $date ) ) {
			wp_send_json_error( __( 'Geçersiz veri.', 'appointment-general' ) );
		}

		$staff_model = new AG_Staff();
		$staff_model->delete_holiday_by_date( $staff_id, $date );

		wp_send_json_success( __( 'İzin silindi.', 'appointment-general' ) );
	}
}
