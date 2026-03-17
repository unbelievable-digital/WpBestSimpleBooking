<?php
/**
 * Admin class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class
 */
class UNBSB_Admin {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructor
	 *
	 * @param string $version Plugin version.
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Bookings', 'unbelievable-salon-booking' ),
			__( 'Bookings', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbelievable-salon-booking',
			array( $this, 'render_dashboard' ),
			'dashicons-calendar-alt',
			30
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Dashboard', 'unbelievable-salon-booking' ),
			__( 'Dashboard', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbelievable-salon-booking',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Bookings', 'unbelievable-salon-booking' ),
			__( 'Bookings', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-bookings',
			array( $this, 'render_bookings' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Calendar', 'unbelievable-salon-booking' ),
			__( 'Calendar', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-calendar',
			array( $this, 'render_calendar' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Categories', 'unbelievable-salon-booking' ),
			__( 'Categories', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-categories',
			array( $this, 'render_categories' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Services', 'unbelievable-salon-booking' ),
			__( 'Services', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-services',
			array( $this, 'render_services' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Staff', 'unbelievable-salon-booking' ),
			__( 'Staff', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-staff',
			array( $this, 'render_staff' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Work Schedule', 'unbelievable-salon-booking' ),
			__( 'Work Schedule', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-staff-schedule',
			array( $this, 'render_staff_schedule' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Customers', 'unbelievable-salon-booking' ),
			__( 'Customers', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-customers',
			array( $this, 'render_customers' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Promo Codes', 'unbelievable-salon-booking' ),
			__( 'Promo Codes', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-promo-codes',
			array( $this, 'render_promo_codes' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Settings', 'unbelievable-salon-booking' ),
			__( 'Settings', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Email Templates', 'unbelievable-salon-booking' ),
			__( 'Email Templates', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-email-templates',
			array( $this, 'render_email_templates' )
		);

		add_submenu_page(
			'unbelievable-salon-booking',
			__( 'Export / Import', 'unbelievable-salon-booking' ),
			__( 'Export / Import', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-export-import',
			array( $this, 'render_export_import' )
		);

		// Hidden page for new booking.
		add_submenu_page(
			null,
			__( 'New Booking', 'unbelievable-salon-booking' ),
			__( 'New Booking', 'unbelievable-salon-booking' ),
			'manage_options',
			'unbsb-new-booking',
			array( $this, 'render_new_booking' )
		);

		// Hidden page for staff new booking.
		add_submenu_page(
			null,
			__( 'New Booking', 'unbelievable-salon-booking' ),
			__( 'New Booking', 'unbelievable-salon-booking' ),
			'unbsb_view_own_bookings',
			'unbsb-staff-new-booking',
			array( $this, 'render_new_booking' )
		);

		// Staff portal menu (visible only to staff role users, not admins).
		if ( ! current_user_can( 'manage_options' ) && current_user_can( 'unbsb_view_own_bookings' ) ) {
			add_menu_page(
				__( 'My Bookings', 'unbelievable-salon-booking' ),
				__( 'My Bookings', 'unbelievable-salon-booking' ),
				'unbsb_view_own_bookings',
				'unbsb-staff-portal',
				array( $this, 'render_staff_bookings' ),
				'dashicons-calendar-alt',
				30
			);

			add_submenu_page(
				'unbsb-staff-portal',
				__( 'My Bookings', 'unbelievable-salon-booking' ),
				__( 'My Bookings', 'unbelievable-salon-booking' ),
				'unbsb_view_own_bookings',
				'unbsb-staff-portal',
				array( $this, 'render_staff_bookings' )
			);

			add_submenu_page(
				'unbsb-staff-portal',
				__( 'My Schedule', 'unbelievable-salon-booking' ),
				__( 'My Schedule', 'unbelievable-salon-booking' ),
				'unbsb_manage_own_schedule',
				'unbsb-staff-schedule-portal',
				array( $this, 'render_staff_schedule_portal' )
			);

			add_submenu_page(
				'unbsb-staff-portal',
				__( 'My Earnings', 'unbelievable-salon-booking' ),
				__( 'My Earnings', 'unbelievable-salon-booking' ),
				'unbsb_view_own_bookings',
				'unbsb-staff-earnings-portal',
				array( $this, 'render_staff_earnings_portal' )
			);

			add_submenu_page(
				'unbsb-staff-portal',
				__( 'My Performance', 'unbelievable-salon-booking' ),
				__( 'My Performance', 'unbelievable-salon-booking' ),
				'unbsb_view_own_bookings',
				'unbsb-staff-performance-portal',
				array( $this, 'render_staff_performance_portal' )
			);
		} else {
			// Hidden pages for admin access (accessible via URL).
			add_submenu_page(
				null,
				__( 'Staff Bookings', 'unbelievable-salon-booking' ),
				__( 'Staff Bookings', 'unbelievable-salon-booking' ),
				'manage_options',
				'unbsb-staff-portal',
				array( $this, 'render_staff_bookings' )
			);

			add_submenu_page(
				null,
				__( 'Staff Schedule', 'unbelievable-salon-booking' ),
				__( 'Staff Schedule', 'unbelievable-salon-booking' ),
				'manage_options',
				'unbsb-staff-schedule-portal',
				array( $this, 'render_staff_schedule_portal' )
			);

			add_submenu_page(
				null,
				__( 'Staff Earnings', 'unbelievable-salon-booking' ),
				__( 'Staff Earnings', 'unbelievable-salon-booking' ),
				'manage_options',
				'unbsb-staff-earnings-portal',
				array( $this, 'render_staff_earnings_portal' )
			);

			add_submenu_page(
				null,
				__( 'Staff Performance', 'unbelievable-salon-booking' ),
				__( 'Staff Performance', 'unbelievable-salon-booking' ),
				'manage_options',
				'unbsb-staff-performance-portal',
				array( $this, 'render_staff_performance_portal' )
			);
		}
	}

	/**
	 * Enqueue admin CSS
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( strpos( $hook, 'unbelievable-salon-booking' ) === false && strpos( $hook, 'unbsb-' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'unbsb-admin',
			UNBSB_PLUGIN_URL . 'admin/css/unbsb-admin.css',
			array(),
			$this->version
		);
	}

	/**
	 * Enqueue admin JS
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'unbelievable-salon-booking' ) === false && strpos( $hook, 'unbsb-' ) === false ) {
			return;
		}

		// Load WordPress Media Library for staff avatar upload.
		if ( false !== strpos( $hook, 'unbsb-staff' ) ) {
			wp_enqueue_media();
		}

		// Load FullCalendar for Calendar page and Staff Portal bookings.
		if ( false !== strpos( $hook, 'unbsb-calendar' ) || false !== strpos( $hook, 'unbsb-staff-portal' ) ) {
			wp_enqueue_script(
				'fullcalendar',
				'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js',
				array(),
				'6.1.15',
				true
			);
		}

		// Load Chart.js for Dashboard (local file).
		if ( 'toplevel_page_unbelievable-salon-booking' === $hook ) {
			wp_enqueue_script(
				'chartjs',
				UNBSB_PLUGIN_URL . 'admin/js/chart.min.js',
				array(),
				'4.4.1',
				true
			);

			wp_enqueue_script(
				'unbsb-dashboard-charts',
				UNBSB_PLUGIN_URL . 'admin/js/unbsb-dashboard-charts.js',
				array( 'chartjs' ),
				$this->version,
				true
			);
		}

		wp_enqueue_script(
			'unbsb-admin',
			UNBSB_PLUGIN_URL . 'admin/js/unbsb-admin.js',
			array(),
			$this->version,
			true
		);

		wp_localize_script(
			'unbsb-admin',
			'unbsbAdmin',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'restUrl'   => rest_url( 'unbsb/v1/' ),
				'nonce'     => wp_create_nonce( 'unbsb_admin_nonce' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'strings'   => array(
					'confirm_delete'             => __( 'Are you sure you want to delete?', 'unbelievable-salon-booking' ),
					'saving'                     => __( 'Saving...', 'unbelievable-salon-booking' ),
					'saved'                      => __( 'Saved!', 'unbelievable-salon-booking' ),
					'error'                      => __( 'An error occurred.', 'unbelievable-salon-booking' ),
					'loading'                    => __( 'Loading...', 'unbelievable-salon-booking' ),
					'sending'                    => __( 'Sending...', 'unbelievable-salon-booking' ),
					'copied'                     => __( 'Copied!', 'unbelievable-salon-booking' ),
					// Category.
					'new_category'               => __( 'New Category', 'unbelievable-salon-booking' ),
					'edit_category'              => __( 'Edit Category', 'unbelievable-salon-booking' ),
					'category_name_required'     => __( 'Category name is required.', 'unbelievable-salon-booking' ),
					'category_added'             => __( 'Category added.', 'unbelievable-salon-booking' ),
					/* translators: %d: Number of services in the category */
					'category_has_services'      => __( 'This category has %d services. Are you sure you want to delete it? (Services will become uncategorized)', 'unbelievable-salon-booking' ),
					// Service.
					'new_service'                => __( 'New Service', 'unbelievable-salon-booking' ),
					'edit_service'               => __( 'Edit Service', 'unbelievable-salon-booking' ),
					// Staff.
					'new_staff'                  => __( 'New Staff', 'unbelievable-salon-booking' ),
					'edit_staff'                 => __( 'Edit Staff', 'unbelievable-salon-booking' ),
					'select_avatar'              => __( 'Select Staff Avatar', 'unbelievable-salon-booking' ),
					'use_image'                  => __( 'Use this image', 'unbelievable-salon-booking' ),
					// Customer.
					'new_customer'               => __( 'New Customer', 'unbelievable-salon-booking' ),
					'edit_customer'              => __( 'Edit Customer', 'unbelievable-salon-booking' ),
					// Booking.
					'fill_required_fields'       => __( 'Please fill in all required fields.', 'unbelievable-salon-booking' ),
					'create_booking'             => __( 'Create Booking', 'unbelievable-salon-booking' ),
					'update_booking'             => __( 'Update Booking', 'unbelievable-salon-booking' ),
					// SMS/Email.
					'enter_test_phone'           => __( 'Please enter a test phone number', 'unbelievable-salon-booking' ),
					'enter_test_email'           => __( 'Please enter a test email address', 'unbelievable-salon-booking' ),
					'select_template'            => __( 'Please select a template', 'unbelievable-salon-booking' ),
					'querying'                   => __( 'Querying...', 'unbelievable-salon-booking' ),
					'save_template'              => __( 'Save Template', 'unbelievable-salon-booking' ),
					'preview'                    => __( 'Preview', 'unbelievable-salon-booking' ),
					'test_send'                  => __( 'Send Test', 'unbelievable-salon-booking' ),
					'save_settings'              => __( 'Save Settings', 'unbelievable-salon-booking' ),
					'save_templates'             => __( 'Save Templates', 'unbelievable-salon-booking' ),
					'send_test_sms'              => __( 'Send Test SMS', 'unbelievable-salon-booking' ),
					'checking'                   => __( 'Checking...', 'unbelievable-salon-booking' ),
					'variable_added'             => __( 'Variable added', 'unbelievable-salon-booking' ),
					'active'                     => __( 'Active', 'unbelievable-salon-booking' ),
					'inactive'                   => __( 'Inactive', 'unbelievable-salon-booking' ),
					// Calendar.
					'month_names'                => array(
						__( 'January', 'unbelievable-salon-booking' ),
						__( 'February', 'unbelievable-salon-booking' ),
						__( 'March', 'unbelievable-salon-booking' ),
						__( 'April', 'unbelievable-salon-booking' ),
						__( 'May', 'unbelievable-salon-booking' ),
						__( 'June', 'unbelievable-salon-booking' ),
						__( 'July', 'unbelievable-salon-booking' ),
						__( 'August', 'unbelievable-salon-booking' ),
						__( 'September', 'unbelievable-salon-booking' ),
						__( 'October', 'unbelievable-salon-booking' ),
						__( 'November', 'unbelievable-salon-booking' ),
						__( 'December', 'unbelievable-salon-booking' ),
					),
					'day_names'                  => array(
						__( 'Mon', 'unbelievable-salon-booking' ),
						__( 'Tue', 'unbelievable-salon-booking' ),
						__( 'Wed', 'unbelievable-salon-booking' ),
						__( 'Thu', 'unbelievable-salon-booking' ),
						__( 'Fri', 'unbelievable-salon-booking' ),
						__( 'Sat', 'unbelievable-salon-booking' ),
						__( 'Sun', 'unbelievable-salon-booking' ),
					),
					// Detail modal.
					'customer'                   => __( 'Customer', 'unbelievable-salon-booking' ),
					'service'                    => __( 'Service', 'unbelievable-salon-booking' ),
					'staff'                      => __( 'Staff', 'unbelievable-salon-booking' ),
					'status'                     => __( 'Status', 'unbelievable-salon-booking' ),
					'price'                      => __( 'Price', 'unbelievable-salon-booking' ),
					// Status labels (calendar).
					'pending'                    => __( 'Pending', 'unbelievable-salon-booking' ),
					'confirmed'                  => __( 'Confirmed', 'unbelievable-salon-booking' ),
					'cancelled'                  => __( 'Cancelled', 'unbelievable-salon-booking' ),
					'no_show'                    => __( 'No Show', 'unbelievable-salon-booking' ),
					// Work schedule.
					'not_working'                => __( 'Not working', 'unbelievable-salon-booking' ),
					'no_holidays'                => __( 'No holidays registered', 'unbelievable-salon-booking' ),
					'confirm_delete_holiday'     => __( 'Do you want to delete this holiday?', 'unbelievable-salon-booking' ),
					'holiday_added'              => __( 'Holiday added', 'unbelievable-salon-booking' ),
					'holiday_deleted'            => __( 'Holiday deleted', 'unbelievable-salon-booking' ),
					// Toolbar.
					'bold_text'                  => __( 'text', 'unbelievable-salon-booking' ),
					'heading'                    => __( 'Heading', 'unbelievable-salon-booking' ),
					'paragraph_text'             => __( 'Paragraph text', 'unbelievable-salon-booking' ),
					'button_text'                => __( 'Button Text', 'unbelievable-salon-booking' ),
					'view_booking_btn'           => __( 'View My Booking', 'unbelievable-salon-booking' ),
					'link_text'                  => __( 'link text', 'unbelievable-salon-booking' ),
					// Promo codes.
					'new_promo_code'             => __( 'New Promo Code', 'unbelievable-salon-booking' ),
					'edit_promo_code'            => __( 'Edit Promo Code', 'unbelievable-salon-booking' ),
					// Staff compensation.
					'compensation'               => __( 'Compensation', 'unbelievable-salon-booking' ),
					'commission_rate'            => __( 'Commission Rate', 'unbelievable-salon-booking' ),
					'monthly_salary'             => __( 'Monthly Salary', 'unbelievable-salon-booking' ),
					'salary_percentage'          => __( 'Percentage', 'unbelievable-salon-booking' ),
					'salary_fixed'               => __( 'Fixed Salary', 'unbelievable-salon-booking' ),
					'salary_mix'                 => __( 'Mix', 'unbelievable-salon-booking' ),
					// Staff WordPress account.
					'wp_account'                 => __( 'WordPress Account', 'unbelievable-salon-booking' ),
					'no_account_linked'          => __( 'No account linked', 'unbelievable-salon-booking' ),
					'create_account'             => __( 'Create Account', 'unbelievable-salon-booking' ),
					'link_existing'              => __( 'Link Existing', 'unbelievable-salon-booking' ),
					'unlink'                     => __( 'Unlink', 'unbelievable-salon-booking' ),
					'account_created'            => __( 'WordPress account created.', 'unbelievable-salon-booking' ),
					'account_linked'             => __( 'Account linked.', 'unbelievable-salon-booking' ),
					'account_unlinked'           => __( 'Account unlinked.', 'unbelievable-salon-booking' ),
					'confirm_unlink'             => __( 'Are you sure you want to unlink this WordPress account?', 'unbelievable-salon-booking' ),
					'email_required_for_account' => __( 'Please enter an email address first.', 'unbelievable-salon-booking' ),
					'no_users_found'             => __( 'No users found.', 'unbelievable-salon-booking' ),
					'searching'                  => __( 'Searching...', 'unbelievable-salon-booking' ),
					// Staff services.
					'select_all'                 => __( 'Select All', 'unbelievable-salon-booking' ),
					'uncategorized'              => __( 'Uncategorized', 'unbelievable-salon-booking' ),
					'custom_price'               => __( 'Custom Price', 'unbelievable-salon-booking' ),
					'custom_duration'            => __( 'Custom Duration', 'unbelievable-salon-booking' ),
					// Export / Import.
					'exporting'                  => __( 'Exporting...', 'unbelievable-salon-booking' ),
					'export_success'             => __( 'Data exported successfully!', 'unbelievable-salon-booking' ),
					'export_error'               => __( 'Export failed. Please try again.', 'unbelievable-salon-booking' ),
					'export_categories'          => __( 'Categories', 'unbelievable-salon-booking' ),
					'export_services'            => __( 'Services', 'unbelievable-salon-booking' ),
					'export_staff'               => __( 'Staff', 'unbelievable-salon-booking' ),
					'export_customers'           => __( 'Customers', 'unbelievable-salon-booking' ),
					'export_bookings'            => __( 'Bookings', 'unbelievable-salon-booking' ),
					'export_promo_codes'         => __( 'Promo Codes', 'unbelievable-salon-booking' ),
					'importing'                  => __( 'Importing...', 'unbelievable-salon-booking' ),
					'import_success'             => __( 'Data imported successfully!', 'unbelievable-salon-booking' ),
					'import_error'               => __( 'Import failed. Please try again.', 'unbelievable-salon-booking' ),
					'import_no_file'             => __( 'Please select a JSON file to import.', 'unbelievable-salon-booking' ),
					'import_invalid_file'        => __( 'Invalid file type. Please select a .json file.', 'unbelievable-salon-booking' ),
					'import_file_too_large'      => __( 'File is too large. Maximum allowed size is 50MB.', 'unbelievable-salon-booking' ),
					'import_confirm_replace'     => __( 'Are you sure? Replace mode will permanently delete ALL existing data and replace it with imported data. This action cannot be undone!', 'unbelievable-salon-booking' ),
					'preparing_import'           => __( 'Preparing import...', 'unbelievable-salon-booking' ),
					'importing_data'             => __( 'Importing data...', 'unbelievable-salon-booking' ),
					'import_complete'            => __( 'Import complete!', 'unbelievable-salon-booking' ),
					'records_imported'           => __( 'records imported', 'unbelievable-salon-booking' ),
					// New Booking page.
					'nb_no_results'              => __( 'No customers found.', 'unbelievable-salon-booking' ),
					'nb_name_phone_required'     => __( 'Name and phone are required.', 'unbelievable-salon-booking' ),
					'nb_save_customer'           => __( 'Save Customer', 'unbelievable-salon-booking' ),
					'nb_select_service_first'    => __( 'Please select at least one service first.', 'unbelievable-salon-booking' ),
					'nb_any_staff'               => __( 'Any Staff', 'unbelievable-salon-booking' ),
					'nb_no_staff_available'      => __( 'No staff available for the selected services.', 'unbelievable-salon-booking' ),
					'nb_select_staff_date'       => __( 'Select staff and date to see available slots.', 'unbelievable-salon-booking' ),
					'nb_no_slots'                => __( 'No available slots for this date.', 'unbelievable-salon-booking' ),
					'nb_not_selected'            => __( 'Not selected', 'unbelievable-salon-booking' ),
					'nb_select_customer'         => __( 'Please select or create a customer.', 'unbelievable-salon-booking' ),
					'nb_select_service'          => __( 'Please select at least one service.', 'unbelievable-salon-booking' ),
					'nb_select_staff'            => __( 'Please select a staff member.', 'unbelievable-salon-booking' ),
					'nb_select_date'             => __( 'Please select a date.', 'unbelievable-salon-booking' ),
					'nb_select_time'             => __( 'Please select a time slot.', 'unbelievable-salon-booking' ),
					// Staff Portal.
					'sp_confirm_booking'         => __( 'Are you sure you want to confirm this booking?', 'unbelievable-salon-booking' ),
					'sp_reject_booking'          => __( 'Are you sure you want to reject this booking?', 'unbelievable-salon-booking' ),
					'sp_booking_confirmed'       => __( 'Booking confirmed.', 'unbelievable-salon-booking' ),
					'sp_booking_rejected'        => __( 'Booking rejected.', 'unbelievable-salon-booking' ),
					'sp_no_bookings'             => __( 'No bookings found for this period.', 'unbelievable-salon-booking' ),
					'sp_date_required'           => __( 'Please select a date.', 'unbelievable-salon-booking' ),
					'sp_holiday_added'           => __( 'Day off added.', 'unbelievable-salon-booking' ),
					'sp_holiday_removed'         => __( 'Day off removed.', 'unbelievable-salon-booking' ),
					'sp_confirm_remove_holiday'  => __( 'Are you sure you want to remove this day off?', 'unbelievable-salon-booking' ),
					'sp_no_holidays'             => __( 'No days off registered.', 'unbelievable-salon-booking' ),
					// Extra Day.
					'sp_time_required'           => __( 'Please enter start and end time.', 'unbelievable-salon-booking' ),
					'sp_end_after_start'         => __( 'End time must be after start time.', 'unbelievable-salon-booking' ),
					'sp_extra_day_added'         => __( 'Extra working day added.', 'unbelievable-salon-booking' ),
					'sp_extra_day_removed'       => __( 'Extra working day removed.', 'unbelievable-salon-booking' ),
					'sp_confirm_remove_extra'    => __( 'Are you sure you want to remove this extra working day?', 'unbelievable-salon-booking' ),
					'sp_no_extra_days'           => __( 'No extra working days registered.', 'unbelievable-salon-booking' ),
					// Complete Booking.
					'complete_booking'           => __( 'Complete Booking', 'unbelievable-salon-booking' ),
					'amount_received'            => __( 'Amount Received', 'unbelievable-salon-booking' ),
					'payment_method'             => __( 'Payment Method', 'unbelievable-salon-booking' ),
					'cash'                       => __( 'Cash', 'unbelievable-salon-booking' ),
					'card'                       => __( 'Card', 'unbelievable-salon-booking' ),
					'transfer'                   => __( 'Transfer', 'unbelievable-salon-booking' ),
					'complete_and_save'          => __( 'Complete & Save', 'unbelievable-salon-booking' ),
					'booking_completed'          => __( 'Booking completed successfully.', 'unbelievable-salon-booking' ),
					'completed'                  => __( 'Completed', 'unbelievable-salon-booking' ),
					// Earnings & Performance.
					'my_earnings'                => __( 'My Earnings', 'unbelievable-salon-booking' ),
					'my_performance'             => __( 'My Performance', 'unbelievable-salon-booking' ),
					'total_earnings'             => __( 'Total Earnings', 'unbelievable-salon-booking' ),
					'total_paid'                 => __( 'Total Paid', 'unbelievable-salon-booking' ),
					'remaining_balance'          => __( 'Remaining Balance', 'unbelievable-salon-booking' ),
					'this_month'                 => __( 'This Month', 'unbelievable-salon-booking' ),
					'last_month'                 => __( 'Last Month', 'unbelievable-salon-booking' ),
					'last_3_months'              => __( 'Last 3 Months', 'unbelievable-salon-booking' ),
					'custom_range'               => __( 'Custom Range', 'unbelievable-salon-booking' ),
					'record_payment'             => __( 'Record Payment', 'unbelievable-salon-booking' ),
					'payment_amount'             => __( 'Amount', 'unbelievable-salon-booking' ),
					'payment_date'               => __( 'Date', 'unbelievable-salon-booking' ),
					'payment_method'             => __( 'Payment Method', 'unbelievable-salon-booking' ),
					'payment_notes'              => __( 'Notes', 'unbelievable-salon-booking' ),
					'payment_recorded'           => __( 'Payment recorded successfully.', 'unbelievable-salon-booking' ),
					'payment_deleted'            => __( 'Payment deleted.', 'unbelievable-salon-booking' ),
					'confirm_delete_payment'     => __( 'Are you sure you want to delete this payment?', 'unbelievable-salon-booking' ),
					'no_earnings'                => __( 'No earnings found for this period.', 'unbelievable-salon-booking' ),
					'no_payments'                => __( 'No payments recorded yet.', 'unbelievable-salon-booking' ),
				),
				'currency'  => array(
					'symbol'   => get_option( 'unbsb_currency_symbol', '₺' ),
					'position' => get_option( 'unbsb_currency_position', 'after' ),
				),
				'locale'    => substr( get_locale(), 0, 2 ),
			)
		);

		// Page-specific data via wp_localize_script.
		if ( false !== strpos( $hook, 'unbsb-categories' ) ) {
			$category_model = new UNBSB_Category();
			wp_localize_script( 'unbsb-admin', 'unbsbCategories', $category_model->get_with_service_count() );
		}

		if ( false !== strpos( $hook, 'unbsb-services' ) ) {
			$service_model  = new UNBSB_Service();
			$category_model = new UNBSB_Category();
			wp_localize_script( 'unbsb-admin', 'unbsbServices', $service_model->get_with_categories() );
			wp_localize_script( 'unbsb-admin', 'unbsbCategories', $category_model->get_active() );
		}

		if ( false !== strpos( $hook, 'unbsb-staff' ) && false === strpos( $hook, 'unbsb-staff-schedule' ) ) {
			$staff_model   = new UNBSB_Staff();
			$service_model = new UNBSB_Service();
			$all_staff     = $staff_model->get_all();

			// Attach service data and WP user info.
			foreach ( $all_staff as &$s ) {
				$s->services        = $staff_model->get_services( $s->id );
				$s->service_details = $staff_model->get_services( $s->id, false );

				// Attach WP user login/email for account display.
				$s->wp_user_login = '';
				$s->wp_user_email = '';
				if ( ! empty( $s->user_id ) ) {
					$wp_user = get_userdata( $s->user_id );
					if ( $wp_user ) {
						$s->wp_user_login = $wp_user->user_login;
						$s->wp_user_email = $wp_user->user_email;
					}
				}
			}
			unset( $s );

			wp_localize_script( 'unbsb-admin', 'unbsbStaff', $all_staff );
			wp_localize_script( 'unbsb-admin', 'unbsbServices', $service_model->get_active() );
		}

		if ( false !== strpos( $hook, 'unbsb-promo-codes' ) ) {
			$promo_model    = new UNBSB_Promo_Code();
			$service_model  = new UNBSB_Service();
			$category_model = new UNBSB_Category();
			wp_localize_script( 'unbsb-admin', 'unbsbPromoCodes', $promo_model->get_all_with_usage() );
			wp_localize_script( 'unbsb-admin', 'unbsbServices', $service_model->get_active() );
			wp_localize_script( 'unbsb-admin', 'unbsbCategories', $category_model->get_active() );
		}

		if ( false !== strpos( $hook, 'unbsb-calendar' ) ) {
			$staff_model = new UNBSB_Staff();
			wp_localize_script( 'unbsb-admin', 'unbsbStaff', $staff_model->get_active() );
		}

		if ( false !== strpos( $hook, 'unbsb-new-booking' ) ) {
			$staff_model   = new UNBSB_Staff();
			$service_model = new UNBSB_Service();
			$all_staff     = $staff_model->get_active();

			// Attach service_ids to each staff member.
			foreach ( $all_staff as &$member ) {
				$member->service_ids = $staff_model->get_services( $member->id, true );
			}
			unset( $member );

			$booking_data = array(
				'staff' => $all_staff,
			);

			// Pre-select staff for staff portal new booking page.
			if ( false !== strpos( $hook, 'unbsb-staff-new-booking' ) ) {
				$current_staff = $this->get_current_staff();
				if ( $current_staff ) {
					$booking_data['preselectedStaffId'] = $current_staff->id;
				}
			}

			wp_localize_script(
				'unbsb-admin',
				'unbsbNewBookingData',
				$booking_data
			);
		}

		// Staff Portal - pass staff_id for schedule page.
		if ( false !== strpos( $hook, 'unbsb-staff-schedule-portal' ) ) {
			$staff = $this->get_current_staff();
			if ( $staff ) {
				wp_localize_script(
					'unbsb-admin',
					'unbsbStaffPortal',
					array(
						'staffId' => $staff->id,
					)
				);
			}
		}

		if ( 'toplevel_page_unbelievable-salon-booking' === $hook ) {
			wp_localize_script(
				'unbsb-dashboard-charts',
				'unbsbChartData',
				array(
					'data'           => $this->get_chart_data(),
					'currencySymbol' => get_option( 'unbsb_currency_symbol', '₺' ),
					'labels'         => array(
						'bookings' => __( 'Bookings', 'unbelievable-salon-booking' ),
						'revenue'  => __( 'Revenue', 'unbelievable-salon-booking' ),
					),
				)
			);
		}
	}

	/**
	 * Get dashboard chart data.
	 *
	 * @return array Chart data.
	 */
	private function get_chart_data() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'unbsb_bookings';
		$services_table = $wpdb->prefix . 'unbsb_services';

		$weekly_data    = array();
		$weekly_labels  = array();
		$weekly_revenue = array();

		for ( $i = 6; $i >= 0; $i-- ) {
			$date            = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
			$weekly_labels[] = date_i18n( 'D', strtotime( $date ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count         = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM ' . $bookings_table . " WHERE booking_date = %s AND status != 'cancelled'",
					$date
				)
			);
			$weekly_data[] = (int) $count;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$revenue          = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COALESCE(SUM(price), 0) FROM ' . $bookings_table . " WHERE booking_date = %s AND status IN ('confirmed', 'completed')",
					$date
				)
			);
			$weekly_revenue[] = (float) $revenue;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$service_stats = $wpdb->get_results(
			'SELECT s.name, COUNT(b.id) as count, s.color
			FROM ' . $bookings_table . ' b
			INNER JOIN ' . $services_table . " s ON b.service_id = s.id
			WHERE b.status != 'cancelled'
			GROUP BY b.service_id
			ORDER BY count DESC
			LIMIT 5"
		);

		$service_labels = array();
		$service_counts = array();
		$service_colors = array();

		foreach ( $service_stats as $stat ) {
			$service_labels[] = $stat->name;
			$service_counts[] = (int) $stat->count;
			$service_colors[] = ! empty( $stat->color ) ? $stat->color : '#3788d8';
		}

		$monthly_labels  = array();
		$monthly_revenue = array();

		for ( $i = 5; $i >= 0; $i-- ) {
			$month_start      = gmdate( 'Y-m-01', strtotime( "-{$i} months" ) );
			$month_end        = gmdate( 'Y-m-t', strtotime( "-{$i} months" ) );
			$monthly_labels[] = date_i18n( 'M', strtotime( $month_start ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$revenue           = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COALESCE(SUM(price), 0) FROM ' . $bookings_table . "
					WHERE booking_date BETWEEN %s AND %s AND status IN ('confirmed', 'completed')",
					$month_start,
					$month_end
				)
			);
			$monthly_revenue[] = (float) $revenue;
		}

		return array(
			'weekly'   => array(
				'labels'  => $weekly_labels,
				'data'    => $weekly_data,
				'revenue' => $weekly_revenue,
			),
			'services' => array(
				'labels' => $service_labels,
				'data'   => $service_counts,
				'colors' => $service_colors,
			),
			'monthly'  => array(
				'labels' => $monthly_labels,
				'data'   => $monthly_revenue,
			),
		);
	}

	/**
	 * Dashboard page
	 */
	public function render_dashboard() {
		global $wpdb;

		$booking_model  = new UNBSB_Booking();
		$service_model  = new UNBSB_Service();
		$staff_model    = new UNBSB_Staff();
		$customer_model = new UNBSB_Customer();

		$stats = array(
			'total_bookings'   => $booking_model->count(),
			'pending_bookings' => $booking_model->count( 'pending' ),
			'today_bookings'   => $booking_model->count_today(),
			'total_services'   => $service_model->count(),
			'total_staff'      => $staff_model->count(),
			'total_customers'  => $customer_model->count(),
		);

		// Recent bookings.
		$recent_bookings = $booking_model->get_all(
			array(
				'orderby' => 'created_at',
				'order'   => 'DESC',
				'limit'   => 10,
			)
		);

		// Chart data - Last 7 days booking counts.
		$bookings_table = $wpdb->prefix . 'unbsb_bookings';
		$weekly_data    = array();
		$weekly_labels  = array();
		$weekly_revenue = array();

		for ( $i = 6; $i >= 0; $i-- ) {
			$date            = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
			$weekly_labels[] = date_i18n( 'D', strtotime( $date ) );

			// Booking count.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count         = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM ' . $bookings_table . " WHERE booking_date = %s AND status != 'cancelled'",
					$date
				)
			);
			$weekly_data[] = (int) $count;

			// Daily revenue.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$revenue          = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COALESCE(SUM(price), 0) FROM ' . $bookings_table . " WHERE booking_date = %s AND status IN ('confirmed', 'completed')",
					$date
				)
			);
			$weekly_revenue[] = (float) $revenue;
		}

		// Service distribution (for pie chart).
		$services_table = $wpdb->prefix . 'unbsb_services';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$service_stats = $wpdb->get_results(
			'SELECT s.name, COUNT(b.id) as count, s.color
			FROM ' . $bookings_table . ' b
			INNER JOIN ' . $services_table . " s ON b.service_id = s.id
			WHERE b.status != 'cancelled'
			GROUP BY b.service_id
			ORDER BY count DESC
			LIMIT 5"
		);

		$service_labels = array();
		$service_counts = array();
		$service_colors = array();

		foreach ( $service_stats as $stat ) {
			$service_labels[] = $stat->name;
			$service_counts[] = (int) $stat->count;
			$service_colors[] = ! empty( $stat->color ) ? $stat->color : '#3788d8';
		}

		// Monthly revenue (last 6 months).
		$monthly_labels  = array();
		$monthly_revenue = array();

		for ( $i = 5; $i >= 0; $i-- ) {
			$month_start      = gmdate( 'Y-m-01', strtotime( "-{$i} months" ) );
			$month_end        = gmdate( 'Y-m-t', strtotime( "-{$i} months" ) );
			$monthly_labels[] = date_i18n( 'M', strtotime( $month_start ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$revenue           = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COALESCE(SUM(price), 0) FROM ' . $bookings_table . "
					WHERE booking_date BETWEEN %s AND %s AND status IN ('confirmed', 'completed')",
					$month_start,
					$month_end
				)
			);
			$monthly_revenue[] = (float) $revenue;
		}

		// Today's bookings.
		$today               = current_time( 'Y-m-d' );
		$today_bookings_list = $booking_model->get_all(
			array(
				'date_from' => $today,
				'date_to'   => $today,
				'orderby'   => 'start_time',
				'order'     => 'ASC',
			)
		);

		// Total revenue this month.
		$month_start = gmdate( 'Y-m-01' );
		$month_end   = gmdate( 'Y-m-t' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$monthly_total = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COALESCE(SUM(price), 0) FROM ' . $bookings_table . "
				WHERE booking_date BETWEEN %s AND %s AND status IN ('confirmed', 'completed')",
				$month_start,
				$month_end
			)
		);

		// --- Enriched metrics ---

		$customers_table = $wpdb->prefix . 'unbsb_customers';
		$staff_table     = $wpdb->prefix . 'unbsb_staff';

		// Top 5 services (with booking count + revenue).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$top_services = $wpdb->get_results(
			'SELECT s.name, COUNT(b.id) as booking_count, COALESCE(SUM(b.price), 0) as total_revenue
			FROM ' . $bookings_table . ' b
			INNER JOIN ' . $services_table . " s ON b.service_id = s.id
			WHERE b.status IN ('confirmed', 'completed')
			GROUP BY b.service_id
			ORDER BY booking_count DESC
			LIMIT 5"
		);

		// Staff performance (this month: bookings + revenue per staff).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$staff_performance = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT st.name, st.avatar_url, COUNT(b.id) as booking_count, COALESCE(SUM(b.price), 0) as total_revenue
				FROM ' . $bookings_table . ' b
				INNER JOIN ' . $staff_table . " st ON b.staff_id = st.id
				WHERE b.booking_date BETWEEN %s AND %s AND b.status IN ('confirmed', 'completed')
				GROUP BY b.staff_id
				ORDER BY total_revenue DESC",
				$month_start,
				$month_end
			)
		);

		// Cancellation rate.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_all_bookings = (int) $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . $bookings_table
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cancelled_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$bookings_table} WHERE status = 'cancelled'"
		);
		$cancellation_rate = $total_all_bookings > 0
			? round( ( $cancelled_count / $total_all_bookings ) * 100, 1 )
			: 0;

		// Customer stats (total, new this month, returning).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_customers_count = (int) $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . $customers_table
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$new_customers_month = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . $customers_table . '
				WHERE created_at >= %s',
				$month_start
			)
		);
		// Returning = customers with more than 1 booking.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$returning_customers = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT customer_email FROM {$bookings_table}
				WHERE status != 'cancelled'
				GROUP BY customer_email
				HAVING COUNT(*) > 1
			) as returning_cust"
		);

		$customer_stats = array(
			'total'     => $total_customers_count,
			'new_month' => $new_customers_month,
			'returning' => $returning_customers,
		);

		// Revenue summary (today, this week, this month).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$revenue_today = (float) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COALESCE(SUM(price), 0) FROM ' . $bookings_table . "
				WHERE booking_date = %s AND status IN ('confirmed', 'completed')",
				$today
			)
		);
		$week_start = gmdate( 'Y-m-d', strtotime( 'monday this week' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$revenue_week = (float) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COALESCE(SUM(price), 0) FROM ' . $bookings_table . "
				WHERE booking_date BETWEEN %s AND %s AND status IN ('confirmed', 'completed')",
				$week_start,
				$today
			)
		);

		$revenue_summary = array(
			'today' => $revenue_today,
			'week'  => $revenue_week,
			'month' => (float) $monthly_total,
		);

		// Average booking value.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$avg_booking_value = (float) $wpdb->get_var(
			"SELECT COALESCE(AVG(price), 0) FROM {$bookings_table} WHERE status IN ('confirmed', 'completed')"
		);

		// Chart data.
		$chart_data = array(
			'weekly'   => array(
				'labels'  => $weekly_labels,
				'data'    => $weekly_data,
				'revenue' => $weekly_revenue,
			),
			'services' => array(
				'labels' => $service_labels,
				'data'   => $service_counts,
				'colors' => $service_colors,
			),
			'monthly'  => array(
				'labels' => $monthly_labels,
				'data'   => $monthly_revenue,
			),
		);

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-dashboard.php';
	}

	/**
	 * Bookings page
	 */
	public function render_bookings() {
		$booking_model  = new UNBSB_Booking();
		$staff_model    = new UNBSB_Staff();
		$service_model  = new UNBSB_Service();
		$category_model = new UNBSB_Category();

		$staff      = $staff_model->get_active();
		$services   = $service_model->get_with_categories();
		$categories = $category_model->get_active();

		// Filters - GET parameters are used for admin page filtering.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for read-only filtering.
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for read-only filtering.
		$staff_id = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for read-only filtering.
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for read-only filtering.
		$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for read-only filtering.
		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

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
		if ( $search ) {
			$args['search'] = $search;
		}

		$bookings = $booking_model->get_all( $args );

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-bookings.php';
	}

	/**
	 * Calendar page
	 */
	public function render_calendar() {
		$staff_model   = new UNBSB_Staff();
		$service_model = new UNBSB_Service();
		$staff         = $staff_model->get_active();
		$services      = $service_model->get_active();

		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-calendar.php';
	}

	/**
	 * Categories page
	 */
	public function render_categories() {
		$category_model = new UNBSB_Category();
		$categories     = $category_model->get_with_service_count();

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-categories.php';
	}

	/**
	 * Services page
	 */
	public function render_services() {
		$service_model  = new UNBSB_Service();
		$category_model = new UNBSB_Category();
		$services       = $service_model->get_with_categories();
		$categories     = $category_model->get_active();

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-services.php';
	}

	/**
	 * Staff page
	 */
	public function render_staff() {
		$staff_model    = new UNBSB_Staff();
		$service_model  = new UNBSB_Service();
		$category_model = new UNBSB_Category();

		$staff      = $staff_model->get_all();
		$services   = $service_model->get_active();
		$categories = $category_model->get_active();

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-staff.php';
	}

	/**
	 * Customers page
	 */
	public function render_customers() {
		$customer_model = new UNBSB_Customer();
		$customers      = $customer_model->get_all( array( 'limit' => 50 ) );

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-customers.php';
	}

	/**
	 * Settings page
	 */
	public function render_settings() {
		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-settings.php';
	}

	/**
	 * Email Templates page
	 */
	public function render_email_templates() {
		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-email-templates.php';
	}

	/**
	 * Work Schedule page
	 */
	public function render_staff_schedule() {
		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-staff-schedule.php';
	}

	/**
	 * Export / Import page
	 */
	public function render_export_import() {
		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-export-import.php';
	}

	/**
	 * New Booking full page
	 */
	public function render_new_booking() {
		$staff_model    = new UNBSB_Staff();
		$service_model  = new UNBSB_Service();
		$category_model = new UNBSB_Category();

		$staff      = $staff_model->get_active();
		$services   = $service_model->get_with_categories();
		$categories = $category_model->get_active();

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-new-booking.php';
	}

	/**
	 * Staff Portal - My Bookings
	 */
	public function render_staff_bookings() {
		$user_id     = get_current_user_id();
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_by_user_id( $user_id );

		if ( ! $staff ) {
			wp_die( esc_html__( 'You are not registered as a staff member.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filtering.
		$filter = isset( $_GET['filter'] ) ? sanitize_text_field( wp_unslash( $_GET['filter'] ) ) : 'all';

		$today = current_time( 'Y-m-d' );
		$args  = array(
			'staff_id' => $staff->id,
			'orderby'  => 'booking_date',
			'order'    => 'ASC',
		);

		if ( 'today' === $filter ) {
			$args['date_from'] = $today;
			$args['date_to']   = $today;
		} elseif ( 'week' === $filter ) {
			$args['date_from'] = gmdate( 'Y-m-d', strtotime( 'monday this week' ) );
			$args['date_to']   = gmdate( 'Y-m-d', strtotime( 'sunday this week' ) );
		} elseif ( 'month' === $filter ) {
			$args['date_from'] = gmdate( 'Y-m-01' );
			$args['date_to']   = gmdate( 'Y-m-t' );
		}

		$bookings = $booking_model->get_all( $args );

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-staff-bookings.php';
	}

	/**
	 * Staff Portal - My Schedule
	 */
	public function render_staff_schedule_portal() {
		$user_id     = get_current_user_id();
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_by_user_id( $user_id );

		if ( ! $staff ) {
			wp_die( esc_html__( 'You are not registered as a staff member.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = $staff->id;

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-staff-schedule-own.php';
	}

	/**
	 * Render staff earnings portal page
	 */
	public function render_staff_earnings_portal() {
		$staff_model = new UNBSB_Staff();

		if ( current_user_can( 'manage_options' ) ) {
			$staff_id = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;
			$staff    = $staff_id ? $staff_model->get( $staff_id ) : null;
		} else {
			$staff = $staff_model->get_by_user_id( get_current_user_id() );
		}

		if ( ! $staff ) {
			wp_die( esc_html__( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$summary         = $staff_model->get_earnings_summary( $staff->id );
		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
		$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );

		include plugin_dir_path( __FILE__ ) . 'partials/admin-staff-earnings.php';
	}

	/**
	 * Render staff performance portal page
	 */
	public function render_staff_performance_portal() {
		$staff_model = new UNBSB_Staff();

		if ( current_user_can( 'manage_options' ) ) {
			$staff_id = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;
			$staff    = $staff_id ? $staff_model->get( $staff_id ) : null;
		} else {
			$staff = $staff_model->get_by_user_id( get_current_user_id() );
		}

		if ( ! $staff ) {
			wp_die( esc_html__( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$date_from       = wp_date( 'Y-m-01' );
		$date_to         = wp_date( 'Y-m-t' );
		$metrics         = $staff_model->get_performance_metrics( $staff->id, $date_from, $date_to );
		$top_services    = $staff_model->get_top_services( $staff->id, $date_from, $date_to );
		$trend           = $staff_model->get_monthly_trend( $staff->id );
		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
		$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );

		include plugin_dir_path( __FILE__ ) . 'partials/admin-staff-performance.php';
	}

	/**
	 * Promo Codes page
	 */
	public function render_promo_codes() {
		$promo_model    = new UNBSB_Promo_Code();
		$service_model  = new UNBSB_Service();
		$category_model = new UNBSB_Category();

		$promo_codes     = $promo_model->get_all_with_usage();
		$services        = $service_model->get_active();
		$categories      = $category_model->get_active();
		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-promo-codes.php';
	}

	/**
	 * AJAX: Save promo code
	 */
	public function ajax_save_promo_code() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'code'                  => isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '',
			'description'           => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'discount_type'         => isset( $_POST['discount_type'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_type'] ) ) : 'percentage',
			'discount_value'        => isset( $_POST['discount_value'] ) ? floatval( $_POST['discount_value'] ) : 0,
			'first_time_only'       => ! empty( $_POST['first_time_only'] ) ? 1 : 0,
			'min_services'          => isset( $_POST['min_services'] ) ? absint( $_POST['min_services'] ) : 0,
			'min_order_amount'      => isset( $_POST['min_order_amount'] ) ? floatval( $_POST['min_order_amount'] ) : 0,
			'max_uses'              => isset( $_POST['max_uses'] ) ? absint( $_POST['max_uses'] ) : 0,
			'max_uses_per_customer' => isset( $_POST['max_uses_per_customer'] ) ? absint( $_POST['max_uses_per_customer'] ) : 0,
			'applicable_services'   => ! empty( $_POST['applicable_services'] ) ? array_map( 'absint', (array) $_POST['applicable_services'] ) : null,
			'applicable_categories' => ! empty( $_POST['applicable_categories'] ) ? array_map( 'absint', (array) $_POST['applicable_categories'] ) : null,
			'start_date'            => isset( $_POST['start_date'] ) && '' !== $_POST['start_date'] ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : null,
			'end_date'              => isset( $_POST['end_date'] ) && '' !== $_POST['end_date'] ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : null,
			'status'                => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		);

		if ( empty( $data['code'] ) ) {
			wp_send_json_error( __( 'Promo code is required.', 'unbelievable-salon-booking' ) );
		}

		// Validate discount value for percentage and fixed_amount types.
		if ( 'cheapest_free' !== $data['discount_type'] && $data['discount_value'] <= 0 ) {
			wp_send_json_error( __( 'Discount value must be greater than 0.', 'unbelievable-salon-booking' ) );
		}

		if ( 'percentage' === $data['discount_type'] && $data['discount_value'] > 100 ) {
			wp_send_json_error( __( 'Percentage discount cannot exceed 100%.', 'unbelievable-salon-booking' ) );
		}

		$promo_model = new UNBSB_Promo_Code();

		// Check for duplicate code (excluding current record when editing).
		$existing = $promo_model->get_by_code( $data['code'] );
		if ( $existing && ( ! $id || (int) $existing->id !== $id ) ) {
			wp_send_json_error( __( 'This promo code already exists.', 'unbelievable-salon-booking' ) );
		}

		if ( $id ) {
			$result = $promo_model->update( $id, $data );
		} else {
			$result = $promo_model->create( $data );
		}

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Promo code saved.', 'unbelievable-salon-booking' ),
					'id'      => $id ? $id : $result,
				)
			);
		} else {
			wp_send_json_error( __( 'Save error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Delete promo code
	 */
	public function ajax_delete_promo_code() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid ID.', 'unbelievable-salon-booking' ) );
		}

		$promo_model = new UNBSB_Promo_Code();
		$result      = $promo_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Promo code deleted.', 'unbelievable-salon-booking' ) );
		} else {
			wp_send_json_error( __( 'Delete error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Save category
	 */
	public function ajax_save_category() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
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

		$category_model = new UNBSB_Category();

		if ( $id ) {
			$result = $category_model->update( $id, $data );
		} else {
			$result = $category_model->create( $data );
		}

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Category saved.', 'unbelievable-salon-booking' ),
					'id'      => $id ? $id : $result,
				)
			);
		} else {
			wp_send_json_error( __( 'Save error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Delete category
	 */
	public function ajax_delete_category() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid ID.', 'unbelievable-salon-booking' ) );
		}

		$category_model = new UNBSB_Category();
		$result         = $category_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Category deleted.', 'unbelievable-salon-booking' ) );
		} else {
			wp_send_json_error( __( 'Delete error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Save service
	 */
	public function ajax_save_service() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'category_id'     => isset( $_POST['category_id'] ) && '' !== $_POST['category_id'] ? absint( $_POST['category_id'] ) : null,
			'name'            => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'description'     => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'duration'        => isset( $_POST['duration'] ) ? absint( $_POST['duration'] ) : 30,
			'price'           => isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : 0,
			'discounted_price' => isset( $_POST['discounted_price'] ) && '' !== $_POST['discounted_price'] ? floatval( $_POST['discounted_price'] ) : null,
			'buffer_before'   => isset( $_POST['buffer_before'] ) ? absint( $_POST['buffer_before'] ) : 0,
			'buffer_after'    => isset( $_POST['buffer_after'] ) ? absint( $_POST['buffer_after'] ) : 0,
			'color'           => isset( $_POST['color'] ) ? sanitize_hex_color( wp_unslash( $_POST['color'] ) ) : '#3788d8',
			'status'          => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		);

		// Validate discounted price.
		if ( null !== $data['discounted_price'] && ( $data['discounted_price'] <= 0 || $data['discounted_price'] >= $data['price'] ) ) {
			wp_send_json_error( __( 'Discounted price must be greater than 0 and less than the regular price.', 'unbelievable-salon-booking' ) );
		}

		$service_model = new UNBSB_Service();

		if ( $id ) {
			$result = $service_model->update( $id, $data );
		} else {
			$result = $service_model->create( $data );
		}

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Service saved.', 'unbelievable-salon-booking' ),
					'id'      => $id ? $id : $result,
				)
			);
		} else {
			wp_send_json_error( __( 'Save error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Delete service
	 */
	public function ajax_delete_service() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid ID.', 'unbelievable-salon-booking' ) );
		}

		$service_model = new UNBSB_Service();
		$result        = $service_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Service deleted.', 'unbelievable-salon-booking' ) );
		} else {
			wp_send_json_error( __( 'Delete error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Save staff
	 */
	public function ajax_save_staff() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		// Build services data with optional custom price/duration.
		$service_ids       = isset( $_POST['services'] ) ? array_map( 'absint', (array) $_POST['services'] ) : array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below per element.
		$service_prices    = isset( $_POST['service_prices'] ) ? (array) wp_unslash( $_POST['service_prices'] ) : array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below per element.
		$service_durations = isset( $_POST['service_durations'] ) ? (array) wp_unslash( $_POST['service_durations'] ) : array();

		$services = array();
		foreach ( $service_ids as $sid ) {
			$service_entry = array( 'service_id' => $sid );

			if ( isset( $service_prices[ $sid ] ) && '' !== $service_prices[ $sid ] ) {
				$service_entry['custom_price'] = floatval( $service_prices[ $sid ] );
			}

			if ( isset( $service_durations[ $sid ] ) && '' !== $service_durations[ $sid ] ) {
				$service_entry['custom_duration'] = absint( $service_durations[ $sid ] );
			}

			$services[] = $service_entry;
		}

		$data = array(
			'name'              => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'email'             => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone'             => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'bio'               => isset( $_POST['bio'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bio'] ) ) : '',
			'status'            => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
			'salary_type'       => isset( $_POST['salary_type'] ) ? sanitize_text_field( wp_unslash( $_POST['salary_type'] ) ) : 'percentage',
			'salary_percentage' => isset( $_POST['salary_percentage'] ) ? floatval( $_POST['salary_percentage'] ) : 0,
			'salary_fixed'      => isset( $_POST['salary_fixed'] ) ? floatval( $_POST['salary_fixed'] ) : 0,
			'services'          => $services,
		);

		$staff_model = new UNBSB_Staff();

		if ( $id ) {
			$result = $staff_model->update( $id, $data );
		} else {
			$result = $staff_model->create( $data );
		}

		if ( false !== $result ) {
			$staff_id_saved = $id ? $id : $result;
			$staff_services = $staff_model->get_services( $staff_id_saved, false );

			// Get saved staff record for user_id info.
			$saved_staff = $staff_model->get( $staff_id_saved );
			$user_id     = ! empty( $saved_staff->user_id ) ? absint( $saved_staff->user_id ) : 0;
			$user_login  = '';

			if ( $user_id ) {
				$wp_user = get_user_by( 'id', $user_id );
				if ( $wp_user ) {
					$user_login = $wp_user->user_login;
				}
			}

			wp_send_json_success(
				array(
					'message'    => __( 'Staff saved.', 'unbelievable-salon-booking' ),
					'id'         => $staff_id_saved,
					'services'   => $staff_services,
					'user_id'    => $user_id,
					'user_login' => $user_login,
				)
			);
		} else {
			wp_send_json_error( __( 'Save error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Delete staff
	 */
	public function ajax_delete_staff() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid ID.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$result      = $staff_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Staff deleted.', 'unbelievable-salon-booking' ) );
		} else {
			wp_send_json_error( __( 'Delete error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Save customer
	 */
	public function ajax_save_customer() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'name'  => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone' => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'notes' => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		);

		if ( empty( $data['name'] ) ) {
			wp_send_json_error( __( 'Customer name is required.', 'unbelievable-salon-booking' ) );
		}

		if ( ! empty( $data['email'] ) && ! is_email( $data['email'] ) ) {
			wp_send_json_error( __( 'Please enter a valid email address.', 'unbelievable-salon-booking' ) );
		}

		$customer_model = new UNBSB_Customer();

		// Check for duplicate email (excluding current customer when editing).
		$existing = $customer_model->get_by_email( $data['email'] );
		if ( $existing && ( ! $id || absint( $existing->id ) !== $id ) ) {
			wp_send_json_error( __( 'A customer with this email already exists.', 'unbelievable-salon-booking' ) );
		}

		if ( $id ) {
			$result = $customer_model->update( $id, $data );
		} else {
			// Create WordPress user if not exists.
			if ( ! email_exists( $data['email'] ) ) {
				$password = wp_generate_password( 12, true, false );
				$user_id  = wp_insert_user(
					array(
						'user_login'   => $data['email'],
						'user_email'   => $data['email'],
						'user_pass'    => $password,
						'display_name' => $data['name'],
						'role'         => 'unbsb_customer',
					)
				);

				if ( ! is_wp_error( $user_id ) ) {
					$data['user_id'] = $user_id;
				}
			} else {
				// Link to existing WP user.
				$wp_user = get_user_by( 'email', $data['email'] );
				if ( $wp_user ) {
					$data['user_id'] = $wp_user->ID;
				}
			}

			$result = $customer_model->create( $data );
		}

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Customer saved.', 'unbelievable-salon-booking' ),
					'id'      => $id ? $id : $result,
				)
			);
		} else {
			wp_send_json_error( __( 'Save error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Delete customer
	 */
	public function ajax_delete_customer() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid ID.', 'unbelievable-salon-booking' ) );
		}

		$customer_model = new UNBSB_Customer();
		$result         = $customer_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Customer deleted.', 'unbelievable-salon-booking' ) );
		} else {
			wp_send_json_error( __( 'Delete error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Delete booking
	 */
	public function ajax_delete_booking() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid booking ID.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();
		$result        = $booking_model->delete( $id );

		if ( $result ) {
			wp_send_json_success( __( 'Booking deleted.', 'unbelievable-salon-booking' ) );
		} else {
			wp_send_json_error( __( 'Delete error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Update booking status
	 */
	public function ajax_update_booking_status() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';

		if ( ! $id || ! $status ) {
			wp_send_json_error( __( 'Invalid parameters.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();
		$result        = $booking_model->update_status( $id, $status );

		if ( $result ) {
			wp_send_json_success( __( 'Status updated.', 'unbelievable-salon-booking' ) );
		} else {
			wp_send_json_error( __( 'Update error.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Get bookings
	 */
	public function ajax_get_bookings() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unbsb_view_own_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$start    = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
		$end      = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : '';
		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		// Staff users can only see their own bookings.
		if ( ! current_user_can( 'manage_options' ) ) {
			$current_staff = $this->get_current_staff();
			if ( $current_staff ) {
				$staff_id = absint( $current_staff->id );
			} else {
				wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
			}
		}

		$calendar = new UNBSB_Calendar();
		$events   = $calendar->get_calendar_events( $start, $end, $staff_id );

		wp_send_json_success( $events );
	}

	/**
	 * AJAX: Create booking
	 */
	public function ajax_create_booking() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		// Allow admin or staff with confirm_bookings capability.
		$is_staff_user = ! current_user_can( 'manage_options' ) && current_user_can( 'unbsb_confirm_bookings' );

		if ( ! current_user_can( 'manage_options' ) && ! $is_staff_user ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		// Support both service_id (single) and service_ids[] (multi) from new booking page.
		$service_id = 0;
		if ( ! empty( $_POST['service_id'] ) ) {
			$service_id = absint( $_POST['service_id'] );
		} elseif ( ! empty( $_POST['service_ids'] ) && is_array( $_POST['service_ids'] ) ) {
			$service_id = absint( $_POST['service_ids'][0] );
		}

		// Staff users must use their own staff_id.
		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( $is_staff_user ) {
			$current_staff = $this->get_current_staff();
			if ( ! $current_staff ) {
				wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
			}
			$staff_id = absint( $current_staff->id );
		}

		$data = array(
			'service_id'     => $service_id,
			'staff_id'       => $staff_id,
			'customer_id'    => isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0,
			'customer_name'  => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
			'customer_email' => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
			'customer_phone' => isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '',
			'booking_date'   => isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '',
			'start_time'     => isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '',
			'status'         => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending',
			'notes'          => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
			'internal_notes' => isset( $_POST['internal_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['internal_notes'] ) ) : '',
		);

		// Required field validation.
		if ( empty( $data['service_id'] ) || empty( $data['staff_id'] ) || empty( $data['customer_name'] ) ||
			empty( $data['booking_date'] ) || empty( $data['start_time'] ) ) {
			wp_send_json_error( __( 'Please fill in all required fields.', 'unbelievable-salon-booking' ) );
		}

		// Email validation (only if provided).
		if ( ! empty( $data['customer_email'] ) && ! is_email( $data['customer_email'] ) ) {
			wp_send_json_error( __( 'Invalid email address.', 'unbelievable-salon-booking' ) );
		}

		// Date format validation.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data['booking_date'] ) ) {
			wp_send_json_error( __( 'Invalid date format.', 'unbelievable-salon-booking' ) );
		}

		// Pass service_ids for multi-service support.
		if ( ! empty( $_POST['service_ids'] ) && is_array( $_POST['service_ids'] ) ) {
			$data['service_ids'] = array_map( 'absint', $_POST['service_ids'] );
		}

		$booking_model = new UNBSB_Booking();
		$result        = $booking_model->create( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( $result ) {
			wp_send_json_success(
				array(
					'message'    => __( 'Booking created successfully.', 'unbelievable-salon-booking' ),
					'booking_id' => $result,
				)
			);
		} else {
			wp_send_json_error( __( 'An error occurred while creating the booking.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Save settings
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$settings = array(
			'unbsb_time_slot_interval',
			'unbsb_booking_lead_time',
			'unbsb_booking_future_days',
			'unbsb_booking_flow_mode',
			'unbsb_currency',
			'unbsb_currency_symbol',
			'unbsb_currency_position',
			'unbsb_date_format',
			'unbsb_time_format',
			'unbsb_admin_email',
			'unbsb_email_from_name',
			'unbsb_email_from_address',
			'unbsb_company_name',
			'unbsb_company_phone',
			'unbsb_company_address',
			// Cancel/reschedule settings.
			'unbsb_cancel_deadline_hours',
			'unbsb_reschedule_deadline_hours',
			'unbsb_max_reschedules',
			// SMS settings.
			'unbsb_sms_provider',
			'unbsb_sms_netgsm_username',
			'unbsb_sms_netgsm_password',
			'unbsb_sms_netgsm_sender',
			'unbsb_sms_reminder_hours',
			// Email settings.
			'unbsb_email_reminder_hours',
			'unbsb_email_logo_url',
			'unbsb_email_primary_color',
			// SEO settings.
			'unbsb_seo_business_type',
			'unbsb_seo_price_range',
			'unbsb_seo_description',
			'unbsb_seo_logo_url',
			'unbsb_seo_city',
			'unbsb_seo_postal_code',
			'unbsb_seo_country',
			// Social media.
			'unbsb_social_facebook',
			'unbsb_social_instagram',
			'unbsb_social_twitter',
			'unbsb_social_twitter_handle',
			// Appearance.
			'unbsb_appearance_primary_color',
			'unbsb_appearance_accent_color',
			'unbsb_appearance_border_radius',
			'unbsb_appearance_font_size',
			// Security / CAPTCHA.
			'unbsb_captcha_provider',
			'unbsb_captcha_site_key',
			'unbsb_captcha_secret_key',
			'unbsb_captcha_min_score',
		);

		// Sensitive settings that need encryption.
		$encrypted_settings = array( 'unbsb_sms_netgsm_password', 'unbsb_captcha_secret_key' );

		foreach ( $settings as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

				// Use encryption for sensitive data.
				if ( in_array( $key, $encrypted_settings, true ) ) {
					UNBSB_Encryption::save_option( $key, $value );
				} else {
					update_option( $key, $value );
				}
			}
		}

		// Checkbox settings (not present in POST if unchecked).
		$checkbox_settings = array(
			'unbsb_enable_ics',
			'unbsb_allow_cancel',
			'unbsb_allow_reschedule',
			'unbsb_enable_multi_service',
			'unbsb_auto_confirm',
			'unbsb_sms_enabled',
			'unbsb_sms_reminder_enabled',
			'unbsb_sms_on_booking',
			'unbsb_sms_on_confirmation',
			'unbsb_sms_on_cancellation',
			'unbsb_email_reminder_enabled',
			// SEO.
			'unbsb_seo_enabled',
			// Security.
			'unbsb_security_logging_enabled',
		);

		foreach ( $checkbox_settings as $key ) {
			$value = isset( $_POST[ $key ] ) ? 'yes' : 'no';
			update_option( $key, $value );
		}

		wp_send_json_success( __( 'Settings saved.', 'unbelievable-salon-booking' ) );
	}

	/**
	 * AJAX: Save working hours
	 */
	public function ajax_save_working_hours() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in update_working_hours method.
		$hours = isset( $_POST['hours'] ) ? wp_unslash( $_POST['hours'] ) : array();

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Invalid staff.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$staff_model->update_working_hours( $staff_id, $hours );

		wp_send_json_success( __( 'Working hours saved.', 'unbelievable-salon-booking' ) );
	}

	/**
	 * AJAX: Send SMS test
	 */
	public function ajax_sms_send_test() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

		if ( empty( $phone ) ) {
			wp_send_json_error( __( 'Phone number is required.', 'unbelievable-salon-booking' ) );
		}

		$sms_manager = new UNBSB_SMS_Manager();
		$result      = $sms_manager->send_test( $phone );

		if ( $result['success'] ) {
			wp_send_json_success( $result['message'] );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * AJAX: Query SMS balance
	 */
	public function ajax_sms_get_balance() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$sms_manager = new UNBSB_SMS_Manager();
		$result      = $sms_manager->get_balance();

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * AJAX: Save SMS templates
	 */
	public function ajax_save_sms_templates() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$templates_raw = isset( $_POST['templates'] ) ? wp_unslash( $_POST['templates'] ) : '';
		$templates     = json_decode( $templates_raw, true );

		if ( empty( $templates ) || ! is_array( $templates ) ) {
			wp_send_json_error( __( 'Invalid template data.', 'unbelievable-salon-booking' ) );
		}

		$sms_manager = new UNBSB_SMS_Manager();

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

		wp_send_json_success( __( 'SMS templates saved.', 'unbelievable-salon-booking' ) );
	}

	/**
	 * AJAX: Save email templates
	 */
	public function ajax_save_email_templates() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$templates_raw = isset( $_POST['templates'] ) ? sanitize_text_field( wp_unslash( $_POST['templates'] ) ) : '';
		$templates     = json_decode( $templates_raw, true );

		if ( empty( $templates ) || ! is_array( $templates ) ) {
			wp_send_json_error( __( 'Invalid template data.', 'unbelievable-salon-booking' ) );
		}

		$notification = new UNBSB_Notification();

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

		wp_send_json_success( __( 'Email templates saved.', 'unbelievable-salon-booking' ) );
	}

	/**
	 * AJAX: Send test email
	 */
	public function ajax_email_send_test() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$email         = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$template_type = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( __( 'Please enter a valid email address.', 'unbelievable-salon-booking' ) );
		}

		if ( empty( $template_type ) ) {
			wp_send_json_error( __( 'Template type is required.', 'unbelievable-salon-booking' ) );
		}

		$notification = new UNBSB_Notification();
		$result       = $notification->send_test_email( $email, $template_type );

		if ( $result['success'] ) {
			wp_send_json_success( $result['message'] );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * AJAX: Email preview
	 */
	public function ajax_email_preview() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$template_type = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';

		if ( empty( $template_type ) ) {
			wp_send_json_error( __( 'Template type is required.', 'unbelievable-salon-booking' ) );
		}

		$notification = new UNBSB_Notification();
		$template     = $notification->get_template( $template_type );

		if ( ! $template ) {
			wp_send_json_error( __( 'Template not found.', 'unbelievable-salon-booking' ) );
		}

		// Create test data for preview.
		$test_booking = (object) array(
			'id'             => 999,
			'customer_name'  => 'Test Customer',
			'customer_email' => 'test@example.com',
			'customer_phone' => '0555 555 55 55',
			'service_name'   => 'Test Service',
			'services_list'  => 'Haircut, Beard Trim',
			'staff_name'     => 'John Smith',
			'booking_date'   => gmdate( 'Y-m-d', strtotime( '+1 day' ) ),
			'start_time'     => '14:00:00',
			'price'          => '150.00',
			'total_duration' => 60,
			'status'         => 'pending',
			'token'          => 'preview-token-123',
		);

		// Use reflection to generate preview HTML.
		$reflection = new ReflectionClass( $notification );

		// Call parse_placeholders method.
		$parse_method = $reflection->getMethod( 'parse_placeholders' );
		$parse_method->setAccessible( true );
		$content = $parse_method->invoke( $notification, $template->content, $test_booking );
		$content = str_replace( '{calendar_links}', '', $content );

		// Call wrap_email_content method.
		$wrap_method = $reflection->getMethod( 'wrap_email_content' );
		$wrap_method->setAccessible( true );
		$html = $wrap_method->invoke( $notification, $content, $template_type );

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * AJAX: Save email settings
	 */
	public function ajax_save_email_settings() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$settings = array(
			'unbsb_email_logo_url',
			'unbsb_email_primary_color',
			'unbsb_email_reminder_hours',
		);

		foreach ( $settings as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				update_option( $key, $value );
			}
		}

		// Checkbox.
		$reminder_enabled = isset( $_POST['unbsb_email_reminder_enabled'] ) && 'yes' === $_POST['unbsb_email_reminder_enabled'] ? 'yes' : 'no';
		update_option( 'unbsb_email_reminder_enabled', $reminder_enabled );

		wp_send_json_success( __( 'Email settings saved.', 'unbelievable-salon-booking' ) );
	}

	/**
	 * AJAX: Get staff schedule data
	 */
	public function ajax_get_staff_schedule() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		// Allow admin or staff with own-schedule capability.
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unbsb_manage_own_schedule' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		// Staff users can only view their own schedule.
		if ( ! current_user_can( 'manage_options' ) ) {
			$current_staff = $this->get_current_staff();
			if ( ! $current_staff || absint( $current_staff->id ) !== $staff_id ) {
				wp_send_json_error( __( 'You can only view your own schedule.', 'unbelievable-salon-booking' ) );
			}
		}

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Invalid staff.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();

		// Working hours.
		$working_hours = $staff_model->get_working_hours( $staff_id );

		// Breaks.
		$breaks = $staff_model->get_breaks( $staff_id );

		// Holidays (next 1 year).
		$start_date = gmdate( 'Y-m-d' );
		$end_date   = gmdate( 'Y-m-d', strtotime( '+1 year' ) );
		$holidays   = $staff_model->get_holidays( $staff_id, $start_date, $end_date );

		wp_send_json_success(
			array(
				'working_hours' => $working_hours,
				'breaks'        => $breaks,
				'holidays'      => $holidays,
			)
		);
	}

	/**
	 * AJAX: Save staff schedule (working hours + breaks)
	 */
	public function ajax_save_staff_schedule() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Invalid staff.', 'unbelievable-salon-booking' ) );
		}

		// Get working hours.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data, sanitized after decode.
		$hours_raw = isset( $_POST['working_hours'] ) ? wp_unslash( $_POST['working_hours'] ) : '';
		$hours     = json_decode( $hours_raw, true );

		// Get breaks.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data, sanitized after decode.
		$breaks_raw = isset( $_POST['breaks'] ) ? wp_unslash( $_POST['breaks'] ) : '';
		$breaks     = json_decode( $breaks_raw, true );

		$staff_model = new UNBSB_Staff();

		// Save working hours.
		if ( is_array( $hours ) ) {
			$staff_model->update_working_hours( $staff_id, $hours );
		}

		// Save breaks.
		if ( is_array( $breaks ) ) {
			$staff_model->update_breaks( $staff_id, $breaks );
		}

		wp_send_json_success( __( 'Schedule saved.', 'unbelievable-salon-booking' ) );
	}

	/**
	 * AJAX: Add staff holiday
	 */
	public function ajax_add_staff_holiday() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id   = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$reason     = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$type       = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'off';
		$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : null;
		$end_time   = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : null;

		if ( ! $staff_id || empty( $date ) ) {
			wp_send_json_error( __( 'Invalid data.', 'unbelievable-salon-booking' ) );
		}

		// Date format validation.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			wp_send_json_error( __( 'Invalid date format.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$result      = $staff_model->add_holiday( $staff_id, $date, $reason, $type, $start_time, $end_time );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => 'extra' === $type
						? __( 'Extra open day added.', 'unbelievable-salon-booking' )
						: __( 'Holiday added.', 'unbelievable-salon-booking' ),
					'id'      => $result,
				)
			);
		} else {
			wp_send_json_error( __( 'A holiday already exists on this date.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Delete staff holiday
	 */
	public function ajax_delete_staff_holiday() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$date     = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

		if ( ! $staff_id || empty( $date ) ) {
			wp_send_json_error( __( 'Invalid data.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$staff_model->delete_holiday_by_date( $staff_id, $date );

		wp_send_json_success( __( 'Holiday deleted.', 'unbelievable-salon-booking' ) );
	}

	/**
	 * AJAX: Search customers
	 */
	public function ajax_search_customers() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unbsb_confirm_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';

		if ( empty( $query ) || strlen( $query ) < 2 ) {
			wp_send_json_success( array() );
		}

		global $wpdb;

		$table       = $wpdb->prefix . 'unbsb_customers';
		$search_term = '%' . $wpdb->esc_like( $query ) . '%';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, name, email, phone FROM ' . $table . '
				WHERE phone LIKE %s OR name LIKE %s OR email LIKE %s
				ORDER BY name ASC
				LIMIT 10',
				$search_term,
				$search_term,
				$search_term
			)
		);

		wp_send_json_success( $results );
	}

	/**
	 * AJAX: Create customer (for new booking page)
	 */
	public function ajax_admin_create_customer() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unbsb_confirm_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$data = array(
			'name'  => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone' => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'notes' => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		);

		if ( empty( $data['name'] ) ) {
			wp_send_json_error( __( 'Customer name is required.', 'unbelievable-salon-booking' ) );
		}

		if ( ! empty( $data['email'] ) && ! is_email( $data['email'] ) ) {
			wp_send_json_error( __( 'Please enter a valid email address.', 'unbelievable-salon-booking' ) );
		}

		$customer_model = new UNBSB_Customer();

		// Check for duplicate email.
		$existing = $customer_model->get_by_email( $data['email'] );
		if ( $existing ) {
			wp_send_json_success(
				array(
					'id'    => $existing->id,
					'name'  => $existing->name,
					'email' => $existing->email,
					'phone' => $existing->phone,
				)
			);
			return;
		}

		// Create WordPress user if not exists.
		if ( ! email_exists( $data['email'] ) ) {
			$password = wp_generate_password( 12, true, false );
			$user_id  = wp_insert_user(
				array(
					'user_login'   => $data['email'],
					'user_email'   => $data['email'],
					'user_pass'    => $password,
					'display_name' => $data['name'],
					'role'         => 'unbsb_customer',
				)
			);

			if ( ! is_wp_error( $user_id ) ) {
				$data['user_id'] = $user_id;
			}
		} else {
			$wp_user = get_user_by( 'email', $data['email'] );
			if ( $wp_user ) {
				$data['user_id'] = $wp_user->ID;
			}
		}

		$result = $customer_model->create( $data );

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'id'    => $result,
					'name'  => $data['name'],
					'email' => $data['email'],
					'phone' => $data['phone'],
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to create customer.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * Handle booking earnings when status changes
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function handle_booking_earnings( $booking_id, $new_status, $old_status ) {
		$staff_model   = new UNBSB_Staff();
		$booking_model = new UNBSB_Booking();

		// Record commission when booking is completed.
		if ( 'completed' === $new_status && 'completed' !== $old_status ) {
			$booking = $booking_model->get( $booking_id );

			if ( ! $booking || empty( $booking->staff_id ) ) {
				return;
			}

			$commission = $staff_model->calculate_commission( $booking->staff_id, floatval( $booking->price ) );
			$staff_model->record_commission( $booking->staff_id, $booking_id, $commission );
		}

		// Remove commission if booking reverted from completed.
		if ( 'completed' === $old_status && 'completed' !== $new_status ) {
			$staff_model->delete_earnings_by_booking( $booking_id );
		}
	}

	/**
	 * Get the current staff record for the logged-in user.
	 *
	 * @return object|null
	 */
	private function get_current_staff() {
		$user_id     = get_current_user_id();
		$staff_model = new UNBSB_Staff();
		return $staff_model->get_by_user_id( $user_id );
	}

	/**
	 * AJAX: Get staff own bookings
	 */
	public function ajax_get_staff_own_bookings() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'unbsb_view_own_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff = $this->get_current_staff();

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$date   = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

		global $wpdb;
		$prefix = $wpdb->prefix . 'unbsb_';

		$sql    = "SELECT b.*, s.name AS service_name
			FROM {$prefix}bookings b
			LEFT JOIN {$prefix}services s ON b.service_id = s.id
			WHERE b.staff_id = %d";
		$params = array( $staff->id );

		if ( ! empty( $status ) ) {
			$sql     .= ' AND b.status = %s';
			$params[] = $status;
		}

		if ( ! empty( $date ) ) {
			$sql     .= ' AND b.booking_date = %s';
			$params[] = $date;
		}

		$sql .= ' ORDER BY b.booking_date DESC, b.start_time DESC LIMIT 100';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$bookings = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		wp_send_json_success( $bookings );
	}

	/**
	 * AJAX: Staff confirm booking
	 */
	public function ajax_staff_confirm_booking() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'unbsb_confirm_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;

		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID.', 'unbelievable-salon-booking' ) );
		}

		$staff = $this->get_current_staff();

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get( $booking_id );

		if ( ! $booking || absint( $booking->staff_id ) !== absint( $staff->id ) ) {
			wp_send_json_error( __( 'You can only confirm your own bookings.', 'unbelievable-salon-booking' ) );
		}

		if ( 'pending' !== $booking->status ) {
			wp_send_json_error( __( 'Only pending bookings can be confirmed.', 'unbelievable-salon-booking' ) );
		}

		$result = $booking_model->update_status( $booking_id, 'confirmed' );

		if ( false !== $result ) {
			wp_send_json_success( array( 'message' => __( 'Booking confirmed.', 'unbelievable-salon-booking' ) ) );
		} else {
			wp_send_json_error( __( 'Could not confirm booking.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Staff reject booking
	 */
	public function ajax_staff_reject_booking() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'unbsb_confirm_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;

		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID.', 'unbelievable-salon-booking' ) );
		}

		$staff = $this->get_current_staff();

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get( $booking_id );

		if ( ! $booking || absint( $booking->staff_id ) !== absint( $staff->id ) ) {
			wp_send_json_error( __( 'You can only reject your own bookings.', 'unbelievable-salon-booking' ) );
		}

		if ( 'pending' !== $booking->status ) {
			wp_send_json_error( __( 'Only pending bookings can be rejected.', 'unbelievable-salon-booking' ) );
		}

		$result = $booking_model->update_status( $booking_id, 'cancelled' );

		if ( false !== $result ) {
			wp_send_json_success( array( 'message' => __( 'Booking rejected.', 'unbelievable-salon-booking' ) ) );
		} else {
			wp_send_json_error( __( 'Could not reject booking.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Staff add own holiday
	 */
	public function ajax_staff_add_holiday() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'unbsb_manage_own_schedule' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff = $this->get_current_staff();

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$reason     = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$type       = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'off';
		$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : null;
		$end_time   = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : null;

		if ( empty( $date ) ) {
			wp_send_json_error( __( 'Date is required.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$result      = $staff_model->add_holiday( $staff->id, $date, $reason, $type, $start_time, $end_time );

		if ( false !== $result ) {
			$message = 'extra' === $type
				? __( 'Extra open day added.', 'unbelievable-salon-booking' )
				: __( 'Holiday added.', 'unbelievable-salon-booking' );
			wp_send_json_success( array( 'message' => $message ) );
		} else {
			wp_send_json_error( __( 'Holiday already exists for this date.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Staff remove own holiday
	 */
	public function ajax_staff_remove_holiday() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'unbsb_manage_own_schedule' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff = $this->get_current_staff();

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$holiday_id = isset( $_POST['holiday_id'] ) ? absint( $_POST['holiday_id'] ) : 0;

		if ( ! $holiday_id ) {
			wp_send_json_error( __( 'Invalid holiday ID.', 'unbelievable-salon-booking' ) );
		}

		// Verify the holiday belongs to this staff.
		global $wpdb;
		$prefix = $wpdb->prefix . 'unbsb_';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$holiday = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$prefix}holidays WHERE id = %d AND staff_id = %d",
				$holiday_id,
				$staff->id
			)
		);

		if ( ! $holiday ) {
			wp_send_json_error( __( 'Holiday not found or does not belong to you.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$result      = $staff_model->delete_holiday( $holiday_id );

		if ( false !== $result ) {
			wp_send_json_success( array( 'message' => __( 'Holiday removed.', 'unbelievable-salon-booking' ) ) );
		} else {
			wp_send_json_error( __( 'Could not remove holiday.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Staff get own holidays
	 */
	public function ajax_staff_get_holidays() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'unbsb_manage_own_schedule' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff = $this->get_current_staff();

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff record not found.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$holidays    = $staff_model->get_holidays( $staff->id );

		wp_send_json_success( $holidays );
	}

	/**
	 * AJAX: Create WordPress user for staff member
	 */
	public function ajax_create_staff_user() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Invalid staff ID.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get( $staff_id );

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff not found.', 'unbelievable-salon-booking' ) );
		}

		// Check if staff already has a linked user.
		if ( ! empty( $staff->user_id ) ) {
			wp_send_json_error( __( 'This staff member already has a linked user account.', 'unbelievable-salon-booking' ) );
		}

		// Generate username from email or name+phone.
		$email = ! empty( $staff->email ) ? $staff->email : '';

		if ( ! empty( $email ) ) {
			// Check if user with this email already exists.
			$existing_user = get_user_by( 'email', $email );
			if ( $existing_user ) {
				// Link to existing user instead.
				$staff_model->update( $staff_id, array( 'user_id' => $existing_user->ID ) );
				$existing_user->add_role( 'unbsb_staff' );

				wp_send_json_success(
					array(
						'message'    => __( 'Existing user found and linked.', 'unbelievable-salon-booking' ),
						'user_id'    => $existing_user->ID,
						'user_login' => $existing_user->user_login,
					)
				);
			}

			$username = sanitize_user( strstr( $email, '@', true ), true );
		} else {
			// Build username from name + phone.
			$name_slug = sanitize_title( $staff->name );
			$phone     = ! empty( $staff->phone ) ? preg_replace( '/[^0-9]/', '', $staff->phone ) : '';
			$username  = $name_slug . ( $phone ? '-' . substr( $phone, -4 ) : '' );
		}

		// Ensure username is unique.
		$base_username = $username;
		$counter       = 1;
		while ( username_exists( $username ) ) {
			$username = $base_username . $counter;
			$counter++;
		}

		// Generate password.
		$password = wp_generate_password( 12, true, false );

		// Build user data.
		$userdata = array(
			'user_login'   => $username,
			'user_pass'    => $password,
			'user_email'   => $email,
			'display_name' => $staff->name,
			'first_name'   => $staff->name,
			'role'         => 'unbsb_staff',
		);

		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( $user_id->get_error_message() );
		}

		// Link user to staff.
		$staff_model->update( $staff_id, array( 'user_id' => $user_id ) );

		// Send new user notification with password reset link.
		wp_new_user_notification( $user_id, null, 'user' );

		wp_send_json_success(
			array(
				'message'    => __( 'User account created and password reset email sent.', 'unbelievable-salon-booking' ),
				'user_id'    => $user_id,
				'user_login' => $username,
			)
		);
	}

	/**
	 * AJAX: Link existing WordPress user to staff member
	 */
	public function ajax_link_staff_user() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$search   = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Invalid staff ID.', 'unbelievable-salon-booking' ) );
		}

		if ( empty( $search ) ) {
			wp_send_json_error( __( 'Please provide an email or username to search.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get( $staff_id );

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff not found.', 'unbelievable-salon-booking' ) );
		}

		// Search by email first, then username.
		$wp_user = get_user_by( 'email', $search );

		if ( ! $wp_user ) {
			$wp_user = get_user_by( 'login', $search );
		}

		if ( ! $wp_user ) {
			wp_send_json_error( __( 'No WordPress user found with this email or username.', 'unbelievable-salon-booking' ) );
		}

		// Check if this user is already linked to another staff member.
		$existing_staff = $staff_model->get_by_user_id( $wp_user->ID );
		if ( $existing_staff && absint( $existing_staff->id ) !== $staff_id ) {
			wp_send_json_error(
				sprintf(
					/* translators: %s: Staff member name */
					__( 'This user is already linked to staff member: %s', 'unbelievable-salon-booking' ),
					$existing_staff->name
				)
			);
		}

		// Link user to staff.
		$staff_model->update( $staff_id, array( 'user_id' => $wp_user->ID ) );

		// Add staff role.
		$wp_user->add_role( 'unbsb_staff' );

		wp_send_json_success(
			array(
				'message'    => __( 'User linked successfully.', 'unbelievable-salon-booking' ),
				'user_id'    => $wp_user->ID,
				'user_login' => $wp_user->user_login,
			)
		);
	}

	/**
	 * AJAX: Unlink WordPress user from staff member
	 */
	public function ajax_unlink_staff_user() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Invalid staff ID.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get( $staff_id );

		if ( ! $staff ) {
			wp_send_json_error( __( 'Staff not found.', 'unbelievable-salon-booking' ) );
		}

		if ( empty( $staff->user_id ) ) {
			wp_send_json_error( __( 'This staff member has no linked user.', 'unbelievable-salon-booking' ) );
		}

		// Remove role from user.
		$wp_user = get_user_by( 'id', $staff->user_id );
		if ( $wp_user ) {
			$wp_user->remove_role( 'unbsb_staff' );
		}

		// Clear user_id from staff.
		$staff_model->update( $staff_id, array( 'user_id' => null ) );

		wp_send_json_success(
			array(
				'message' => __( 'User unlinked successfully.', 'unbelievable-salon-booking' ),
			)
		);
	}

	/**
	 * Restrict admin menu for staff users
	 */
	public function restrict_staff_admin_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! current_user_can( 'unbsb_view_own_bookings' ) ) {
			return;
		}

		// Remove default WordPress menu pages for staff users.
		remove_menu_page( 'index.php' );              // Dashboard.
		remove_menu_page( 'edit.php' );                // Posts.
		remove_menu_page( 'upload.php' );              // Media.
		remove_menu_page( 'edit-comments.php' );       // Comments.
		remove_menu_page( 'themes.php' );              // Appearance.
		remove_menu_page( 'plugins.php' );             // Plugins.
		remove_menu_page( 'users.php' );               // Users.
		remove_menu_page( 'tools.php' );               // Tools.
		remove_menu_page( 'options-general.php' );     // Settings.
		remove_menu_page( 'edit.php?post_type=page' ); // Pages.

		// Remove the main admin booking menu (staff has their own portal).
		remove_menu_page( 'unbelievable-salon-booking' );
	}

	/**
	 * Restrict admin bar for staff users
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public function restrict_staff_admin_bar( $wp_admin_bar ) {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! current_user_can( 'unbsb_view_own_bookings' ) ) {
			return;
		}

		$wp_admin_bar->remove_node( 'wp-logo' );
		$wp_admin_bar->remove_node( 'comments' );
		$wp_admin_bar->remove_node( 'new-content' );
		$wp_admin_bar->remove_node( 'updates' );
		$wp_admin_bar->remove_node( 'site-name' );
	}

	/**
	 * Redirect staff users to their portal after login
	 *
	 * @param string  $redirect_to           Redirect URL.
	 * @param string  $requested_redirect_to Requested redirect URL.
	 * @param WP_User $user                  User object.
	 *
	 * @return string
	 */
	public function staff_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! is_a( $user, 'WP_User' ) ) {
			return $redirect_to;
		}

		// Only redirect if staff role and not admin.
		if ( in_array( 'unbsb_staff', (array) $user->roles, true ) && ! $user->has_cap( 'manage_options' ) ) {
			return admin_url( 'admin.php?page=unbsb-staff-portal' );
		}

		return $redirect_to;
	}

	/**
	 * AJAX: Complete booking with payment info
	 */
	public function ajax_complete_booking_with_payment() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		$is_admin      = current_user_can( 'manage_options' );
		$is_staff_user = ! $is_admin && current_user_can( 'unbsb_confirm_bookings' );

		if ( ! $is_admin && ! $is_staff_user ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$booking_id     = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
		$paid_amount    = isset( $_POST['paid_amount'] ) ? floatval( $_POST['paid_amount'] ) : 0;
		$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';

		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID.', 'unbelievable-salon-booking' ) );
		}

		if ( $paid_amount < 0 ) {
			wp_send_json_error( __( 'Invalid payment amount.', 'unbelievable-salon-booking' ) );
		}

		if ( ! in_array( $payment_method, array( 'cash', 'card', 'transfer' ), true ) ) {
			wp_send_json_error( __( 'Invalid payment method.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get( $booking_id );

		if ( ! $booking ) {
			wp_send_json_error( __( 'Booking not found.', 'unbelievable-salon-booking' ) );
		}

		// Staff users can only complete their own bookings.
		if ( $is_staff_user ) {
			$current_staff = $this->get_current_staff();
			if ( ! $current_staff || absint( $booking->staff_id ) !== absint( $current_staff->id ) ) {
				wp_send_json_error( __( 'You can only complete your own bookings.', 'unbelievable-salon-booking' ) );
			}
		}

		// Save payment data.
		$booking_model->update(
			$booking_id,
			array(
				'paid_amount'    => $paid_amount,
				'payment_method' => $payment_method,
			)
		);

		// Update status to completed (triggers unbsb_booking_status_changed for commission).
		$result = $booking_model->update_status( $booking_id, 'completed' );

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Booking completed.', 'unbelievable-salon-booking' ),
				)
			);
		} else {
			wp_send_json_error( __( 'Could not complete booking.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * Get calendar events (FullCalendar format)
	 */
	public function ajax_get_calendar_events() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		$is_admin      = current_user_can( 'manage_options' );
		$is_staff_user = ! $is_admin && current_user_can( 'unbsb_view_own_bookings' );

		if ( ! $is_admin && ! $is_staff_user ) {
			wp_send_json_error( __( 'Unauthorized.', 'unbelievable-salon-booking' ) );
		}

		$start    = isset( $_GET['start'] ) ? sanitize_text_field( wp_unslash( $_GET['start'] ) ) : '';
		$end      = isset( $_GET['end'] ) ? sanitize_text_field( wp_unslash( $_GET['end'] ) ) : '';
		$staff_id = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;

		// Staff users can only see their own bookings.
		if ( $is_staff_user ) {
			$current_staff = $this->get_current_staff();
			if ( ! $current_staff ) {
				wp_send_json_error( __( 'Staff not found.', 'unbelievable-salon-booking' ) );
			}
			$staff_id = absint( $current_staff->id );
		}

		$booking_model = new UNBSB_Booking();

		$args = array(
			'date_from' => $start ? gmdate( 'Y-m-d', strtotime( $start ) ) : '',
			'date_to'   => $end ? gmdate( 'Y-m-d', strtotime( $end ) ) : '',
			'orderby'   => 'start_time',
			'order'     => 'ASC',
		);

		if ( $staff_id ) {
			$args['staff_id'] = $staff_id;
		}

		$bookings = $booking_model->get_all( $args );

		$status_colors = array(
			'pending'   => '#f59e0b',
			'confirmed' => '#10b981',
			'cancelled' => '#ef4444',
			'completed' => '#6366f1',
			'no_show'   => '#6b7280',
		);

		$events = array();
		foreach ( $bookings as $booking ) {
			$color = isset( $status_colors[ $booking->status ] ) ? $status_colors[ $booking->status ] : '#4f46e5';

			$events[] = array(
				'id'            => absint( $booking->id ),
				'title'         => $booking->customer_name,
				'start'         => $booking->booking_date . 'T' . $booking->start_time,
				'end'           => $booking->booking_date . 'T' . $booking->end_time,
				'color'         => $color,
				'extendedProps' => array(
					'customer_name'  => $booking->customer_name,
					'customer_phone' => $booking->customer_phone,
					'service_name'   => $booking->service_name,
					'staff_name'     => $booking->staff_name,
					'staff_id'       => absint( $booking->staff_id ),
					'status'         => $booking->status,
					'price'          => number_format( (float) $booking->price, 2, '.', '' ),
					'booking_id'     => absint( $booking->id ),
				),
			);
		}

		wp_send_json_success( $events );
	}

	/**
	 * AJAX: Get booking detail for edit form
	 */
	public function ajax_get_booking_detail() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unbsb_manage_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid booking ID.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get_with_details( $id );

		if ( ! $booking ) {
			wp_send_json_error( __( 'Booking not found.', 'unbelievable-salon-booking' ) );
		}

		wp_send_json_success( $booking );
	}

	/**
	 * AJAX: Update booking
	 */
	public function ajax_update_booking() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unbsb_manage_bookings' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid booking ID.', 'unbelievable-salon-booking' ) );
		}

		$booking_model = new UNBSB_Booking();
		$existing      = $booking_model->get( $id );

		if ( ! $existing ) {
			wp_send_json_error( __( 'Booking not found.', 'unbelievable-salon-booking' ) );
		}

		$data = array();

		// Collect editable fields.
		if ( isset( $_POST['service_id'] ) ) {
			$data['service_id'] = absint( $_POST['service_id'] );
		}

		if ( isset( $_POST['staff_id'] ) ) {
			$data['staff_id'] = absint( $_POST['staff_id'] );
		}

		if ( isset( $_POST['booking_date'] ) ) {
			$data['booking_date'] = sanitize_text_field( wp_unslash( $_POST['booking_date'] ) );
		}

		if ( isset( $_POST['start_time'] ) ) {
			$data['start_time'] = sanitize_text_field( wp_unslash( $_POST['start_time'] ) );
		}

		if ( isset( $_POST['customer_name'] ) ) {
			$data['customer_name'] = sanitize_text_field( wp_unslash( $_POST['customer_name'] ) );
		}

		if ( isset( $_POST['customer_email'] ) ) {
			$data['customer_email'] = sanitize_email( wp_unslash( $_POST['customer_email'] ) );
		}

		if ( isset( $_POST['customer_phone'] ) ) {
			$data['customer_phone'] = sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) );
		}

		if ( isset( $_POST['notes'] ) ) {
			$data['notes'] = sanitize_textarea_field( wp_unslash( $_POST['notes'] ) );
		}

		if ( isset( $_POST['internal_notes'] ) ) {
			$data['internal_notes'] = sanitize_textarea_field( wp_unslash( $_POST['internal_notes'] ) );
		}

		if ( isset( $_POST['price'] ) ) {
			$data['price'] = floatval( $_POST['price'] );
		}

		if ( empty( $data ) ) {
			wp_send_json_error( __( 'No fields to update.', 'unbelievable-salon-booking' ) );
		}

		// Validate date format if provided.
		if ( ! empty( $data['booking_date'] ) && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data['booking_date'] ) ) {
			wp_send_json_error( __( 'Invalid date format.', 'unbelievable-salon-booking' ) );
		}

		// Validate email if provided.
		if ( ! empty( $data['customer_email'] ) && ! is_email( $data['customer_email'] ) ) {
			wp_send_json_error( __( 'Invalid email address.', 'unbelievable-salon-booking' ) );
		}

		// Recalculate end_time if date/time/service/staff changed.
		$new_staff_id    = $data['staff_id'] ?? $existing->staff_id;
		$new_service_id  = $data['service_id'] ?? $existing->service_id;
		$new_date        = $data['booking_date'] ?? $existing->booking_date;
		$new_start_time  = $data['start_time'] ?? $existing->start_time;

		// Recalculate end_time based on service duration.
		$service_model = new UNBSB_Service();
		$staff_model   = new UNBSB_Staff();
		$service       = $service_model->get( $new_service_id );

		if ( $service ) {
			$custom = $staff_model->get_service_custom_data( $new_staff_id, $new_service_id );

			$effective_duration = ( $custom && null !== $custom->custom_duration )
				? intval( $custom->custom_duration )
				: intval( $service->duration );

			$duration          = $effective_duration + intval( $service->buffer_after );
			$start             = strtotime( $new_start_time );
			$data['end_time']  = gmdate( 'H:i:s', $start + ( $duration * 60 ) );

			$data['total_duration'] = $duration;
		}

		// Conflict check (exclude current booking).
		$conflict_data = array(
			'staff_id'     => $new_staff_id,
			'booking_date' => $new_date,
			'start_time'   => $new_start_time,
			'end_time'     => $data['end_time'] ?? $existing->end_time,
		);

		if ( $booking_model->has_conflict( $conflict_data, $id ) ) {
			wp_send_json_error( __( 'Another booking exists in this time slot for this staff member.', 'unbelievable-salon-booking' ) );
		}

		// Multi-service support.
		if ( ! empty( $_POST['service_ids'] ) && is_array( $_POST['service_ids'] ) ) {
			$service_ids    = array_map( 'absint', $_POST['service_ids'] );
			$total_duration = 0;
			$total_price    = 0;
			$services_data  = array();

			foreach ( $service_ids as $index => $sid ) {
				$svc = $service_model->get( $sid );
				if ( $svc ) {
					$custom = $staff_model->get_service_custom_data( $new_staff_id, $sid );

					$eff_duration = ( $custom && null !== $custom->custom_duration )
						? intval( $custom->custom_duration )
						: intval( $svc->duration );

					if ( $custom && null !== $custom->custom_price ) {
						$eff_price = floatval( $custom->custom_price );
					} elseif ( ! empty( $svc->discounted_price ) && floatval( $svc->discounted_price ) < floatval( $svc->price ) ) {
						$eff_price = floatval( $svc->discounted_price );
					} else {
						$eff_price = floatval( $svc->price );
					}

					$total_duration += $eff_duration;
					$total_price    += $eff_price;

					$services_data[] = array(
						'service_id' => $sid,
						'staff_id'   => $new_staff_id,
						'price'      => $eff_price,
						'duration'   => $eff_duration,
						'sort_order' => $index,
					);

					if ( count( $service_ids ) - 1 === $index ) {
						$total_duration += intval( $svc->buffer_after );
					}
				}
			}

			$data['service_id']     = $service_ids[0];
			$data['total_duration'] = $total_duration;
			$start                  = strtotime( $new_start_time );
			$data['end_time']       = gmdate( 'H:i:s', $start + ( $total_duration * 60 ) );

			// Only override price if not manually set.
			if ( ! isset( $_POST['price'] ) ) {
				$data['price'] = $total_price;
			}

			// Update booking_services table.
			$booking_service = new UNBSB_Booking_Service();
			$booking_service->delete_by_booking( $id );
			$booking_service->add_multiple( $id, $services_data );
		}

		$result = $booking_model->update( $id, $data );

		if ( false !== $result ) {
			$updated_booking = $booking_model->get_with_details( $id );
			wp_send_json_success(
				array(
					'message' => __( 'Booking updated successfully.', 'unbelievable-salon-booking' ),
					'booking' => $updated_booking,
				)
			);
		} else {
			wp_send_json_error( __( 'An error occurred while updating the booking.', 'unbelievable-salon-booking' ) );
		}
	}

	/**
	 * AJAX: Export customers as CSV
	 */
	public function ajax_export_customers_csv() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'unbelievable-salon-booking' ) );
		}

		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_customers';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$customers = $wpdb->get_results(
			"SELECT id, name, email, phone, notes, created_at FROM {$table} ORDER BY id ASC",
			ARRAY_A
		);

		if ( null === $customers ) {
			$customers = array();
		}

		// Build CSV string.
		$output = fopen( 'php://temp', 'r+' );
		fputcsv( $output, array( 'id', 'name', 'email', 'phone', 'notes', 'created_at' ) );

		foreach ( $customers as $row ) {
			fputcsv( $output, $row );
		}

		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );

		wp_send_json_success(
			array(
				'csv'      => $csv,
				'filename' => 'customers-' . gmdate( 'Y-m-d' ) . '.csv',
				'count'    => count( $customers ),
			)
		);
	}

	/**
	 * AJAX: Import customers from CSV
	 */
	public function ajax_import_customers_csv() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'unbelievable-salon-booking' ) );
		}

		if ( empty( $_FILES['csv_file'] ) || ! empty( $_FILES['csv_file']['error'] ) ) {
			wp_send_json_error( __( 'No valid CSV file uploaded.', 'unbelievable-salon-booking' ) );
		}

		$file_path = sanitize_text_field( wp_unslash( $_FILES['csv_file']['tmp_name'] ) );

		if ( ! is_uploaded_file( $file_path ) ) {
			wp_send_json_error( __( 'Invalid upload.', 'unbelievable-salon-booking' ) );
		}

		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			wp_send_json_error( __( 'Could not read file.', 'unbelievable-salon-booking' ) );
		}

		$customer_model = new UNBSB_Customer();
		$added          = 0;
		$skipped        = 0;
		$row_num        = 0;

		while ( false !== ( $row = fgetcsv( $handle ) ) ) {
			++$row_num;

			// Skip header row.
			if ( 1 === $row_num ) {
				$first_cell = strtolower( trim( $row[0] ?? '' ) );
				if ( in_array( $first_cell, array( 'name', 'id', 'email' ), true ) ) {
					continue;
				}
			}

			// Expected CSV columns: name, email, phone, notes.
			$name  = sanitize_text_field( $row[0] ?? '' );
			$email = sanitize_email( $row[1] ?? '' );
			$phone = sanitize_text_field( $row[2] ?? '' );
			$notes = sanitize_textarea_field( $row[3] ?? '' );

			if ( empty( $name ) || empty( $email ) ) {
				++$skipped;
				continue;
			}

			// Check email uniqueness.
			$existing = $customer_model->get_by_email( $email );
			if ( $existing ) {
				++$skipped;
				continue;
			}

			$customer_model->create(
				array(
					'name'  => $name,
					'email' => $email,
					'phone' => $phone,
					'notes' => $notes,
				)
			);
			++$added;
		}

		fclose( $handle );

		wp_send_json_success(
			array(
				/* translators: 1: number of customers added, 2: number of customers skipped */
				'message' => sprintf( __( '%1$d customers added, %2$d skipped.', 'unbelievable-salon-booking' ), $added, $skipped ),
				'added'   => $added,
				'skipped' => $skipped,
			)
		);
	}

}
