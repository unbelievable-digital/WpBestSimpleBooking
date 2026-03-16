<?php
/**
 * Admin Bookings Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );
$time_format     = get_option( 'unbsb_time_format', 'H:i' );

$statuses = array(
	''          => __( 'All Statuses', 'unbelievable-salon-booking' ),
	'pending'   => __( 'Pending', 'unbelievable-salon-booking' ),
	'confirmed' => __( 'Confirmed', 'unbelievable-salon-booking' ),
	'cancelled' => __( 'Cancelled', 'unbelievable-salon-booking' ),
	'completed' => __( 'Completed', 'unbelievable-salon-booking' ),
	'no_show'   => __( 'No Show', 'unbelievable-salon-booking' ),
);
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<h1><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-new-booking' ) ); ?>" class="unbsb-btn unbsb-btn-primary">
			<span class="dashicons dashicons-plus-alt"></span>
			<?php esc_html_e( 'New Booking', 'unbelievable-salon-booking' ); ?>
		</a>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-header">
			<h2><?php esc_html_e( 'Filter', 'unbelievable-salon-booking' ); ?></h2>
		</div>
		<div class="unbsb-card-body">
			<form method="get" class="unbsb-filter-form">
				<input type="hidden" name="page" value="unbsb-bookings">

				<div class="unbsb-filter-row">
					<div class="unbsb-filter-field">
						<label for="status"><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></label>
						<select name="status" id="status">
							<?php foreach ( $statuses as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="unbsb-filter-field">
						<label for="staff_id"><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></label>
						<select name="staff_id" id="staff_id">
							<option value=""><?php esc_html_e( 'All Staff', 'unbelievable-salon-booking' ); ?></option>
							<?php foreach ( $staff as $staff_member ) : ?>
								<option value="<?php echo esc_attr( $staff_member->id ); ?>" <?php selected( $staff_id, $staff_member->id ); ?>>
									<?php echo esc_html( $staff_member->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="unbsb-filter-field">
						<label for="date_from"><?php esc_html_e( 'Start Date', 'unbelievable-salon-booking' ); ?></label>
						<input type="date" name="date_from" id="date_from" value="<?php echo esc_attr( $date_from ); ?>">
					</div>

					<div class="unbsb-filter-field">
						<label for="date_to"><?php esc_html_e( 'End Date', 'unbelievable-salon-booking' ); ?></label>
						<input type="date" name="date_to" id="date_to" value="<?php echo esc_attr( $date_to ); ?>">
					</div>

					<div class="unbsb-filter-actions">
						<button type="submit" class="unbsb-btn unbsb-btn-primary">
							<?php esc_html_e( 'Filter', 'unbelievable-salon-booking' ); ?>
						</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-bookings' ) ); ?>" class="unbsb-btn unbsb-btn-secondary">
							<?php esc_html_e( 'Clear', 'unbelievable-salon-booking' ); ?>
						</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body">
			<?php if ( ! empty( $bookings ) ) : ?>
				<table class="unbsb-table unbsb-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Date/Time', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Price', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $bookings as $booking ) : ?>
							<tr data-id="<?php echo esc_attr( $booking->id ); ?>">
								<td>#<?php echo esc_html( $booking->id ); ?></td>
								<td>
									<strong><?php echo esc_html( $booking->customer_name ); ?></strong>
									<div class="unbsb-text-small">
										<?php echo esc_html( $booking->customer_email ); ?>
										<?php if ( $booking->customer_phone ) : ?>
											<br><?php echo esc_html( $booking->customer_phone ); ?>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<span class="unbsb-service-badge" style="border-left-color: <?php echo esc_attr( $booking->service_color ?? '#3788d8' ); ?>">
										<?php echo esc_html( $booking->service_name ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $booking->staff_name ); ?></td>
								<td>
									<strong><?php echo esc_html( date_i18n( $date_format, strtotime( $booking->booking_date ) ) ); ?></strong>
									<div class="unbsb-text-small">
										<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->start_time ) ) ); ?> -
										<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->end_time ) ) ); ?>
									</div>
								</td>
								<td>
									<?php echo esc_html( number_format( $booking->price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?>
								</td>
								<td>
									<select class="unbsb-status-select" data-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php foreach ( $statuses as $value => $label ) : ?>
											<?php if ( '' !== $value ) : ?>
												<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $booking->status, $value ); ?>>
													<?php echo esc_html( $label ); ?>
												</option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<div class="unbsb-actions">
										<?php if ( 'confirmed' === $booking->status ) : ?>
											<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-success unbsb-complete-booking" data-id="<?php echo esc_attr( $booking->id ); ?>" data-service="<?php echo esc_attr( $booking->service_name ); ?>" data-price="<?php echo esc_attr( number_format( $booking->price, 2, '.', '' ) ); ?>" title="<?php esc_attr_e( 'Complete', 'unbelievable-salon-booking' ); ?>">
												<span class="dashicons dashicons-yes-alt"></span>
											</button>
										<?php endif; ?>
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-view-booking" data-id="<?php echo esc_attr( $booking->id ); ?>" title="<?php esc_attr_e( 'View', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-visibility"></span>
										</button>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="unbsb-empty-state">
					<span class="dashicons dashicons-calendar-alt"></span>
					<p><?php esc_html_e( 'No bookings found.', 'unbelievable-salon-booking' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Booking Detail Modal -->
<div id="unbsb-booking-modal" class="unbsb-modal unbsb-modal-booking-detail" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-medium">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-booking">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Booking Details', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-booking-detail-id"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body" id="unbsb-booking-detail">
			<!-- Populated via AJAX -->
		</div>
	</div>
</div>

<!-- Complete Booking Modal -->
<div id="unbsb-complete-booking-modal" class="unbsb-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-compact">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-booking">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-yes-alt"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Complete Booking', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-complete-booking-id"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body">
			<div class="unbsb-complete-service-info" id="unbsb-complete-service-info"></div>

			<div class="unbsb-form-group">
				<label for="unbsb-complete-amount">
					<?php esc_html_e( 'Amount Received', 'unbelievable-salon-booking' ); ?>
				</label>
				<div class="unbsb-input-with-suffix">
					<input type="number" id="unbsb-complete-amount" step="0.01" min="0">
					<span class="unbsb-input-suffix"><?php echo esc_html( $currency_symbol ); ?></span>
				</div>
			</div>

			<div class="unbsb-form-group">
				<label><?php esc_html_e( 'Payment Method', 'unbelievable-salon-booking' ); ?></label>
				<div class="unbsb-payment-methods">
					<label class="unbsb-payment-method">
						<input type="radio" name="unbsb_payment_method" value="cash" checked>
						<span class="unbsb-payment-method-label">
							<span class="dashicons dashicons-money-alt"></span>
							<?php esc_html_e( 'Cash', 'unbelievable-salon-booking' ); ?>
						</span>
					</label>
					<label class="unbsb-payment-method">
						<input type="radio" name="unbsb_payment_method" value="card">
						<span class="unbsb-payment-method-label">
							<span class="dashicons dashicons-credit-card"></span>
							<?php esc_html_e( 'Card', 'unbelievable-salon-booking' ); ?>
						</span>
					</label>
					<label class="unbsb-payment-method">
						<input type="radio" name="unbsb_payment_method" value="transfer">
						<span class="unbsb-payment-method-label">
							<span class="dashicons dashicons-bank"></span>
							<?php esc_html_e( 'Transfer', 'unbelievable-salon-booking' ); ?>
						</span>
					</label>
				</div>
			</div>
		</div>
		<div class="unbsb-modal-footer">
			<button type="button" class="unbsb-btn unbsb-btn-ghost unbsb-modal-close">
				<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
			</button>
			<button type="button" class="unbsb-btn unbsb-btn-success" id="unbsb-complete-booking-save">
				<span class="dashicons dashicons-yes-alt"></span>
				<?php esc_html_e( 'Complete & Save', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>

<!-- Add Booking Modal -->
<div id="unbsb-add-booking-modal" class="unbsb-modal unbsb-modal-booking" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-wide">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-booking">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-plus-alt"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Create New Booking', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle"><?php esc_html_e( 'Create a booking for a customer', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body unbsb-modal-body-sections">
			<form id="unbsb-add-booking-form">
				<div class="unbsb-modal-columns">
					<!-- Left Column - Booking Information -->
					<div class="unbsb-modal-column unbsb-modal-column-main">
						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-calendar-alt"></span>
								<h4><?php esc_html_e( 'Booking Information', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="booking-staff">
											<span class="dashicons dashicons-businessman" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-primary);"></span>
											<?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?> <span class="required">*</span>
										</label>
										<select id="booking-staff" name="staff_id" required>
											<option value=""><?php esc_html_e( '-- Select Staff --', 'unbelievable-salon-booking' ); ?></option>
											<?php foreach ( $staff as $staff_member ) : ?>
												<option value="<?php echo esc_attr( $staff_member->id ); ?>">
													<?php echo esc_html( $staff_member->name ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="unbsb-form-group">
										<label for="booking-service">
											<span class="dashicons dashicons-admin-tools" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-success);"></span>
											<?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?> <span class="required">*</span>
										</label>
										<select id="booking-service" name="service_id" required>
											<option value=""><?php esc_html_e( '-- Select Service --', 'unbelievable-salon-booking' ); ?></option>
											<?php
											// Group services by categories.
											$grouped_services = array();
											$uncategorized    = array();

											foreach ( $services as $service ) {
												if ( ! empty( $service->category_id ) && ! empty( $service->category_name ) ) {
													$grouped_services[ $service->category_id ]['name']       = $service->category_name;
													$grouped_services[ $service->category_id ]['services'][] = $service;
												} else {
													$uncategorized[] = $service;
												}
											}

											// Categorized services.
											foreach ( $grouped_services as $category_id => $cat_data ) :
												?>
												<optgroup label="<?php echo esc_attr( $cat_data['name'] ); ?>">
													<?php foreach ( $cat_data['services'] as $service ) : ?>
														<option value="<?php echo esc_attr( $service->id ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>" data-price="<?php echo esc_attr( $service->price ); ?>">
															<?php echo esc_html( $service->name ); ?> (<?php echo esc_html( $service->duration ); ?> min)
														</option>
													<?php endforeach; ?>
												</optgroup>
											<?php endforeach; ?>

											<?php // Uncategorized services. ?>
											<?php if ( ! empty( $uncategorized ) ) : ?>
												<?php if ( ! empty( $grouped_services ) ) : ?>
													<optgroup label="<?php esc_attr_e( 'Other', 'unbelievable-salon-booking' ); ?>">
												<?php endif; ?>
													<?php foreach ( $uncategorized as $service ) : ?>
														<option value="<?php echo esc_attr( $service->id ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>" data-price="<?php echo esc_attr( $service->price ); ?>">
															<?php echo esc_html( $service->name ); ?> (<?php echo esc_html( $service->duration ); ?> min)
														</option>
													<?php endforeach; ?>
												<?php if ( ! empty( $grouped_services ) ) : ?>
													</optgroup>
												<?php endif; ?>
											<?php endif; ?>
										</select>
									</div>
								</div>

								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="booking-date">
											<span class="dashicons dashicons-calendar" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-warning);"></span>
											<?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?> <span class="required">*</span>
										</label>
										<input type="date" id="booking-date" name="booking_date" required min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
									</div>
									<div class="unbsb-form-group">
										<label for="booking-time">
											<span class="dashicons dashicons-clock" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-info);"></span>
											<?php esc_html_e( 'Time', 'unbelievable-salon-booking' ); ?> <span class="required">*</span>
										</label>
										<input type="text" id="booking-time" name="start_time" class="unbsb-time-input" required placeholder="09:00" maxlength="5" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" inputmode="numeric" autocomplete="off">
									</div>
								</div>

								<!-- Selected service info -->
								<div class="unbsb-selected-service-info" id="booking-service-info" style="display: none;">
									<div class="unbsb-service-info-content">
										<span class="unbsb-service-info-duration">
											<span class="dashicons dashicons-clock"></span>
											<span id="booking-service-duration">30</span> min
										</span>
										<span class="unbsb-service-info-price">
											<span class="dashicons dashicons-money-alt"></span>
											<span id="booking-service-price">0.00</span> <?php echo esc_html( $currency_symbol ); ?>
										</span>
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
										<label for="booking-customer-name"><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<input type="text" id="booking-customer-name" name="customer_name" placeholder="<?php esc_attr_e( 'Customer full name', 'unbelievable-salon-booking' ); ?>" required>
									</div>
									<div class="unbsb-form-group">
										<label for="booking-customer-email"><?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<input type="email" id="booking-customer-email" name="customer_email" placeholder="example@email.com" required>
									</div>
								</div>

								<div class="unbsb-form-group">
									<label for="booking-customer-phone"><?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?></label>
									<input type="tel" id="booking-customer-phone" name="customer_phone" placeholder="0532 xxx xx xx">
								</div>
							</div>
						</div>
					</div>

					<!-- Right Column - Status and Notes -->
					<div class="unbsb-modal-column unbsb-modal-column-side">
						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-flag"></span>
								<h4><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-status-options">
									<label class="unbsb-status-option">
										<input type="radio" name="status" value="pending" checked>
										<span class="unbsb-status-option-content unbsb-status-option-pending">
											<span class="dashicons dashicons-clock"></span>
											<span><?php esc_html_e( 'Pending', 'unbelievable-salon-booking' ); ?></span>
										</span>
									</label>
									<label class="unbsb-status-option">
										<input type="radio" name="status" value="confirmed">
										<span class="unbsb-status-option-content unbsb-status-option-confirmed">
											<span class="dashicons dashicons-yes-alt"></span>
											<span><?php esc_html_e( 'Confirmed', 'unbelievable-salon-booking' ); ?></span>
										</span>
									</label>
								</div>
							</div>
						</div>

						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-edit-page"></span>
								<h4><?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="booking-notes"><?php esc_html_e( 'Customer Note', 'unbelievable-salon-booking' ); ?></label>
									<textarea id="booking-notes" name="notes" rows="2" placeholder="<?php esc_attr_e( 'Note visible to customer...', 'unbelievable-salon-booking' ); ?>"></textarea>
								</div>

								<div class="unbsb-form-group">
									<label for="booking-internal-notes">
										<?php esc_html_e( 'Internal Note', 'unbelievable-salon-booking' ); ?>
										<span class="unbsb-badge unbsb-badge-sm unbsb-badge-muted"><?php esc_html_e( 'Private', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<textarea id="booking-internal-notes" name="internal_notes" rows="2" placeholder="<?php esc_attr_e( 'Only admin can see...', 'unbelievable-salon-booking' ); ?>"></textarea>
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
			<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-save-booking">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Create Booking', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
