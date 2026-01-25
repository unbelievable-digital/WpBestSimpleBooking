<?php
/**
 * Plugin aktivasyon sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activator sınıfı
 */
class AG_Activator {

	/**
	 * Aktivasyon işlemleri
	 */
	public static function activate() {
		self::create_tables();
		self::create_default_options();
		self::create_capabilities();
		self::run_migrations();

		// Versiyon kaydet
		update_option( 'ag_version', AG_VERSION );
		update_option( 'ag_db_version', '1.5.0' );

		// Rewrite rules flush
		flush_rewrite_rules();
	}

	/**
	 * Veritabanı tablolarını oluştur
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'ag_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Categories tablosu
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

		// Services tablosu
		$sql = "CREATE TABLE {$prefix}services (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			category_id BIGINT(20) UNSIGNED,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			duration INT NOT NULL DEFAULT 30,
			price DECIMAL(10,2) NOT NULL DEFAULT 0,
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

		// Staff tablosu
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

		// Staff services tablosu
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

		// Working hours tablosu
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

		// Breaks tablosu
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

		// Holidays tablosu
		$sql = "CREATE TABLE {$prefix}holidays (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id BIGINT(20) UNSIGNED,
			date DATE NOT NULL,
			reason VARCHAR(255),
			PRIMARY KEY (id),
			KEY staff_date (staff_id, date)
		) $charset_collate;";
		dbDelta( $sql );

		// Customers tablosu
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

		// Bookings tablosu
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

		// Booking Services tablosu (Çoklu hizmet desteği)
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

		// SMS Queue tablosu
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

		// SMS Templates tablosu
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

		// Email Templates tablosu
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
	}

	/**
	 * Varsayılan ayarları oluştur
	 */
	private static function create_default_options() {
		$defaults = array(
			'ag_time_slot_interval'       => 30,
			'ag_booking_lead_time'        => 60,
			'ag_booking_future_days'      => 30,
			'ag_booking_flow_mode'        => 'service_first',
			'ag_currency'                 => 'TRY',
			'ag_currency_symbol'          => '₺',
			'ag_currency_position'        => 'after',
			'ag_date_format'              => 'd.m.Y',
			'ag_time_format'              => 'H:i',
			'ag_admin_email'              => get_option( 'admin_email' ),
			'ag_email_from_name'          => get_bloginfo( 'name' ),
			'ag_email_from_address'       => get_option( 'admin_email' ),
			'ag_sms_enabled'              => 0,
			'ag_company_name'             => get_bloginfo( 'name' ),
			'ag_company_phone'            => '',
			'ag_company_address'          => '',
			// ICS ayarları.
			'ag_enable_ics'               => 'yes',
			// İptal/Yeniden planlama ayarları.
			'ag_allow_cancel'             => 'yes',
			'ag_allow_reschedule'         => 'yes',
			'ag_cancel_deadline_hours'    => 24,
			'ag_reschedule_deadline_hours' => 24,
			'ag_max_reschedules'          => 2,
			// Çoklu hizmet ayarları.
			'ag_enable_multi_service'     => 'no',
			// SMS ayarları.
			'ag_sms_enabled'              => 'no',
			'ag_sms_provider'             => 'netgsm',
			'ag_sms_netgsm_username'      => '',
			'ag_sms_netgsm_password'      => '',
			'ag_sms_netgsm_sender'        => '',
			'ag_sms_reminder_enabled'     => 'yes',
			'ag_sms_reminder_hours'       => '24',
			'ag_sms_on_booking'           => 'yes',
			'ag_sms_on_confirmation'      => 'yes',
			'ag_sms_on_cancellation'      => 'no',
			// E-posta ayarları.
			'ag_email_reminder_enabled'   => 'yes',
			'ag_email_reminder_hours'     => '24',
			'ag_email_logo_url'           => '',
			'ag_email_primary_color'      => '#3b82f6',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}

	/**
	 * Özel yetkiler oluştur
	 */
	private static function create_capabilities() {
		$admin = get_role( 'administrator' );

		if ( $admin ) {
			$admin->add_cap( 'ag_manage_bookings' );
			$admin->add_cap( 'ag_manage_services' );
			$admin->add_cap( 'ag_manage_staff' );
			$admin->add_cap( 'ag_manage_settings' );
		}
	}

	/**
	 * Veritabanı migrasyonlarını çalıştır
	 */
	private static function run_migrations() {
		$current_db_version = get_option( 'ag_db_version', '1.0.0' );

		// v1.1.0 - Reschedule kolonları.
		if ( version_compare( $current_db_version, '1.1.0', '<' ) ) {
			self::migration_1_1_0();
		}

		// v1.2.0 - Çoklu hizmet desteği.
		if ( version_compare( $current_db_version, '1.2.0', '<' ) ) {
			self::migration_1_2_0();
		}

		// v1.3.0 - SMS desteği.
		if ( version_compare( $current_db_version, '1.3.0', '<' ) ) {
			self::migration_1_3_0();
		}

		// v1.4.0 - E-posta şablonları.
		if ( version_compare( $current_db_version, '1.4.0', '<' ) ) {
			self::migration_1_4_0();
		}

		// v1.5.0 - Kategori sistemi.
		if ( version_compare( $current_db_version, '1.5.0', '<' ) ) {
			self::migration_1_5_0();
		}
	}

	/**
	 * Migration 1.1.0 - Reschedule kolonları ekle
	 */
	private static function migration_1_1_0() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ag_bookings';

		// reschedule_count kolonu.
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'reschedule_count'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN reschedule_count INT DEFAULT 0 AFTER token" );
		}

