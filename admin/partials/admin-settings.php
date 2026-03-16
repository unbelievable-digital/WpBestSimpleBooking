<?php
/**
 * Admin Settings Template - Modern Tab Design
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current settings.
$settings = array(
	'unbsb_time_slot_interval'        => get_option( 'unbsb_time_slot_interval', 30 ),
	'unbsb_booking_lead_time'         => get_option( 'unbsb_booking_lead_time', 60 ),
	'unbsb_booking_future_days'       => get_option( 'unbsb_booking_future_days', 30 ),
	'unbsb_booking_flow_mode'         => get_option( 'unbsb_booking_flow_mode', 'service_first' ),
	'unbsb_currency'                  => get_option( 'unbsb_currency', 'TRY' ),
	'unbsb_currency_symbol'           => get_option( 'unbsb_currency_symbol', '₺' ),
	'unbsb_currency_position'         => get_option( 'unbsb_currency_position', 'after' ),
	'unbsb_date_format'               => get_option( 'unbsb_date_format', 'd.m.Y' ),
	'unbsb_time_format'               => get_option( 'unbsb_time_format', 'H:i' ),
	'unbsb_admin_email'               => get_option( 'unbsb_admin_email', get_option( 'admin_email' ) ),
	'unbsb_email_from_name'           => get_option( 'unbsb_email_from_name', get_bloginfo( 'name' ) ),
	'unbsb_email_from_address'        => get_option( 'unbsb_email_from_address', get_option( 'admin_email' ) ),
	'unbsb_company_name'              => get_option( 'unbsb_company_name', get_bloginfo( 'name' ) ),
	'unbsb_company_phone'             => get_option( 'unbsb_company_phone', '' ),
	'unbsb_company_address'           => get_option( 'unbsb_company_address', '' ),
	'unbsb_enable_ics'                => get_option( 'unbsb_enable_ics', 'yes' ),
	'unbsb_allow_cancel'              => get_option( 'unbsb_allow_cancel', 'yes' ),
	'unbsb_allow_reschedule'          => get_option( 'unbsb_allow_reschedule', 'yes' ),
	'unbsb_cancel_deadline_hours'     => get_option( 'unbsb_cancel_deadline_hours', 24 ),
	'unbsb_reschedule_deadline_hours' => get_option( 'unbsb_reschedule_deadline_hours', 24 ),
	'unbsb_max_reschedules'           => get_option( 'unbsb_max_reschedules', 2 ),
	'unbsb_enable_multi_service'      => get_option( 'unbsb_enable_multi_service', 'no' ),
	'unbsb_sms_enabled'               => get_option( 'unbsb_sms_enabled', 'no' ),
	'unbsb_sms_provider'              => get_option( 'unbsb_sms_provider', 'netgsm' ),
	'unbsb_sms_netgsm_username'       => get_option( 'unbsb_sms_netgsm_username', '' ),
	'unbsb_sms_netgsm_password'       => UNBSB_Encryption::get_option( 'unbsb_sms_netgsm_password', '' ),
	'unbsb_sms_netgsm_sender'         => get_option( 'unbsb_sms_netgsm_sender', '' ),
	'unbsb_sms_reminder_enabled'      => get_option( 'unbsb_sms_reminder_enabled', 'yes' ),
	'unbsb_sms_reminder_hours'        => get_option( 'unbsb_sms_reminder_hours', 24 ),
	'unbsb_sms_on_booking'            => get_option( 'unbsb_sms_on_booking', 'yes' ),
	'unbsb_sms_on_confirmation'       => get_option( 'unbsb_sms_on_confirmation', 'yes' ),
	'unbsb_sms_on_cancellation'       => get_option( 'unbsb_sms_on_cancellation', 'no' ),
	// SEO Settings.
	'unbsb_seo_enabled'               => get_option( 'unbsb_seo_enabled', 'yes' ),
	'unbsb_seo_business_type'         => get_option( 'unbsb_seo_business_type', 'BeautySalon' ),
	'unbsb_seo_description'           => get_option( 'unbsb_seo_description', '' ),
	'unbsb_seo_logo_url'              => get_option( 'unbsb_seo_logo_url', '' ),
	'unbsb_seo_price_range'           => get_option( 'unbsb_seo_price_range', '₺₺' ),
	'unbsb_seo_city'                  => get_option( 'unbsb_seo_city', '' ),
	'unbsb_seo_postal_code'           => get_option( 'unbsb_seo_postal_code', '' ),
	'unbsb_seo_country'               => get_option( 'unbsb_seo_country', 'TR' ),
	'unbsb_social_facebook'           => get_option( 'unbsb_social_facebook', '' ),
	'unbsb_social_instagram'          => get_option( 'unbsb_social_instagram', '' ),
	'unbsb_social_twitter'            => get_option( 'unbsb_social_twitter', '' ),
	'unbsb_social_twitter_handle'     => get_option( 'unbsb_social_twitter_handle', '' ),
	// Security / CAPTCHA.
	'unbsb_captcha_provider'          => get_option( 'unbsb_captcha_provider', 'none' ),
	'unbsb_captcha_site_key'          => get_option( 'unbsb_captcha_site_key', '' ),
	'unbsb_captcha_secret_key'        => UNBSB_Encryption::get_option( 'unbsb_captcha_secret_key', '' ),
	'unbsb_captcha_min_score'         => get_option( 'unbsb_captcha_min_score', '0.5' ),
	'unbsb_security_logging_enabled'  => get_option( 'unbsb_security_logging_enabled', 'yes' ),
);

// Get SMS templates.
$sms_templates = array();
if ( class_exists( 'UNBSB_SMS_Manager' ) ) {
	$sms_manager   = new UNBSB_SMS_Manager();
	$sms_templates = $sms_manager->get_templates();
}

// Tab definitions.
$unbsb_tabs = array(
	'general'  => array(
		'label' => __( 'General', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-admin-generic',
	),
	'business' => array(
		'label' => __( 'Business', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-building',
	),
	'format'   => array(
		'label' => __( 'Currency & Date', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-calendar-alt',
	),
	'email'    => array(
		'label' => __( 'Email', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-email',
	),
	'booking'  => array(
		'label' => __( 'Booking', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-clock',
	),
	'sms'      => array(
		'label' => __( 'SMS', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-smartphone',
	),
	'seo'      => array(
		'label' => __( 'SEO', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-search',
	),
	'security' => array(
		'label' => __( 'Security', 'unbelievable-salon-booking' ),
		'icon'  => 'dashicons-shield',
	),
);

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for read-only tab selection.
$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
if ( ! array_key_exists( $current_tab, $unbsb_tabs ) ) {
	$current_tab = 'general';
}
?>

<div class="unbsb-settings-page">
	<!-- Header -->
	<div class="unbsb-settings-header">
		<div class="unbsb-settings-header-content">
			<div class="unbsb-settings-header-icon">
				<span class="dashicons dashicons-admin-settings"></span>
			</div>
			<div class="unbsb-settings-header-text">
				<h1><?php esc_html_e( 'Settings', 'unbelievable-salon-booking' ); ?></h1>
				<p><?php esc_html_e( 'Customize and configure your booking system.', 'unbelievable-salon-booking' ); ?></p>
			</div>
		</div>
	</div>

	<div class="unbsb-settings-container">
		<!-- Tab Navigation -->
		<div class="unbsb-settings-nav">
			<ul class="unbsb-settings-tabs">
				<?php foreach ( $unbsb_tabs as $tab_id => $unbsb_tab ) : ?>
				<li class="unbsb-settings-tab <?php echo $current_tab === $tab_id ? 'active' : ''; ?>">
					<a href="<?php echo esc_url( add_query_arg( 'tab', $tab_id ) ); ?>">
						<span class="dashicons <?php echo esc_attr( $unbsb_tab['icon'] ); ?>"></span>
						<span class="tab-label"><?php echo esc_html( $unbsb_tab['label'] ); ?></span>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<!-- Tab Content -->
		<div class="unbsb-settings-content">
			<form id="unbsb-settings-form">
				<?php if ( 'general' === $current_tab ) : ?>
				<!-- GENERAL SETTINGS -->
				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'Booking Settings', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Set the basic operating parameters for the booking system.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-settings-grid">
						<div class="unbsb-setting-item">
							<label for="unbsb_time_slot_interval">
								<span class="setting-label"><?php esc_html_e( 'Time Slot Interval', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Duration between booking slots', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<div class="unbsb-input-group">
								<select name="unbsb_time_slot_interval" id="unbsb_time_slot_interval" class="unbsb-select">
									<option value="15" <?php selected( $settings['unbsb_time_slot_interval'], 15 ); ?>><?php esc_html_e( '15 minutes', 'unbelievable-salon-booking' ); ?></option>
									<option value="30" <?php selected( $settings['unbsb_time_slot_interval'], 30 ); ?>><?php esc_html_e( '30 minutes', 'unbelievable-salon-booking' ); ?></option>
									<option value="60" <?php selected( $settings['unbsb_time_slot_interval'], 60 ); ?>><?php esc_html_e( '60 minutes', 'unbelievable-salon-booking' ); ?></option>
								</select>
							</div>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_booking_lead_time">
								<span class="setting-label"><?php esc_html_e( 'Minimum Booking Lead Time', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Minimum minutes before a booking can be made', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<div class="unbsb-input-group">
								<input type="number" name="unbsb_booking_lead_time" id="unbsb_booking_lead_time" value="<?php echo esc_attr( $settings['unbsb_booking_lead_time'] ); ?>" min="0" step="30" class="unbsb-input">
								<span class="unbsb-input-suffix"><?php esc_html_e( 'minutes', 'unbelievable-salon-booking' ); ?></span>
							</div>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_booking_future_days">
								<span class="setting-label"><?php esc_html_e( 'Future Booking Limit', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'How many days ahead bookings can be made', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<div class="unbsb-input-group">
								<input type="number" name="unbsb_booking_future_days" id="unbsb_booking_future_days" value="<?php echo esc_attr( $settings['unbsb_booking_future_days'] ); ?>" min="1" max="365" class="unbsb-input">
								<span class="unbsb-input-suffix"><?php esc_html_e( 'days', 'unbelievable-salon-booking' ); ?></span>
							</div>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_booking_flow_mode">
								<span class="setting-label"><?php esc_html_e( 'Booking Flow Mode', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'The step order the customer will follow', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<select name="unbsb_booking_flow_mode" id="unbsb_booking_flow_mode" class="unbsb-select">
								<option value="service_first" <?php selected( $settings['unbsb_booking_flow_mode'], 'service_first' ); ?>><?php esc_html_e( 'Service First', 'unbelievable-salon-booking' ); ?></option>
								<option value="staff_first" <?php selected( $settings['unbsb_booking_flow_mode'], 'staff_first' ); ?>><?php esc_html_e( 'Staff First', 'unbelievable-salon-booking' ); ?></option>
								<option value="service_only" <?php selected( $settings['unbsb_booking_flow_mode'], 'service_only' ); ?>><?php esc_html_e( 'Service Only (Auto Staff)', 'unbelievable-salon-booking' ); ?></option>
								<option value="staff_only" <?php selected( $settings['unbsb_booking_flow_mode'], 'staff_only' ); ?>><?php esc_html_e( 'Staff Only (All Services)', 'unbelievable-salon-booking' ); ?></option>
							</select>
						</div>
					</div>

					<div class="unbsb-setting-item unbsb-setting-toggle">
						<div class="unbsb-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Multiple Services', 'unbelievable-salon-booking' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Customers can select multiple services in one booking', 'unbelievable-salon-booking' ); ?></span>
						</div>
						<label class="unbsb-toggle">
							<input type="checkbox" name="unbsb_enable_multi_service" id="unbsb_enable_multi_service" value="yes" <?php checked( $settings['unbsb_enable_multi_service'], 'yes' ); ?>>
							<span class="unbsb-toggle-slider"></span>
						</label>
					</div>
				</div>

				<?php elseif ( 'business' === $current_tab ) : ?>
				<!-- BUSINESS INFORMATION -->
				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'Business Information', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Enter business information that will appear in email and SMS notifications.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-settings-form-single">
						<div class="unbsb-setting-item">
							<label for="unbsb_company_name">
								<span class="setting-label"><?php esc_html_e( 'Business Name', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="text" name="unbsb_company_name" id="unbsb_company_name" value="<?php echo esc_attr( $settings['unbsb_company_name'] ); ?>" class="unbsb-input unbsb-input-lg" placeholder="<?php esc_attr_e( 'e.g. Elite Hair Salon', 'unbelievable-salon-booking' ); ?>">
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_company_phone">
								<span class="setting-label"><?php esc_html_e( 'Business Phone', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="tel" name="unbsb_company_phone" id="unbsb_company_phone" value="<?php echo esc_attr( $settings['unbsb_company_phone'] ); ?>" class="unbsb-input unbsb-input-lg" placeholder="<?php esc_attr_e( '+1 555 555 5555', 'unbelievable-salon-booking' ); ?>">
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_company_address">
								<span class="setting-label"><?php esc_html_e( 'Business Address', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<textarea name="unbsb_company_address" id="unbsb_company_address" rows="3" class="unbsb-textarea" placeholder="<?php esc_attr_e( 'Full address...', 'unbelievable-salon-booking' ); ?>"><?php echo esc_textarea( $settings['unbsb_company_address'] ); ?></textarea>
						</div>
					</div>
				</div>

				<?php elseif ( 'format' === $current_tab ) : ?>
				<!-- CURRENCY & DATE/TIME -->
				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'Currency', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Set how prices are displayed.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-settings-grid unbsb-grid-3">
						<div class="unbsb-setting-item">
							<label for="unbsb_currency">
								<span class="setting-label"><?php esc_html_e( 'Currency Code', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="text" name="unbsb_currency" id="unbsb_currency" value="<?php echo esc_attr( $settings['unbsb_currency'] ); ?>" maxlength="3" class="unbsb-input" placeholder="TRY">
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_currency_symbol">
								<span class="setting-label"><?php esc_html_e( 'Currency Symbol', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="text" name="unbsb_currency_symbol" id="unbsb_currency_symbol" value="<?php echo esc_attr( $settings['unbsb_currency_symbol'] ); ?>" maxlength="5" class="unbsb-input" placeholder="₺">
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_currency_position">
								<span class="setting-label"><?php esc_html_e( 'Currency Position', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<select name="unbsb_currency_position" id="unbsb_currency_position" class="unbsb-select">
								<option value="before" <?php selected( $settings['unbsb_currency_position'], 'before' ); ?>><?php esc_html_e( 'Before ($100)', 'unbelievable-salon-booking' ); ?></option>
								<option value="after" <?php selected( $settings['unbsb_currency_position'], 'after' ); ?>><?php esc_html_e( 'After (100$)', 'unbelievable-salon-booking' ); ?></option>
							</select>
						</div>
					</div>
				</div>

				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'Date and Time Format', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Choose how dates and times are displayed.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-settings-grid">
						<div class="unbsb-setting-item">
							<label for="unbsb_date_format">
								<span class="setting-label"><?php esc_html_e( 'Date Format', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<select name="unbsb_date_format" id="unbsb_date_format" class="unbsb-select">
								<option value="d.m.Y" <?php selected( $settings['unbsb_date_format'], 'd.m.Y' ); ?>>31.12.2024</option>
								<option value="d/m/Y" <?php selected( $settings['unbsb_date_format'], 'd/m/Y' ); ?>>31/12/2024</option>
								<option value="Y-m-d" <?php selected( $settings['unbsb_date_format'], 'Y-m-d' ); ?>>2024-12-31</option>
								<option value="d F Y" <?php selected( $settings['unbsb_date_format'], 'd F Y' ); ?>>31 December 2024</option>
							</select>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_time_format">
								<span class="setting-label"><?php esc_html_e( 'Time Format', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<select name="unbsb_time_format" id="unbsb_time_format" class="unbsb-select">
								<option value="H:i" <?php selected( $settings['unbsb_time_format'], 'H:i' ); ?>>14:30 (24-hour)</option>
								<option value="g:i A" <?php selected( $settings['unbsb_time_format'], 'g:i A' ); ?>>2:30 PM (12-hour)</option>
							</select>
						</div>
					</div>
				</div>

				<?php elseif ( 'email' === $current_tab ) : ?>
				<!-- EMAIL SETTINGS -->
				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'Email Settings', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Configure the sending settings for booking notification emails.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-settings-form-single">
						<div class="unbsb-setting-item">
							<label for="unbsb_admin_email">
								<span class="setting-label"><?php esc_html_e( 'Admin Email', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'New booking notifications will be sent to this address', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="email" name="unbsb_admin_email" id="unbsb_admin_email" value="<?php echo esc_attr( $settings['unbsb_admin_email'] ); ?>" class="unbsb-input unbsb-input-lg">
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_email_from_name">
								<span class="setting-label"><?php esc_html_e( 'Sender Name', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Sender name that will appear in emails', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="text" name="unbsb_email_from_name" id="unbsb_email_from_name" value="<?php echo esc_attr( $settings['unbsb_email_from_name'] ); ?>" class="unbsb-input unbsb-input-lg">
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_email_from_address">
								<span class="setting-label"><?php esc_html_e( 'Sender Email', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Email address that emails will be sent from', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="email" name="unbsb_email_from_address" id="unbsb_email_from_address" value="<?php echo esc_attr( $settings['unbsb_email_from_address'] ); ?>" class="unbsb-input unbsb-input-lg">
						</div>
					</div>

					<div class="unbsb-info-box">
						<span class="dashicons dashicons-info"></span>
						<p><?php esc_html_e( 'To edit email templates, go to the "Email Templates" page from the left menu.', 'unbelievable-salon-booking' ); ?></p>
					</div>
				</div>

				<?php elseif ( 'booking' === $current_tab ) : ?>
				<!-- BOOKING SETTINGS -->
				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'Cancellation and Rescheduling', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Set the rules for customer booking cancellation and rescheduling.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-setting-item unbsb-setting-toggle">
						<div class="unbsb-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Customer Cancellation', 'unbelievable-salon-booking' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Customers can cancel their bookings', 'unbelievable-salon-booking' ); ?></span>
						</div>
						<label class="unbsb-toggle">
							<input type="checkbox" name="unbsb_allow_cancel" id="unbsb_allow_cancel" value="yes" <?php checked( $settings['unbsb_allow_cancel'], 'yes' ); ?>>
							<span class="unbsb-toggle-slider"></span>
						</label>
					</div>

					<div class="unbsb-setting-item unbsb-setting-toggle">
						<div class="unbsb-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Customer Rescheduling', 'unbelievable-salon-booking' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Customers can reschedule their bookings', 'unbelievable-salon-booking' ); ?></span>
						</div>
						<label class="unbsb-toggle">
							<input type="checkbox" name="unbsb_allow_reschedule" id="unbsb_allow_reschedule" value="yes" <?php checked( $settings['unbsb_allow_reschedule'], 'yes' ); ?>>
							<span class="unbsb-toggle-slider"></span>
						</label>
					</div>

					<div class="unbsb-settings-grid unbsb-grid-3">
						<div class="unbsb-setting-item">
							<label for="unbsb_cancel_deadline_hours">
								<span class="setting-label"><?php esc_html_e( 'Cancellation Deadline', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Hours before the booking', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<div class="unbsb-input-group">
								<input type="number" name="unbsb_cancel_deadline_hours" id="unbsb_cancel_deadline_hours" value="<?php echo esc_attr( $settings['unbsb_cancel_deadline_hours'] ); ?>" min="0" max="168" class="unbsb-input">
								<span class="unbsb-input-suffix"><?php esc_html_e( 'hours', 'unbelievable-salon-booking' ); ?></span>
							</div>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_reschedule_deadline_hours">
								<span class="setting-label"><?php esc_html_e( 'Rescheduling Deadline', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Hours before the booking', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<div class="unbsb-input-group">
								<input type="number" name="unbsb_reschedule_deadline_hours" id="unbsb_reschedule_deadline_hours" value="<?php echo esc_attr( $settings['unbsb_reschedule_deadline_hours'] ); ?>" min="0" max="168" class="unbsb-input">
								<span class="unbsb-input-suffix"><?php esc_html_e( 'hours', 'unbelievable-salon-booking' ); ?></span>
							</div>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_max_reschedules">
								<span class="setting-label"><?php esc_html_e( 'Max. Reschedules', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'How many times a booking can be rescheduled', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<div class="unbsb-input-group">
								<input type="number" name="unbsb_max_reschedules" id="unbsb_max_reschedules" value="<?php echo esc_attr( $settings['unbsb_max_reschedules'] ); ?>" min="1" max="10" class="unbsb-input">
								<span class="unbsb-input-suffix"><?php esc_html_e( 'times', 'unbelievable-salon-booking' ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'Calendar Integration', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Configure the ability to add bookings to calendar apps.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-setting-item unbsb-setting-toggle">
						<div class="unbsb-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Calendar File (ICS)', 'unbelievable-salon-booking' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Attach .ics file and calendar links to emails', 'unbelievable-salon-booking' ); ?></span>
						</div>
						<label class="unbsb-toggle">
							<input type="checkbox" name="unbsb_enable_ics" id="unbsb_enable_ics" value="yes" <?php checked( $settings['unbsb_enable_ics'], 'yes' ); ?>>
							<span class="unbsb-toggle-slider"></span>
						</label>
					</div>

					<div class="unbsb-calendar-providers">
						<div class="unbsb-provider-item">
							<span class="dashicons dashicons-google"></span>
							<span>Google Calendar</span>
						</div>
						<div class="unbsb-provider-item">
							<span class="dashicons dashicons-email-alt"></span>
							<span>Outlook</span>
						</div>
						<div class="unbsb-provider-item">
							<span class="dashicons dashicons-calendar"></span>
							<span>Apple Calendar</span>
						</div>
						<div class="unbsb-provider-item">
							<span class="dashicons dashicons-calendar-alt"></span>
							<span>Yahoo Calendar</span>
						</div>
					</div>
				</div>

				<?php elseif ( 'sms' === $current_tab ) : ?>
				<!-- SMS AYARLARI -->
				<div class="unbsb-settings-section">
					<div class="unbsb-section-header">
						<h2><?php esc_html_e( 'SMS Notifications', 'unbelievable-salon-booking' ); ?></h2>
						<p><?php esc_html_e( 'Configure SMS notification settings with NetGSM.', 'unbelievable-salon-booking' ); ?></p>
					</div>

					<div class="unbsb-setting-item unbsb-setting-toggle unbsb-toggle-main">
						<div class="unbsb-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Enable SMS Notifications', 'unbelievable-salon-booking' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Booking notifications will be sent via SMS', 'unbelievable-salon-booking' ); ?></span>
						</div>
						<label class="unbsb-toggle">
							<input type="checkbox" name="unbsb_sms_enabled" id="unbsb_sms_enabled" value="yes" <?php checked( $settings['unbsb_sms_enabled'], 'yes' ); ?>>
							<span class="unbsb-toggle-slider"></span>
						</label>
					</div>

					<div id="unbsb-sms-settings" class="unbsb-sms-settings-inner" style="<?php echo 'yes' !== $settings['unbsb_sms_enabled'] ? 'display:none;' : ''; ?>">
						<div class="unbsb-settings-subsection">
							<h3><?php esc_html_e( 'NetGSM API Credentials', 'unbelievable-salon-booking' ); ?></h3>

							<div class="unbsb-settings-grid unbsb-grid-3">
								<div class="unbsb-setting-item">
									<label for="unbsb_sms_netgsm_username">
										<span class="setting-label"><?php esc_html_e( 'Username', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<input type="text" name="unbsb_sms_netgsm_username" id="unbsb_sms_netgsm_username" value="<?php echo esc_attr( $settings['unbsb_sms_netgsm_username'] ); ?>" class="unbsb-input">
								</div>

								<div class="unbsb-setting-item">
									<label for="unbsb_sms_netgsm_password">
										<span class="setting-label"><?php esc_html_e( 'Password', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<input type="password" name="unbsb_sms_netgsm_password" id="unbsb_sms_netgsm_password" value="<?php echo esc_attr( $settings['unbsb_sms_netgsm_password'] ); ?>" class="unbsb-input">
								</div>

								<div class="unbsb-setting-item">
									<label for="unbsb_sms_netgsm_sender">
										<span class="setting-label"><?php esc_html_e( 'Sender ID', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<input type="text" name="unbsb_sms_netgsm_sender" id="unbsb_sms_netgsm_sender" value="<?php echo esc_attr( $settings['unbsb_sms_netgsm_sender'] ); ?>" class="unbsb-input">
								</div>
							</div>

							<div class="unbsb-sms-actions">
								<button type="button" class="unbsb-btn unbsb-btn-outline" id="unbsb-sms-check-balance">
									<span class="dashicons dashicons-chart-area"></span>
									<?php esc_html_e( 'Check Balance', 'unbelievable-salon-booking' ); ?>
								</button>
								<span id="unbsb-sms-balance-result" class="unbsb-balance-result"></span>

								<div class="unbsb-sms-test">
									<input type="tel" id="unbsb_sms_test_phone" placeholder="5xxxxxxxxx" class="unbsb-input">
									<button type="button" class="unbsb-btn unbsb-btn-outline" id="unbsb-sms-send-test">
										<span class="dashicons dashicons-smartphone"></span>
										<?php esc_html_e( 'Test SMS', 'unbelievable-salon-booking' ); ?>
									</button>
								</div>
							</div>
						</div>

						<div class="unbsb-settings-subsection">
							<h3><?php esc_html_e( 'Notification Triggers', 'unbelievable-salon-booking' ); ?></h3>

							<div class="unbsb-toggle-list">
								<div class="unbsb-setting-item unbsb-setting-toggle">
									<div class="unbsb-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'New Booking', 'unbelievable-salon-booking' ); ?></span>
									</div>
									<label class="unbsb-toggle unbsb-toggle-sm">
										<input type="checkbox" name="unbsb_sms_on_booking" value="yes" <?php checked( $settings['unbsb_sms_on_booking'], 'yes' ); ?>>
										<span class="unbsb-toggle-slider"></span>
									</label>
								</div>

								<div class="unbsb-setting-item unbsb-setting-toggle">
									<div class="unbsb-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'Booking Confirmation', 'unbelievable-salon-booking' ); ?></span>
									</div>
									<label class="unbsb-toggle unbsb-toggle-sm">
										<input type="checkbox" name="unbsb_sms_on_confirmation" value="yes" <?php checked( $settings['unbsb_sms_on_confirmation'], 'yes' ); ?>>
										<span class="unbsb-toggle-slider"></span>
									</label>
								</div>

								<div class="unbsb-setting-item unbsb-setting-toggle">
									<div class="unbsb-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'Booking Cancellation', 'unbelievable-salon-booking' ); ?></span>
									</div>
									<label class="unbsb-toggle unbsb-toggle-sm">
										<input type="checkbox" name="unbsb_sms_on_cancellation" value="yes" <?php checked( $settings['unbsb_sms_on_cancellation'], 'yes' ); ?>>
										<span class="unbsb-toggle-slider"></span>
									</label>
								</div>

								<div class="unbsb-setting-item unbsb-setting-toggle">
									<div class="unbsb-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'Reminder', 'unbelievable-salon-booking' ); ?></span>
									</div>
									<label class="unbsb-toggle unbsb-toggle-sm">
										<input type="checkbox" name="unbsb_sms_reminder_enabled" value="yes" <?php checked( $settings['unbsb_sms_reminder_enabled'], 'yes' ); ?>>
										<span class="unbsb-toggle-slider"></span>
									</label>
								</div>
							</div>

							<div class="unbsb-setting-item" style="margin-top: 20px;">
								<label for="unbsb_sms_reminder_hours">
									<span class="setting-label"><?php esc_html_e( 'Reminder Time', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<select name="unbsb_sms_reminder_hours" id="unbsb_sms_reminder_hours" class="unbsb-select">
									<option value="1" <?php selected( $settings['unbsb_sms_reminder_hours'], 1 ); ?>><?php esc_html_e( '1 hour before', 'unbelievable-salon-booking' ); ?></option>
									<option value="2" <?php selected( $settings['unbsb_sms_reminder_hours'], 2 ); ?>><?php esc_html_e( '2 hours before', 'unbelievable-salon-booking' ); ?></option>
									<option value="3" <?php selected( $settings['unbsb_sms_reminder_hours'], 3 ); ?>><?php esc_html_e( '3 hours before', 'unbelievable-salon-booking' ); ?></option>
									<option value="6" <?php selected( $settings['unbsb_sms_reminder_hours'], 6 ); ?>><?php esc_html_e( '6 hours before', 'unbelievable-salon-booking' ); ?></option>
									<option value="12" <?php selected( $settings['unbsb_sms_reminder_hours'], 12 ); ?>><?php esc_html_e( '12 hours before', 'unbelievable-salon-booking' ); ?></option>
									<option value="24" <?php selected( $settings['unbsb_sms_reminder_hours'], 24 ); ?>><?php esc_html_e( '1 day before', 'unbelievable-salon-booking' ); ?></option>
									<option value="48" <?php selected( $settings['unbsb_sms_reminder_hours'], 48 ); ?>><?php esc_html_e( '2 days before', 'unbelievable-salon-booking' ); ?></option>
								</select>
							</div>
						</div>

						<?php if ( ! empty( $sms_templates ) ) : ?>
						<div class="unbsb-settings-subsection">
							<h3><?php esc_html_e( 'SMS Templates', 'unbelievable-salon-booking' ); ?></h3>
							<p class="unbsb-subsection-desc">
								<?php esc_html_e( 'Variables:', 'unbelievable-salon-booking' ); ?>
								<code>{customer_name}</code> <code>{service_name}</code> <code>{staff_name}</code> <code>{booking_date}</code> <code>{booking_time}</code> <code>{price}</code>
							</p>

							<div class="unbsb-sms-templates">
								<?php foreach ( $sms_templates as $template ) : ?>
								<div class="unbsb-sms-template-item">
									<div class="unbsb-template-header">
										<span class="unbsb-template-name"><?php echo esc_html( $template->name ); ?></span>
										<label class="unbsb-toggle unbsb-toggle-sm">
											<input type="checkbox" name="sms_template_active_<?php echo esc_attr( $template->id ); ?>" value="1" <?php checked( $template->is_active, 1 ); ?>>
											<span class="unbsb-toggle-slider"></span>
										</label>
									</div>
									<textarea name="sms_template_<?php echo esc_attr( $template->id ); ?>" rows="2" class="unbsb-textarea"><?php echo esc_textarea( $template->message ); ?></textarea>
								</div>
								<?php endforeach; ?>
							</div>

							<button type="button" class="unbsb-btn unbsb-btn-outline" id="unbsb-save-sms-templates">
								<span class="dashicons dashicons-saved"></span>
								<?php esc_html_e( 'Save Templates', 'unbelievable-salon-booking' ); ?>
							</button>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- SEO Tab -->
				<?php if ( 'seo' === $current_tab ) : ?>
				<div class="unbsb-settings-section active" id="tab-seo">
					<div class="unbsb-section-header">
						<span class="dashicons dashicons-search"></span>
						<div>
							<h2><?php esc_html_e( 'SEO Settings', 'unbelievable-salon-booking' ); ?></h2>
							<p><?php esc_html_e( 'Search engine optimization and social media settings.', 'unbelievable-salon-booking' ); ?></p>
						</div>
					</div>

					<div class="unbsb-settings-subsection">
						<h3><?php esc_html_e( 'Schema.org & Structured Data', 'unbelievable-salon-booking' ); ?></h3>
						<p class="unbsb-subsection-desc">
							<?php esc_html_e( 'Help Google and other search engines better understand your business.', 'unbelievable-salon-booking' ); ?>
						</p>

						<div class="unbsb-setting-item unbsb-setting-toggle">
							<div class="unbsb-toggle-content">
								<span class="setting-label"><?php esc_html_e( 'SEO Enabled', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-desc"><?php esc_html_e( 'Enable Schema.org and Open Graph tags.', 'unbelievable-salon-booking' ); ?></span>
							</div>
							<label class="unbsb-toggle">
								<input type="checkbox" name="unbsb_seo_enabled" value="yes" <?php checked( $settings['unbsb_seo_enabled'], 'yes' ); ?>>
								<span class="unbsb-toggle-slider"></span>
							</label>
						</div>

						<div class="unbsb-settings-grid">
							<div class="unbsb-setting-item">
								<label for="unbsb_seo_business_type">
									<span class="setting-label"><?php esc_html_e( 'Business Type', 'unbelievable-salon-booking' ); ?></span>
									<span class="setting-desc"><?php esc_html_e( 'Schema.org business category.', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<select name="unbsb_seo_business_type" id="unbsb_seo_business_type" class="unbsb-select">
									<option value="BeautySalon" <?php selected( $settings['unbsb_seo_business_type'], 'BeautySalon' ); ?>><?php esc_html_e( 'Beauty Salon', 'unbelievable-salon-booking' ); ?></option>
									<option value="HairSalon" <?php selected( $settings['unbsb_seo_business_type'], 'HairSalon' ); ?>><?php esc_html_e( 'Hair Salon / Barber', 'unbelievable-salon-booking' ); ?></option>
									<option value="NailSalon" <?php selected( $settings['unbsb_seo_business_type'], 'NailSalon' ); ?>><?php esc_html_e( 'Nail Salon', 'unbelievable-salon-booking' ); ?></option>
									<option value="DaySpa" <?php selected( $settings['unbsb_seo_business_type'], 'DaySpa' ); ?>><?php esc_html_e( 'Spa', 'unbelievable-salon-booking' ); ?></option>
									<option value="HealthAndBeautyBusiness" <?php selected( $settings['unbsb_seo_business_type'], 'HealthAndBeautyBusiness' ); ?>><?php esc_html_e( 'Health & Beauty', 'unbelievable-salon-booking' ); ?></option>
									<option value="Dentist" <?php selected( $settings['unbsb_seo_business_type'], 'Dentist' ); ?>><?php esc_html_e( 'Dental Clinic', 'unbelievable-salon-booking' ); ?></option>
									<option value="Physician" <?php selected( $settings['unbsb_seo_business_type'], 'Physician' ); ?>><?php esc_html_e( 'Physician', 'unbelievable-salon-booking' ); ?></option>
									<option value="Optician" <?php selected( $settings['unbsb_seo_business_type'], 'Optician' ); ?>><?php esc_html_e( 'Optician', 'unbelievable-salon-booking' ); ?></option>
									<option value="TattooParlor" <?php selected( $settings['unbsb_seo_business_type'], 'TattooParlor' ); ?>><?php esc_html_e( 'Tattoo Parlor', 'unbelievable-salon-booking' ); ?></option>
									<option value="LocalBusiness" <?php selected( $settings['unbsb_seo_business_type'], 'LocalBusiness' ); ?>><?php esc_html_e( 'General Business', 'unbelievable-salon-booking' ); ?></option>
								</select>
							</div>

							<div class="unbsb-setting-item">
								<label for="unbsb_seo_price_range">
									<span class="setting-label"><?php esc_html_e( 'Price Range', 'unbelievable-salon-booking' ); ?></span>
									<span class="setting-desc"><?php esc_html_e( 'Your general price level.', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<select name="unbsb_seo_price_range" id="unbsb_seo_price_range" class="unbsb-select">
									<option value="₺" <?php selected( $settings['unbsb_seo_price_range'], '₺' ); ?>><?php esc_html_e( '$ - Budget', 'unbelievable-salon-booking' ); ?></option>
									<option value="₺₺" <?php selected( $settings['unbsb_seo_price_range'], '₺₺' ); ?>><?php esc_html_e( '$$ - Moderate', 'unbelievable-salon-booking' ); ?></option>
									<option value="₺₺₺" <?php selected( $settings['unbsb_seo_price_range'], '₺₺₺' ); ?>><?php esc_html_e( '$$$ - High', 'unbelievable-salon-booking' ); ?></option>
									<option value="₺₺₺₺" <?php selected( $settings['unbsb_seo_price_range'], '₺₺₺₺' ); ?>><?php esc_html_e( '$$$$ - Luxury', 'unbelievable-salon-booking' ); ?></option>
								</select>
							</div>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_seo_description">
								<span class="setting-label"><?php esc_html_e( 'SEO Description', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-desc"><?php esc_html_e( 'Description displayed in social media shares.', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<textarea name="unbsb_seo_description" id="unbsb_seo_description" rows="2" class="unbsb-textarea" placeholder="<?php esc_attr_e( 'Book an appointment online - quick and easy!', 'unbelievable-salon-booking' ); ?>"><?php echo esc_textarea( $settings['unbsb_seo_description'] ); ?></textarea>
						</div>

						<div class="unbsb-setting-item">
							<label for="unbsb_seo_logo_url">
								<span class="setting-label"><?php esc_html_e( 'Logo URL', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-desc"><?php esc_html_e( 'Logo displayed in social media and search results.', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<input type="url" name="unbsb_seo_logo_url" id="unbsb_seo_logo_url" class="unbsb-input" value="<?php echo esc_url( $settings['unbsb_seo_logo_url'] ); ?>" placeholder="https://example.com/logo.png">
						</div>
					</div>

					<div class="unbsb-settings-subsection">
						<h3><?php esc_html_e( 'Location Information', 'unbelievable-salon-booking' ); ?></h3>
						<p class="unbsb-subsection-desc">
							<?php esc_html_e( 'Enter your location details for local SEO.', 'unbelievable-salon-booking' ); ?>
						</p>

						<div class="unbsb-settings-grid unbsb-grid-3">
							<div class="unbsb-setting-item">
								<label for="unbsb_seo_city">
									<span class="setting-label"><?php esc_html_e( 'City', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<input type="text" name="unbsb_seo_city" id="unbsb_seo_city" class="unbsb-input" value="<?php echo esc_attr( $settings['unbsb_seo_city'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. New York', 'unbelievable-salon-booking' ); ?>">
							</div>

							<div class="unbsb-setting-item">
								<label for="unbsb_seo_postal_code">
									<span class="setting-label"><?php esc_html_e( 'Postal Code', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<input type="text" name="unbsb_seo_postal_code" id="unbsb_seo_postal_code" class="unbsb-input" value="<?php echo esc_attr( $settings['unbsb_seo_postal_code'] ); ?>" placeholder="34000">
							</div>

							<div class="unbsb-setting-item">
								<label for="unbsb_seo_country">
									<span class="setting-label"><?php esc_html_e( 'Country Code', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<input type="text" name="unbsb_seo_country" id="unbsb_seo_country" class="unbsb-input" value="<?php echo esc_attr( $settings['unbsb_seo_country'] ); ?>" placeholder="TR" maxlength="2">
							</div>
						</div>
					</div>

					<div class="unbsb-settings-subsection">
						<h3><?php esc_html_e( 'Social Media', 'unbelievable-salon-booking' ); ?></h3>
						<p class="unbsb-subsection-desc">
							<?php esc_html_e( 'Connect your social media accounts.', 'unbelievable-salon-booking' ); ?>
						</p>

						<div class="unbsb-settings-grid">
							<div class="unbsb-setting-item">
								<label for="unbsb_social_facebook">
									<span class="setting-label">
										<span class="dashicons dashicons-facebook-alt" style="color: #1877f2;"></span>
										<?php esc_html_e( 'Facebook', 'unbelievable-salon-booking' ); ?>
									</span>
								</label>
								<input type="url" name="unbsb_social_facebook" id="unbsb_social_facebook" class="unbsb-input" value="<?php echo esc_url( $settings['unbsb_social_facebook'] ); ?>" placeholder="https://facebook.com/yourbusiness">
							</div>

							<div class="unbsb-setting-item">
								<label for="unbsb_social_instagram">
									<span class="setting-label">
										<span class="dashicons dashicons-instagram" style="color: #e4405f;"></span>
										<?php esc_html_e( 'Instagram', 'unbelievable-salon-booking' ); ?>
									</span>
								</label>
								<input type="url" name="unbsb_social_instagram" id="unbsb_social_instagram" class="unbsb-input" value="<?php echo esc_url( $settings['unbsb_social_instagram'] ); ?>" placeholder="https://instagram.com/yourbusiness">
							</div>
						</div>

						<div class="unbsb-settings-grid">
							<div class="unbsb-setting-item">
								<label for="unbsb_social_twitter">
									<span class="setting-label">
										<span class="dashicons dashicons-twitter" style="color: #1da1f2;"></span>
										<?php esc_html_e( 'Twitter/X URL', 'unbelievable-salon-booking' ); ?>
									</span>
								</label>
								<input type="url" name="unbsb_social_twitter" id="unbsb_social_twitter" class="unbsb-input" value="<?php echo esc_url( $settings['unbsb_social_twitter'] ); ?>" placeholder="https://twitter.com/yourbusiness">
							</div>

							<div class="unbsb-setting-item">
								<label for="unbsb_social_twitter_handle">
									<span class="setting-label">
										<?php esc_html_e( 'Twitter Username', 'unbelievable-salon-booking' ); ?>
									</span>
									<span class="setting-desc"><?php esc_html_e( 'Enter without @.', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<input type="text" name="unbsb_social_twitter_handle" id="unbsb_social_twitter_handle" class="unbsb-input" value="<?php echo esc_attr( $settings['unbsb_social_twitter_handle'] ); ?>" placeholder="yourbusiness">
							</div>
						</div>
					</div>

					<div class="unbsb-info-box">
						<span class="dashicons dashicons-info"></span>
						<div>
							<p><strong><?php esc_html_e( 'How Does SEO Work?', 'unbelievable-salon-booking' ); ?></strong></p>
							<p><?php esc_html_e( 'These settings add Schema.org structured data and Open Graph tags to your booking page. Google uses this information to display your business better in search results.', 'unbelievable-salon-booking' ); ?></p>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Security Tab -->
				<?php if ( 'security' === $current_tab ) : ?>
				<div class="unbsb-settings-section active" id="tab-security">
					<div class="unbsb-section-header">
						<span class="dashicons dashicons-shield"></span>
						<div>
							<h2><?php esc_html_e( 'Security Settings', 'unbelievable-salon-booking' ); ?></h2>
							<p><?php esc_html_e( 'CAPTCHA and security logging settings.', 'unbelievable-salon-booking' ); ?></p>
						</div>
					</div>

					<div class="unbsb-settings-subsection">
						<h3><?php esc_html_e( 'CAPTCHA Protection', 'unbelievable-salon-booking' ); ?></h3>
						<p class="unbsb-subsection-desc">
							<?php esc_html_e( 'Protect the booking form from spam and bot attacks.', 'unbelievable-salon-booking' ); ?>
						</p>

						<div class="unbsb-setting-item">
							<label for="unbsb_captcha_provider">
								<span class="setting-label"><?php esc_html_e( 'CAPTCHA Provider', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-desc"><?php esc_html_e( 'Select the CAPTCHA service you want to use.', 'unbelievable-salon-booking' ); ?></span>
							</label>
							<select name="unbsb_captcha_provider" id="unbsb_captcha_provider" class="unbsb-select">
								<option value="none" <?php selected( $settings['unbsb_captcha_provider'], 'none' ); ?>><?php esc_html_e( 'Disabled', 'unbelievable-salon-booking' ); ?></option>
								<option value="recaptcha" <?php selected( $settings['unbsb_captcha_provider'], 'recaptcha' ); ?>><?php esc_html_e( 'Google reCAPTCHA v3', 'unbelievable-salon-booking' ); ?></option>
								<option value="hcaptcha" <?php selected( $settings['unbsb_captcha_provider'], 'hcaptcha' ); ?>><?php esc_html_e( 'hCaptcha', 'unbelievable-salon-booking' ); ?></option>
							</select>
						</div>

						<div id="unbsb-captcha-settings" style="<?php echo 'none' === $settings['unbsb_captcha_provider'] ? 'display:none;' : ''; ?>">
							<div class="unbsb-settings-grid">
								<div class="unbsb-setting-item">
									<label for="unbsb_captcha_site_key">
										<span class="setting-label"><?php esc_html_e( 'Site Key', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<input type="text" name="unbsb_captcha_site_key" id="unbsb_captcha_site_key" class="unbsb-input" value="<?php echo esc_attr( $settings['unbsb_captcha_site_key'] ); ?>" placeholder="6Lc...">
								</div>

								<div class="unbsb-setting-item">
									<label for="unbsb_captcha_secret_key">
										<span class="setting-label"><?php esc_html_e( 'Secret Key', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<input type="password" name="unbsb_captcha_secret_key" id="unbsb_captcha_secret_key" class="unbsb-input" value="<?php echo esc_attr( $settings['unbsb_captcha_secret_key'] ); ?>" placeholder="6Lc...">
								</div>
							</div>

							<div class="unbsb-setting-item" id="unbsb-recaptcha-score" style="<?php echo 'recaptcha' !== $settings['unbsb_captcha_provider'] ? 'display:none;' : ''; ?>">
								<label for="unbsb_captcha_min_score">
									<span class="setting-label"><?php esc_html_e( 'Minimum Score (reCAPTCHA v3)', 'unbelievable-salon-booking' ); ?></span>
									<span class="setting-desc"><?php esc_html_e( 'Range 0.0 - 1.0. Lower score = stricter control.', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<select name="unbsb_captcha_min_score" id="unbsb_captcha_min_score" class="unbsb-select">
									<option value="0.3" <?php selected( $settings['unbsb_captcha_min_score'], '0.3' ); ?>>0.3 - <?php esc_html_e( 'Very Lenient', 'unbelievable-salon-booking' ); ?></option>
									<option value="0.5" <?php selected( $settings['unbsb_captcha_min_score'], '0.5' ); ?>>0.5 - <?php esc_html_e( 'Normal (Recommended)', 'unbelievable-salon-booking' ); ?></option>
									<option value="0.7" <?php selected( $settings['unbsb_captcha_min_score'], '0.7' ); ?>>0.7 - <?php esc_html_e( 'Strict', 'unbelievable-salon-booking' ); ?></option>
									<option value="0.9" <?php selected( $settings['unbsb_captcha_min_score'], '0.9' ); ?>>0.9 - <?php esc_html_e( 'Very Strict', 'unbelievable-salon-booking' ); ?></option>
								</select>
							</div>
						</div>

						<div class="unbsb-info-box">
							<span class="dashicons dashicons-info"></span>
							<div>
								<p><strong><?php esc_html_e( 'Where to Get CAPTCHA Keys?', 'unbelievable-salon-booking' ); ?></strong></p>
								<p>
									<strong>reCAPTCHA v3:</strong> <a href="https://www.google.com/recaptcha/admin" target="_blank">google.com/recaptcha/admin</a><br>
									<strong>hCaptcha:</strong> <a href="https://dashboard.hcaptcha.com/signup" target="_blank">dashboard.hcaptcha.com</a>
								</p>
							</div>
						</div>
					</div>

					<div class="unbsb-settings-subsection">
						<h3><?php esc_html_e( 'Security Logging', 'unbelievable-salon-booking' ); ?></h3>
						<p class="unbsb-subsection-desc">
							<?php esc_html_e( 'Record and monitor security events.', 'unbelievable-salon-booking' ); ?>
						</p>

						<div class="unbsb-setting-item unbsb-setting-toggle">
							<div class="unbsb-toggle-content">
								<span class="setting-label"><?php esc_html_e( 'Enable Security Logging', 'unbelievable-salon-booking' ); ?></span>
								<span class="setting-desc"><?php esc_html_e( 'Log rate limit, spam, and suspicious activities.', 'unbelievable-salon-booking' ); ?></span>
							</div>
							<label class="unbsb-toggle">
								<input type="checkbox" name="unbsb_security_logging_enabled" value="yes" <?php checked( $settings['unbsb_security_logging_enabled'], 'yes' ); ?>>
								<span class="unbsb-toggle-slider"></span>
							</label>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Save Button -->
				<div class="unbsb-settings-footer">
					<button type="submit" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-save-settings">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Changes', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
