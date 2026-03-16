<?php
/**
 * ICS Generator class - Calendar file generation
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ICS Generator class
 */
class UNBSB_ICS_Generator {

	/**
	 * Generate ICS content from booking data
	 *
	 * @param object $booking Booking object (from get_with_details).
	 *
	 * @return string ICS file content.
	 */
	public function generate( $booking ) {
		$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'unbsb_company_address', '' );
		$company_phone   = get_option( 'unbsb_company_phone', '' );

		// Calculate start and end times.
		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		// WordPress timezone.
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		// Convert to UTC.
		$start_utc = $this->convert_to_utc( $start_datetime, $timezone );
		$end_utc   = $this->convert_to_utc( $end_datetime, $timezone );

		// Unique identifier.
		$uid = 'booking-' . $booking->id . '-' . $booking->token . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

		// Event title.
		$summary = sprintf(
			/* translators: 1: Service name, 2: Company name */
			__( '%1$s - %2$s', 'unbelievable-salon-booking' ),
			$booking->service_name,
			$company_name
		);

		// Description.
		$description_lines = array(
			/* translators: %s: Staff name */
			sprintf( __( 'Staff: %s', 'unbelievable-salon-booking' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			$currency_symbol     = get_option( 'unbsb_currency_symbol', '₺' );
			$description_lines[] = sprintf(
				/* translators: %s: Price with currency */
				__( 'Price: %s', 'unbelievable-salon-booking' ),
				$booking->price . ' ' . $currency_symbol
			);
		}

		if ( ! empty( $company_phone ) ) {
			/* translators: %s: Phone number */
			$description_lines[] = sprintf( __( 'Phone: %s', 'unbelievable-salon-booking' ), $company_phone );
		}

		$description = implode( '\\n', $description_lines );

		// Location.
		$location = ! empty( $company_address ) ? $this->escape_ics_text( $company_address ) : '';

		// Generate ICS content.
		$ics_content  = "BEGIN:VCALENDAR\r\n";
		$ics_content .= "VERSION:2.0\r\n";
		$ics_content .= "PRODID:-//Unbelievable Salon Booking//WordPress Plugin//TR\r\n";
		$ics_content .= "CALSCALE:GREGORIAN\r\n";
		$ics_content .= "METHOD:REQUEST\r\n";
		$ics_content .= "BEGIN:VTIMEZONE\r\n";
		$ics_content .= "TZID:{$timezone}\r\n";
		$ics_content .= "END:VTIMEZONE\r\n";
		$ics_content .= "BEGIN:VEVENT\r\n";
		$ics_content .= "UID:{$uid}\r\n";
		$ics_content .= 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ) . "\r\n";
		$ics_content .= "DTSTART:{$start_utc}\r\n";
		$ics_content .= "DTEND:{$end_utc}\r\n";
		$ics_content .= 'SUMMARY:' . $this->escape_ics_text( $summary ) . "\r\n";
		$ics_content .= 'DESCRIPTION:' . $this->escape_ics_text( $description ) . "\r\n";

		if ( ! empty( $location ) ) {
			$ics_content .= "LOCATION:{$location}\r\n";
		}

		// Add reminder (1 hour before).
		$ics_content .= "BEGIN:VALARM\r\n";
		$ics_content .= "TRIGGER:-PT1H\r\n";
		$ics_content .= "ACTION:DISPLAY\r\n";
		$ics_content .= 'DESCRIPTION:' . __( 'Booking Reminder', 'unbelievable-salon-booking' ) . "\r\n";
		$ics_content .= "END:VALARM\r\n";

		// 1 day before reminder.
		$ics_content .= "BEGIN:VALARM\r\n";
		$ics_content .= "TRIGGER:-P1D\r\n";
		$ics_content .= "ACTION:DISPLAY\r\n";
		$ics_content .= 'DESCRIPTION:' . __( 'You have a booking tomorrow', 'unbelievable-salon-booking' ) . "\r\n";
		$ics_content .= "END:VALARM\r\n";

		$ics_content .= "STATUS:CONFIRMED\r\n";
		$ics_content .= "SEQUENCE:0\r\n";
		$ics_content .= "END:VEVENT\r\n";
		$ics_content .= "END:VCALENDAR\r\n";

		return $ics_content;
	}

	/**
	 * Save ICS file temporarily
	 *
	 * @param object $booking Booking object.
	 *
	 * @return string|false File path or false.
	 */
	public function save_temp_file( $booking ) {
		$ics_content = $this->generate( $booking );

		$upload_dir = wp_upload_dir();
		$temp_dir   = $upload_dir['basedir'] . '/unbsb-temp/';

		// Create directory if it doesn't exist.
		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );

