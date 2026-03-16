<?php
/**
 * Staff Portal - My Schedule Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$days_of_week = array(
	1 => __( 'Monday', 'unbelievable-salon-booking' ),
	2 => __( 'Tuesday', 'unbelievable-salon-booking' ),
	3 => __( 'Wednesday', 'unbelievable-salon-booking' ),
	4 => __( 'Thursday', 'unbelievable-salon-booking' ),
	5 => __( 'Friday', 'unbelievable-salon-booking' ),
	6 => __( 'Saturday', 'unbelievable-salon-booking' ),
	0 => __( 'Sunday', 'unbelievable-salon-booking' ),
);
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<div>
			<h1><?php esc_html_e( 'My Schedule', 'unbelievable-salon-booking' ); ?></h1>
			<p class="unbsb-subtitle"><?php esc_html_e( 'Your working hours and days off', 'unbelievable-salon-booking' ); ?></p>
		</div>
	</div>

	<div class="unbsb-sp-schedule-layout">
		<!-- Left: Working Hours -->
		<div class="unbsb-sp-schedule-main">
			<div class="unbsb-card">
				<div class="unbsb-card-header">
					<h2>
						<span class="dashicons dashicons-clock" style="color: var(--unbsb-primary); margin-right: 6px;"></span>
						<?php esc_html_e( 'Working Hours', 'unbelievable-salon-booking' ); ?>
					</h2>
				</div>
				<div class="unbsb-card-body" style="padding: 0;">
					<div class="unbsb-sp-hours-list" id="unbsb-sp-hours-list">
						<!-- Populated via AJAX -->
						<div class="unbsb-sp-hours-loading">
							<span class="dashicons dashicons-update-alt unbsb-spin"></span>
							<?php esc_html_e( 'Loading...', 'unbelievable-salon-booking' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Right: Off Days -->
		<div class="unbsb-sp-schedule-sidebar">
			<div class="unbsb-card">
				<div class="unbsb-card-header">
					<h2>
						<span class="dashicons dashicons-palmtree" style="color: var(--unbsb-warning); margin-right: 6px;"></span>
						<?php esc_html_e( 'Days Off', 'unbelievable-salon-booking' ); ?>
					</h2>
					<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-primary" id="unbsb-sp-add-offday-btn">
						<span class="dashicons dashicons-plus-alt2"></span>
						<?php esc_html_e( 'Add', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
				<div class="unbsb-card-body">
					<!-- Add Off Day Form (hidden by default) -->
					<div class="unbsb-sp-offday-form" id="unbsb-sp-offday-form" style="display: none;">
						<div class="unbsb-form-group">
							<label for="unbsb-sp-offday-date"><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
							<input type="date" id="unbsb-sp-offday-date" min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
						</div>
						<div class="unbsb-form-group">
							<label for="unbsb-sp-offday-reason"><?php esc_html_e( 'Reason', 'unbelievable-salon-booking' ); ?></label>
							<input type="text" id="unbsb-sp-offday-reason" placeholder="<?php esc_attr_e( 'Optional...', 'unbelievable-salon-booking' ); ?>">
						</div>
						<div class="unbsb-sp-offday-actions">
							<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-sm" id="unbsb-sp-save-offday">
								<span class="dashicons dashicons-saved"></span>
								<?php esc_html_e( 'Save', 'unbelievable-salon-booking' ); ?>
							</button>
							<button type="button" class="unbsb-btn unbsb-btn-ghost unbsb-btn-sm" id="unbsb-sp-cancel-offday">
								<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
							</button>
						</div>
					</div>

					<!-- Off Days List -->
					<div class="unbsb-sp-offdays-list" id="unbsb-sp-offdays-list">
						<div class="unbsb-sp-hours-loading">
							<span class="dashicons dashicons-update-alt unbsb-spin"></span>
							<?php esc_html_e( 'Loading...', 'unbelievable-salon-booking' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
