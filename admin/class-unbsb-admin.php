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
					// Customer.
					'new_customer'               => __( 'New Customer', 'unbelievable-salon-booking' ),
					'edit_customer'              => __( 'Edit Customer', 'unbelievable-salon-booking' ),
					// Booking.
					'fill_required_fields'       => __( 'Please fill in all required fields.', 'unbelievable-salon-booking' ),
					'create_booking'             => __( 'Create Booking', 'unbelievable-salon-booking' ),
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
				),
				'currency'  => array(
					'symbol'   => get_option( 'unbsb_currency_symbol', '₺' ),
					'position' => get_option( 'unbsb_currency_position', 'after' ),
				),
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

			// Attach service data with custom prices/durations.
			foreach ( $all_staff as &$s ) {
				$s->services      = $staff_model->get_services( $s->id );
				$s->service_details = $staff_model->get_services( $s->id, false );
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

			wp_localize_script(
				'unbsb-admin',
				'unbsbNewBookingData',
				array(
					'staff' => $all_staff,
				)
			);
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

		include UNBSB_PLUGIN_DIR . 'admin/partials/admin-bookings.php';
	}

	/**
	 * Calendar page
	 */
	public function render_calendar() {
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_active();

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

			wp_send_json_success(
				array(
					'message'  => __( 'Staff saved.', 'unbelievable-salon-booking' ),
					'id'       => $staff_id_saved,
					'services' => $staff_services,
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

		if ( empty( $data['name'] ) || empty( $data['email'] ) ) {
			wp_send_json_error( __( 'Name and email are required.', 'unbelievable-salon-booking' ) );
		}

		if ( ! is_email( $data['email'] ) ) {
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

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$start    = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
		$end      = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : '';
		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		$calendar = new UNBSB_Calendar();
		$events   = $calendar->get_calendar_events( $start, $end, $staff_id );

		wp_send_json_success( $events );
	}

	/**
	 * AJAX: Create booking
	 */
	public function ajax_create_booking() {
		check_ajax_referer( 'unbsb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		// Support both service_id (single) and service_ids[] (multi) from new booking page.
		$service_id = 0;
		if ( ! empty( $_POST['service_id'] ) ) {
			$service_id = absint( $_POST['service_id'] );
		} elseif ( ! empty( $_POST['service_ids'] ) && is_array( $_POST['service_ids'] ) ) {
			$service_id = absint( $_POST['service_ids'][0] );
		}

		$data = array(
			'service_id'     => $service_id,
			'staff_id'       => isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0,
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
			empty( $data['customer_email'] ) || empty( $data['booking_date'] ) || empty( $data['start_time'] ) ) {
			wp_send_json_error( __( 'Please fill in all required fields.', 'unbelievable-salon-booking' ) );
		}

		// Email validation.
		if ( ! is_email( $data['customer_email'] ) ) {
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

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

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

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$date     = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$reason   = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( ! $staff_id || empty( $date ) ) {
			wp_send_json_error( __( 'Invalid data.', 'unbelievable-salon-booking' ) );
		}

		// Date format validation.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			wp_send_json_error( __( 'Invalid date format.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$result      = $staff_model->add_holiday( $staff_id, $date, $reason );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Holiday added.', 'unbelievable-salon-booking' ),
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

		if ( ! current_user_can( 'manage_options' ) ) {
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

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'unbelievable-salon-booking' ) );
		}

		$data = array(
			'name'  => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone' => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'notes' => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		);

		if ( empty( $data['name'] ) || empty( $data['email'] ) ) {
			wp_send_json_error( __( 'Name and email are required.', 'unbelievable-salon-booking' ) );
		}

		if ( ! is_email( $data['email'] ) ) {
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

}