			// Protect with .htaccess.
			$htaccess_content = "Order deny,allow\nDeny from all";
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Using file_put_contents for temp ICS file.
			file_put_contents( $temp_dir . '.htaccess', $htaccess_content );
		}

		$filename = 'booking-' . $booking->id . '-' . wp_generate_password( 8, false ) . '.ics';
		$filepath = $temp_dir . $filename;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Using file_put_contents for temp ICS file.
		$result = file_put_contents( $filepath, $ics_content );

		if ( false === $result ) {
			return false;
		}

		return $filepath;
	}

	/**
	 * Delete temporary file
	 *
	 * @param string $filepath File path.
	 */
	public function delete_temp_file( $filepath ) {
		if ( file_exists( $filepath ) ) {
			wp_delete_file( $filepath );
		}
	}

	/**
	 * Generate Google Calendar add link
	 *
	 * @param object $booking Booking object.
	 *
	 * @return string Google Calendar link.
	 */
	public function get_google_calendar_url( $booking ) {
		$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'unbsb_company_address', '' );
		$company_phone   = get_option( 'unbsb_company_phone', '' );
		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );

		// Date/time format: YYYYMMDDTHHmmss.
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		$start_utc = $this->convert_to_utc( $start_datetime, $timezone );
		$end_utc   = $this->convert_to_utc( $end_datetime, $timezone );

		// Title.
		$title = sprintf( '%s - %s', $booking->service_name, $company_name );

		// Description.
		$details_parts = array(
			/* translators: %s: Staff name */
			sprintf( __( 'Staff: %s', 'unbelievable-salon-booking' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			/* translators: %s: Price with currency */
			$details_parts[] = sprintf( __( 'Price: %s', 'unbelievable-salon-booking' ), $booking->price . ' ' . $currency_symbol );
		}

		if ( ! empty( $company_phone ) ) {
			/* translators: %s: Phone number */
			$details_parts[] = sprintf( __( 'Phone: %s', 'unbelievable-salon-booking' ), $company_phone );
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
	 * Generate Outlook.com/Office 365 calendar link
	 *
	 * @param object $booking Booking object.
	 *
	 * @return string Outlook Calendar link.
	 */
	public function get_outlook_calendar_url( $booking ) {
		$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'unbsb_company_address', '' );
		$company_phone   = get_option( 'unbsb_company_phone', '' );
		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );

		// ISO 8601 format.
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		$start_datetime = $booking->booking_date . ' ' . $booking->start_time;
		$end_datetime   = $booking->booking_date . ' ' . $booking->end_time;

		$tz = new DateTimeZone( $timezone );

		$start_dt = new DateTime( $start_datetime, $tz );
		$end_dt   = new DateTime( $end_datetime, $tz );

		// Outlook web uses ISO format.
		$start_iso = $start_dt->format( 'Y-m-d\TH:i:s' );
		$end_iso   = $end_dt->format( 'Y-m-d\TH:i:s' );

		// Title.
		$subject = sprintf( '%s - %s', $booking->service_name, $company_name );

		// Description.
		$body_parts = array(
			/* translators: %s: Staff name */
			sprintf( __( 'Staff: %s', 'unbelievable-salon-booking' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			/* translators: %s: Price with currency */
			$body_parts[] = sprintf( __( 'Price: %s', 'unbelievable-salon-booking' ), $booking->price . ' ' . $currency_symbol );
		}

		if ( ! empty( $company_phone ) ) {
			/* translators: %s: Phone number */
			$body_parts[] = sprintf( __( 'Phone: %s', 'unbelievable-salon-booking' ), $company_phone );
		}

		$body = implode( "\n", $body_parts );

		$params = array(
			'path'     => '/calendar/action/compose',
			'rru'      => 'addevent',
			'startdt'  => $start_iso,
			'enddt'    => $end_iso,
			'subject'  => $subject,
			'body'     => $body,
			'location' => $company_address,
		);

		return 'https://outlook.live.com/calendar/0/deeplink/compose?' . http_build_query( $params );
	}

	/**
	 * Generate Yahoo Calendar add link
	 *
	 * @param object $booking Booking object.
	 *
	 * @return string Yahoo Calendar link.
	 */
	public function get_yahoo_calendar_url( $booking ) {
		$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$company_address = get_option( 'unbsb_company_address', '' );
		$company_phone   = get_option( 'unbsb_company_phone', '' );
		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );

		// Date format: YYYYMMDDTHHmmss.
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

		// Title.
		$title = sprintf( '%s - %s', $booking->service_name, $company_name );

		// Description.
		$desc_parts = array(
			/* translators: %s: Staff name */
			sprintf( __( 'Staff: %s', 'unbelievable-salon-booking' ), $booking->staff_name ),
		);

		if ( ! empty( $booking->price ) ) {
			/* translators: %s: Price with currency */
			$desc_parts[] = sprintf( __( 'Price: %s', 'unbelievable-salon-booking' ), $booking->price . ' ' . $currency_symbol );
		}

		if ( ! empty( $company_phone ) ) {
			/* translators: %s: Phone number */
			$desc_parts[] = sprintf( __( 'Phone: %s', 'unbelievable-salon-booking' ), $company_phone );
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
	 * Generate all calendar links as HTML
	 *
	 * @param object $booking Booking object.
	 *
	 * @return string HTML content.
	 */
	public function get_calendar_links_html( $booking ) {
		$google_url  = $this->get_google_calendar_url( $booking );
		$outlook_url = $this->get_outlook_calendar_url( $booking );
		$yahoo_url   = $this->get_yahoo_calendar_url( $booking );

		$html  = '<div style="margin: 20px 0; text-align: center;">';
		$html .= '<p style="margin-bottom: 10px; color: #666;">' . __( 'Add to your calendar:', 'unbelievable-salon-booking' ) . '</p>';
		$html .= '<div style="display: inline-block;">';

		// Google Calendar button.
		$html .= sprintf(
			'<a href="%s" target="_blank" style="display: inline-block; padding: 10px 16px; margin: 5px; background: #4285f4; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">%s</a>',
			esc_url( $google_url ),
			__( 'Google Calendar', 'unbelievable-salon-booking' )
		);

		// Outlook button.
		$html .= sprintf(
			'<a href="%s" target="_blank" style="display: inline-block; padding: 10px 16px; margin: 5px; background: #0078d4; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">%s</a>',
			esc_url( $outlook_url ),
			__( 'Outlook', 'unbelievable-salon-booking' )
		);

		// Yahoo button.
		$html .= sprintf(
			'<a href="%s" target="_blank" style="display: inline-block; padding: 10px 16px; margin: 5px; background: #720e9e; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">%s</a>',
			esc_url( $yahoo_url ),
			__( 'Yahoo Calendar', 'unbelievable-salon-booking' )
		);

		$html .= '</div>';
		$html .= '<p style="margin-top: 10px; font-size: 12px; color: #999;">' . __( 'Apple Calendar users can use the .ics file attached to the email.', 'unbelievable-salon-booking' ) . '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Convert local time to UTC
	 *
	 * @param string $datetime Date time (Y-m-d H:i:s).
	 * @param string $timezone Timezone.
	 *
	 * @return string Date time in UTC format (YmdTHisZ).
	 */
	private function convert_to_utc( $datetime, $timezone ) {
		try {
			$tz = new DateTimeZone( $timezone );
			$dt = new DateTime( $datetime, $tz );
			$dt->setTimezone( new DateTimeZone( 'UTC' ) );

			return $dt->format( 'Ymd\THis\Z' );
		} catch ( Exception $e ) {
			// Fallback: Return date as is.
			return gmdate( 'Ymd\THis\Z', strtotime( $datetime ) );
		}
	}

	/**
	 * Escape ICS text values
	 *
	 * @param string $text Text.
	 *
	 * @return string Escaped text.
	 */
	private function escape_ics_text( $text ) {
		// Escape per ICS specification.
		$text = str_replace( '\\', '\\\\', $text );
		$text = str_replace( "\n", '\\n', $text );
		$text = str_replace( "\r", '', $text );
		$text = str_replace( ',', '\\,', $text );
		$text = str_replace( ';', '\\;', $text );

		return $text;
	}

	/**
	 * Generate ICS for cancellation
	 *
	 * @param object $booking Booking object.
	 *
	 * @return string ICS file content.
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

		$company_name = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$summary      = sprintf( '%s - %s', $booking->service_name, $company_name );

		$ics_content  = "BEGIN:VCALENDAR\r\n";
		$ics_content .= "VERSION:2.0\r\n";
		$ics_content .= "PRODID:-//Unbelievable Salon Booking//WordPress Plugin//TR\r\n";
		$ics_content .= "CALSCALE:GREGORIAN\r\n";
		$ics_content .= "METHOD:CANCEL\r\n";
		$ics_content .= "BEGIN:VEVENT\r\n";
		$ics_content .= "UID:{$uid}\r\n";
		$ics_content .= 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ) . "\r\n";
		$ics_content .= "DTSTART:{$start_utc}\r\n";
		$ics_content .= "DTEND:{$end_utc}\r\n";
		$ics_content .= 'SUMMARY:' . $this->escape_ics_text( $summary ) . "\r\n";
		$ics_content .= "STATUS:CANCELLED\r\n";
		$ics_content .= "SEQUENCE:1\r\n";
		$ics_content .= "END:VEVENT\r\n";
		$ics_content .= "END:VCALENDAR\r\n";

		return $ics_content;
	}

	/**
	 * Save cancellation ICS file temporarily
	 *
	 * @param object $booking Booking object.
	 *
	 * @return string|false File path or false.
	 */
	public function save_cancellation_temp_file( $booking ) {
		$ics_content = $this->generate_cancellation( $booking );

		$upload_dir = wp_upload_dir();
		$temp_dir   = $upload_dir['basedir'] . '/unbsb-temp/';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );

			$htaccess_content = "Order deny,allow\nDeny from all";
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Using file_put_contents for temp ICS file.
			file_put_contents( $temp_dir . '.htaccess', $htaccess_content );
		}

		$filename = 'cancel-' . $booking->id . '-' . wp_generate_password( 8, false ) . '.ics';
		$filepath = $temp_dir . $filename;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Using file_put_contents for temp ICS file.
		$result = file_put_contents( $filepath, $ics_content );

		if ( false === $result ) {
			return false;
		}

		return $filepath;
	}
}
