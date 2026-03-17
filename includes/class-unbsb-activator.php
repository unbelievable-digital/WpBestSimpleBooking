<?php
/**
 * Plugin activation class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activator class
 */
class UNBSB_Activator {

	/**
	 * Activation operations
	 */
	public static function activate() {
		self::create_tables();
		self::create_default_options();
		self::create_capabilities();
		self::run_migrations();

		// Save version.
		update_option( 'unbsb_version', UNBSB_VERSION );
		update_option( 'unbsb_db_version', '2.2.0' );

		// Rewrite rules flush.
		flush_rewrite_rules();
	}

	/**
	 * Create database tables
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Categories table.
		$sql = "CREATE TABLE {$prefix}categories (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			color VARCHAR(7) DEFAULT '#3788d8',
			icon VARCHAR(100) DEFAULT '',
			status VARCHAR(20) DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Services table.
		$sql = "CREATE TABLE {$prefix}services (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			category_id BIGINT(20) UNSIGNED,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			duration INT NOT NULL DEFAULT 30,
			price DECIMAL(10,2) NOT NULL DEFAULT 0,
			discounted_price DECIMAL(10,2) DEFAULT NULL,
			buffer_before INT DEFAULT 0,
			buffer_after INT DEFAULT 0,
			color VARCHAR(7) DEFAULT '#3788d8',
			status VARCHAR(20) DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY category_id (category_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Staff table.
		$sql = "CREATE TABLE {$prefix}staff (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED,
			name VARCHAR(255) NOT NULL,
			email VARCHAR(255),
			phone VARCHAR(50),
			bio TEXT,
			avatar_url VARCHAR(500),
			status VARCHAR(20) DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Staff earnings table.
		$sql = "CREATE TABLE {$prefix}staff_earnings (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			booking_id BIGINT(20) UNSIGNED,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0,
			type VARCHAR(20) DEFAULT 'commission',
			period VARCHAR(7),
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY staff_id (staff_id),
			KEY booking_id (booking_id),
			KEY period (period)
		) $charset_collate;";
		dbDelta( $sql );

		// Staff payments table.
		$sql = "CREATE TABLE {$prefix}staff_payments (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			amount DECIMAL(10,2) NOT NULL,
			payment_date DATE NOT NULL,
			payment_method VARCHAR(50) DEFAULT NULL,
			notes TEXT,
			recorded_by BIGINT(20) UNSIGNED NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY staff_id (staff_id),
			KEY payment_date (payment_date)
		) $charset_collate;";
		dbDelta( $sql );

		// Staff services table.
		$sql = "CREATE TABLE {$prefix}staff_services (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			service_id BIGINT(20) UNSIGNED NOT NULL,
			custom_price DECIMAL(10,2),
			custom_duration INT,
			PRIMARY KEY (id),
			UNIQUE KEY staff_service (staff_id, service_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Working hours table.
		$sql = "CREATE TABLE {$prefix}working_hours (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			day_of_week TINYINT NOT NULL,
			start_time TIME NOT NULL,
			end_time TIME NOT NULL,
			is_working TINYINT(1) DEFAULT 1,
			PRIMARY KEY (id),
			KEY staff_day (staff_id, day_of_week)
		) $charset_collate;";
		dbDelta( $sql );

		// Breaks table.
		$sql = "CREATE TABLE {$prefix}breaks (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			day_of_week TINYINT NOT NULL,
			start_time TIME NOT NULL,
			end_time TIME NOT NULL,
			PRIMARY KEY (id),
			KEY staff_day (staff_id, day_of_week)
		) $charset_collate;";
		dbDelta( $sql );

		// Holidays table.
		$sql = "CREATE TABLE {$prefix}holidays (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED,
			date DATE NOT NULL,
			reason VARCHAR(255),
			type VARCHAR(20) DEFAULT 'off',
			start_time TIME DEFAULT NULL,
			end_time TIME DEFAULT NULL,
			PRIMARY KEY (id),
			KEY staff_date (staff_id, date)
		) $charset_collate;";
		dbDelta( $sql );

		// Customers table.
		$sql = "CREATE TABLE {$prefix}customers (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED,
			name VARCHAR(255) NOT NULL,
			email VARCHAR(255) NOT NULL,
			phone VARCHAR(50),
			notes TEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY email (email),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Bookings table.
		$sql = "CREATE TABLE {$prefix}bookings (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			service_id BIGINT(20) UNSIGNED NOT NULL,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			customer_id BIGINT(20) UNSIGNED,
			customer_name VARCHAR(255) NOT NULL,
			customer_email VARCHAR(255) NOT NULL,
			customer_phone VARCHAR(50),
			booking_date DATE NOT NULL,
			start_time TIME NOT NULL,
			end_time TIME NOT NULL,
			price DECIMAL(10,2) NOT NULL,
			total_duration INT DEFAULT 0,
			status VARCHAR(20) DEFAULT 'pending',
			notes TEXT,
			internal_notes TEXT,
			token VARCHAR(64),
			reschedule_count INT DEFAULT 0,
			original_booking_id BIGINT(20) UNSIGNED,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY service_id (service_id),
			KEY staff_id (staff_id),
			KEY customer_id (customer_id),
			KEY booking_date (booking_date),
			KEY status (status),
			KEY original_booking_id (original_booking_id),
			UNIQUE KEY token (token)
		) $charset_collate;";
		dbDelta( $sql );

		// Booking Services table (multi-service support).
		$sql = "CREATE TABLE {$prefix}booking_services (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			booking_id BIGINT(20) UNSIGNED NOT NULL,
			service_id BIGINT(20) UNSIGNED NOT NULL,
			staff_id BIGINT(20) UNSIGNED,
			price DECIMAL(10,2) NOT NULL,
			duration INT NOT NULL,
			sort_order INT DEFAULT 0,
			PRIMARY KEY (id),
			KEY booking_id (booking_id),
			KEY service_id (service_id)
		) $charset_collate;";
		dbDelta( $sql );

		// SMS Queue table.
		$sql = "CREATE TABLE {$prefix}sms_queue (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			booking_id BIGINT(20) UNSIGNED NOT NULL,
			phone VARCHAR(50) NOT NULL,
			message TEXT NOT NULL,
			scheduled_at DATETIME NOT NULL,
			sent_at DATETIME,
			status VARCHAR(20) DEFAULT 'pending',
			attempts INT DEFAULT 0,
			provider_response TEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY booking_id (booking_id),
			KEY scheduled_at (scheduled_at),
			KEY status (status)
		) $charset_collate;";
		dbDelta( $sql );

		// SMS Templates table.
		$sql = "CREATE TABLE {$prefix}sms_templates (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(100) NOT NULL,
			type VARCHAR(50) NOT NULL,
			message TEXT NOT NULL,
			is_active TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY type (type)
		) $charset_collate;";
		dbDelta( $sql );

		// Email Templates table.
		$sql = "CREATE TABLE {$prefix}email_templates (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(100) NOT NULL,
			type VARCHAR(50) NOT NULL,
			subject VARCHAR(255) NOT NULL,
			content LONGTEXT NOT NULL,
			is_active TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY type (type)
		) $charset_collate;";
		dbDelta( $sql );

		// Promo Codes table.
		$sql = "CREATE TABLE {$prefix}promo_codes (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			code VARCHAR(50) NOT NULL,
			description TEXT,
			discount_type VARCHAR(20) NOT NULL DEFAULT 'percentage',
			discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
			first_time_only TINYINT(1) DEFAULT 0,
			min_services INT DEFAULT 0,
			min_order_amount DECIMAL(10,2) DEFAULT 0,
			max_uses INT DEFAULT 0,
			max_uses_per_customer INT DEFAULT 0,
			applicable_services TEXT,
			applicable_categories TEXT,
			start_date DATE,
			end_date DATE,
			status VARCHAR(20) DEFAULT 'active',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY code (code),
			KEY status (status)
		) $charset_collate;";
		dbDelta( $sql );

		// Promo Code Usage table.
		$sql = "CREATE TABLE {$prefix}promo_code_usage (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			promo_code_id BIGINT(20) UNSIGNED NOT NULL,
			booking_id BIGINT(20) UNSIGNED NOT NULL,
			customer_email VARCHAR(255) NOT NULL,
			discount_amount DECIMAL(10,2) NOT NULL,
			used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY promo_code_id (promo_code_id),
			KEY booking_id (booking_id),
			KEY customer_email (customer_email)
		) $charset_collate;";
		dbDelta( $sql );
	}

	/**
	 * Create default options
	 */
	private static function create_default_options() {
		$defaults = array(
			'unbsb_time_slot_interval'        => 30,
			'unbsb_booking_lead_time'         => 60,
			'unbsb_booking_future_days'       => 30,
			'unbsb_booking_flow_mode'         => 'service_first',
			'unbsb_currency'                  => 'TRY',
			'unbsb_currency_symbol'           => '₺',
			'unbsb_currency_position'         => 'after',
			'unbsb_date_format'               => 'd.m.Y',
			'unbsb_time_format'               => 'H:i',
			'unbsb_admin_email'               => get_option( 'admin_email' ),
			'unbsb_email_from_name'           => get_bloginfo( 'name' ),
			'unbsb_email_from_address'        => get_option( 'admin_email' ),
			'unbsb_company_name'              => get_bloginfo( 'name' ),
			'unbsb_company_phone'             => '',
			'unbsb_company_address'           => '',
			// ICS settings.
			'unbsb_enable_ics'                => 'yes',
			// Cancel/Reschedule settings.
			'unbsb_allow_cancel'              => 'yes',
			'unbsb_allow_reschedule'          => 'yes',
			'unbsb_cancel_deadline_hours'     => 24,
			'unbsb_reschedule_deadline_hours' => 24,
			'unbsb_max_reschedules'           => 2,
			// Multi-service settings.
			'unbsb_enable_multi_service'      => 'no',
			// Auto-confirm bookings.
			'unbsb_auto_confirm'              => 'no',
			// SMS settings.
			'unbsb_sms_enabled'               => 'no',
			'unbsb_sms_provider'              => 'netgsm',
			'unbsb_sms_netgsm_username'       => '',
			'unbsb_sms_netgsm_password'       => '',
			'unbsb_sms_netgsm_sender'         => '',
			'unbsb_sms_reminder_enabled'      => 'yes',
			'unbsb_sms_reminder_hours'        => '24',
			'unbsb_sms_on_booking'            => 'yes',
			'unbsb_sms_on_confirmation'       => 'yes',
			'unbsb_sms_on_cancellation'       => 'no',
			// Email settings.
			'unbsb_email_reminder_enabled'    => 'yes',
			'unbsb_email_reminder_hours'      => '24',
			'unbsb_email_logo_url'            => '',
			'unbsb_email_primary_color'       => '#3b82f6',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}

	/**
	 * Create custom capabilities
	 */
	private static function create_capabilities() {
		$admin = get_role( 'administrator' );

		if ( $admin ) {
			$admin->add_cap( 'unbsb_manage_bookings' );
			$admin->add_cap( 'unbsb_manage_services' );
			$admin->add_cap( 'unbsb_manage_staff' );
			$admin->add_cap( 'unbsb_manage_settings' );
			// Staff portal capabilities (admin has them too).
			$admin->add_cap( 'unbsb_view_own_bookings' );
			$admin->add_cap( 'unbsb_manage_own_schedule' );
			$admin->add_cap( 'unbsb_confirm_bookings' );
		}

		// Create Customer role.
		if ( ! get_role( 'unbsb_customer' ) ) {
			add_role(
				'unbsb_customer',
				__( 'Salon Customer', 'unbelievable-salon-booking' ),
				array(
					'read' => true,
				)
			);
		}

		// Create Staff role.
		remove_role( 'unbsb_staff' );
		add_role(
			'unbsb_staff',
			__( 'Salon Staff', 'unbelievable-salon-booking' ),
			array(
				'read'                      => true,
				'unbsb_view_own_bookings'   => true,
				'unbsb_manage_own_schedule' => true,
				'unbsb_confirm_bookings'    => true,
			)
		);
	}

	/**
	 * Run database migrations
	 */
	private static function run_migrations() {
		$current_db_version = get_option( 'unbsb_db_version', '1.0.0' );

		// v1.1.0 - Reschedule columns.
		if ( version_compare( $current_db_version, '1.1.0', '<' ) ) {
			self::migration_1_1_0();
		}

		// v1.2.0 - Multi-service support.
		if ( version_compare( $current_db_version, '1.2.0', '<' ) ) {
			self::migration_1_2_0();
		}

		// v1.3.0 - SMS support.
		if ( version_compare( $current_db_version, '1.3.0', '<' ) ) {
			self::migration_1_3_0();
		}

		// v1.4.0 - Email templates.
		if ( version_compare( $current_db_version, '1.4.0', '<' ) ) {
			self::migration_1_4_0();
		}

		// v1.5.0 - Category system.
		if ( version_compare( $current_db_version, '1.5.0', '<' ) ) {
			self::migration_1_5_0();
		}

		// v1.6.0 - Security logging.
		if ( version_compare( $current_db_version, '1.6.0', '<' ) ) {
			self::migration_1_6_0();
		}

		// v1.7.0 - Discounted price.
		if ( version_compare( $current_db_version, '1.7.0', '<' ) ) {
			self::migration_1_7_0();
		}

		// v1.8.0 - Promo codes.
		if ( version_compare( $current_db_version, '1.8.0', '<' ) ) {
			self::migration_1_8_0();
		}

		// v1.9.0 - Staff salary/commission system.
		if ( version_compare( $current_db_version, '1.9.0', '<' ) ) {
			self::migration_1_9_0();
		}

		// v2.0.0 - Payment tracking on bookings.
		if ( version_compare( $current_db_version, '2.0.0', '<' ) ) {
			self::migration_2_0_0();
		}

		// v2.1.0 - Extra open day support for holidays.
		if ( version_compare( $current_db_version, '2.1.0', '<' ) ) {
			self::migration_2_1_0();
		}

		// v2.2.0 - Staff payments table.
		if ( version_compare( $current_db_version, '2.2.0', '<' ) ) {
			self::migration_2_2_0();
		}
	}

	/**
	 * Migration 1.1.0 - Add reschedule columns
	 */
	private static function migration_1_1_0() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'unbsb_bookings';

		// reschedule_count column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'reschedule_count'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN reschedule_count INT DEFAULT 0 AFTER token" );
		}

