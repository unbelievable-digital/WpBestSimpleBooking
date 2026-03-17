<?php
/**
 * Notification class - Email notifications
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification class
 */
class UNBSB_Notification {

	/**
	 * ICS Generator instance
	 *
	 * @var UNBSB_ICS_Generator
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
		$this->templates_table = $wpdb->prefix . 'unbsb_email_templates';

		// When a booking is created.
		add_action( 'unbsb_after_booking_created', array( $this, 'send_booking_created_emails' ), 10, 2 );

		// When status changes.
		add_action( 'unbsb_booking_status_changed', array( $this, 'send_status_changed_email' ), 10, 3 );

		// Reminder cron.
		add_action( 'unbsb_send_reminder_emails', array( $this, 'process_reminder_emails' ) );

		// Cron schedule.
		if ( ! wp_next_scheduled( 'unbsb_send_reminder_emails' ) ) {
			wp_schedule_event( time(), 'hourly', 'unbsb_send_reminder_emails' );
		}

		// Load ICS generator.
		$this->load_ics_generator();
	}

	/**
	 * Load ICS Generator
	 */
	private function load_ics_generator() {
		$this->ics_generator = new UNBSB_ICS_Generator();
	}

	/**
	 * Send email when a booking is created
	 *
	 * @param int   $booking_id   Booking ID.
	 * @param array $booking_data Booking data.
	 */
	public function send_booking_created_emails( $booking_id, $booking_data ) {
		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get_with_details( $booking_id );

		if ( ! $booking ) {
			return;
		}

		// Email to customer.
		do_action( 'unbsb_before_send_notification', $booking_id, 'booking_received' );
		$this->send_customer_email( $booking, 'booking_received' );

		// Email to admin.
		do_action( 'unbsb_before_send_notification', $booking_id, 'admin_new_booking' );
		$this->send_admin_email( $booking, 'admin_new_booking' );

		// Email to assigned staff.
		do_action( 'unbsb_before_send_notification', $booking_id, 'staff_new_booking' );
		$this->send_staff_email( $booking, 'staff_new_booking' );
	}

	/**
	 * Send email when status changes
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function send_status_changed_email( $booking_id, $new_status, $old_status ) {
		if ( $old_status === $new_status ) {
			return;
		}

		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get_with_details( $booking_id );

		if ( ! $booking ) {
			return;
		}

		switch ( $new_status ) {
			case 'confirmed':
				do_action( 'unbsb_before_send_notification', $booking_id, 'booking_confirmed' );
				$this->send_customer_email( $booking, 'booking_confirmed', true );
				break;

			case 'cancelled':
				do_action( 'unbsb_before_send_notification', $booking_id, 'booking_cancelled' );
				$this->send_customer_email( $booking, 'booking_cancelled', false, true );
				break;
		}
	}

	/**
	 * Process reminder emails
	 */
	public function process_reminder_emails() {
		if ( 'yes' !== get_option( 'unbsb_email_reminder_enabled', 'yes' ) ) {
			return;
		}

		$hours = intval( get_option( 'unbsb_email_reminder_hours', 24 ) );

		global $wpdb;
		$bookings_table = $wpdb->prefix . 'unbsb_bookings';

		// Find bookings to remind.
		$reminder_date = gmdate( 'Y-m-d', strtotime( "+{$hours} hours" ) );
		$reminder_time = gmdate( 'H:i:s', strtotime( "+{$hours} hours" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$bookings = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT b.* FROM ' . $bookings_table . " b
				WHERE b.booking_date = %s
				AND b.start_time <= %s
				AND b.status IN ('pending', 'confirmed')
				AND b.customer_email IS NOT NULL
				AND b.customer_email != ''",
				$reminder_date,
				$reminder_time
			)
		);

		// Filter out bookings that already had reminders sent.
		$bookings = array_filter(
			$bookings,
			function ( $b ) {
				return false === get_option( '_unbsb_reminder_sent_' . $b->id );
			}
		);

		$booking_model = new UNBSB_Booking();

		foreach ( $bookings as $booking_row ) {
			$booking = $booking_model->get_with_details( $booking_row->id );
			if ( $booking ) {
				$this->send_customer_email( $booking, 'booking_reminder', true );
				// Mark as reminder sent.
				update_option( '_unbsb_reminder_sent_' . $booking->id, time() );
			}
		}
	}

