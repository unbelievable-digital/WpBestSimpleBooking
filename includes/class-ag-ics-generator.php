<?php
/**
 * ICS Generator sınıfı - Takvim dosyası oluşturma
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ICS Generator sınıfı
 */
class AG_ICS_Generator {

	/**
	 * Randevu verilerinden ICS içeriği oluştur
	 *
	 * @param object $booking Randevu objesi (get_with_details'ten).
	 *
	 * @return string ICS dosya içeriği.
	 */
	public function generate( $booking ) {
		$company_name    = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'ag_company_address', '' );
		$company_phone   = get_option( 'ag_company_phone', '' );

		// Başlangıç ve bitiş zamanlarını hesapla.
		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		// WordPress timezone.
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		// UTC'ye çevir.
		$start_utc = $this->convert_to_utc( $start_datetime, $timezone );
		$end_utc   = $this->convert_to_utc( $end_datetime, $timezone );

		// Unique identifier.
		$uid = 'booking-' . $booking->id . '-' . $booking->token . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

		// Etkinlik başlığı.
		$summary = sprintf(
			/* translators: 1: Service name, 2: Company name */
			__( '%1$s - %2$s', 'appointment-general' ),
			$booking->service_name,
			$company_name
		);

		// Açıklama.
		$description_lines = array(
			/* translators: %s: Staff name */
			sprintf( __( 'Personel: %s', 'appointment-general' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			$currency_symbol    = get_option( 'ag_currency_symbol', '₺' );
			$description_lines[] = sprintf(
				/* translators: %s: Price with currency */
				__( 'Ücret: %s', 'appointment-general' ),
				$booking->price . ' ' . $currency_symbol
			);
		}

		if ( ! empty( $company_phone ) ) {
			/* translators: %s: Phone number */
			$description_lines[] = sprintf( __( 'Telefon: %s', 'appointment-general' ), $company_phone );
		}

		$description = implode( '\\n', $description_lines );

		// Konum.
		$location = ! empty( $company_address ) ? $this->escape_ics_text( $company_address ) : '';

		// ICS içeriği oluştur.
		$ics_content = "BEGIN:VCALENDAR\r\n";
		$ics_content .= "VERSION:2.0\r\n";
		$ics_content .= "PRODID:-//Appointment General//WordPress Plugin//TR\r\n";
		$ics_content .= "CALSCALE:GREGORIAN\r\n";
		$ics_content .= "METHOD:REQUEST\r\n";
		$ics_content .= "BEGIN:VTIMEZONE\r\n";
		$ics_content .= "TZID:{$timezone}\r\n";
		$ics_content .= "END:VTIMEZONE\r\n";
		$ics_content .= "BEGIN:VEVENT\r\n";
		$ics_content .= "UID:{$uid}\r\n";
		$ics_content .= "DTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "\r\n";
		$ics_content .= "DTSTART:{$start_utc}\r\n";
		$ics_content .= "DTEND:{$end_utc}\r\n";
		$ics_content .= "SUMMARY:" . $this->escape_ics_text( $summary ) . "\r\n";
		$ics_content .= "DESCRIPTION:" . $this->escape_ics_text( $description ) . "\r\n";

		if ( ! empty( $location ) ) {
			$ics_content .= "LOCATION:{$location}\r\n";
		}

		// Hatırlatıcı ekle (1 saat önce).
		$ics_content .= "BEGIN:VALARM\r\n";
		$ics_content .= "TRIGGER:-PT1H\r\n";
		$ics_content .= "ACTION:DISPLAY\r\n";
		$ics_content .= "DESCRIPTION:" . __( 'Randevu Hatırlatması', 'appointment-general' ) . "\r\n";
		$ics_content .= "END:VALARM\r\n";

		// 1 gün önce hatırlatıcı.
		$ics_content .= "BEGIN:VALARM\r\n";
		$ics_content .= "TRIGGER:-P1D\r\n";
		$ics_content .= "ACTION:DISPLAY\r\n";
		$ics_content .= "DESCRIPTION:" . __( 'Yarın randevunuz var', 'appointment-general' ) . "\r\n";
		$ics_content .= "END:VALARM\r\n";

		$ics_content .= "STATUS:CONFIRMED\r\n";
		$ics_content .= "SEQUENCE:0\r\n";
		$ics_content .= "END:VEVENT\r\n";
		$ics_content .= "END:VCALENDAR\r\n";

		return $ics_content;
	}

	/**
	 * ICS dosyasını geçici olarak kaydet
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return string|false Dosya yolu veya false.
	 */
	public function save_temp_file( $booking ) {
		$ics_content = $this->generate( $booking );

		$upload_dir = wp_upload_dir();
		$temp_dir   = $upload_dir['basedir'] . '/ag-temp/';

		// Klasör yoksa oluştur.
		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );

			// .htaccess ile koru.
			$htaccess_content = "Order deny,allow\nDeny from all";
			file_put_contents( $temp_dir . '.htaccess', $htaccess_content );
		}

