<?php
/**
 * Admin Staff Schedule Page
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get staff members.
$staff_model = new UNBSB_Staff();
$all_staff   = $staff_model->get_active();

// Days of the week.
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

<div class="unbsb-schedule-page">
	<!-- Header -->
	<div class="unbsb-settings-header">
		<div class="unbsb-settings-header-content">
			<div class="unbsb-settings-header-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="unbsb-settings-header-text">
				<h1><?php esc_html_e( 'Work Schedule', 'unbelievable-salon-booking' ); ?></h1>
				<p><?php esc_html_e( 'Manage staff working hours, breaks, and days off.', 'unbelievable-salon-booking' ); ?></p>
			</div>
		</div>
	</div>

	<?php if ( empty( $all_staff ) ) : ?>
		<div class="unbsb-card" style="margin-top: 24px;">
			<div class="unbsb-card-body" style="text-align: center; padding: 60px 20px;">
				<span class="dashicons dashicons-admin-users" style="font-size: 48px; color: #d1d5db; margin-bottom: 15px;"></span>
				<h3><?php esc_html_e( 'No staff added yet', 'unbelievable-salon-booking' ); ?></h3>
				<p style="color: #6b7280;"><?php esc_html_e( 'You must add staff first to set up the work schedule.', 'unbelievable-salon-booking' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-staff' ) ); ?>" class="unbsb-btn unbsb-btn-primary" style="margin-top: 15px;">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Add Staff', 'unbelievable-salon-booking' ); ?>
				</a>
			</div>
		</div>
	<?php else : ?>

	<!-- Staff Selection -->
	<div class="unbsb-schedule-staff-selector">
		<label for="unbsb-schedule-staff"><?php esc_html_e( 'Select Staff:', 'unbelievable-salon-booking' ); ?></label>
		<select id="unbsb-schedule-staff" class="unbsb-select-large">
			<?php foreach ( $all_staff as $staff ) : ?>
				<option value="<?php echo esc_attr( $staff->id ); ?>"><?php echo esc_html( $staff->name ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<!-- Main Content Grid -->
	<div class="unbsb-schedule-grid">
		<!-- Left: Weekly Schedule -->
		<div class="unbsb-schedule-weekly">
			<div class="unbsb-card">
				<div class="unbsb-card-header">
					<h3>
						<span class="dashicons dashicons-clock"></span>
						<?php esc_html_e( 'Weekly Work Schedule', 'unbelievable-salon-booking' ); ?>
					</h3>
				</div>
				<div class="unbsb-card-body" style="padding: 0;">
					<div class="unbsb-days-list" id="unbsb-days-list">
						<?php foreach ( $days_of_week as $day_num => $day_name ) : ?>
						<div class="unbsb-day-item" data-day="<?php echo esc_attr( $day_num ); ?>">
							<div class="unbsb-day-header">
								<label class="unbsb-day-toggle">
									<input type="checkbox" class="unbsb-day-working" data-day="<?php echo esc_attr( $day_num ); ?>">
									<span class="unbsb-day-name"><?php echo esc_html( $day_name ); ?></span>
								</label>
								<span class="unbsb-day-status"></span>
							</div>
							<div class="unbsb-day-content">
								<div class="unbsb-day-times">
									<div class="unbsb-time-row">
										<label><?php esc_html_e( 'Working:', 'unbelievable-salon-booking' ); ?></label>
										<input type="text" class="unbsb-time-input unbsb-time-start" data-day="<?php echo esc_attr( $day_num ); ?>" value="09:00" placeholder="09:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
										<span class="unbsb-time-separator">-</span>
										<input type="text" class="unbsb-time-input unbsb-time-end" data-day="<?php echo esc_attr( $day_num ); ?>" value="18:00" placeholder="18:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
									</div>
								</div>
								<div class="unbsb-day-breaks">
									<div class="unbsb-breaks-header">
										<span><?php esc_html_e( 'Breaks', 'unbelievable-salon-booking' ); ?></span>
										<button type="button" class="unbsb-add-break-btn" data-day="<?php echo esc_attr( $day_num ); ?>">
											<span class="dashicons dashicons-plus-alt2"></span>
										</button>
									</div>
									<div class="unbsb-breaks-list" data-day="<?php echo esc_attr( $day_num ); ?>">
										<!-- Populated via JavaScript -->
									</div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="unbsb-card-footer">
					<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-save-schedule">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Schedule', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Right: Holiday Calendar -->
		<div class="unbsb-schedule-calendar-wrap">
			<div class="unbsb-card">
				<div class="unbsb-card-header">
					<h3>
						<span class="dashicons dashicons-calendar"></span>
						<?php esc_html_e( 'Days Off Calendar', 'unbelievable-salon-booking' ); ?>
					</h3>
				</div>
				<div class="unbsb-card-body">
					<div class="unbsb-schedule-calendar">
						<div class="unbsb-calendar-nav">
							<button type="button" class="unbsb-calendar-prev">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
							</button>
							<span class="unbsb-calendar-month-year"></span>
							<button type="button" class="unbsb-calendar-next">
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</button>
						</div>
						<div class="unbsb-calendar-weekdays">
							<span><?php esc_html_e( 'Mon', 'unbelievable-salon-booking' ); ?></span>
							<span><?php esc_html_e( 'Tue', 'unbelievable-salon-booking' ); ?></span>
							<span><?php esc_html_e( 'Wed', 'unbelievable-salon-booking' ); ?></span>
							<span><?php esc_html_e( 'Thu', 'unbelievable-salon-booking' ); ?></span>
							<span><?php esc_html_e( 'Fri', 'unbelievable-salon-booking' ); ?></span>
							<span><?php esc_html_e( 'Sat', 'unbelievable-salon-booking' ); ?></span>
							<span><?php esc_html_e( 'Sun', 'unbelievable-salon-booking' ); ?></span>
						</div>
						<div class="unbsb-calendar-days" id="unbsb-calendar-days">
							<!-- Days populated via JavaScript -->
						</div>
					</div>
					<div class="unbsb-calendar-legend">
						<span class="unbsb-legend-item">
							<span class="unbsb-legend-dot unbsb-legend-holiday"></span>
							<?php esc_html_e( 'Day Off', 'unbelievable-salon-booking' ); ?>
						</span>
						<span class="unbsb-legend-item">
							<span class="unbsb-legend-dot unbsb-legend-today"></span>
							<?php esc_html_e( 'Today', 'unbelievable-salon-booking' ); ?>
						</span>
					</div>
				</div>
			</div>

			<!-- Holiday List -->
			<div class="unbsb-card" style="margin-top: 20px;">
				<div class="unbsb-card-header">
					<h3>
						<span class="dashicons dashicons-dismiss"></span>
						<?php esc_html_e( 'Recorded Days Off', 'unbelievable-salon-booking' ); ?>
					</h3>
				</div>
				<div class="unbsb-card-body" style="padding: 0;">
					<div class="unbsb-holidays-list" id="unbsb-holidays-list">
						<!-- Holidays populated via JavaScript -->
						<div class="unbsb-holidays-empty">
							<span class="dashicons dashicons-yes-alt"></span>
							<p><?php esc_html_e( 'No recorded days off', 'unbelievable-salon-booking' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php endif; ?>
</div>

<!-- Add Holiday Modal -->
<div id="unbsb-holiday-modal" class="unbsb-modal">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-sm">
		<div class="unbsb-modal-header">
			<h3>
				<span class="dashicons dashicons-calendar"></span>
				<?php esc_html_e( 'Add Day Off', 'unbelievable-salon-booking' ); ?>
			</h3>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body">
			<input type="hidden" id="unbsb-holiday-date">
			<div class="unbsb-holiday-date-display">
				<span class="dashicons dashicons-calendar-alt"></span>
				<span id="unbsb-holiday-date-text"></span>
			</div>
			<div class="unbsb-form-group">
				<label for="unbsb-holiday-reason"><?php esc_html_e( 'Reason (optional)', 'unbelievable-salon-booking' ); ?></label>
				<input type="text" id="unbsb-holiday-reason" placeholder="<?php esc_attr_e( 'e.g. Annual leave, Sick leave...', 'unbelievable-salon-booking' ); ?>">
			</div>
		</div>
		<div class="unbsb-modal-footer">
			<button type="button" class="unbsb-btn unbsb-btn-secondary unbsb-modal-cancel">
				<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
			</button>
			<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-holiday-btn">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'Add Day Off', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