	/**
	 * Send email to customer
	 *
	 * @param object $booking       Booking.
	 * @param string $template_type Template type.
	 * @param bool   $include_ics   Whether to include ICS file.
	 * @param bool   $cancel_ics    Whether it is a cancellation ICS file.
	 */
	public function send_customer_email( $booking, $template_type, $include_ics = false, $cancel_ics = false ) {
		$template = $this->get_template( $template_type );

		if ( ! $template || ! $template->is_active ) {
			return;
		}

		$to = $booking->customer_email;

		// Skip if no customer email.
		if ( empty( $to ) || ! is_email( $to ) ) {
			return;
		}
		$subject = $this->parse_placeholders( $template->subject, $booking );
		$content = $this->parse_placeholders( $template->content, $booking );

		// ICS attachment.
		$attachments  = array();
		$ics_filepath = null;

		if ( $this->is_ics_enabled() && $include_ics ) {
			// Calendar links.
			$calendar_links = $this->ics_generator->get_calendar_links_html( $booking );
			$content        = str_replace( '{calendar_links}', $calendar_links, $content );

			// ICS file.
			if ( $cancel_ics ) {
				$ics_filepath = $this->ics_generator->save_cancellation_temp_file( $booking );
			} else {
				$ics_filepath = $this->ics_generator->save_temp_file( $booking );
			}

			if ( $ics_filepath ) {
				$attachments[] = $ics_filepath;
			}
		}

		// Clear calendar links (if ICS is disabled).
		$content = str_replace( '{calendar_links}', '', $content );

		// Wrap with HTML wrapper.
		$message = $this->wrap_email_content( $content, $template_type );

		$this->send_email( $to, $subject, $message, $attachments );

		// Delete temporary ICS file.
		if ( $ics_filepath ) {
			$this->ics_generator->delete_temp_file( $ics_filepath );
		}
	}

	/**
	 * Send email to assigned staff member
	 *
	 * @param object $booking       Booking.
	 * @param string $template_type Template type.
	 */
	public function send_staff_email( $booking, $template_type ) {
		if ( empty( $booking->staff_id ) ) {
			return;
		}

		$template = $this->get_template( $template_type );

		if ( ! $template || ! $template->is_active ) {
			return;
		}

		// Get staff email.
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get( $booking->staff_id );

		if ( ! $staff || empty( $staff->email ) ) {
			return;
		}

		$to      = $staff->email;
		$subject = $this->parse_placeholders( $template->subject, $booking );
		$content = $this->parse_placeholders( $template->content, $booking );

		// Clear calendar links.
		$content = str_replace( '{calendar_links}', '', $content );

		// Wrap with HTML wrapper.
		$message = $this->wrap_email_content( $content, $template_type );

		$this->send_email( $to, $subject, $message );
	}

	/**
	 * Send email to admin
	 *
	 * @param object $booking       Booking.
	 * @param string $template_type Template type.
	 */
	public function send_admin_email( $booking, $template_type ) {
		$template = $this->get_template( $template_type );

		if ( ! $template || ! $template->is_active ) {
			return;
		}

		$to      = get_option( 'unbsb_admin_email', get_option( 'admin_email' ) );
		$subject = $this->parse_placeholders( $template->subject, $booking );
		$content = $this->parse_placeholders( $template->content, $booking );

		// Clear calendar links.
		$content = str_replace( '{calendar_links}', '', $content );

		// Wrap with HTML wrapper.
		$message = $this->wrap_email_content( $content, $template_type );

		$this->send_email( $to, $subject, $message );
	}