		// original_booking_id kolonu.
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'original_booking_id'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN original_booking_id BIGINT(20) UNSIGNED AFTER reschedule_count" );
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY original_booking_id (original_booking_id)" );
		}
	}

	/**
	 * Migration 1.2.0 - Çoklu hizmet desteği
	 */
	private static function migration_1_2_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'ag_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Booking Services tablosu.
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

		// total_duration kolonu.
		$table_name    = $wpdb->prefix . 'ag_bookings';
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'total_duration'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN total_duration INT DEFAULT 0 AFTER price" );
		}
	}

	/**
	 * Migration 1.3.0 - SMS desteği
	 */
	private static function migration_1_3_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'ag_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// SMS Queue tablosu.
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

		// SMS Templates tablosu.
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

		// Varsayılan SMS şablonları.
		self::create_default_sms_templates();
	}

	/**
	 * Varsayılan SMS şablonlarını oluştur
	 */
	private static function create_default_sms_templates() {
		global $wpdb;

		$table = $wpdb->prefix . 'ag_sms_templates';

		$templates = array(
			array(
				'name'    => 'Yeni Randevu',
				'type'    => 'booking_created',
				'message' => 'Sayin {customer_name}, {booking_date} tarihinde saat {booking_time} icin {service_name} randevunuz olusturuldu. {company_name}',
			),
			array(
				'name'    => 'Randevu Onayı',
				'type'    => 'booking_confirmed',
				'message' => 'Sayin {customer_name}, {booking_date} {booking_time} randevunuz onaylandi. Sizi bekliyoruz! {company_name}',
			),
			array(
				'name'    => 'Randevu İptali',
				'type'    => 'booking_cancelled',
				'message' => 'Sayin {customer_name}, {booking_date} {booking_time} randevunuz iptal edildi. {company_name}',
			),
			array(
				'name'    => 'Hatırlatma',
				'type'    => 'reminder',
				'message' => 'Hatirlatma: Sayin {customer_name}, yarin saat {booking_time} icin {service_name} randevunuz var. {company_name}',
			),
		);

		foreach ( $templates as $template ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE type = %s",
					$template['type']
				)
			);

			if ( ! $exists ) {
				$wpdb->insert(
					$table,
					$template,
					array( '%s', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Migration 1.4.0 - E-posta şablonları
	 */
	private static function migration_1_4_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'ag_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Email Templates tablosu.
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

		// Varsayılan e-posta şablonları.
		self::create_default_email_templates();
	}

	/**
	 * Varsayılan e-posta şablonlarını oluştur
	 */
	private static function create_default_email_templates() {
		global $wpdb;

		$table = $wpdb->prefix . 'ag_email_templates';

		$templates = array(
			array(
				'name'    => 'Randevu Alındı',
				'type'    => 'booking_received',
				'subject' => 'Randevu Talebiniz Alındı - {company_name}',
				'content' => self::get_default_booking_received_template(),
			),
			array(
				'name'    => 'Randevu Onaylandı',
				'type'    => 'booking_confirmed',
				'subject' => 'Randevunuz Onaylandı - {company_name}',
				'content' => self::get_default_booking_confirmed_template(),
			),
			array(
				'name'    => 'Randevu İptal Edildi',
				'type'    => 'booking_cancelled',
				'subject' => 'Randevunuz İptal Edildi - {company_name}',
				'content' => self::get_default_booking_cancelled_template(),
			),
			array(
				'name'    => 'Randevu Hatırlatma',
				'type'    => 'booking_reminder',
				'subject' => 'Randevu Hatırlatması - {company_name}',
				'content' => self::get_default_booking_reminder_template(),
			),
			array(
				'name'    => 'Admin: Yeni Randevu',
				'type'    => 'admin_new_booking',
				'subject' => 'Yeni Randevu Talebi: {customer_name}',
				'content' => self::get_default_admin_new_booking_template(),
			),
		);

		foreach ( $templates as $template ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE type = %s",
					$template['type']
				)
			);

			if ( ! $exists ) {
				$wpdb->insert(
					$table,
					$template,
					array( '%s', '%s', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Varsayılan "Randevu Alındı" e-posta şablonu
	 */
	private static function get_default_booking_received_template() {
		return '<p>Sayın <strong>{customer_name}</strong>,</p>

<p>Randevu talebiniz başarıyla alınmıştır. En kısa sürede onay durumu hakkında bilgilendirileceksiniz.</p>

<h3>Randevu Detayları</h3>
<table>
<tr><td><strong>Hizmet(ler):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Personel:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Tarih:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Saat:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Süre:</strong></td><td>{total_duration}</td></tr>
<tr><td><strong>Ücret:</strong></td><td>{price}</td></tr>
</table>

<p>Randevunuz onaylandığında size bilgi verilecektir.</p>

<p style="text-align: center;">
<a href="{manage_booking_url}" class="button">Randevumu Görüntüle</a>
</p>

<p style="text-align: center; font-size: 13px; color: #6b7280;">Bu link üzerinden randevunuzu iptal edebilir veya değiştirebilirsiniz.</p>';
	}

	/**
	 * Varsayılan "Randevu Onaylandı" e-posta şablonu
	 */
	private static function get_default_booking_confirmed_template() {
		return '<p>Sayın <strong>{customer_name}</strong>,</p>

<p style="font-size: 18px; color: #10b981;"><strong>Randevunuz onaylanmıştır!</strong></p>

<p>Sizi aşağıdaki tarihte bekliyoruz:</p>

<h3>Randevu Detayları</h3>
<table class="confirmed">
<tr><td><strong>Hizmet(ler):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Personel:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Tarih:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Saat:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Süre:</strong></td><td>{total_duration}</td></tr>
</table>

<p style="text-align: center;">
<a href="{manage_booking_url}" class="button">Randevumu Yönet</a>
</p>

<p style="text-align: center; font-size: 13px; color: #6b7280;">Randevunuzu iptal etmeniz veya değiştirmeniz gerekirse yukarıdaki butonu kullanabilirsiniz.</p>

{calendar_links}';
	}

	/**
	 * Varsayılan "Randevu İptal Edildi" e-posta şablonu
	 */
	private static function get_default_booking_cancelled_template() {
		return '<p>Sayın <strong>{customer_name}</strong>,</p>

<p style="font-size: 18px; color: #ef4444;"><strong>Randevunuz iptal edilmiştir.</strong></p>

<h3>İptal Edilen Randevu</h3>
<table class="cancelled">
<tr><td><strong>Hizmet(ler):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Tarih:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Saat:</strong></td><td>{booking_time}</td></tr>
</table>

<p>Yeni bir randevu almak isterseniz web sitemizi ziyaret edebilir veya bizimle iletişime geçebilirsiniz.</p>

<p>Anlayışınız için teşekkür ederiz.</p>';
	}

	/**
	 * Varsayılan "Randevu Hatırlatma" e-posta şablonu
	 */
	private static function get_default_booking_reminder_template() {
		return '<p>Sayın <strong>{customer_name}</strong>,</p>

<p style="font-size: 18px; color: #f59e0b;"><strong>Randevu Hatırlatması</strong></p>

<p>Yaklaşan randevunuzu hatırlatmak istiyoruz:</p>

<h3>Randevu Detayları</h3>
<table class="reminder">
<tr><td><strong>Hizmet(ler):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Personel:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Tarih:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Saat:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Süre:</strong></td><td>{total_duration}</td></tr>
</table>

<p><strong>Adres:</strong><br>{company_address}</p>

<p style="text-align: center;">
<a href="{manage_booking_url}" class="button">Randevumu Görüntüle</a>
</p>

<p style="text-align: center; font-size: 13px; color: #6b7280;">Herhangi bir değişiklik için yukarıdaki butonu kullanabilirsiniz.</p>

{calendar_links}';
	}

	/**
	 * Migration 1.5.0 - Kategori sistemi
	 */
	private static function migration_1_5_0() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'ag_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Categories tablosu.
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

		// services tablosuna category_id kolonu ekle.
		$table_name    = $wpdb->prefix . 'ag_services';
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'category_id'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN category_id BIGINT(20) UNSIGNED AFTER id" );
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY category_id (category_id)" );
		}
	}

	/**
	 * Varsayılan "Admin: Yeni Randevu" e-posta şablonu
	 */
	private static function get_default_admin_new_booking_template() {
		return '<p><strong>Yeni bir randevu talebi alındı!</strong></p>

<h3>Müşteri Bilgileri</h3>
<table>
<tr><td><strong>Ad Soyad:</strong></td><td>{customer_name}</td></tr>
<tr><td><strong>E-posta:</strong></td><td>{customer_email}</td></tr>
<tr><td><strong>Telefon:</strong></td><td>{customer_phone}</td></tr>
</table>

<h3>Randevu Detayları</h3>
<table>
<tr><td><strong>Hizmet(ler):</strong></td><td>{services_list}</td></tr>
<tr><td><strong>Personel:</strong></td><td>{staff_name}</td></tr>
<tr><td><strong>Tarih:</strong></td><td>{booking_date}</td></tr>
<tr><td><strong>Saat:</strong></td><td>{booking_time}</td></tr>
<tr><td><strong>Süre:</strong></td><td>{total_duration}</td></tr>
<tr><td><strong>Ücret:</strong></td><td>{price}</td></tr>
</table>

<p style="text-align: center;">
<a href="{admin_url}" class="button">Yönetim Paneline Git</a>
</p>';
	}
}
