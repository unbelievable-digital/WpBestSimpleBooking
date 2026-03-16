<?php
/**
 * Admin New Booking Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );
$time_format     = get_option( 'unbsb_time_format', 'H:i' );
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<div>
			<h1><?php esc_html_e( 'New Booking', 'unbelievable-salon-booking' ); ?></h1>
			<p class="unbsb-subtitle"><?php esc_html_e( 'Create a new booking for a customer', 'unbelievable-salon-booking' ); ?></p>
		</div>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-bookings' ) ); ?>" class="unbsb-btn unbsb-btn-secondary">
			<span class="dashicons dashicons-arrow-left-alt"></span>
			<?php esc_html_e( 'Back to Bookings', 'unbelievable-salon-booking' ); ?>
		</a>
	</div>

	<form id="unbsb-new-booking-form">
		<div class="unbsb-new-booking-layout">
			<!-- Left Column - Main Form -->
			<div class="unbsb-new-booking-main">

				<!-- Customer Section -->
				<div class="unbsb-card" id="unbsb-nb-customer-section">
					<div class="unbsb-card-header">
						<h2>
							<span class="dashicons dashicons-admin-users" style="color: var(--unbsb-primary); margin-right: 6px;"></span>
							<?php esc_html_e( 'Customer', 'unbelievable-salon-booking' ); ?>
						</h2>
						<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-secondary" id="unbsb-nb-new-customer-btn">
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'New Customer', 'unbelievable-salon-booking' ); ?>
						</button>
					</div>
					<div class="unbsb-card-body">
						<!-- Customer Search -->
						<div class="unbsb-nb-customer-search" id="unbsb-nb-customer-search">
							<div class="unbsb-form-group">
								<label for="unbsb-nb-customer-query">
									<?php esc_html_e( 'Search by phone number or name', 'unbelievable-salon-booking' ); ?>
								</label>
								<div class="unbsb-nb-search-wrap">
									<span class="dashicons dashicons-search unbsb-nb-search-icon"></span>
									<input type="text" id="unbsb-nb-customer-query" placeholder="<?php esc_attr_e( '05XX XXX XX XX or customer name...', 'unbelievable-salon-booking' ); ?>" autocomplete="off">
									<span class="unbsb-nb-search-spinner" id="unbsb-nb-search-spinner" style="display: none;">
										<span class="dashicons dashicons-update-alt unbsb-spin"></span>
									</span>
								</div>
								<!-- Search Results Dropdown -->
								<div class="unbsb-nb-search-results" id="unbsb-nb-search-results" style="display: none;"></div>
							</div>
						</div>

						<!-- Selected Customer Card -->
						<div class="unbsb-nb-selected-customer" id="unbsb-nb-selected-customer" style="display: none;">
							<div class="unbsb-nb-customer-card">
								<div class="unbsb-nb-customer-avatar">
									<span class="dashicons dashicons-admin-users"></span>
								</div>
								<div class="unbsb-nb-customer-info">
									<strong id="unbsb-nb-customer-name"></strong>
									<span id="unbsb-nb-customer-phone"><span class="dashicons dashicons-phone"></span> <span></span></span>
									<span id="unbsb-nb-customer-email"><span class="dashicons dashicons-email"></span> <span></span></span>
								</div>
								<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-secondary" id="unbsb-nb-change-customer">
									<?php esc_html_e( 'Change', 'unbelievable-salon-booking' ); ?>
								</button>
							</div>
							<input type="hidden" name="customer_id" id="unbsb-nb-customer-id" value="">
						</div>

						<!-- New Customer Inline Form -->
						<div class="unbsb-nb-new-customer-form" id="unbsb-nb-new-customer-form" style="display: none;">
							<div class="unbsb-form-row-2">
								<div class="unbsb-form-group">
									<label for="unbsb-nb-new-name"><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
									<input type="text" id="unbsb-nb-new-name" placeholder="<?php esc_attr_e( 'Customer full name', 'unbelievable-salon-booking' ); ?>">
								</div>
								<div class="unbsb-form-group">
									<label for="unbsb-nb-new-phone"><?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
									<input type="tel" id="unbsb-nb-new-phone" placeholder="05XX XXX XX XX">
								</div>
							</div>
							<div class="unbsb-form-row-2">
								<div class="unbsb-form-group">
									<label for="unbsb-nb-new-email"><?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?></label>
									<input type="email" id="unbsb-nb-new-email" placeholder="example@email.com">
								</div>
								<div class="unbsb-form-group">
									<label for="unbsb-nb-new-notes"><?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?></label>
									<input type="text" id="unbsb-nb-new-notes" placeholder="<?php esc_attr_e( 'Optional notes...', 'unbelievable-salon-booking' ); ?>">
								</div>
							</div>
							<div class="unbsb-nb-new-customer-actions">
								<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-sm" id="unbsb-nb-save-customer">
									<span class="dashicons dashicons-saved"></span>
									<?php esc_html_e( 'Save Customer', 'unbelievable-salon-booking' ); ?>
								</button>
								<button type="button" class="unbsb-btn unbsb-btn-ghost unbsb-btn-sm" id="unbsb-nb-cancel-new-customer">
									<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Services Section -->
				<div class="unbsb-card" id="unbsb-nb-services-section">
					<div class="unbsb-card-header">
						<h2>
							<span class="dashicons dashicons-admin-tools" style="color: var(--unbsb-success); margin-right: 6px;"></span>
							<?php esc_html_e( 'Services', 'unbelievable-salon-booking' ); ?>
						</h2>
					</div>
					<div class="unbsb-card-body">
						<!-- Category Filter -->
						<?php if ( ! empty( $categories ) ) : ?>
							<div class="unbsb-nb-category-filter" id="unbsb-nb-category-filter">
								<button type="button" class="unbsb-nb-cat-btn active" data-category="all">
									<?php esc_html_e( 'All', 'unbelievable-salon-booking' ); ?>
								</button>
								<?php foreach ( $categories as $category ) : ?>
									<button type="button" class="unbsb-nb-cat-btn" data-category="<?php echo esc_attr( $category->id ); ?>" style="--cat-color: <?php echo esc_attr( $category->color ); ?>">
										<?php echo esc_html( $category->name ); ?>
									</button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<!-- Service Search -->
						<div class="unbsb-nb-service-search">
							<span class="dashicons dashicons-search unbsb-nb-service-search-icon"></span>
							<input type="text" id="unbsb-nb-service-search" placeholder="<?php esc_attr_e( 'Search services...', 'unbelievable-salon-booking' ); ?>" autocomplete="off">
						</div>

						<!-- Services List (grouped by category accordion) -->
						<div class="unbsb-nb-services-list" id="unbsb-nb-services-list">
							<?php if ( ! empty( $services ) ) : ?>
								<?php
								// Group services by category.
								$grouped   = array();
								$no_cat    = array();
								foreach ( $services as $service ) {
									if ( ! empty( $service->category_id ) && ! empty( $service->category_name ) ) {
										$grouped[ $service->category_id ]['name']       = $service->category_name;
										$grouped[ $service->category_id ]['color']      = $service->category_color ?? '#3788d8';
										$grouped[ $service->category_id ]['services'][] = $service;
									} else {
										$no_cat[] = $service;
									}
								}
								?>
								<?php foreach ( $grouped as $cat_id => $cat_data ) : ?>
									<div class="unbsb-nb-cat-group" data-category="<?php echo esc_attr( $cat_id ); ?>">
										<div class="unbsb-nb-cat-group-header" data-toggle="category">
											<span class="unbsb-nb-cat-group-color" style="background-color: <?php echo esc_attr( $cat_data['color'] ); ?>"></span>
											<span class="unbsb-nb-cat-group-name"><?php echo esc_html( $cat_data['name'] ); ?></span>
											<span class="unbsb-nb-cat-group-count"><?php echo count( $cat_data['services'] ); ?></span>
											<button type="button" class="unbsb-nb-cat-select-all unbsb-btn unbsb-btn-sm unbsb-btn-ghost" data-cat-id="<?php echo esc_attr( $cat_id ); ?>">
												<?php esc_html_e( 'Select All', 'unbelievable-salon-booking' ); ?>
											</button>
											<span class="dashicons dashicons-arrow-down-alt2 unbsb-nb-cat-group-arrow"></span>
										</div>
										<div class="unbsb-nb-cat-group-body">
											<div class="unbsb-nb-services-grid">
												<?php foreach ( $cat_data['services'] as $service ) : ?>
													<label class="unbsb-nb-service-item" data-category="<?php echo esc_attr( $service->category_id ); ?>" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-service-name="<?php echo esc_attr( strtolower( $service->name ) ); ?>">
														<input type="checkbox" name="service_ids[]" value="<?php echo esc_attr( $service->id ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>" data-price="<?php echo esc_attr( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) ? $service->discounted_price : $service->price ); ?>" data-name="<?php echo esc_attr( $service->name ); ?>">
														<span class="unbsb-nb-service-check">
															<span class="dashicons dashicons-yes"></span>
														</span>
														<span class="unbsb-nb-service-name"><?php echo esc_html( $service->name ); ?></span>
														<span class="unbsb-nb-service-meta">
															<span class="unbsb-nb-service-duration"><?php echo esc_html( $service->duration ); ?>′</span>
															<span class="unbsb-nb-service-price">
																<?php if ( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) ) : ?>
																	<span class="unbsb-price-original"><?php echo esc_html( number_format( $service->price, 2 ) ); ?></span>
																	<?php echo esc_html( number_format( $service->discounted_price, 2 ) ); ?>
																<?php else : ?>
																	<?php echo esc_html( number_format( $service->price, 2 ) ); ?>
																<?php endif; ?>
																<?php echo esc_html( $currency_symbol ); ?>
															</span>
														</span>
														<span class="unbsb-nb-service-color" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
													</label>
												<?php endforeach; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>

								<?php if ( ! empty( $no_cat ) ) : ?>
									<div class="unbsb-nb-cat-group" data-category="0">
										<div class="unbsb-nb-cat-group-header" data-toggle="category">
											<span class="unbsb-nb-cat-group-color" style="background-color: #94a3b8"></span>
											<span class="unbsb-nb-cat-group-name"><?php esc_html_e( 'Other', 'unbelievable-salon-booking' ); ?></span>
											<span class="unbsb-nb-cat-group-count"><?php echo count( $no_cat ); ?></span>
											<button type="button" class="unbsb-nb-cat-select-all unbsb-btn unbsb-btn-sm unbsb-btn-ghost" data-cat-id="0">
												<?php esc_html_e( 'Select All', 'unbelievable-salon-booking' ); ?>
											</button>
											<span class="dashicons dashicons-arrow-down-alt2 unbsb-nb-cat-group-arrow"></span>
										</div>
										<div class="unbsb-nb-cat-group-body">
											<div class="unbsb-nb-services-grid">
												<?php foreach ( $no_cat as $service ) : ?>
													<label class="unbsb-nb-service-item" data-category="0" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-service-name="<?php echo esc_attr( strtolower( $service->name ) ); ?>">
														<input type="checkbox" name="service_ids[]" value="<?php echo esc_attr( $service->id ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>" data-price="<?php echo esc_attr( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) ? $service->discounted_price : $service->price ); ?>" data-name="<?php echo esc_attr( $service->name ); ?>">
														<span class="unbsb-nb-service-check">
															<span class="dashicons dashicons-yes"></span>
														</span>
														<span class="unbsb-nb-service-name"><?php echo esc_html( $service->name ); ?></span>
														<span class="unbsb-nb-service-meta">
															<span class="unbsb-nb-service-duration"><?php echo esc_html( $service->duration ); ?>′</span>
															<span class="unbsb-nb-service-price">
																<?php if ( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) ) : ?>
																	<span class="unbsb-price-original"><?php echo esc_html( number_format( $service->price, 2 ) ); ?></span>
																	<?php echo esc_html( number_format( $service->discounted_price, 2 ) ); ?>
																<?php else : ?>
																	<?php echo esc_html( number_format( $service->price, 2 ) ); ?>
																<?php endif; ?>
																<?php echo esc_html( $currency_symbol ); ?>
															</span>
														</span>
														<span class="unbsb-nb-service-color" style="background-color: <?php echo esc_attr( $service->color ?? '#94a3b8' ); ?>"></span>
													</label>
												<?php endforeach; ?>
											</div>
										</div>
									</div>
								<?php endif; ?>

							<?php else : ?>
								<div class="unbsb-empty-state">
									<span class="dashicons dashicons-admin-tools"></span>
									<p><?php esc_html_e( 'No services found.', 'unbelievable-salon-booking' ); ?></p>
								</div>
							<?php endif; ?>
						</div>

						<!-- No results message for search -->
						<div class="unbsb-nb-service-no-results" id="unbsb-nb-service-no-results" style="display: none;">
							<p class="unbsb-text-muted" style="text-align: center; padding: 16px 0;">
								<?php esc_html_e( 'No services match your search.', 'unbelievable-salon-booking' ); ?>
							</p>
						</div>

						<!-- Selected Services Summary -->
						<div class="unbsb-nb-services-summary" id="unbsb-nb-services-summary" style="display: none;">
							<div class="unbsb-nb-summary-row">
								<span><?php esc_html_e( 'Total Duration', 'unbelievable-salon-booking' ); ?>:</span>
								<strong><span id="unbsb-nb-total-duration">0</span> <?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></strong>
							</div>
							<div class="unbsb-nb-summary-row">
								<span><?php esc_html_e( 'Total Price', 'unbelievable-salon-booking' ); ?>:</span>
								<strong><span id="unbsb-nb-total-price">0.00</span> <?php echo esc_html( $currency_symbol ); ?></strong>
							</div>
						</div>
					</div>
				</div>

				<!-- Staff Section -->
				<div class="unbsb-card" id="unbsb-nb-staff-section">
					<div class="unbsb-card-header">
						<h2>
							<span class="dashicons dashicons-businessman" style="color: var(--unbsb-info); margin-right: 6px;"></span>
							<?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?>
						</h2>
					</div>
					<div class="unbsb-card-body">
						<div class="unbsb-nb-staff-list" id="unbsb-nb-staff-list">
							<p class="unbsb-text-muted"><?php esc_html_e( 'Please select at least one service first.', 'unbelievable-salon-booking' ); ?></p>
						</div>
					</div>
				</div>

				<!-- Date & Time Section -->
				<div class="unbsb-card" id="unbsb-nb-datetime-section">
					<div class="unbsb-card-header">
						<h2>
							<span class="dashicons dashicons-calendar-alt" style="color: var(--unbsb-warning); margin-right: 6px;"></span>
							<?php esc_html_e( 'Date & Time', 'unbelievable-salon-booking' ); ?>
						</h2>
					</div>
					<div class="unbsb-card-body">
						<div class="unbsb-form-row-2">
							<div class="unbsb-form-group">
								<label for="unbsb-nb-date"><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
								<input type="date" id="unbsb-nb-date" name="booking_date" min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" required>
							</div>
							<div class="unbsb-form-group">
								<label><?php esc_html_e( 'Available Slots', 'unbelievable-salon-booking' ); ?></label>
								<div class="unbsb-nb-slots-wrap" id="unbsb-nb-slots-wrap">
									<p class="unbsb-text-muted"><?php esc_html_e( 'Select staff and date to see available slots.', 'unbelievable-salon-booking' ); ?></p>
								</div>
								<input type="hidden" name="start_time" id="unbsb-nb-start-time" value="">
							</div>
						</div>
					</div>
				</div>

				<!-- Notes Section -->
				<div class="unbsb-card" id="unbsb-nb-notes-section">
					<div class="unbsb-card-header">
						<h2>
							<span class="dashicons dashicons-edit-page" style="color: var(--unbsb-secondary); margin-right: 6px;"></span>
							<?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?>
						</h2>
					</div>
					<div class="unbsb-card-body">
						<div class="unbsb-form-row-2">
							<div class="unbsb-form-group">
								<label for="unbsb-nb-notes"><?php esc_html_e( 'Customer Note', 'unbelievable-salon-booking' ); ?></label>
								<textarea id="unbsb-nb-notes" name="notes" rows="3" placeholder="<?php esc_attr_e( 'Note visible to customer...', 'unbelievable-salon-booking' ); ?>"></textarea>
							</div>
							<div class="unbsb-form-group">
								<label for="unbsb-nb-internal-notes">
									<?php esc_html_e( 'Internal Note', 'unbelievable-salon-booking' ); ?>
									<span class="unbsb-badge unbsb-badge-sm unbsb-badge-muted"><?php esc_html_e( 'Private', 'unbelievable-salon-booking' ); ?></span>
								</label>
								<textarea id="unbsb-nb-internal-notes" name="internal_notes" rows="3" placeholder="<?php esc_attr_e( 'Only admin can see...', 'unbelievable-salon-booking' ); ?>"></textarea>
							</div>
						</div>
					</div>
				</div>

			</div>

			<!-- Right Column - Booking Summary -->
			<div class="unbsb-new-booking-sidebar">
				<div class="unbsb-card unbsb-nb-summary-card" id="unbsb-nb-summary-card">
					<div class="unbsb-card-header">
						<h2>
							<span class="dashicons dashicons-clipboard" style="color: var(--unbsb-primary); margin-right: 6px;"></span>
							<?php esc_html_e( 'Booking Summary', 'unbelievable-salon-booking' ); ?>
						</h2>
					</div>
					<div class="unbsb-card-body">
						<div class="unbsb-nb-summary-list" id="unbsb-nb-summary-list">
							<!-- Customer -->
							<div class="unbsb-nb-summary-item" id="unbsb-nb-sum-customer">
								<span class="unbsb-nb-summary-label">
									<span class="dashicons dashicons-admin-users"></span>
									<?php esc_html_e( 'Customer', 'unbelievable-salon-booking' ); ?>
								</span>
								<span class="unbsb-nb-summary-value unbsb-text-muted" id="unbsb-nb-sum-customer-val">
									<?php esc_html_e( 'Not selected', 'unbelievable-salon-booking' ); ?>
								</span>
							</div>
							<!-- Services -->
							<div class="unbsb-nb-summary-item" id="unbsb-nb-sum-services">
								<span class="unbsb-nb-summary-label">
									<span class="dashicons dashicons-admin-tools"></span>
									<?php esc_html_e( 'Services', 'unbelievable-salon-booking' ); ?>
								</span>
								<span class="unbsb-nb-summary-value unbsb-text-muted" id="unbsb-nb-sum-services-val">
									<?php esc_html_e( 'Not selected', 'unbelievable-salon-booking' ); ?>
								</span>
							</div>
							<!-- Staff -->
							<div class="unbsb-nb-summary-item" id="unbsb-nb-sum-staff">
								<span class="unbsb-nb-summary-label">
									<span class="dashicons dashicons-businessman"></span>
									<?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?>
								</span>
								<span class="unbsb-nb-summary-value unbsb-text-muted" id="unbsb-nb-sum-staff-val">
									<?php esc_html_e( 'Not selected', 'unbelievable-salon-booking' ); ?>
								</span>
							</div>
							<!-- Date/Time -->
							<div class="unbsb-nb-summary-item" id="unbsb-nb-sum-datetime">
								<span class="unbsb-nb-summary-label">
									<span class="dashicons dashicons-calendar-alt"></span>
									<?php esc_html_e( 'Date & Time', 'unbelievable-salon-booking' ); ?>
								</span>
								<span class="unbsb-nb-summary-value unbsb-text-muted" id="unbsb-nb-sum-datetime-val">
									<?php esc_html_e( 'Not selected', 'unbelievable-salon-booking' ); ?>
								</span>
							</div>
							<!-- Total -->
							<div class="unbsb-nb-summary-total">
								<div class="unbsb-nb-summary-total-row">
									<span><?php esc_html_e( 'Duration', 'unbelievable-salon-booking' ); ?></span>
									<strong><span id="unbsb-nb-sum-duration">0</span> <?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></strong>
								</div>
								<div class="unbsb-nb-summary-total-row unbsb-nb-summary-total-price">
									<span><?php esc_html_e( 'Total', 'unbelievable-salon-booking' ); ?></span>
									<strong><span id="unbsb-nb-sum-price">0.00</span> <?php echo esc_html( $currency_symbol ); ?></strong>
								</div>
							</div>
						</div>
					</div>
					<div class="unbsb-nb-summary-footer">
						<!-- Status -->
						<div class="unbsb-nb-status-select">
							<label><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></label>
							<div class="unbsb-status-options unbsb-status-options-inline">
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
						<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg unbsb-btn-block" id="unbsb-nb-create-booking">
							<span class="dashicons dashicons-saved"></span>
							<?php esc_html_e( 'Create Booking', 'unbelievable-salon-booking' ); ?>
						</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-bookings' ) ); ?>" class="unbsb-btn unbsb-btn-ghost unbsb-btn-block" style="text-align: center; margin-top: 8px;">
							<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