	/**
	 * Get template from database
	 *
	 * @param string $type Template type.
	 *
	 * @return object|null
	 */
	public function get_template( $type ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $this->templates_table . ' WHERE type = %s',
				$type
			)
		);
	}

	/**
	 * Get all templates
	 *
	 * @return array
	 */
	public function get_all_templates() {
		global $wpdb;

		// Create table and add default templates (if not exists).
		$this->ensure_templates_exist();

		$safe_table = esc_sql( $this->templates_table );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM `{$safe_table}` ORDER BY id ASC" );
	}

	/**
	 * Ensure templates exist
	 */
	private function ensure_templates_exist() {
		global $wpdb;

		$safe_table = esc_sql( $this->templates_table );

		// Check if table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $this->templates_table )
		);

		if ( ! $table_exists ) {
			// Create table.
			$this->create_email_templates_table();
		}

		// Check if templates exist.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$safe_table}`" );

		if ( 0 === intval( $count ) ) {
			// Create default templates.
			$this->create_default_email_templates();
		}
	}

	/**
	 * Create email templates table
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
	 * Create default email templates
	 */
	private function create_default_email_templates() {
		global $wpdb;

		$templates = array(
			array(
				'name'    => __( 'Booking Received', 'unbelievable-salon-booking' ),
				'type'    => 'booking_received',
				'subject' => __( 'Your Booking Request Has Been Received', 'unbelievable-salon-booking' ) . ' - {company_name}',
				'content' => $this->get_default_booking_received_template(),
			),
			array(
				'name'    => __( 'Booking Confirmed', 'unbelievable-salon-booking' ),
				'type'    => 'booking_confirmed',
				'subject' => __( 'Your Booking Has Been Confirmed', 'unbelievable-salon-booking' ) . ' - {company_name}',
				'content' => $this->get_default_booking_confirmed_template(),
			),
			array(
				'name'    => __( 'Booking Cancelled', 'unbelievable-salon-booking' ),
				'type'    => 'booking_cancelled',
				'subject' => __( 'Your Booking Has Been Cancelled', 'unbelievable-salon-booking' ) . ' - {company_name}',
				'content' => $this->get_default_booking_cancelled_template(),
			),
			array(
				'name'    => __( 'Booking Reminder', 'unbelievable-salon-booking' ),
				'type'    => 'booking_reminder',
				'subject' => __( 'Booking Reminder', 'unbelievable-salon-booking' ) . ' - {company_name}',
				'content' => $this->get_default_booking_reminder_template(),
			),
			array(
				'name'    => __( 'Admin: New Booking', 'unbelievable-salon-booking' ),
				'type'    => 'admin_new_booking',
				'subject' => __( 'New Booking Request', 'unbelievable-salon-booking' ) . ': {customer_name}',
				'content' => $this->get_default_admin_new_booking_template(),
			),
			array(
				'name'    => __( 'Staff: New Booking', 'unbelievable-salon-booking' ),
				'type'    => 'staff_new_booking',
				'subject' => __( 'New Booking Assigned to You', 'unbelievable-salon-booking' ) . ' - {company_name}',
				'content' => $this->get_default_staff_new_booking_template(),
			),
		);

		foreach ( $templates as $template ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT id FROM ' . $this->templates_table . ' WHERE type = %s',
					$template['type']
				)
			);

			if ( ! $exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert(
					$this->templates_table,
					$template,
					array( '%s', '%s', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Default "Booking Received" email template
	 */
	private function get_default_booking_received_template() {
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
	private function get_default_booking_confirmed_template() {
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
	private function get_default_booking_cancelled_template() {
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
	private function get_default_booking_reminder_template() {
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
	 * Default "Admin: New Booking" email template
	 */
	private function get_default_admin_new_booking_template() {
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
	private function get_default_staff_new_booking_template() {
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
	 * Update template
	 *
	 * @param int    $id        Template ID.
	 * @param string $subject   Subject.
	 * @param string $content   Content.
	 * @param bool   $is_active Whether active.
	 *
	 * @return bool
	 */
	public function update_template( $id, $subject, $content, $is_active = true ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
	 * Replace placeholders
	 *
	 * @param string $content Content.
	 * @param object $booking Booking.
	 *
	 * @return string
	 */
	private function parse_placeholders( $content, $booking ) {
		$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$company_phone   = get_option( 'unbsb_company_phone', '' );
		$company_address = get_option( 'unbsb_company_address', '' );
		$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
		$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );
		$time_format     = get_option( 'unbsb_time_format', 'H:i' );

		$formatted_date = date_i18n( $date_format, strtotime( $booking->booking_date ) );
		$formatted_time = date_i18n( $time_format, strtotime( $booking->start_time ) );

		// Manage booking URL.
		$manage_url = $this->get_manage_booking_url( $booking->token );

		// Use services_list for multi-service.
		$services_list = ! empty( $booking->services_list ) ? $booking->services_list : $booking->service_name;

		// Total duration.
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
			'{total_duration}'     => $total_duration . ' ' . __( 'min', 'unbelievable-salon-booking' ),
			'{company_name}'       => $company_name,
			'{company_phone}'      => $company_phone,
			'{company_address}'    => nl2br( $company_address ),
			'{booking_id}'         => $booking->id,
			'{status}'             => $this->get_status_label( $booking->status ),
			'{manage_booking_url}' => $manage_url,
			'{admin_url}'          => admin_url( 'admin.php?page=unbsb-bookings' ),
		);

		$content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $content );

		return apply_filters( 'unbsb_filter_email_content', $content, $booking );
	}

	/**
	 * Wrap email content with HTML wrapper
	 *
	 * @param string $content       Content.
	 * @param string $template_type Template type.
	 *
	 * @return string
	 */
	private function wrap_email_content( $content, $template_type ) {
		$company_name  = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$company_phone = get_option( 'unbsb_company_phone', '' );
		$primary_color = get_option( 'unbsb_email_primary_color', '#3b82f6' );
		$logo_url      = get_option( 'unbsb_email_logo_url', '' );

		// Accent color based on template type.
		$accent_colors = array(
			'booking_received'  => '#3b82f6', // Blue.
			'booking_confirmed' => '#10b981', // Green.
			'booking_cancelled' => '#ef4444', // Red.
			'booking_reminder'  => '#f59e0b', // Orange.
			'admin_new_booking' => '#8b5cf6', // Purple.
			'staff_new_booking' => '#0891b2', // Cyan.
		);
		$accent_color  = $accent_colors[ $template_type ] ?? $primary_color;

		$logo_html = '';
		if ( $logo_url ) {
			$logo_html = '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $company_name ) . '" style="max-height: 60px; width: auto;">';
		} else {
			$logo_html = '<span style="font-size: 24px; font-weight: 700; color: ' . esc_attr( $accent_color ) . ';">' . esc_html( $company_name ) . '</span>';
		}

		$html = '<!DOCTYPE html>
<html lang="en">
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

		$company_address = get_option( 'unbsb_company_address', '' );
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
			esc_html__( 'This email was sent by %s.', 'unbelievable-salon-booking' ),
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
	 * Adjust color value (darker/lighter)
	 *
	 * @param string $hex    Hex color.
	 * @param int    $amount Change amount (-255 to 255).
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
	 * Get status label
	 *
	 * @param string $status Status.
	 *
	 * @return string
	 */
	private function get_status_label( $status ) {
		$labels = array(
			'pending'   => __( 'Pending', 'unbelievable-salon-booking' ),
			'confirmed' => __( 'Confirmed', 'unbelievable-salon-booking' ),
			'cancelled' => __( 'Cancelled', 'unbelievable-salon-booking' ),
			'completed' => __( 'Completed', 'unbelievable-salon-booking' ),
			'no_show'   => __( 'No Show', 'unbelievable-salon-booking' ),
		);

		return $labels[ $status ] ?? $status;
	}

	/**
	 * Send email
	 *
	 * @param string $to          Recipient.
	 * @param string $subject     Subject.
	 * @param string $message     Message.
	 * @param array  $attachments Attachments (file paths).
	 *
	 * @return bool
	 */
	private function send_email( $to, $subject, $message, $attachments = array() ) {
		$from_name  = get_option( 'unbsb_email_from_name', get_bloginfo( 'name' ) );
		$from_email = get_option( 'unbsb_email_from_address', get_option( 'admin_email' ) );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			"From: {$from_name} <{$from_email}>",
		);

		return wp_mail( $to, $subject, $message, $headers, $attachments );
	}

	/**
	 * Check if ICS feature is enabled
	 *
	 * @return bool
	 */
	private function is_ics_enabled() {
		return 'yes' === get_option( 'unbsb_enable_ics', 'yes' );
	}

	/**
	 * Generate manage booking URL
	 *
	 * @param string $token Booking token.
	 *
	 * @return string URL.
	 */
	private function get_manage_booking_url( $token ) {
		// Check if manage booking page is configured.
		$page_id = get_option( 'unbsb_manage_booking_page', 0 );

		if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
			$base_url = get_permalink( $page_id );
		} else {
			// Use home URL as default.
			$base_url = home_url( '/' );
		}

		return add_query_arg(
			array(
				'unbsb_action' => 'manage_booking',
				'token'        => $token,
			),
			$base_url
		);
	}

	/**
	 * Send test email
	 *
	 * @param string $email         Recipient email.
	 * @param string $template_type Template type.
	 *
	 * @return array
	 */
	public function send_test_email( $email, $template_type ) {
		$template = $this->get_template( $template_type );

		if ( ! $template ) {
			return array(
				'success' => false,
				'message' => __( 'Template not found.', 'unbelievable-salon-booking' ),
			);
		}

		// Create test data.
		$test_booking = (object) array(
			'id'             => 999,
			'customer_name'  => 'Test Customer',
			'customer_email' => $email,
			'customer_phone' => '0555 555 55 55',
			'service_name'   => 'Test Service',
			'services_list'  => 'Test Service 1, Test Service 2',
			'staff_name'     => 'Test Staff',
			'booking_date'   => gmdate( 'Y-m-d', strtotime( '+1 day' ) ),
			'start_time'     => '14:00:00',
			'price'          => '150.00',
			'total_duration' => 60,
			'status'         => 'pending',
			'token'          => 'test-token-123',
		);

		$subject = $this->parse_placeholders( $template->subject, $test_booking );
		$content = $this->parse_placeholders( $template->content, $test_booking );
		$content = str_replace( '{calendar_links}', '', $content );
		$message = $this->wrap_email_content( $content, $template_type );

		$result = $this->send_email( $email, '[TEST] ' . $subject, $message );

		if ( $result ) {
			return array(
				'success' => true,
				'message' => __( 'Test email sent.', 'unbelievable-salon-booking' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => __( 'Email could not be sent.', 'unbelievable-salon-booking' ),
			);
		}
	}
}
