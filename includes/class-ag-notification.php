<?php
/**
 * Notification sınıfı - Email bildirimleri
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification sınıfı
 */
class AG_Notification {

	/**
	 * ICS Generator instance
	 *
	 * @var AG_ICS_Generator
	 */
	private $ics_generator;

	/**
	 * Email templates table
	 *
	 * @var string
	 */
	private $templates_table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->templates_table = $wpdb->prefix . 'ag_email_templates';

		// Randevu oluşturulduğunda
		add_action( 'ag_after_booking_created', array( $this, 'send_booking_created_emails' ), 10, 2 );

		// Durum değiştiğinde
		add_action( 'ag_booking_status_changed', array( $this, 'send_status_changed_email' ), 10, 3 );

		// Hatırlatma cron
		add_action( 'ag_send_reminder_emails', array( $this, 'process_reminder_emails' ) );

		// Cron schedule
		if ( ! wp_next_scheduled( 'ag_send_reminder_emails' ) ) {
			wp_schedule_event( time(), 'hourly', 'ag_send_reminder_emails' );
		}

		// ICS generator yükle.
		$this->load_ics_generator();
	}

	/**
	 * ICS Generator'ı yükle
	 */
	private function load_ics_generator() {
		if ( ! class_exists( 'AG_ICS_Generator' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-ag-ics-generator.php';
		}
		$this->ics_generator = new AG_ICS_Generator();
	}

	/**
	 * Randevu oluşturulduğunda email gönder
	 *
	 * @param int   $booking_id   Randevu ID.
	 * @param array $booking_data Randevu verileri.
	 */
	public function send_booking_created_emails( $booking_id, $booking_data ) {
		$booking_model = new AG_Booking();
		$booking       = $booking_model->get_with_details( $booking_id );

		if ( ! $booking ) {
			return;
		}

		// Müşteriye email
		$this->send_customer_email( $booking, 'booking_received' );

		// Admin'e email
		$this->send_admin_email( $booking, 'admin_new_booking' );
	}

	/**
	 * Durum değiştiğinde email gönder
	 *
	 * @param int    $booking_id Randevu ID.
	 * @param string $new_status Yeni durum.
	 * @param string $old_status Eski durum.
	 */
	public function send_status_changed_email( $booking_id, $new_status, $old_status ) {
		if ( $new_status === $old_status ) {
			return;
		}

		$booking_model = new AG_Booking();
		$booking       = $booking_model->get_with_details( $booking_id );

		if ( ! $booking ) {
			return;
		}

		switch ( $new_status ) {
			case 'confirmed':
				$this->send_customer_email( $booking, 'booking_confirmed', true );
				break;

			case 'cancelled':
				$this->send_customer_email( $booking, 'booking_cancelled', false, true );
				break;
		}
	}

	/**
	 * Hatırlatma e-postalarını işle
	 */
	public function process_reminder_emails() {
		if ( 'yes' !== get_option( 'ag_email_reminder_enabled', 'yes' ) ) {
			return;
		}

		$hours = intval( get_option( 'ag_email_reminder_hours', 24 ) );

		global $wpdb;
		$prefix = $wpdb->prefix . 'ag_';

		// Hatırlatılacak randevuları bul
		$reminder_date = gmdate( 'Y-m-d', strtotime( "+{$hours} hours" ) );
		$reminder_time = gmdate( 'H:i:s', strtotime( "+{$hours} hours" ) );

		$bookings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.* FROM {$prefix}bookings b
				WHERE b.booking_date = %s
				AND b.start_time <= %s
				AND b.status IN ('pending', 'confirmed')
				AND b.customer_email IS NOT NULL
				AND b.customer_email != ''
				AND NOT EXISTS (
					SELECT 1 FROM {$wpdb->postmeta} pm
					WHERE pm.post_id = b.id
					AND pm.meta_key = '_ag_reminder_sent'
				)",
				$reminder_date,
				$reminder_time
			)
		);

		$booking_model = new AG_Booking();

		foreach ( $bookings as $booking_row ) {
			$booking = $booking_model->get_with_details( $booking_row->id );
			if ( $booking ) {
				$this->send_customer_email( $booking, 'booking_reminder', true );
				// Hatırlatma gönderildi olarak işaretle
				update_option( '_ag_reminder_sent_' . $booking->id, time() );
			}
		}
	}

	/**
	 * Müşteriye email gönder
	 *
	 * @param object $booking       Randevu.
	 * @param string $template_type Şablon tipi.
	 * @param bool   $include_ics   ICS dosyası eklensin mi.
	 * @param bool   $cancel_ics    İptal ICS dosyası mı.
	 */
	public function send_customer_email( $booking, $template_type, $include_ics = false, $cancel_ics = false ) {
		$template = $this->get_template( $template_type );

		if ( ! $template || ! $template->is_active ) {
			return;
		}

		$to      = $booking->customer_email;
		$subject = $this->parse_placeholders( $template->subject, $booking );
		$content = $this->parse_placeholders( $template->content, $booking );

		// ICS eklentisi
		$attachments  = array();
		$ics_filepath = null;

		if ( $this->is_ics_enabled() && $include_ics ) {
			// Takvim linkleri
			$calendar_links = $this->ics_generator->get_calendar_links_html( $booking );
			$content        = str_replace( '{calendar_links}', $calendar_links, $content );

			// ICS dosyası
			if ( $cancel_ics ) {
				$ics_filepath = $this->ics_generator->save_cancellation_temp_file( $booking );
			} else {
				$ics_filepath = $this->ics_generator->save_temp_file( $booking );
			}

			if ( $ics_filepath ) {
				$attachments[] = $ics_filepath;
			}
		}

		// Takvim linklerini temizle (ICS kapalıysa)
		$content = str_replace( '{calendar_links}', '', $content );

		// HTML wrapper ile sar
		$message = $this->wrap_email_content( $content, $template_type );

		$this->send_email( $to, $subject, $message, $attachments );

		// Geçici ICS dosyasını sil
		if ( $ics_filepath ) {
			$this->ics_generator->delete_temp_file( $ics_filepath );
		}
	}

	/**
	 * Admin'e email gönder
	 *
	 * @param object $booking       Randevu.
	 * @param string $template_type Şablon tipi.
	 */
	public function send_admin_email( $booking, $template_type ) {
		$template = $this->get_template( $template_type );

		if ( ! $template || ! $template->is_active ) {
			return;
		}

		$to      = get_option( 'ag_admin_email', get_option( 'admin_email' ) );
		$subject = $this->parse_placeholders( $template->subject, $booking );
		$content = $this->parse_placeholders( $template->content, $booking );

		// Takvim linklerini temizle
		$content = str_replace( '{calendar_links}', '', $content );

		// HTML wrapper ile sar
		$message = $this->wrap_email_content( $content, $template_type );

		$this->send_email( $to, $subject, $message );
	}

	/**
	 * Şablonu veritabanından getir
	 *
	 * @param string $type Şablon tipi.
	 *
	 * @return object|null
	 */
	public function get_template( $type ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->templates_table} WHERE type = %s",
				$type
			)
		);
	}

	/**
	 * Tüm şablonları getir
	 *
	 * @return array
	 */
	public function get_all_templates() {
		global $wpdb;

		// Tabloyu oluştur ve varsayılan şablonları ekle (eğer yoksa).
		$this->ensure_templates_exist();

		return $wpdb->get_results( "SELECT * FROM {$this->templates_table} ORDER BY id ASC" );
	}

	/**
	 * Şablonların mevcut olduğundan emin ol
	 */
	private function ensure_templates_exist() {
		global $wpdb;

		// Tablo var mı kontrol et.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$this->templates_table}'" );

		if ( ! $table_exists ) {
			// Tabloyu oluştur.
			$this->create_email_templates_table();
		}

		// Şablon var mı kontrol et.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->templates_table}" );

		if ( 0 === intval( $count ) ) {
			// Varsayılan şablonları oluştur.
			$this->create_default_email_templates();
		}
	}

	/**
	 * E-posta şablonları tablosunu oluştur
	 */
	private function create_email_templates_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$this->templates_table} (
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
	 * Varsayılan e-posta şablonlarını oluştur
	 */
	private function create_default_email_templates() {
		global $wpdb;

		$templates = array(
			array(
				'name'    => __( 'Randevu Alındı', 'appointment-general' ),
				'type'    => 'booking_received',
				'subject' => __( 'Randevu Talebiniz Alındı', 'appointment-general' ) . ' - {company_name}',
				'content' => $this->get_default_booking_received_template(),
			),
			array(
				'name'    => __( 'Randevu Onaylandı', 'appointment-general' ),
				'type'    => 'booking_confirmed',
				'subject' => __( 'Randevunuz Onaylandı', 'appointment-general' ) . ' - {company_name}',
				'content' => $this->get_default_booking_confirmed_template(),
			),
			array(
				'name'    => __( 'Randevu İptal Edildi', 'appointment-general' ),
				'type'    => 'booking_cancelled',
				'subject' => __( 'Randevunuz İptal Edildi', 'appointment-general' ) . ' - {company_name}',
				'content' => $this->get_default_booking_cancelled_template(),
			),
			array(
				'name'    => __( 'Randevu Hatırlatma', 'appointment-general' ),
				'type'    => 'booking_reminder',
				'subject' => __( 'Randevu Hatırlatması', 'appointment-general' ) . ' - {company_name}',
				'content' => $this->get_default_booking_reminder_template(),
			),
			array(
				'name'    => __( 'Admin: Yeni Randevu', 'appointment-general' ),
				'type'    => 'admin_new_booking',
				'subject' => __( 'Yeni Randevu Talebi', 'appointment-general' ) . ': {customer_name}',
				'content' => $this->get_default_admin_new_booking_template(),
			),
		);

		foreach ( $templates as $template ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$this->templates_table} WHERE type = %s",
					$template['type']
				)
			);

			if ( ! $exists ) {
				$wpdb->insert(
					$this->templates_table,
					$template,
					array( '%s', '%s', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Varsayılan "Randevu Alındı" e-posta şablonu
	 */
	private function get_default_booking_received_template() {
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
	private function get_default_booking_confirmed_template() {
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
	private function get_default_booking_cancelled_template() {
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
	private function get_default_booking_reminder_template() {
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
	 * Varsayılan "Admin: Yeni Randevu" e-posta şablonu
	 */
	private function get_default_admin_new_booking_template() {
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

	/**
	 * Şablonu güncelle
	 *
	 * @param int    $id        Şablon ID.
	 * @param string $subject   Konu.
	 * @param string $content   İçerik.
	 * @param bool   $is_active Aktif mi.
	 *
	 * @return bool
	 */
	public function update_template( $id, $subject, $content, $is_active = true ) {
		global $wpdb;

		return (bool) $wpdb->update(
			$this->templates_table,
			array(
				'subject'   => sanitize_text_field( $subject ),
				'content'   => wp_kses_post( $content ),
				'is_active' => $is_active ? 1 : 0,
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Placeholder'ları değiştir
	 *
	 * @param string $content İçerik.
	 * @param object $booking Randevu.
	 *
	 * @return string
	 */
	private function parse_placeholders( $content, $booking ) {
		$company_name    = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
		$company_phone   = get_option( 'ag_company_phone', '' );
		$company_address = get_option( 'ag_company_address', '' );
		$currency_symbol = get_option( 'ag_currency_symbol', '₺' );
		$date_format     = get_option( 'ag_date_format', 'd.m.Y' );
		$time_format     = get_option( 'ag_time_format', 'H:i' );

		$formatted_date = date_i18n( $date_format, strtotime( $booking->booking_date ) );
		$formatted_time = date_i18n( $time_format, strtotime( $booking->start_time ) );

		// Manage booking URL
		$manage_url = $this->get_manage_booking_url( $booking->token );

		// Çoklu hizmet için services_list kullan
		$services_list = ! empty( $booking->services_list ) ? $booking->services_list : $booking->service_name;

		// Toplam süre
		$total_duration = ! empty( $booking->total_duration )
			? $booking->total_duration
			: ( ! empty( $booking->service_duration ) ? $booking->service_duration : 0 );

		$placeholders = array(
			'{customer_name}'      => $booking->customer_name,
			'{customer_email}'     => $booking->customer_email,
			'{customer_phone}'     => $booking->customer_phone ?? '-',
			'{service_name}'       => $booking->service_name,
			'{services_list}'      => $services_list,
			'{staff_name}'         => $booking->staff_name,
			'{booking_date}'       => $formatted_date,
			'{booking_time}'       => $formatted_time,
			'{price}'              => $booking->price . ' ' . $currency_symbol,
			'{total_duration}'     => $total_duration . ' ' . __( 'dk', 'appointment-general' ),
			'{company_name}'       => $company_name,
			'{company_phone}'      => $company_phone,
			'{company_address}'    => nl2br( $company_address ),
			'{booking_id}'         => $booking->id,
			'{status}'             => $this->get_status_label( $booking->status ),
			'{manage_booking_url}' => $manage_url,
			'{admin_url}'          => admin_url( 'admin.php?page=ag-bookings' ),
		);

		$content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $content );

		return apply_filters( 'ag_filter_email_content', $content, $booking );
	}

	/**
	 * E-posta içeriğini HTML wrapper ile sar
	 *
	 * @param string $content       İçerik.
	 * @param string $template_type Şablon tipi.
	 *
	 * @return string
	 */
	private function wrap_email_content( $content, $template_type ) {
		$company_name  = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
		$company_phone = get_option( 'ag_company_phone', '' );
		$primary_color = get_option( 'ag_email_primary_color', '#3b82f6' );
		$logo_url      = get_option( 'ag_email_logo_url', '' );

		// Template tipine göre accent renk
		$accent_colors = array(
			'booking_received'  => '#3b82f6', // Mavi
			'booking_confirmed' => '#10b981', // Yeşil
			'booking_cancelled' => '#ef4444', // Kırmızı
			'booking_reminder'  => '#f59e0b', // Turuncu
			'admin_new_booking' => '#8b5cf6', // Mor
		);
		$accent_color = $accent_colors[ $template_type ] ?? $primary_color;

		$logo_html = '';
		if ( $logo_url ) {
			$logo_html = '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $company_name ) . '" style="max-height: 60px; width: auto;">';
		} else {
			$logo_html = '<span style="font-size: 24px; font-weight: 700; color: ' . esc_attr( $accent_color ) . ';">' . esc_html( $company_name ) . '</span>';
		}

		$html = '<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>' . esc_html( $company_name ) . '</title>
	<!--[if mso]>
	<noscript>
		<xml>
			<o:OfficeDocumentSettings>
				<o:PixelsPerInch>96</o:PixelsPerInch>
			</o:OfficeDocumentSettings>
		</xml>
	</noscript>
	<![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif;">
	<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f3f4f6;">
		<tr>
			<td style="padding: 40px 20px;">
				<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; margin: 0 auto;">

					<!-- Header -->
					<tr>
						<td style="background: linear-gradient(135deg, ' . esc_attr( $accent_color ) . ' 0%, ' . esc_attr( $this->adjust_color( $accent_color, -20 ) ) . ' 100%); padding: 30px 40px; border-radius: 16px 16px 0 0; text-align: center;">
							' . $logo_html . '
						</td>
					</tr>

					<!-- Content -->
					<tr>
						<td style="background-color: #ffffff; padding: 40px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
								<tr>
									<td style="font-size: 16px; line-height: 1.6; color: #374151;">
										<style>
											h3 { color: #1f2937; font-size: 18px; font-weight: 600; margin: 25px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb; }
											table { width: 100%; border-collapse: collapse; margin: 15px 0; }
											table td { padding: 12px 15px; border-bottom: 1px solid #e5e7eb; }
											table tr:last-child td { border-bottom: none; }
											table td:first-child { color: #6b7280; width: 140px; }
											table.confirmed { background: #ecfdf5; border-radius: 8px; border-left: 4px solid #10b981; }
											table.cancelled { background: #fef2f2; border-radius: 8px; border-left: 4px solid #ef4444; }
											table.reminder { background: #fffbeb; border-radius: 8px; border-left: 4px solid #f59e0b; }
											.button { display: inline-block; padding: 14px 32px; background-color: ' . esc_attr( $accent_color ) . '; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; margin: 10px 0; }
											.button:hover { background-color: ' . esc_attr( $this->adjust_color( $accent_color, -15 ) ) . '; }
											p { margin: 0 0 16px 0; }
										</style>
										' . $content . '
									</td>
								</tr>
							</table>
						</td>
					</tr>

					<!-- Footer -->
					<tr>
						<td style="padding: 30px 40px; text-align: center;">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
								<tr>
									<td style="font-size: 14px; color: #6b7280; line-height: 1.6;">
										<strong style="color: #374151;">' . esc_html( $company_name ) . '</strong><br>';

		if ( $company_phone ) {
			$html .= '<a href="tel:' . esc_attr( $company_phone ) . '" style="color: ' . esc_attr( $accent_color ) . '; text-decoration: none;">' . esc_html( $company_phone ) . '</a><br>';
		}

		$company_address = get_option( 'ag_company_address', '' );
		if ( $company_address ) {
			$html .= '<span style="color: #9ca3af;">' . esc_html( $company_address ) . '</span>';
		}

		$html .= '
									</td>
								</tr>
								<tr>
									<td style="padding-top: 20px; font-size: 12px; color: #9ca3af;">
										' . sprintf(
											/* translators: %s: Company name */
											esc_html__( 'Bu e-posta %s tarafından gönderilmiştir.', 'appointment-general' ),
											esc_html( $company_name )
										) . '
									</td>
								</tr>
							</table>
						</td>
					</tr>

				</table>
			</td>
		</tr>
	</table>
</body>
</html>';

		return $html;
	}

	/**
	 * Renk değerini ayarla (daha koyu/açık)
	 *
	 * @param string $hex    Hex renk.
	 * @param int    $amount Değişim miktarı (-255 to 255).
	 *
	 * @return string
	 */
	private function adjust_color( $hex, $amount ) {
		$hex = ltrim( $hex, '#' );

		$r = max( 0, min( 255, hexdec( substr( $hex, 0, 2 ) ) + $amount ) );
		$g = max( 0, min( 255, hexdec( substr( $hex, 2, 2 ) ) + $amount ) );
		$b = max( 0, min( 255, hexdec( substr( $hex, 4, 2 ) ) + $amount ) );

		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	/**
	 * Durum etiketini getir
	 *
	 * @param string $status Durum.
	 *
	 * @return string
	 */
	private function get_status_label( $status ) {
		$labels = array(
			'pending'   => __( 'Beklemede', 'appointment-general' ),
			'confirmed' => __( 'Onaylandı', 'appointment-general' ),
			'cancelled' => __( 'İptal Edildi', 'appointment-general' ),
			'completed' => __( 'Tamamlandı', 'appointment-general' ),
			'no_show'   => __( 'Gelmedi', 'appointment-general' ),
		);

		return $labels[ $status ] ?? $status;
	}

	/**
	 * Email gönder
	 *
	 * @param string $to          Alıcı.
	 * @param string $subject     Konu.
	 * @param string $message     Mesaj.
	 * @param array  $attachments Eklentiler (dosya yolları).
	 *
	 * @return bool
	 */
	private function send_email( $to, $subject, $message, $attachments = array() ) {
		$from_name  = get_option( 'ag_email_from_name', get_bloginfo( 'name' ) );
		$from_email = get_option( 'ag_email_from_address', get_option( 'admin_email' ) );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			"From: {$from_name} <{$from_email}>",
		);

		return wp_mail( $to, $subject, $message, $headers, $attachments );
	}

	/**
	 * ICS özelliği aktif mi kontrol et
	 *
	 * @return bool
	 */
	private function is_ics_enabled() {
		return 'yes' === get_option( 'ag_enable_ics', 'yes' );
	}

	/**
	 * Randevu yönetim URL'si oluştur
	 *
	 * @param string $token Randevu token.
	 *
	 * @return string URL.
	 */
	private function get_manage_booking_url( $token ) {
		// Manage booking sayfası ayarlı mı kontrol et.
		$page_id = get_option( 'ag_manage_booking_page', 0 );

		if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
			$base_url = get_permalink( $page_id );
		} else {
			// Varsayılan olarak home URL kullan.
			$base_url = home_url( '/' );
		}

		return add_query_arg( array(
			'ag_action' => 'manage_booking',
			'token'     => $token,
		), $base_url );
	}

	/**
	 * Test e-postası gönder
	 *
	 * @param string $email         Alıcı e-posta.
	 * @param string $template_type Şablon tipi.
	 *
	 * @return array
	 */
	public function send_test_email( $email, $template_type ) {
		$template = $this->get_template( $template_type );

		if ( ! $template ) {
			return array(
				'success' => false,
				'message' => __( 'Şablon bulunamadı.', 'appointment-general' ),
			);
		}

		// Test verisi oluştur
		$test_booking = (object) array(
			'id'               => 999,
			'customer_name'    => 'Test Müşteri',
			'customer_email'   => $email,
			'customer_phone'   => '0555 555 55 55',
			'service_name'     => 'Test Hizmeti',
			'services_list'    => 'Test Hizmeti 1, Test Hizmeti 2',
			'staff_name'       => 'Test Personel',
			'booking_date'     => gmdate( 'Y-m-d', strtotime( '+1 day' ) ),
			'start_time'       => '14:00:00',
			'price'            => '150.00',
			'total_duration'   => 60,
			'status'           => 'pending',
			'token'            => 'test-token-123',
		);

		$subject = $this->parse_placeholders( $template->subject, $test_booking );
		$content = $this->parse_placeholders( $template->content, $test_booking );
		$content = str_replace( '{calendar_links}', '', $content );
		$message = $this->wrap_email_content( $content, $template_type );

		$result = $this->send_email( $email, '[TEST] ' . $subject, $message );

		if ( $result ) {
			return array(
				'success' => true,
				'message' => __( 'Test e-postası gönderildi.', 'appointment-general' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => __( 'E-posta gönderilemedi.', 'appointment-general' ),
			);
		}
	}
}