		$filename = 'randevu-' . $booking->id . '-' . wp_generate_password( 8, false ) . '.ics';
		$filepath = $temp_dir . $filename;

		$result = file_put_contents( $filepath, $ics_content );

		if ( false === $result ) {
			return false;
		}

		return $filepath;
	}

	/**
	 * Geçici dosyayı sil
	 *
	 * @param string $filepath Dosya yolu.
	 */
	public function delete_temp_file( $filepath ) {
		if ( file_exists( $filepath ) ) {
			unlink( $filepath );
		}
	}

	/**
	 * Google Calendar ekleme linki oluştur
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return string Google Calendar linki.
	 */
	public function get_google_calendar_url( $booking ) {
		$company_name    = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'ag_company_address', '' );
		$company_phone   = get_option( 'ag_company_phone', '' );
		$currency_symbol = get_option( 'ag_currency_symbol', '₺' );

		// Tarih/saat formatı: YYYYMMDDTHHmmss.
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		$start_utc = $this->convert_to_utc( $start_datetime, $timezone );
		$end_utc   = $this->convert_to_utc( $end_datetime, $timezone );

		// Başlık.
		$title = sprintf( '%s - %s', $booking->service_name, $company_name );

		// Açıklama.
		$details_parts = array(
			sprintf( __( 'Personel: %s', 'appointment-general' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			$details_parts[] = sprintf( __( 'Ücret: %s', 'appointment-general' ), $booking->price . ' ' . $currency_symbol );
		}

		if ( ! empty( $company_phone ) ) {
			$details_parts[] = sprintf( __( 'Telefon: %s', 'appointment-general' ), $company_phone );
		}

		$details = implode( "\n", $details_parts );

		$params = array(
			'action'   => 'TEMPLATE',
			'text'     => $title,
			'dates'    => $start_utc . '/' . $end_utc,
			'details'  => $details,
			'location' => $company_address,
			'ctz'      => $timezone,
		);

		return 'https://calendar.google.com/calendar/render?' . http_build_query( $params );
	}

	/**
	 * Outlook.com/Office 365 takvim linki oluştur
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return string Outlook Calendar linki.
	 */
	public function get_outlook_calendar_url( $booking ) {
		$company_name    = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'ag_company_address', '' );
		$company_phone   = get_option( 'ag_company_phone', '' );
		$currency_symbol = get_option( 'ag_currency_symbol', '₺' );

		// ISO 8601 formatı.
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		$tz = new DateTimeZone( $timezone );

		$start_dt = new DateTime( $start_datetime, $tz );
		$end_dt   = new DateTime( $end_datetime, $tz );

		// Outlook web ISO format kullanıyor.
		$start_iso = $start_dt->format( 'Y-m-d\TH:i:s' );
		$end_iso   = $end_dt->format( 'Y-m-d\TH:i:s' );

		// Başlık.
		$subject = sprintf( '%s - %s', $booking->service_name, $company_name );

		// Açıklama.
		$body_parts = array(
			sprintf( __( 'Personel: %s', 'appointment-general' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			$body_parts[] = sprintf( __( 'Ücret: %s', 'appointment-general' ), $booking->price . ' ' . $currency_symbol );
		}

		if ( ! empty( $company_phone ) ) {
			$body_parts[] = sprintf( __( 'Telefon: %s', 'appointment-general' ), $company_phone );
		}

		$body = implode( "\n", $body_parts );

		$params = array(
			'path'      => '/calendar/action/compose',
			'rru'       => 'addevent',
			'startdt'   => $start_iso,
			'enddt'     => $end_iso,
			'subject'   => $subject,
			'body'      => $body,
			'location'  => $company_address,
		);

		return 'https://outlook.live.com/calendar/0/deeplink/compose?' . http_build_query( $params );
	}

	/**
	 * Yahoo Calendar ekleme linki oluştur
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return string Yahoo Calendar linki.
	 */
	public function get_yahoo_calendar_url( $booking ) {
		$company_name    = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'ag_company_address', '' );
		$company_phone   = get_option( 'ag_company_phone', '' );
		$currency_symbol = get_option( 'ag_currency_symbol', '₺' );

		// Tarih formatı: YYYYMMDDTHHmmss.
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		$tz       = new DateTimeZone( $timezone );
		$start_dt = new DateTime( $start_datetime, $tz );
		$end_dt   = new DateTime( $end_datetime, $tz );

		$start_fmt = $start_dt->format( 'Ymd\THis' );
		$end_fmt   = $end_dt->format( 'Ymd\THis' );

		// Başlık.
		$title = sprintf( '%s - %s', $booking->service_name, $company_name );

		// Açıklama.
		$desc_parts = array(
			sprintf( __( 'Personel: %s', 'appointment-general' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			$desc_parts[] = sprintf( __( 'Ücret: %s', 'appointment-general' ), $booking->price . ' ' . $currency_symbol );
		}

		if ( ! empty( $company_phone ) ) {
			$desc_parts[] = sprintf( __( 'Telefon: %s', 'appointment-general' ), $company_phone );
		}

		$desc = implode( "\n", $desc_parts );

		$params = array(
			'v'      => '60',
			'title'  => $title,
			'st'     => $start_fmt,
			'et'     => $end_fmt,
			'desc'   => $desc,
			'in_loc' => $company_address,
		);

		return 'https://calendar.yahoo.com/?' . http_build_query( $params );
	}

	/**
	 * Tüm takvim linklerini HTML olarak oluştur
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return string HTML içerik.
	 */
	public function get_calendar_links_html( $booking ) {
		$google_url  = $this->get_google_calendar_url( $booking );
		$outlook_url = $this->get_outlook_calendar_url( $booking );
		$yahoo_url   = $this->get_yahoo_calendar_url( $booking );

		$html = '<div style="margin: 20px 0; text-align: center;">';
		$html .= '<p style="margin-bottom: 10px; color: #666;">' . __( 'Takviminize ekleyin:', 'appointment-general' ) . '</p>';
		$html .= '<div style="display: inline-block;">';

		// Google Calendar butonu.
		$html .= sprintf(
			'<a href="%s" target="_blank" style="display: inline-block; padding: 10px 16px; margin: 5px; background: #4285f4; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">%s</a>',
			esc_url( $google_url ),
			__( 'Google Calendar', 'appointment-general' )
		);

		// Outlook butonu.
		$html .= sprintf(
			'<a href="%s" target="_blank" style="display: inline-block; padding: 10px 16px; margin: 5px; background: #0078d4; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">%s</a>',
			esc_url( $outlook_url ),
			__( 'Outlook', 'appointment-general' )
		);

		// Yahoo butonu.
		$html .= sprintf(
			'<a href="%s" target="_blank" style="display: inline-block; padding: 10px 16px; margin: 5px; background: #720e9e; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">%s</a>',
			esc_url( $yahoo_url ),
			__( 'Yahoo Calendar', 'appointment-general' )
		);

		$html .= '</div>';
		$html .= '<p style="margin-top: 10px; font-size: 12px; color: #999;">' . __( 'Apple Calendar kullanıcıları e-postaya ekli .ics dosyasını kullanabilir.', 'appointment-general' ) . '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Yerel zamanı UTC'ye çevir
	 *
	 * @param string $datetime Tarih saat (Y-m-d H:i:s).
	 * @param string $timezone Zaman dilimi.
	 *
	 * @return string UTC formatında tarih saat (YmdTHisZ).
	 */
	private function convert_to_utc( $datetime, $timezone ) {
		try {
			$tz = new DateTimeZone( $timezone );
			$dt = new DateTime( $datetime, $tz );
			$dt->setTimezone( new DateTimeZone( 'UTC' ) );

			return $dt->format( 'Ymd\THis\Z' );
		} catch ( Exception $e ) {
			// Fallback: Tarihi olduğu gibi döndür.
			return gmdate( 'Ymd\THis\Z', strtotime( $datetime ) );
		}
	}

	/**
	 * ICS metin değerlerini escape et
	 *
	 * @param string $text Metin.
	 *
	 * @return string Escape edilmiş metin.
	 */
	private function escape_ics_text( $text ) {
		// ICS spesifikasyonuna göre escape.
		$text = str_replace( '\\', '\\\\', $text );
		$text = str_replace( "\n", '\\n', $text );
		$text = str_replace( "\r", '', $text );
		$text = str_replace( ',', '\\,', $text );
		$text = str_replace( ';', '\\;', $text );

		return $text;
	}

	/**
	 * İptal durumu için ICS oluştur
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return string ICS dosya içeriği.
	 */
	public function generate_cancellation( $booking ) {
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		$start_utc = $this->convert_to_utc( $start_datetime, $timezone );
		$end_utc   = $this->convert_to_utc( $end_datetime, $timezone );

		$uid = 'booking-' . $booking->id . '-' . $booking->token . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

		$company_name = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
		$summary      = sprintf( '%s - %s', $booking->service_name, $company_name );

		$ics_content = "BEGIN:VCALENDAR\r\n";
		$ics_content .= "VERSION:2.0\r\n";
		$ics_content .= "PRODID:-//Appointment General//WordPress Plugin//TR\r\n";
		$ics_content .= "CALSCALE:GREGORIAN\r\n";
		$ics_content .= "METHOD:CANCEL\r\n";
		$ics_content .= "BEGIN:VEVENT\r\n";
		$ics_content .= "UID:{$uid}\r\n";
		$ics_content .= "DTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "\r\n";
		$ics_content .= "DTSTART:{$start_utc}\r\n";
		$ics_content .= "DTEND:{$end_utc}\r\n";
		$ics_content .= "SUMMARY:" . $this->escape_ics_text( $summary ) . "\r\n";
		$ics_content .= "STATUS:CANCELLED\r\n";
		$ics_content .= "SEQUENCE:1\r\n";
		$ics_content .= "END:VEVENT\r\n";
		$ics_content .= "END:VCALENDAR\r\n";

		return $ics_content;
	}

	/**
	 * İptal ICS dosyasını geçici olarak kaydet
	 *
	 * @param object $booking Randevu objesi.
	 *
	 * @return string|false Dosya yolu veya false.
	 */
	public function save_cancellation_temp_file( $booking ) {
		$ics_content = $this->generate_cancellation( $booking );

		$upload_dir = wp_upload_dir();
		$temp_dir   = $upload_dir['basedir'] . '/ag-temp/';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );

			$htaccess_content = "Order deny,allow\nDeny from all";
			file_put_contents( $temp_dir . '.htaccess', $htaccess_content );
		}

		$filename = 'iptal-' . $booking->id . '-' . wp_generate_password( 8, false ) . '.ics';
		$filepath = $temp_dir . $filename;

		$result = file_put_contents( $filepath, $ics_content );

		if ( false === $result ) {
			return false;
		}

		return $filepath;
	}
}
