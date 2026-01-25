<?php
/**
 * Core plugin sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ana plugin sınıfı
 */
class AG_Core {

	/**
	 * Loader instance
	 *
	 * @var AG_Loader
	 */
	protected $loader;

	/**
	 * Plugin versiyonu
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->version = AG_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_api_hooks();
		$this->init_sms_manager();
	}

	/**
	 * Bağımlılıkları yükle
	 */
	private function load_dependencies() {
		// Loader
		require_once AG_PLUGIN_DIR . 'includes/class-ag-loader.php';

		// i18n
		require_once AG_PLUGIN_DIR . 'includes/class-ag-i18n.php';

		// Database
		require_once AG_PLUGIN_DIR . 'includes/class-ag-database.php';

		// Models
		require_once AG_PLUGIN_DIR . 'includes/models/class-ag-category.php';
		require_once AG_PLUGIN_DIR . 'includes/models/class-ag-service.php';
		require_once AG_PLUGIN_DIR . 'includes/models/class-ag-staff.php';
		require_once AG_PLUGIN_DIR . 'includes/models/class-ag-booking.php';
		require_once AG_PLUGIN_DIR . 'includes/models/class-ag-booking-service.php';
		require_once AG_PLUGIN_DIR . 'includes/models/class-ag-customer.php';

		// Services
		require_once AG_PLUGIN_DIR . 'includes/class-ag-calendar.php';
		require_once AG_PLUGIN_DIR . 'includes/class-ag-notification.php';
		require_once AG_PLUGIN_DIR . 'includes/class-ag-sms-manager.php';

		// Admin
		require_once AG_PLUGIN_DIR . 'admin/class-ag-admin.php';

		// Public
		require_once AG_PLUGIN_DIR . 'public/class-ag-public.php';

		// REST API
		require_once AG_PLUGIN_DIR . 'includes/class-ag-rest-api.php';

		$this->loader = new AG_Loader();
	}

	/**
	 * Dil dosyalarını ayarla
	 */
	private function set_locale() {
		$i18n = new AG_i18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Admin hook'larını tanımla
	 */
	private function define_admin_hooks() {
		$admin = new AG_Admin( $this->version );

		// Admin menü
		$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );

		// Admin assets
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		// Admin AJAX
		$this->loader->add_action( 'wp_ajax_ag_save_category', $admin, 'ajax_save_category' );
		$this->loader->add_action( 'wp_ajax_ag_delete_category', $admin, 'ajax_delete_category' );
		$this->loader->add_action( 'wp_ajax_ag_save_service', $admin, 'ajax_save_service' );
		$this->loader->add_action( 'wp_ajax_ag_delete_service', $admin, 'ajax_delete_service' );
		$this->loader->add_action( 'wp_ajax_ag_save_staff', $admin, 'ajax_save_staff' );
		$this->loader->add_action( 'wp_ajax_ag_delete_staff', $admin, 'ajax_delete_staff' );
		$this->loader->add_action( 'wp_ajax_ag_update_booking_status', $admin, 'ajax_update_booking_status' );
		$this->loader->add_action( 'wp_ajax_ag_get_bookings', $admin, 'ajax_get_bookings' );
		$this->loader->add_action( 'wp_ajax_ag_admin_create_booking', $admin, 'ajax_create_booking' );
		$this->loader->add_action( 'wp_ajax_ag_save_settings', $admin, 'ajax_save_settings' );
		$this->loader->add_action( 'wp_ajax_ag_save_working_hours', $admin, 'ajax_save_working_hours' );

		// SMS AJAX handlers.
		$this->loader->add_action( 'wp_ajax_ag_sms_send_test', $admin, 'ajax_sms_send_test' );
		$this->loader->add_action( 'wp_ajax_ag_sms_get_balance', $admin, 'ajax_sms_get_balance' );
		$this->loader->add_action( 'wp_ajax_ag_save_sms_templates', $admin, 'ajax_save_sms_templates' );

		// Email template AJAX handlers.
		$this->loader->add_action( 'wp_ajax_ag_save_email_templates', $admin, 'ajax_save_email_templates' );
		$this->loader->add_action( 'wp_ajax_ag_email_send_test', $admin, 'ajax_email_send_test' );
		$this->loader->add_action( 'wp_ajax_ag_email_preview', $admin, 'ajax_email_preview' );
		$this->loader->add_action( 'wp_ajax_ag_save_email_settings', $admin, 'ajax_save_email_settings' );

		// Staff schedule AJAX handlers.
		$this->loader->add_action( 'wp_ajax_ag_get_staff_schedule', $admin, 'ajax_get_staff_schedule' );
		$this->loader->add_action( 'wp_ajax_ag_save_staff_schedule', $admin, 'ajax_save_staff_schedule' );
		$this->loader->add_action( 'wp_ajax_ag_add_staff_holiday', $admin, 'ajax_add_staff_holiday' );
		$this->loader->add_action( 'wp_ajax_ag_delete_staff_holiday', $admin, 'ajax_delete_staff_holiday' );
	}

	/**
	 * Public hook'larını tanımla
	 */
	private function define_public_hooks() {
		$public = new AG_Public( $this->version );

		// Public assets
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

		// Shortcodes
		$this->loader->add_action( 'init', $public, 'register_shortcodes' );

		// Public AJAX
		$this->loader->add_action( 'wp_ajax_ag_get_available_slots', $public, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_nopriv_ag_get_available_slots', $public, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_ag_create_booking', $public, 'ajax_create_booking' );
		$this->loader->add_action( 'wp_ajax_nopriv_ag_create_booking', $public, 'ajax_create_booking' );
		$this->loader->add_action( 'wp_ajax_ag_get_staff_for_service', $public, 'ajax_get_staff_for_service' );
		$this->loader->add_action( 'wp_ajax_nopriv_ag_get_staff_for_service', $public, 'ajax_get_staff_for_service' );
		$this->loader->add_action( 'wp_ajax_ag_get_all_staff', $public, 'ajax_get_all_staff' );
		$this->loader->add_action( 'wp_ajax_nopriv_ag_get_all_staff', $public, 'ajax_get_all_staff' );
		$this->loader->add_action( 'wp_ajax_ag_get_services_for_staff', $public, 'ajax_get_services_for_staff' );
		$this->loader->add_action( 'wp_ajax_nopriv_ag_get_services_for_staff', $public, 'ajax_get_services_for_staff' );
	}

	/**
	 * REST API hook'larını tanımla
	 */
	private function define_api_hooks() {
		$api = new AG_REST_API();
		$this->loader->add_action( 'rest_api_init', $api, 'register_routes' );
	}

	/**
	 * SMS Manager'ı başlat
	 */
	private function init_sms_manager() {
		// SMS Manager'ı sadece SMS aktifse başlat
		if ( 'yes' === get_option( 'ag_sms_enabled', 'no' ) ) {
			new AG_SMS_Manager();
		}
	}

	/**
	 * Plugin'i çalıştır
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Plugin versiyonunu döndür
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
