<?php
/**
 * Admin Calendar Template (FullCalendar)
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<h1><?php esc_html_e( 'Calendar', 'unbelievable-salon-booking' ); ?></h1>
		<div class="unbsb-calendar-filters">
			<select id="unbsb-cal-staff">
				<option value=""><?php esc_html_e( 'All Staff', 'unbelievable-salon-booking' ); ?></option>
				<?php foreach ( $staff as $staff_member ) : ?>
					<option value="<?php echo esc_attr( $staff_member->id ); ?>"><?php echo esc_html( $staff_member->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body unbsb-fc-wrap">
			<div id="unbsb-calendar"></div>
		</div>
	</div>
</div>

<!-- Booking Detail Modal -->
<div id="unbsb-cal-booking-modal" class="unbsb-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-compact">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-booking">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Booking Details', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-cal-booking-id"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body" id="unbsb-cal-booking-detail">
			<!-- Populated via JS -->
		</div>
		<div class="unbsb-modal-footer" id="unbsb-cal-booking-actions">
			<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-danger" id="unbsb-cal-delete-booking">
				<span class="dashicons dashicons-trash"></span>
				<?php esc_html_e( 'Delete', 'unbelievable-salon-booking' ); ?>
			</button>
			<div class="unbsb-modal-footer-right">
				<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-success" id="unbsb-cal-complete-booking" style="display: none;">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Complete', 'unbelievable-salon-booking' ); ?>
				</button>
				<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-cal-edit-booking">
					<span class="dashicons dashicons-edit"></span>
					<?php esc_html_e( 'Edit', 'unbelievable-salon-booking' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Booking Modal (Calendar) -->
<div id="unbsb-edit-booking-modal" class="unbsb-modal unbsb-modal-booking" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-wide">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-booking">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-edit"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Edit Booking', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-edit-booking-id"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body unbsb-modal-body-sections">
			<form id="unbsb-edit-booking-form">
				<input type="hidden" name="id" id="edit-booking-id" value="">
				<div class="unbsb-modal-columns">
					<div class="unbsb-modal-column unbsb-modal-column-main">
						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-calendar-alt"></span>
								<h4><?php esc_html_e( 'Booking Information', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="edit-booking-staff"><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<select id="edit-booking-staff" name="staff_id" required>
											<option value=""><?php esc_html_e( '-- Select Staff --', 'unbelievable-salon-booking' ); ?></option>
											<?php foreach ( $staff as $staff_member ) : ?>
												<option value="<?php echo esc_attr( $staff_member->id ); ?>"><?php echo esc_html( $staff_member->name ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="unbsb-form-group">
										<label for="edit-booking-service"><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<select id="edit-booking-service" name="service_id" required>
											<option value=""><?php esc_html_e( '-- Select Service --', 'unbelievable-salon-booking' ); ?></option>
											<?php foreach ( $services as $service ) : ?>
												<option value="<?php echo esc_attr( $service->id ); ?>"><?php echo esc_html( $service->name ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="edit-booking-date"><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<input type="date" id="edit-booking-date" name="booking_date" required>
									</div>
									<div class="unbsb-form-group">
										<label for="edit-booking-time"><?php esc_html_e( 'Time', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<input type="text" id="edit-booking-time" name="start_time" class="unbsb-time-input" required placeholder="09:00" maxlength="5" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" inputmode="numeric" autocomplete="off">
									</div>
								</div>
								<div class="unbsb-form-group">
									<label for="edit-booking-price"><?php esc_html_e( 'Price', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-input-with-suffix">
										<input type="number" id="edit-booking-price" name="price" step="0.01" min="0">
										<span class="unbsb-input-suffix"><?php echo esc_html( $currency_symbol ); ?></span>
									</div>
								</div>
							</div>
						</div>
						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-admin-users"></span>
								<h4><?php esc_html_e( 'Customer Information', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="edit-booking-customer-name"><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<input type="text" id="edit-booking-customer-name" name="customer_name" required>
									</div>
									<div class="unbsb-form-group">
										<label for="edit-booking-customer-email"><?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?></label>
										<input type="email" id="edit-booking-customer-email" name="customer_email">
									</div>
								</div>
								<div class="unbsb-form-group">
									<label for="edit-booking-customer-phone"><?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?></label>
									<input type="tel" id="edit-booking-customer-phone" name="customer_phone">
								</div>
							</div>
						</div>
					</div>
					<div class="unbsb-modal-column unbsb-modal-column-side">
						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-edit-page"></span>
								<h4><?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="edit-booking-notes"><?php esc_html_e( 'Customer Note', 'unbelievable-salon-booking' ); ?></label>
									<textarea id="edit-booking-notes" name="notes" rows="3"></textarea>
								</div>
								<div class="unbsb-form-group">
									<label for="edit-booking-internal-notes">
										<?php esc_html_e( 'Internal Note', 'unbelievable-salon-booking' ); ?>
										<span class="unbsb-badge unbsb-badge-sm unbsb-badge-muted"><?php esc_html_e( 'Private', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<textarea id="edit-booking-internal-notes" name="internal_notes" rows="3"></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="unbsb-modal-footer">
			<button type="button" class="unbsb-btn unbsb-btn-ghost unbsb-modal-close">
				<span class="dashicons dashicons-no-alt"></span>
				<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
			</button>
			<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-update-booking-save">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Update Booking', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
