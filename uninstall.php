<?php
/**
 * Plugin uninstall script
 *
 * @package Appointment_General
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options
$options = array(
	'ag_version',
	'ag_time_slot_interval',
	'ag_booking_lead_time',
	'ag_booking_future_days',
	'ag_currency',
	'ag_currency_symbol',
	'ag_currency_position',
	'ag_date_format',
	'ag_time_format',
	'ag_admin_email',
	'ag_email_from_name',
	'ag_email_from_address',
	'ag_sms_enabled',
	'ag_company_name',
	'ag_company_phone',
	'ag_company_address',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Drop custom tables
global $wpdb;

$tables = array(
	$wpdb->prefix . 'ag_categories',
	$wpdb->prefix . 'ag_services',
	$wpdb->prefix . 'ag_staff',
	$wpdb->prefix . 'ag_staff_services',
	$wpdb->prefix . 'ag_working_hours',
	$wpdb->prefix . 'ag_breaks',
	$wpdb->prefix . 'ag_holidays',
	$wpdb->prefix . 'ag_customers',
	$wpdb->prefix . 'ag_bookings',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

// Remove capabilities
$admin = get_role( 'administrator' );

if ( $admin ) {
	$admin->remove_cap( 'ag_manage_bookings' );
	$admin->remove_cap( 'ag_manage_services' );
	$admin->remove_cap( 'ag_manage_staff' );
	$admin->remove_cap( 'ag_manage_settings' );
}

// Clear any cached data
wp_cache_flush();
