<?php
/**
 * Plugin uninstall script
 *
 * @package Unbelievable_Salon_Booking
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
$options = array(
	'unbsb_version',
	'unbsb_db_version',
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
	'unbsb_sms_enabled',
	'unbsb_company_name',
	'unbsb_company_phone',
	'unbsb_company_address',
	// ICS options.
	'unbsb_enable_ics',
	// Cancel/Reschedule options.
	'unbsb_allow_cancel',
	'unbsb_allow_reschedule',
	'unbsb_cancel_deadline_hours',
	'unbsb_reschedule_deadline_hours',
	'unbsb_max_reschedules',
	// Multi-service options.
	'unbsb_enable_multi_service',
	// SMS options.
	'unbsb_sms_provider',
	'unbsb_sms_netgsm_username',
	'unbsb_sms_netgsm_password',
	'unbsb_sms_netgsm_sender',
	'unbsb_sms_reminder_enabled',
	'unbsb_sms_reminder_hours',
	'unbsb_sms_on_booking',
	'unbsb_sms_on_confirmation',
	'unbsb_sms_on_cancellation',
	// Email options.
	'unbsb_email_reminder_enabled',
	'unbsb_email_reminder_hours',
	'unbsb_email_logo_url',
	'unbsb_email_primary_color',
	// SEO options.
	'unbsb_seo_enabled',
	'unbsb_seo_business_type',
	'unbsb_seo_price_range',
	'unbsb_seo_description',
	'unbsb_seo_logo_url',
	'unbsb_seo_city',
	'unbsb_seo_postal_code',
	'unbsb_seo_country',
	// Social media options.
	'unbsb_social_facebook',
	'unbsb_social_instagram',
	'unbsb_social_twitter',
	'unbsb_social_twitter_handle',
	// Security options.
	'unbsb_captcha_provider',
	'unbsb_captcha_site_key',
	'unbsb_captcha_secret_key',
	'unbsb_captcha_min_score',
	'unbsb_security_logging_enabled',
	'unbsb_security_log_last_cleanup',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Drop custom tables.
global $wpdb;

$tables = array(
	$wpdb->prefix . 'unbsb_categories',
	$wpdb->prefix . 'unbsb_services',
	$wpdb->prefix . 'unbsb_staff',
	$wpdb->prefix . 'unbsb_staff_services',
	$wpdb->prefix . 'unbsb_working_hours',
	$wpdb->prefix . 'unbsb_breaks',
	$wpdb->prefix . 'unbsb_holidays',
	$wpdb->prefix . 'unbsb_customers',
	$wpdb->prefix . 'unbsb_bookings',
	$wpdb->prefix . 'unbsb_booking_services',
	$wpdb->prefix . 'unbsb_sms_queue',
	$wpdb->prefix . 'unbsb_sms_templates',
	$wpdb->prefix . 'unbsb_email_templates',
	$wpdb->prefix . 'unbsb_security_logs',
	$wpdb->prefix . 'unbsb_promo_codes',
	$wpdb->prefix . 'unbsb_promo_code_usage',
);

foreach ( $tables as $table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// Remove capabilities.
$admin = get_role( 'administrator' );

if ( $admin ) {
	$admin->remove_cap( 'unbsb_manage_bookings' );
	$admin->remove_cap( 'unbsb_manage_services' );
	$admin->remove_cap( 'unbsb_manage_staff' );
	$admin->remove_cap( 'unbsb_manage_settings' );
}

// Remove Customer role.
remove_role( 'unbsb_customer' );

// Clear any cached data.
wp_cache_flush();
