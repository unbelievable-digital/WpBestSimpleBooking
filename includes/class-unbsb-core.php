<?php
/**
 * Core plugin class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 */
class UNBSB_Core {

	/**
	 * Loader instance
	 *
	 * @var UNBSB_Loader
	 */
	protected $loader;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->version = UNBSB_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_api_hooks();
		$this->init_notifications();
		$this->init_sms_manager();
		$this->init_seo();
	}

	/**
	 * Load dependencies
	 */
	private function load_dependencies() {
		// Loader.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-loader.php';

		// i18n.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-i18n.php';

		// Database.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-database.php';

		// Models.
		require_once UNBSB_PLUGIN_DIR . 'includes/models/class-unbsb-category.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/models/class-unbsb-service.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/models/class-unbsb-staff.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/models/class-unbsb-booking.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/models/class-unbsb-booking-service.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/models/class-unbsb-customer.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/models/class-unbsb-promo-code.php';

		// Services.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-calendar.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-notification.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-sms-manager.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-seo.php';

		// Admin.
		require_once UNBSB_PLUGIN_DIR . 'admin/class-unbsb-admin.php';

		// Public.
		require_once UNBSB_PLUGIN_DIR . 'public/class-unbsb-public.php';

		// Security.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-encryption.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-rate-limiter.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-security-logger.php';
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-captcha.php';

		// Booking Manager.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-booking-manager.php';

		// ICS Generator.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-ics-generator.php';

		// Export/Import.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-export-import.php';

		// REST API.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-rest-api.php';

		$this->loader = new UNBSB_Loader();
	}

	/**
	 * Set locale for internationalization
	 */
	private function set_locale() {
		$i18n = new UNBSB_I18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Define admin hooks
	 */
	private function define_admin_hooks() {
		$admin = new UNBSB_Admin( $this->version );

		// Admin menu.
		$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );

		// Admin assets.
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		// Admin AJAX.
		$this->loader->add_action( 'wp_ajax_unbsb_save_category', $admin, 'ajax_save_category' );
		$this->loader->add_action( 'wp_ajax_unbsb_delete_category', $admin, 'ajax_delete_category' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_service', $admin, 'ajax_save_service' );
		$this->loader->add_action( 'wp_ajax_unbsb_delete_service', $admin, 'ajax_delete_service' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_staff', $admin, 'ajax_save_staff' );
		$this->loader->add_action( 'wp_ajax_unbsb_delete_staff', $admin, 'ajax_delete_staff' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_customer', $admin, 'ajax_save_customer' );
		$this->loader->add_action( 'wp_ajax_unbsb_delete_customer', $admin, 'ajax_delete_customer' );
		$this->loader->add_action( 'wp_ajax_unbsb_update_booking_status', $admin, 'ajax_update_booking_status' );
		$this->loader->add_action( 'wp_ajax_unbsb_delete_booking', $admin, 'ajax_delete_booking' );
		$this->loader->add_action( 'wp_ajax_unbsb_get_bookings', $admin, 'ajax_get_bookings' );
		$this->loader->add_action( 'wp_ajax_unbsb_admin_create_booking', $admin, 'ajax_create_booking' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_settings', $admin, 'ajax_save_settings' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_working_hours', $admin, 'ajax_save_working_hours' );

		// SMS AJAX handlers.
		$this->loader->add_action( 'wp_ajax_unbsb_sms_send_test', $admin, 'ajax_sms_send_test' );
		$this->loader->add_action( 'wp_ajax_unbsb_sms_get_balance', $admin, 'ajax_sms_get_balance' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_sms_templates', $admin, 'ajax_save_sms_templates' );

		// Email template AJAX handlers.
		$this->loader->add_action( 'wp_ajax_unbsb_save_email_templates', $admin, 'ajax_save_email_templates' );
		$this->loader->add_action( 'wp_ajax_unbsb_email_send_test', $admin, 'ajax_email_send_test' );
		$this->loader->add_action( 'wp_ajax_unbsb_email_preview', $admin, 'ajax_email_preview' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_email_settings', $admin, 'ajax_save_email_settings' );

		// Staff schedule AJAX handlers.
		$this->loader->add_action( 'wp_ajax_unbsb_get_staff_schedule', $admin, 'ajax_get_staff_schedule' );
		$this->loader->add_action( 'wp_ajax_unbsb_save_staff_schedule', $admin, 'ajax_save_staff_schedule' );
		$this->loader->add_action( 'wp_ajax_unbsb_add_staff_holiday', $admin, 'ajax_add_staff_holiday' );
		$this->loader->add_action( 'wp_ajax_unbsb_delete_staff_holiday', $admin, 'ajax_delete_staff_holiday' );

		// Promo code AJAX handlers.
		$this->loader->add_action( 'wp_ajax_unbsb_save_promo_code', $admin, 'ajax_save_promo_code' );
		$this->loader->add_action( 'wp_ajax_unbsb_delete_promo_code', $admin, 'ajax_delete_promo_code' );

		// Customer search/create AJAX handlers (for new booking page).
		$this->loader->add_action( 'wp_ajax_unbsb_search_customers', $admin, 'ajax_search_customers' );
		$this->loader->add_action( 'wp_ajax_unbsb_admin_create_customer', $admin, 'ajax_admin_create_customer' );

		// Staff earnings on booking completion.
		$this->loader->add_action( 'unbsb_booking_status_changed', $admin, 'handle_booking_earnings', 10, 3 );

		// Staff portal AJAX handlers.
		$this->loader->add_action( 'wp_ajax_unbsb_get_staff_own_bookings', $admin, 'ajax_get_staff_own_bookings' );
		$this->loader->add_action( 'wp_ajax_unbsb_staff_confirm_booking', $admin, 'ajax_staff_confirm_booking' );
		$this->loader->add_action( 'wp_ajax_unbsb_staff_reject_booking', $admin, 'ajax_staff_reject_booking' );
		$this->loader->add_action( 'wp_ajax_unbsb_staff_edit_booking', $admin, 'ajax_staff_edit_booking' );
		$this->loader->add_action( 'wp_ajax_unbsb_staff_add_holiday', $admin, 'ajax_staff_add_holiday' );
		$this->loader->add_action( 'wp_ajax_unbsb_staff_remove_holiday', $admin, 'ajax_staff_remove_holiday' );
		$this->loader->add_action( 'wp_ajax_unbsb_staff_get_holidays', $admin, 'ajax_staff_get_holidays' );

		// Staff user management AJAX handlers.
		$this->loader->add_action( 'wp_ajax_unbsb_create_staff_user', $admin, 'ajax_create_staff_user' );
		$this->loader->add_action( 'wp_ajax_unbsb_link_staff_user', $admin, 'ajax_link_staff_user' );
		$this->loader->add_action( 'wp_ajax_unbsb_unlink_staff_user', $admin, 'ajax_unlink_staff_user' );

		// Booking edit.
		$this->loader->add_action( 'wp_ajax_unbsb_get_booking_detail', $admin, 'ajax_get_booking_detail' );
		$this->loader->add_action( 'wp_ajax_unbsb_update_booking', $admin, 'ajax_update_booking' );

		// Booking completion with payment.
		$this->loader->add_action( 'wp_ajax_unbsb_complete_booking_with_payment', $admin, 'ajax_complete_booking_with_payment' );

		// Calendar events.
		$this->loader->add_action( 'wp_ajax_unbsb_get_calendar_events', $admin, 'ajax_get_calendar_events' );

		// Customer CSV import/export.
		$this->loader->add_action( 'wp_ajax_unbsb_export_customers_csv', $admin, 'ajax_export_customers_csv' );
		$this->loader->add_action( 'wp_ajax_unbsb_import_customers_csv', $admin, 'ajax_import_customers_csv' );

		// Staff admin restrictions.
		$this->loader->add_action( 'admin_menu', $admin, 'restrict_staff_admin_menu', 999 );
		$this->loader->add_action( 'admin_bar_menu', $admin, 'restrict_staff_admin_bar', 999 );

		// Staff login redirect.
		$this->loader->add_filter( 'login_redirect', $admin, 'staff_login_redirect', 10, 3 );
	}

	/**
	 * Define public hooks
	 */
	private function define_public_hooks() {
		$public = new UNBSB_Public( $this->version );

		// Public assets.
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

		// Meta Pixel base code.
		$this->loader->add_action( 'wp_head', $public, 'render_meta_pixel' );

		// Shortcodes.
		$this->loader->add_action( 'init', $public, 'register_shortcodes' );

		// Public AJAX.
		$this->loader->add_action( 'wp_ajax_unbsb_get_available_slots', $public, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_get_available_slots', $public, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_unbsb_create_booking', $public, 'ajax_create_booking' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_create_booking', $public, 'ajax_create_booking' );
		$this->loader->add_action( 'wp_ajax_unbsb_get_staff_for_service', $public, 'ajax_get_staff_for_service' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_get_staff_for_service', $public, 'ajax_get_staff_for_service' );
		$this->loader->add_action( 'wp_ajax_unbsb_get_all_staff', $public, 'ajax_get_all_staff' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_get_all_staff', $public, 'ajax_get_all_staff' );
		$this->loader->add_action( 'wp_ajax_unbsb_get_services_for_staff', $public, 'ajax_get_services_for_staff' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_get_services_for_staff', $public, 'ajax_get_services_for_staff' );
		$this->loader->add_action( 'wp_ajax_unbsb_get_staff_nearest_slots', $public, 'ajax_get_staff_nearest_slots' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_get_staff_nearest_slots', $public, 'ajax_get_staff_nearest_slots' );

		// Customer account AJAX.
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_customer_login', $public, 'ajax_customer_login' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_customer_register', $public, 'ajax_customer_register' );

		// Promo code validation AJAX.
		$this->loader->add_action( 'wp_ajax_unbsb_validate_promo_code', $public, 'ajax_validate_promo_code' );
		$this->loader->add_action( 'wp_ajax_nopriv_unbsb_validate_promo_code', $public, 'ajax_validate_promo_code' );
	}

	/**
	 * Define REST API hooks
	 */
	private function define_api_hooks() {
		$api = new UNBSB_REST_API();
		$this->loader->add_action( 'rest_api_init', $api, 'register_routes' );
	}

	/**
	 * Initialize Notifications (registers email hooks)
	 */
	private function init_notifications() {
		new UNBSB_Notification();
	}

	/**
	 * Initialize SMS Manager
	 */
	private function init_sms_manager() {
		// Initialize SMS Manager only if SMS is enabled.
		if ( 'yes' === get_option( 'unbsb_sms_enabled', 'no' ) ) {
			new UNBSB_SMS_Manager();
		}
	}

	/**
	 * Initialize SEO
	 */
	private function init_seo() {
		// SEO only runs on frontend and when enabled.
		if ( ! is_admin() && 'yes' === get_option( 'unbsb_seo_enabled', 'yes' ) ) {
			new UNBSB_SEO();
		}
	}

	/**
	 * Run the plugin
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