		// original_booking_id column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'original_booking_id'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN original_booking_id BIGINT(20) UNSIGNED AFTER reschedule_count" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY original_booking_id (original_booking_id)" );
		}
	}

	/**
	 * Migration 1.2.0 - Multi-service support
	 */
	private static function migration_1_2_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Booking Services table.
		$sql = "CREATE TABLE {$prefix}booking_services (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			booking_id BIGINT(20) UNSIGNED NOT NULL,
			service_id BIGINT(20) UNSIGNED NOT NULL,
			staff_id BIGINT(20) UNSIGNED,
			price DECIMAL(10,2) NOT NULL,
			duration INT NOT NULL,
			sort_order INT DEFAULT 0,
			PRIMARY KEY (id),
			KEY booking_id (booking_id),
			KEY service_id (service_id)
		) $charset_collate;";
		dbDelta( $sql );

		// total_duration column.
		$table_name = $wpdb->prefix . 'unbsb_bookings';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'total_duration'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN total_duration INT DEFAULT 0 AFTER price" );
		}
	}

	/**
	 * Migration 1.3.0 - SMS support
	 */
	private static function migration_1_3_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// SMS Queue table.
		$sql = "CREATE TABLE {$prefix}sms_queue (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			booking_id BIGINT(20) UNSIGNED NOT NULL,
			phone VARCHAR(50) NOT NULL,
			message TEXT NOT NULL,
			scheduled_at DATETIME NOT NULL,
			sent_at DATETIME,
			status VARCHAR(20) DEFAULT 'pending',
			attempts INT DEFAULT 0,
			provider_response TEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY booking_id (booking_id),
			KEY scheduled_at (scheduled_at),
			KEY status (status)
		) $charset_collate;";
		dbDelta( $sql );

		// SMS Templates table.
		$sql = "CREATE TABLE {$prefix}sms_templates (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(100) NOT NULL,
			type VARCHAR(50) NOT NULL,
			message TEXT NOT NULL,
			is_active TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY type (type)
		) $charset_collate;";
		dbDelta( $sql );

		// Default SMS templates.
		self::create_default_sms_templates();
	}

	/**
	 * Create default SMS templates
	 */
	private static function create_default_sms_templates() {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_sms_templates';

		$templates = array(
			array(
				'name'    => 'New Booking',
				'type'    => 'booking_created',
				'message' => 'Dear {customer_name}, your booking for {service_name} on {booking_date} at {booking_time} has been created. {company_name}',
			),
			array(
				'name'    => 'Booking Confirmation',
				'type'    => 'booking_confirmed',
				'message' => 'Dear {customer_name}, your booking on {booking_date} at {booking_time} has been confirmed. We look forward to seeing you! {company_name}',
			),
			array(
				'name'    => 'Booking Cancellation',
				'type'    => 'booking_cancelled',
				'message' => 'Dear {customer_name}, your booking on {booking_date} at {booking_time} has been cancelled. {company_name}',
			),
			array(
				'name'    => 'Reminder',
				'type'    => 'reminder',
				'message' => 'Reminder: Dear {customer_name}, you have a booking for {service_name} tomorrow at {booking_time}. {company_name}',
			),
		);

		foreach ( $templates as $template ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE type = %s",
					$template['type']
				)
			);

			if ( ! $exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert(
					$table,
					$template,
					array( '%s', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Migration 1.4.0 - Email templates.
	 */
	private static function migration_1_4_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Email Templates table.
		$sql = "CREATE TABLE {$prefix}email_templates (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(100) NOT NULL,
			type VARCHAR(50) NOT NULL,
			subject VARCHAR(255) NOT NULL,
			content LONGTEXT NOT NULL,
			is_active TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY type (type)
		) $charset_collate;";
		dbDelta( $sql );

		// Default email templates.
		self::create_default_email_templates();
	}

	/**
	 * Create default email templates
	 */
	private static function create_default_email_templates() {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_email_templates';

		$templates = array(
			array(
				'name'    => 'Booking Received',
				'type'    => 'booking_received',
				'subject' => 'Your Booking Request Has Been Received - {company_name}',
				'content' => self::get_default_booking_received_template(),
			),
			array(
				'name'    => 'Booking Confirmed',
				'type'    => 'booking_confirmed',
				'subject' => 'Your Booking Has Been Confirmed - {company_name}',
				'content' => self::get_default_booking_confirmed_template(),
			),
			array(
				'name'    => 'Booking Cancelled',
				'type'    => 'booking_cancelled',
				'subject' => 'Your Booking Has Been Cancelled - {company_name}',
				'content' => self::get_default_booking_cancelled_template(),
			),
			array(
				'name'    => 'Booking Reminder',
				'type'    => 'booking_reminder',
				'subject' => 'Booking Reminder - {company_name}',
				'content' => self::get_default_booking_reminder_template(),
			),
			array(
				'name'    => 'Admin: New Booking',
				'type'    => 'admin_new_booking',
				'subject' => 'New Booking Request: {customer_name}',
				'content' => self::get_default_admin_new_booking_template(),
			),
			array(
				'name'    => 'Staff: New Booking',
				'type'    => 'staff_new_booking',
				'subject' => 'New Booking Assigned to You - {company_name}',
				'content' => self::get_default_staff_new_booking_template(),
			),
			array(
				'name'    => 'Booking Rescheduled',
				'type'    => 'booking_rescheduled',
				'subject' => 'Your Booking Has Been Rescheduled - {company_name}',
				'content' => self::get_default_booking_rescheduled_template(),
			),
		);

		foreach ( $templates as $template ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE type = %s",
					$template['type']
				)
			);

			if ( ! $exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert(
					$table,
					$template,
					array( '%s', '%s', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Default "Booking Received" email template
	 */
	private static function get_default_booking_received_template() {
		return '<p>Dear <strong>{customer_name}</strong>,</p>

<p>Your booking request has been received successfully. You will be notified about the confirmation status shortly.</p>

<h3>Booking Details</h3>
<table>
<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Staff:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Duration:</strong></td><td>{total_duration}</td></tr>
<tr><td><strong>Price:</strong></td><td>{price}</td></tr>
</table>

<p>You will be notified when your booking is confirmed.</p>

<p style="text-align: center;">
<a href="{manage_booking_url}" class="button">View My Booking</a>
</p>

<p style="text-align: center; font-size: 13px; color: #6b7280;">You can cancel or reschedule your booking through this link.</p>';
	}

	/**
	 * Default "Booking Confirmed" email template
	 */
	private static function get_default_booking_confirmed_template() {
		return '<p>Dear <strong>{customer_name}</strong>,</p>

<p style="font-size: 18px; color: #10b981;"><strong>Your booking has been confirmed!</strong></p>

<p>We look forward to seeing you on the following date:</p>

<h3>Booking Details</h3>
<table class="confirmed">
<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Staff:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Duration:</strong></td><td>{total_duration}</td></tr>
</table>

<p style="text-align: center;">
<a href="{manage_booking_url}" class="button">Manage My Booking</a>
</p>

<p style="text-align: center; font-size: 13px; color: #6b7280;">If you need to cancel or reschedule your booking, you can use the button above.</p>

{calendar_links}';
	}

	/**
	 * Default "Booking Cancelled" email template
	 */
	private static function get_default_booking_cancelled_template() {
		return '<p>Dear <strong>{customer_name}</strong>,</p>

<p style="font-size: 18px; color: #ef4444;"><strong>Your booking has been cancelled.</strong></p>

<h3>Cancelled Booking</h3>
<table class="cancelled">
<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>
</table>

<p>If you would like to make a new booking, you can visit our website or contact us.</p>

<p>Thank you for your understanding.</p>';
	}

	/**
	 * Default "Booking Reminder" email template
	 */
	private static function get_default_booking_reminder_template() {
		return '<p>Dear <strong>{customer_name}</strong>,</p>

<p style="font-size: 18px; color: #f59e0b;"><strong>Booking Reminder</strong></p>

<p>We would like to remind you of your upcoming booking:</p>

<h3>Booking Details</h3>
<table class="reminder">
<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Staff:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Duration:</strong></td><td>{total_duration}</td></tr>
</table>

<p><strong>Address:</strong><br>{company_address}</p>

<p style="text-align: center;">
<a href="{manage_booking_url}" class="button">View My Booking</a>
</p>

<p style="text-align: center; font-size: 13px; color: #6b7280;">You can use the button above for any changes.</p>

{calendar_links}';
	}

	/**
	 * Migration 1.5.0 - Category system
	 */
	private static function migration_1_5_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Categories table.
		$sql = "CREATE TABLE {$prefix}categories (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			color VARCHAR(7) DEFAULT '#3788d8',
			icon VARCHAR(100) DEFAULT '',
			status VARCHAR(20) DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Add category_id column to services table.
		$table_name = $wpdb->prefix . 'unbsb_services';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'category_id'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN category_id BIGINT(20) UNSIGNED AFTER id" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY category_id (category_id)" );
		}
	}

	/**
	 * Default "Admin: New Booking" email template
	 */
	private static function get_default_admin_new_booking_template() {
		return '<p><strong>A new booking request has been received!</strong></p>

<h3>Customer Information</h3>
<table>
<tr><td><strong>Full Name:</strong></td><td>{customer_name}</td></tr>
<tr><td><strong>Email:</strong></td><td>{customer_email}</td></tr>
<tr><td><strong>Phone:</strong></td><td>{customer_phone}</td></tr>
</table>

<h3>Booking Details</h3>
<table>
<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Staff:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Duration:</strong></td><td>{total_duration}</td></tr>
<tr><td><strong>Price:</strong></td><td>{price}</td></tr>
</table>

<p style="text-align: center;">
<a href="{admin_url}" class="button">Go to Admin Panel</a>
</p>';
	}

	/**
	 * Default "Staff: New Booking" email template
	 */
	private static function get_default_staff_new_booking_template() {
		return '<p>Hello <strong>{staff_name}</strong>,</p>

<p>A new booking has been assigned to you.</p>

<h3>Customer Information</h3>
<table>
<tr><td><strong>Full Name:</strong></td><td>{customer_name}</td></tr>
<tr><td><strong>Phone:</strong></td><td>{customer_phone}</td></tr>
</table>

<h3>Booking Details</h3>
<table>
<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Duration:</strong></td><td>{total_duration}</td></tr>
</table>

<p>Please log in to your staff portal to confirm or manage this booking.</p>

<p style="text-align: center;">
<a href="{admin_url}" class="button">Go to Staff Portal</a>
</p>';
	}

	/**
	 * Default "Booking Rescheduled" email template
	 */
	private static function get_default_booking_rescheduled_template() {
		return '<p>Dear <strong>{customer_name}</strong>,</p>

<p style="font-size: 18px; color: #6366f1;"><strong>Your booking has been rescheduled.</strong></p>

<h3>Previous Booking</h3>
<table class="cancelled">
<tr><td><strong>Date:</strong></td><td>{old_booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{old_booking_time}</td></tr>
</table>

<h3>New Booking Details</h3>
<table class="confirmed">
<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Staff:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Duration:</strong></td><td>{total_duration}</td></tr>
</table>

<p style="text-align: center;">
<a href="{manage_booking_url}" class="button">Manage My Booking</a>
</p>

<p style="text-align: center; font-size: 13px; color: #6b7280;">If you need to make further changes, you can use the button above.</p>';
	}

	/**
	 * Migration 1.6.0 - Security logging table
	 */
	private static function migration_1_6_0() {
		// Create security logger table.
		require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-security-logger.php';
		UNBSB_Security_Logger::create_table();

		// Default setting.
		add_option( 'unbsb_security_logging_enabled', 'yes' );
	}

	/**
	 * Migration 1.8.0 - Promo codes system
	 */
	private static function migration_1_8_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Promo Codes table.
		$sql = "CREATE TABLE {$prefix}promo_codes (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			code VARCHAR(50) NOT NULL,
			description TEXT,
			discount_type VARCHAR(20) NOT NULL DEFAULT 'percentage',
			discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
			first_time_only TINYINT(1) DEFAULT 0,
			min_services INT DEFAULT 0,
			min_order_amount DECIMAL(10,2) DEFAULT 0,
			max_uses INT DEFAULT 0,
			max_uses_per_customer INT DEFAULT 0,
			applicable_services TEXT,
			applicable_categories TEXT,
			start_date DATE,
			end_date DATE,
			status VARCHAR(20) DEFAULT 'active',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY code (code),
			KEY status (status)
		) $charset_collate;";
		dbDelta( $sql );

		// Promo Code Usage table.
		$sql = "CREATE TABLE {$prefix}promo_code_usage (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			promo_code_id BIGINT(20) UNSIGNED NOT NULL,
			booking_id BIGINT(20) UNSIGNED NOT NULL,
			customer_email VARCHAR(255) NOT NULL,
			discount_amount DECIMAL(10,2) NOT NULL,
			used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY promo_code_id (promo_code_id),
			KEY booking_id (booking_id),
			KEY customer_email (customer_email)
		) $charset_collate;";
		dbDelta( $sql );

		// Add promo_code_id and discount_amount to bookings table.
		$table_name = $wpdb->prefix . 'unbsb_bookings';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'promo_code_id'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN promo_code_id BIGINT(20) UNSIGNED DEFAULT NULL AFTER internal_notes" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0 AFTER promo_code_id" );
		}
	}

	/**
	 * Migration 1.7.0 - Add discounted_price column to services
	 */
	private static function migration_1_7_0() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'unbsb_services';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'discounted_price'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN discounted_price DECIMAL(10,2) DEFAULT NULL AFTER price" );
		}
	}

	/**
	 * Migration 1.9.0 - Staff salary/commission system
	 */
	private static function migration_1_9_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Staff earnings table.
		$sql = "CREATE TABLE {$prefix}staff_earnings (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			booking_id BIGINT(20) UNSIGNED,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0,
			type VARCHAR(20) DEFAULT 'commission',
			period VARCHAR(7),
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY staff_id (staff_id),
			KEY booking_id (booking_id),
			KEY period (period)
		) $charset_collate;";
		dbDelta( $sql );

		// Add salary columns to staff table.
		$table_name = $wpdb->prefix . 'unbsb_staff';

		// salary_type column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'salary_type'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN salary_type VARCHAR(20) DEFAULT 'percentage' AFTER sort_order" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN salary_percentage DECIMAL(5,2) DEFAULT 0 AFTER salary_type" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN salary_fixed DECIMAL(10,2) DEFAULT 0 AFTER salary_percentage" );
		}
	}

	/**
	 * Migration 2.0.0 - Payment tracking on bookings
	 */
	private static function migration_2_0_0() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'unbsb_bookings';

		// paid_amount column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'paid_amount'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN paid_amount DECIMAL(10,2) DEFAULT NULL AFTER discount_amount" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER paid_amount" );
		}
	}

	/**
	 * Migration 2.1.0 - Extra open day support for holidays
	 */
	private static function migration_2_1_0() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'unbsb_holidays';

		// type column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s',
				'type'
			)
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN type VARCHAR(20) DEFAULT 'off' AFTER reason" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN start_time TIME DEFAULT NULL AFTER type" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN end_time TIME DEFAULT NULL AFTER start_time" );
		}
	}

	/**
	 * Migration 2.2.0 - Staff payments table
	 */
	private static function migration_2_2_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'unbsb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$prefix}staff_payments (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED NOT NULL,
			amount DECIMAL(10,2) NOT NULL,
			payment_date DATE NOT NULL,
			payment_method VARCHAR(50) DEFAULT NULL,
			notes TEXT,
			recorded_by BIGINT(20) UNSIGNED NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY staff_id (staff_id),
			KEY payment_date (payment_date)
		) $charset_collate;";
		dbDelta( $sql );
	}
}
