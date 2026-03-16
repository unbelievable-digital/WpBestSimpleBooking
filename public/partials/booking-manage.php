<?php
/**
 * Booking Management Template - Customer booking management
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Token check.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not required for public booking management via token.
$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

if ( empty( $token ) ) {
	echo '<div class="unbsb-manage-error">';
	echo '<p>' . esc_html__( 'Invalid booking link.', 'unbelievable-salon-booking' ) . '</p>';
	echo '</div>';
	return;
}

// Load Booking Manager.
if ( ! class_exists( 'UNBSB_Booking_Manager' ) ) {
	require_once UNBSB_PLUGIN_DIR . 'includes/class-unbsb-booking-manager.php';
}

$booking_manager = new UNBSB_Booking_Manager();
$booking_model   = new UNBSB_Booking();

// Randevuyu getir.
$booking = $booking_model->get_by_token( $token );

if ( ! $booking ) {
	echo '<div class="unbsb-manage-error">';
	echo '<p>' . esc_html__( 'Booking not found.', 'unbelievable-salon-booking' ) . '</p>';
	echo '</div>';
	return;
}

// Get detailed information.
$booking = $booking_model->get_with_details( $booking->id );

// Get settings.
$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );
$time_format     = get_option( 'unbsb_time_format', 'H:i' );
$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );

// Check cancel and reschedule permissions.
$can_cancel     = $booking_manager->can_cancel( $booking );
$can_reschedule = $booking_manager->can_reschedule( $booking );

// Durum renkleri.
$status_colors = array(
	'pending'   => '#f59e0b',
	'confirmed' => '#10b981',
	'cancelled' => '#ef4444',
	'completed' => '#6b7280',
	'no_show'   => '#8b5cf6',
);

$status_labels = array(
	'pending'   => __( 'Pending', 'unbelievable-salon-booking' ),
	'confirmed' => __( 'Confirmed', 'unbelievable-salon-booking' ),
	'cancelled' => __( 'Cancelled', 'unbelievable-salon-booking' ),
	'completed' => __( 'Completed', 'unbelievable-salon-booking' ),
	'no_show'   => __( 'No Show', 'unbelievable-salon-booking' ),
);

$formatted_date = date_i18n( $date_format, strtotime( $booking->booking_date ) );
$formatted_time = date_i18n( $time_format, strtotime( $booking->start_time ) );
?>

<div class="unbsb-manage-booking" data-token="<?php echo esc_attr( $token ); ?>">
	<div class="unbsb-manage-header">
		<h2><?php esc_html_e( 'Booking Details', 'unbelievable-salon-booking' ); ?></h2>
		<p class="unbsb-company-name"><?php echo esc_html( $company_name ); ?></p>
	</div>

	<!-- Status Card -->
	<div class="unbsb-status-card" style="border-left-color: <?php echo esc_attr( $status_colors[ $booking->status ] ?? '#6b7280' ); ?>;">
		<div class="unbsb-status-badge" style="background-color: <?php echo esc_attr( $status_colors[ $booking->status ] ?? '#6b7280' ); ?>;">
			<?php echo esc_html( $status_labels[ $booking->status ] ?? $booking->status ); ?>
		</div>
		<div class="unbsb-booking-id">
			<?php
			/* translators: %d: Booking ID */
			printf( esc_html__( 'Booking #%d', 'unbelievable-salon-booking' ), (int) $booking->id );
			?>
		</div>
	</div>

	<!-- Randevu Bilgileri -->
	<div class="unbsb-manage-card">
		<div class="unbsb-info-row">
			<span class="unbsb-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
					<line x1="16" y1="2" x2="16" y2="6"></line>
					<line x1="8" y1="2" x2="8" y2="6"></line>
					<line x1="3" y1="10" x2="21" y2="10"></line>
				</svg>
			</span>
			<div class="unbsb-info-content">
				<span class="unbsb-info-label"><?php esc_html_e( 'Date and Time', 'unbelievable-salon-booking' ); ?></span>
				<span class="unbsb-info-value"><?php echo esc_html( $formatted_date . ' - ' . $formatted_time ); ?></span>
			</div>
		</div>

		<div class="unbsb-info-row">
			<span class="unbsb-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
					<line x1="7" y1="7" x2="7.01" y2="7"></line>
				</svg>
			</span>
			<div class="unbsb-info-content">
				<span class="unbsb-info-label"><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></span>
				<span class="unbsb-info-value"><?php echo esc_html( $booking->service_name ); ?></span>
			</div>
		</div>

		<div class="unbsb-info-row">
			<span class="unbsb-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
					<circle cx="12" cy="7" r="4"></circle>
				</svg>
			</span>
			<div class="unbsb-info-content">
				<span class="unbsb-info-label"><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></span>
				<span class="unbsb-info-value"><?php echo esc_html( $booking->staff_name ); ?></span>
			</div>
		</div>

		<div class="unbsb-info-row">
			<span class="unbsb-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<line x1="12" y1="1" x2="12" y2="23"></line>
					<path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
				</svg>
			</span>
			<div class="unbsb-info-content">
				<span class="unbsb-info-label"><?php esc_html_e( 'Price', 'unbelievable-salon-booking' ); ?></span>
				<span class="unbsb-info-value"><?php echo esc_html( $booking->price . ' ' . $currency_symbol ); ?></span>
			</div>
		</div>
	</div>

	<?php if ( 'cancelled' !== $booking->status && 'completed' !== $booking->status ) : ?>
	<!-- Action Buttons -->
	<div class="unbsb-manage-actions">
		<?php if ( $can_reschedule['can_reschedule'] ) : ?>
		<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-reschedule" id="unbsb-open-reschedule">
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
				<line x1="16" y1="2" x2="16" y2="6"></line>
				<line x1="8" y1="2" x2="8" y2="6"></line>
				<line x1="3" y1="10" x2="21" y2="10"></line>
			</svg>
			<?php esc_html_e( 'Reschedule Booking', 'unbelievable-salon-booking' ); ?>
		</button>
		<?php elseif ( ! empty( $can_reschedule['reason'] ) ) : ?>
		<div class="unbsb-notice unbsb-notice-warning">
			<p><?php echo esc_html( $can_reschedule['reason'] ); ?></p>
		</div>
		<?php endif; ?>

		<?php if ( $can_cancel['can_cancel'] ) : ?>
		<button type="button" class="unbsb-btn unbsb-btn-danger unbsb-btn-cancel" id="unbsb-open-cancel">
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="12" cy="12" r="10"></circle>
				<line x1="15" y1="9" x2="9" y2="15"></line>
				<line x1="9" y1="9" x2="15" y2="15"></line>
			</svg>
			<?php esc_html_e( 'Cancel Booking', 'unbelievable-salon-booking' ); ?>
		</button>
		<?php elseif ( ! empty( $can_cancel['reason'] ) ) : ?>
		<div class="unbsb-notice unbsb-notice-warning">
			<p><?php echo esc_html( $can_cancel['reason'] ); ?></p>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- Reschedule Modal -->
	<div class="unbsb-modal" id="unbsb-reschedule-modal" style="display: none;">
		<div class="unbsb-modal-overlay"></div>
		<div class="unbsb-modal-content">
			<div class="unbsb-modal-header">
				<h3><?php esc_html_e( 'Reschedule Booking', 'unbelievable-salon-booking' ); ?></h3>
				<button type="button" class="unbsb-modal-close">&times;</button>
			</div>
			<div class="unbsb-modal-body">
				<form id="unbsb-reschedule-form">
					<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
					<input type="hidden" name="staff_id" value="<?php echo esc_attr( $booking->staff_id ); ?>">
					<input type="hidden" name="service_id" value="<?php echo esc_attr( $booking->service_id ); ?>">

					<div class="unbsb-form-group">
						<label for="unbsb-reschedule-date"><?php esc_html_e( 'New Date', 'unbelievable-salon-booking' ); ?></label>
						<input type="date" id="unbsb-reschedule-date" name="new_date" required
							min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>"
							max="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+' . get_option( 'unbsb_booking_future_days', 30 ) . ' days' ) ) ); ?>">
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb-reschedule-time"><?php esc_html_e( 'New Time', 'unbelievable-salon-booking' ); ?></label>
						<select id="unbsb-reschedule-time" name="new_time" required disabled>
							<option value=""><?php esc_html_e( 'Select a date first', 'unbelievable-salon-booking' ); ?></option>
						</select>
					</div>

					<div class="unbsb-form-actions">
						<button type="button" class="unbsb-btn unbsb-btn-secondary unbsb-modal-cancel">
							<?php esc_html_e( 'Go Back', 'unbelievable-salon-booking' ); ?>
						</button>
						<button type="submit" class="unbsb-btn unbsb-btn-primary" id="unbsb-submit-reschedule">
							<?php esc_html_e( 'Update Booking', 'unbelievable-salon-booking' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Cancel Modal -->
	<div class="unbsb-modal" id="unbsb-cancel-modal" style="display: none;">
		<div class="unbsb-modal-overlay"></div>
		<div class="unbsb-modal-content">
			<div class="unbsb-modal-header">
				<h3><?php esc_html_e( 'Cancel Booking', 'unbelievable-salon-booking' ); ?></h3>
				<button type="button" class="unbsb-modal-close">&times;</button>
			</div>
			<div class="unbsb-modal-body">
				<div class="unbsb-cancel-warning">
					<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
					<p><?php esc_html_e( 'This action cannot be undone. Your booking will be cancelled.', 'unbelievable-salon-booking' ); ?></p>
				</div>

				<form id="unbsb-cancel-form">
					<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">

					<div class="unbsb-form-group">
						<label for="unbsb-cancel-reason"><?php esc_html_e( 'Cancellation Reason (optional)', 'unbelievable-salon-booking' ); ?></label>
						<textarea id="unbsb-cancel-reason" name="reason" rows="3" placeholder="<?php esc_attr_e( 'Why do you want to cancel?', 'unbelievable-salon-booking' ); ?>"></textarea>
					</div>

					<div class="unbsb-form-actions">
						<button type="button" class="unbsb-btn unbsb-btn-secondary unbsb-modal-cancel">
							<?php esc_html_e( 'Go Back', 'unbelievable-salon-booking' ); ?>
						</button>
						<button type="submit" class="unbsb-btn unbsb-btn-danger" id="unbsb-submit-cancel">
							<?php esc_html_e( 'Yes, Cancel', 'unbelievable-salon-booking' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
