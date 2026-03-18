<?php
/**
 * Admin Email Templates Page
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get email templates.
$notification    = new UNBSB_Notification();
$email_templates = $notification->get_all_templates();

// Settings.
$settings = array(
	'unbsb_email_reminder_enabled' => get_option( 'unbsb_email_reminder_enabled', 'yes' ),
	'unbsb_email_reminder_hours'   => get_option( 'unbsb_email_reminder_hours', 24 ),
	'unbsb_email_logo_url'         => get_option( 'unbsb_email_logo_url', '' ),
	'unbsb_email_primary_color'    => get_option( 'unbsb_email_primary_color', '#3b82f6' ),
);

// Template types and icons.
$template_icons = array(
	'booking_received'  => 'dashicons-email-alt',
	'booking_confirmed' => 'dashicons-yes-alt',
	'booking_cancelled' => 'dashicons-dismiss',
	'booking_reminder'  => 'dashicons-bell',
	'admin_new_booking' => 'dashicons-admin-users',
);

$template_colors = array(
	'booking_received'  => '#3b82f6',
	'booking_confirmed' => '#10b981',
	'booking_cancelled' => '#ef4444',
	'booking_reminder'  => '#f59e0b',
	'admin_new_booking' => '#8b5cf6',
);

$template_descriptions = array(
	'booking_received'  => __( 'Sent when a customer creates a booking request.', 'unbelievable-salon-booking' ),
	'booking_confirmed' => __( 'Sent when a booking is confirmed by admin.', 'unbelievable-salon-booking' ),
	'booking_cancelled' => __( 'Sent when a booking is cancelled.', 'unbelievable-salon-booking' ),
	'booking_reminder'  => __( 'Sent as a reminder before the booking.', 'unbelievable-salon-booking' ),
	'admin_new_booking' => __( 'Sent to admin when a new booking arrives.', 'unbelievable-salon-booking' ),
);
?>

<div class="unbsb-email-templates-page">
	<!-- Header -->
	<div class="unbsb-settings-header">
		<div class="unbsb-settings-header-content">
			<div class="unbsb-settings-header-icon">
				<span class="dashicons dashicons-email-alt"></span>
			</div>
			<div class="unbsb-settings-header-text">
				<h1><?php esc_html_e( 'Email Templates', 'unbelievable-salon-booking' ); ?></h1>
				<p><?php esc_html_e( 'Customize emails sent to customers and administrators.', 'unbelievable-salon-booking' ); ?></p>
			</div>
		</div>
	</div>

	<div class="unbsb-email-templates-layout">
		<!-- Left Panel: Template List -->
		<div class="unbsb-email-sidebar">
			<div class="unbsb-card">
				<div class="unbsb-card-header">
					<h3><?php esc_html_e( 'Templates', 'unbelievable-salon-booking' ); ?></h3>
				</div>
				<div class="unbsb-card-body" style="padding: 0;">
					<ul class="unbsb-template-list">
						<?php foreach ( $email_templates as $index => $template ) : ?>
						<li class="unbsb-template-item<?php echo 0 === $index ? ' active' : ''; ?>"
							data-template-id="<?php echo esc_attr( $template->id ); ?>"
							data-template-type="<?php echo esc_attr( $template->type ); ?>">
							<div class="unbsb-template-item-icon" style="background-color: <?php echo esc_attr( $template_colors[ $template->type ] ?? '#6b7280' ); ?>">
								<span class="dashicons <?php echo esc_attr( $template_icons[ $template->type ] ?? 'dashicons-email' ); ?>"></span>
							</div>
							<div class="unbsb-template-item-info">
								<span class="unbsb-template-item-name"><?php echo esc_html( $template->name ); ?></span>
								<span class="unbsb-template-item-status <?php echo $template->is_active ? 'active' : 'inactive'; ?>">
									<?php echo $template->is_active ? esc_html__( 'Active', 'unbelievable-salon-booking' ) : esc_html__( 'Inactive', 'unbelievable-salon-booking' ); ?>
								</span>
							</div>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<!-- General Settings -->
			<div class="unbsb-card" style="margin-top: 20px;">
				<div class="unbsb-card-header">
					<h3><?php esc_html_e( 'Email Settings', 'unbelievable-salon-booking' ); ?></h3>
				</div>
				<div class="unbsb-card-body">
					<div class="unbsb-form-group">
						<label for="unbsb_email_logo_url"><?php esc_html_e( 'Logo URL', 'unbelievable-salon-booking' ); ?></label>
						<input type="url" name="unbsb_email_logo_url" id="unbsb_email_logo_url" value="<?php echo esc_url( $settings['unbsb_email_logo_url'] ); ?>" placeholder="https://...">
						<small class="unbsb-help-text"><?php esc_html_e( 'If left empty, business name will be shown', 'unbelievable-salon-booking' ); ?></small>
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb_email_primary_color"><?php esc_html_e( 'Primary Color', 'unbelievable-salon-booking' ); ?></label>
						<div style="display: flex; gap: 10px; align-items: center;">
							<input type="color" name="unbsb_email_primary_color" id="unbsb_email_primary_color" value="<?php echo esc_attr( $settings['unbsb_email_primary_color'] ); ?>" style="width: 50px; height: 38px;">
							<input type="text" id="unbsb_email_primary_color_text" value="<?php echo esc_attr( $settings['unbsb_email_primary_color'] ); ?>" style="width: 100px; font-family: monospace;">
						</div>
					</div>

					<hr style="margin: 15px 0;">

					<div class="unbsb-form-group">
						<label class="unbsb-checkbox-label">
							<input type="checkbox" name="unbsb_email_reminder_enabled" id="unbsb_email_reminder_enabled" value="yes" <?php checked( $settings['unbsb_email_reminder_enabled'], 'yes' ); ?>>
							<?php esc_html_e( 'Send Reminder Email', 'unbelievable-salon-booking' ); ?>
						</label>
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb_email_reminder_hours"><?php esc_html_e( 'Reminder Time', 'unbelievable-salon-booking' ); ?></label>
						<select name="unbsb_email_reminder_hours" id="unbsb_email_reminder_hours">
							<option value="1" <?php selected( $settings['unbsb_email_reminder_hours'], 1 ); ?>>1 <?php esc_html_e( 'hours before', 'unbelievable-salon-booking' ); ?></option>
							<option value="2" <?php selected( $settings['unbsb_email_reminder_hours'], 2 ); ?>>2 <?php esc_html_e( 'hours before', 'unbelievable-salon-booking' ); ?></option>
							<option value="6" <?php selected( $settings['unbsb_email_reminder_hours'], 6 ); ?>>6 <?php esc_html_e( 'hours before', 'unbelievable-salon-booking' ); ?></option>
							<option value="12" <?php selected( $settings['unbsb_email_reminder_hours'], 12 ); ?>>12 <?php esc_html_e( 'hours before', 'unbelievable-salon-booking' ); ?></option>
							<option value="24" <?php selected( $settings['unbsb_email_reminder_hours'], 24 ); ?>>1 <?php esc_html_e( 'days before', 'unbelievable-salon-booking' ); ?></option>
							<option value="48" <?php selected( $settings['unbsb_email_reminder_hours'], 48 ); ?>>2 <?php esc_html_e( 'days before', 'unbelievable-salon-booking' ); ?></option>
						</select>
					</div>

					<button type="button" class="unbsb-btn unbsb-btn-secondary unbsb-btn-block" id="unbsb-save-email-settings">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Settings', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Right Panel: Template Editor -->
		<div class="unbsb-email-editor">
			<?php foreach ( $email_templates as $index => $template ) : ?>
			<div class="unbsb-template-editor<?php echo 0 === $index ? ' active' : ''; ?>" id="editor-<?php echo esc_attr( $template->id ); ?>">
				<div class="unbsb-card">
					<div class="unbsb-card-header" style="display: flex; justify-content: space-between; align-items: center;">
						<div>
							<h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
								<span class="dashicons <?php echo esc_attr( $template_icons[ $template->type ] ?? 'dashicons-email' ); ?>" style="color: <?php echo esc_attr( $template_colors[ $template->type ] ?? '#6b7280' ); ?>"></span>
								<?php echo esc_html( $template->name ); ?>
							</h3>
							<p class="unbsb-help-text" style="margin: 5px 0 0 0;"><?php echo esc_html( $template_descriptions[ $template->type ] ?? '' ); ?></p>
						</div>
						<div style="display: flex; gap: 10px; align-items: center;">
							<label class="unbsb-switch">
								<input type="checkbox" class="unbsb-template-active" data-template-id="<?php echo esc_attr( $template->id ); ?>" <?php checked( $template->is_active, 1 ); ?>>
								<span class="unbsb-switch-slider"></span>
							</label>
							<span class="unbsb-switch-label"><?php esc_html_e( 'Active', 'unbelievable-salon-booking' ); ?></span>
						</div>
					</div>
					<div class="unbsb-card-body">
						<!-- Subject -->
						<div class="unbsb-form-group">
							<label><?php esc_html_e( 'Email Subject', 'unbelievable-salon-booking' ); ?></label>
							<input type="text" class="unbsb-template-subject" data-template-id="<?php echo esc_attr( $template->id ); ?>" value="<?php echo esc_attr( $template->subject ); ?>">
						</div>

						<!-- Editor Toolbar -->
						<div class="unbsb-form-group">
							<label><?php esc_html_e( 'Email Content', 'unbelievable-salon-booking' ); ?></label>
							<div class="unbsb-editor-wrapper">
								<div class="unbsb-editor-toolbar">
									<div class="unbsb-toolbar-group">
										<button type="button" class="unbsb-toolbar-btn" data-command="bold" title="<?php esc_attr_e( 'Bold', 'unbelievable-salon-booking' ); ?>">
											<strong>B</strong>
										</button>
										<button type="button" class="unbsb-toolbar-btn" data-command="italic" title="<?php esc_attr_e( 'Italic', 'unbelievable-salon-booking' ); ?>">
											<em>I</em>
										</button>
										<button type="button" class="unbsb-toolbar-btn" data-command="link" title="<?php esc_attr_e( 'Link', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-admin-links"></span>
										</button>
									</div>
									<div class="unbsb-toolbar-separator"></div>
									<div class="unbsb-toolbar-group">
										<button type="button" class="unbsb-toolbar-btn" data-command="h3" title="<?php esc_attr_e( 'Heading', 'unbelievable-salon-booking' ); ?>">
											H3
										</button>
										<button type="button" class="unbsb-toolbar-btn" data-command="p" title="<?php esc_attr_e( 'Paragraph', 'unbelievable-salon-booking' ); ?>">
											P
										</button>
									</div>
									<div class="unbsb-toolbar-separator"></div>
									<div class="unbsb-toolbar-group">
										<button type="button" class="unbsb-toolbar-btn" data-command="table" title="<?php esc_attr_e( 'Details Table', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-editor-table"></span>
										</button>
										<button type="button" class="unbsb-toolbar-btn" data-command="button" title="<?php esc_attr_e( 'Action Button', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-button"></span>
										</button>
									</div>
									<div class="unbsb-toolbar-separator"></div>
									<div class="unbsb-toolbar-group">
										<select class="unbsb-insert-variable">
											<option value=""><?php esc_html_e( 'Insert Variable', 'unbelievable-salon-booking' ); ?></option>
											<optgroup label="<?php esc_attr_e( 'Customer', 'unbelievable-salon-booking' ); ?>">
												<option value="{customer_name}"><?php esc_html_e( 'Customer Name', 'unbelievable-salon-booking' ); ?></option>
												<option value="{customer_email}"><?php esc_html_e( 'Customer Email', 'unbelievable-salon-booking' ); ?></option>
												<option value="{customer_phone}"><?php esc_html_e( 'Customer Phone', 'unbelievable-salon-booking' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Booking', 'unbelievable-salon-booking' ); ?>">
												<option value="{services_list}"><?php esc_html_e( 'Services', 'unbelievable-salon-booking' ); ?></option>
												<option value="{staff_name}"><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></option>
												<option value="{booking_date}"><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?></option>
												<option value="{booking_time}"><?php esc_html_e( 'Time', 'unbelievable-salon-booking' ); ?></option>
												<option value="{total_duration}"><?php esc_html_e( 'Duration', 'unbelievable-salon-booking' ); ?></option>
												<option value="{price}"><?php esc_html_e( 'Price', 'unbelievable-salon-booking' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Reschedule', 'unbelievable-salon-booking' ); ?>">
												<option value="{old_booking_date}"><?php esc_html_e( 'Old Date (reschedule)', 'unbelievable-salon-booking' ); ?></option>
												<option value="{old_booking_time}"><?php esc_html_e( 'Old Time (reschedule)', 'unbelievable-salon-booking' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Business', 'unbelievable-salon-booking' ); ?>">
												<option value="{company_name}"><?php esc_html_e( 'Business Name', 'unbelievable-salon-booking' ); ?></option>
												<option value="{company_phone}"><?php esc_html_e( 'Business Phone', 'unbelievable-salon-booking' ); ?></option>
												<option value="{company_address}"><?php esc_html_e( 'Business Address', 'unbelievable-salon-booking' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Links', 'unbelievable-salon-booking' ); ?>">
												<option value="{manage_booking_url}"><?php esc_html_e( 'Booking Management Link', 'unbelievable-salon-booking' ); ?></option>
												<option value="{calendar_links}"><?php esc_html_e( 'Calendar Links', 'unbelievable-salon-booking' ); ?></option>
											</optgroup>
										</select>
									</div>
								</div>
								<textarea class="unbsb-template-content" data-template-id="<?php echo esc_attr( $template->id ); ?>" rows="15"><?php echo esc_textarea( $template->content ); ?></textarea>
							</div>
						</div>

						<!-- Action Buttons -->
						<div class="unbsb-editor-actions">
							<div class="unbsb-editor-actions-left">
								<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-save-template" data-template-id="<?php echo esc_attr( $template->id ); ?>">
									<span class="dashicons dashicons-saved"></span>
									<?php esc_html_e( 'Save Template', 'unbelievable-salon-booking' ); ?>
								</button>
								<button type="button" class="unbsb-btn unbsb-btn-secondary unbsb-preview-template" data-template-type="<?php echo esc_attr( $template->type ); ?>">
									<span class="dashicons dashicons-visibility"></span>
									<?php esc_html_e( 'Preview', 'unbelievable-salon-booking' ); ?>
								</button>
							</div>
							<div class="unbsb-editor-actions-right">
								<input type="email" class="unbsb-test-email-input" placeholder="test@example.com">
								<button type="button" class="unbsb-btn unbsb-btn-outline unbsb-send-test-email" data-template-type="<?php echo esc_attr( $template->type ); ?>">
									<span class="dashicons dashicons-email"></span>
									<?php esc_html_e( 'Send Test', 'unbelievable-salon-booking' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Variables Help Card -->
				<div class="unbsb-card unbsb-variables-help" style="margin-top: 20px;">
					<div class="unbsb-card-header">
						<h3><?php esc_html_e( 'Available Variables', 'unbelievable-salon-booking' ); ?></h3>
					</div>
					<div class="unbsb-card-body">
						<div class="unbsb-variables-grid">
							<div class="unbsb-variable-group">
								<h4><?php esc_html_e( 'Customer Information', 'unbelievable-salon-booking' ); ?></h4>
								<code>{customer_name}</code>
								<code>{customer_email}</code>
								<code>{customer_phone}</code>
							</div>
							<div class="unbsb-variable-group">
								<h4><?php esc_html_e( 'Booking Details', 'unbelievable-salon-booking' ); ?></h4>
								<code>{services_list}</code>
								<code>{staff_name}</code>
								<code>{booking_date}</code>
								<code>{booking_time}</code>
								<code>{total_duration}</code>
								<code>{price}</code>
							</div>
							<div class="unbsb-variable-group">
								<h4><?php esc_html_e( 'Business Information', 'unbelievable-salon-booking' ); ?></h4>
								<code>{company_name}</code>
								<code>{company_phone}</code>
								<code>{company_address}</code>
							</div>
							<div class="unbsb-variable-group">
								<h4><?php esc_html_e( 'Reschedule', 'unbelievable-salon-booking' ); ?></h4>
								<code>{old_booking_date}</code>
								<code>{old_booking_time}</code>
							</div>
							<div class="unbsb-variable-group">
								<h4><?php esc_html_e( 'Special', 'unbelievable-salon-booking' ); ?></h4>
								<code>{manage_booking_url}</code>
								<code>{calendar_links}</code>
								<code>{admin_url}</code>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<!-- Email Preview Modal -->
<div class="unbsb-modal" id="unbsb-email-preview-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-lg" style="max-width: 750px;">
		<div class="unbsb-modal-header">
			<h3><?php esc_html_e( 'Email Preview', 'unbelievable-salon-booking' ); ?></h3>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body" style="padding: 0;">
			<div class="unbsb-preview-device-bar">
				<button type="button" class="unbsb-device-btn active" data-width="100%">
					<span class="dashicons dashicons-desktop"></span>
				</button>
				<button type="button" class="unbsb-device-btn" data-width="375px">
					<span class="dashicons dashicons-smartphone"></span>
				</button>
			</div>
			<div class="unbsb-preview-container">
				<iframe id="unbsb-email-preview-frame"></iframe>
			</div>
		</div>
	</div>
</div>
