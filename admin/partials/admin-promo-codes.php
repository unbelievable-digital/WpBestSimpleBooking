<?php
/**
 * Admin Promo Codes Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<h1><?php esc_html_e( 'Promo Codes', 'unbelievable-salon-booking' ); ?></h1>
		<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-promo-code">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'New Promo Code', 'unbelievable-salon-booking' ); ?>
		</button>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body">
			<?php if ( ! empty( $promo_codes ) ) : ?>
				<table class="unbsb-table unbsb-table-striped" id="unbsb-promo-codes-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Code', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Description', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Type', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Value', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Usage', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Date Range', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Rules', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $promo_codes as $promo_code ) : ?>
							<tr data-id="<?php echo esc_attr( $promo_code->id ); ?>">
								<td>
									<strong style="font-family: monospace; font-size: 13px; letter-spacing: 0.5px;"><?php echo esc_html( $promo_code->code ); ?></strong>
									<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-copy-code" data-code="<?php echo esc_attr( $promo_code->code ); ?>" title="<?php esc_attr_e( 'Copy Code', 'unbelievable-salon-booking' ); ?>" style="margin-left: 4px; vertical-align: middle;">
										<span class="dashicons dashicons-clipboard"></span>
									</button>
								</td>
								<td>
									<?php if ( ! empty( $promo_code->description ) ) : ?>
										<div class="unbsb-text-small unbsb-text-muted">
											<?php echo esc_html( wp_trim_words( $promo_code->description, 10 ) ); ?>
										</div>
									<?php else : ?>
										<span class="unbsb-text-muted">-</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ( 'percentage' === $promo_code->discount_type ) : ?>
										<span class="unbsb-promo-type-badge" style="background-color: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; padding: 2px 8px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
											<?php esc_html_e( 'Percentage', 'unbelievable-salon-booking' ); ?>
										</span>
									<?php elseif ( 'fixed_amount' === $promo_code->discount_type ) : ?>
										<span class="unbsb-promo-type-badge" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d; padding: 2px 8px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
											<?php esc_html_e( 'Fixed Amount', 'unbelievable-salon-booking' ); ?>
										</span>
									<?php elseif ( 'cheapest_free' === $promo_code->discount_type ) : ?>
										<span class="unbsb-promo-type-badge" style="background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; padding: 2px 8px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
											<?php esc_html_e( 'Cheapest Free', 'unbelievable-salon-booking' ); ?>
										</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ( 'percentage' === $promo_code->discount_type ) : ?>
										<?php echo esc_html( $promo_code->discount_value ); ?>%
									<?php elseif ( 'fixed_amount' === $promo_code->discount_type ) : ?>
										<?php echo esc_html( number_format( $promo_code->discount_value, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?>
									<?php else : ?>
										<span class="unbsb-text-muted">&mdash;</span>
									<?php endif; ?>
								</td>
								<td>
									<?php
									$usage_count = isset( $promo_code->usage_count ) ? intval( $promo_code->usage_count ) : 0;
									$max_uses    = isset( $promo_code->max_uses ) ? intval( $promo_code->max_uses ) : 0;
									if ( $max_uses > 0 ) :
										?>
										<?php echo esc_html( $usage_count . ' / ' . $max_uses ); ?>
									<?php else : ?>
										<?php
										/* translators: %d: number of times the promo code has been used */
										echo esc_html( $usage_count );
										?> / &infin;
									<?php endif; ?>
								</td>
								<td>
									<?php if ( ! empty( $promo_code->start_date ) && ! empty( $promo_code->end_date ) ) : ?>
										<span style="white-space: nowrap;"><?php echo esc_html( $promo_code->start_date ); ?></span>
										<br>
										<span style="white-space: nowrap;"><?php echo esc_html( $promo_code->end_date ); ?></span>
									<?php elseif ( ! empty( $promo_code->start_date ) ) : ?>
										<?php
										/* translators: %s: start date of the promo code */
										echo esc_html( sprintf( __( 'From %s', 'unbelievable-salon-booking' ), $promo_code->start_date ) );
										?>
									<?php elseif ( ! empty( $promo_code->end_date ) ) : ?>
										<?php
										/* translators: %s: end date of the promo code */
										echo esc_html( sprintf( __( 'Until %s', 'unbelievable-salon-booking' ), $promo_code->end_date ) );
										?>
									<?php else : ?>
										<span class="unbsb-text-muted">&mdash;</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ( ! empty( $promo_code->first_time_only ) ) : ?>
										<span class="unbsb-promo-type-badge" style="background-color: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; padding: 1px 6px; border-radius: 3px; font-size: 11px; white-space: nowrap; margin-bottom: 2px; display: inline-block;">
											<?php esc_html_e( 'First Time', 'unbelievable-salon-booking' ); ?>
										</span>
									<?php endif; ?>
									<?php if ( ! empty( $promo_code->min_services ) && intval( $promo_code->min_services ) > 0 ) : ?>
										<span class="unbsb-promo-type-badge" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d; padding: 1px 6px; border-radius: 3px; font-size: 11px; white-space: nowrap; margin-bottom: 2px; display: inline-block;">
											<?php
											/* translators: %d: minimum number of services required */
											echo esc_html( sprintf( __( 'Min %d services', 'unbelievable-salon-booking' ), intval( $promo_code->min_services ) ) );
											?>
										</span>
									<?php endif; ?>
									<?php if ( ! empty( $promo_code->min_order_amount ) && floatval( $promo_code->min_order_amount ) > 0 ) : ?>
										<span class="unbsb-promo-type-badge" style="background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; padding: 1px 6px; border-radius: 3px; font-size: 11px; white-space: nowrap; margin-bottom: 2px; display: inline-block;">
											<?php
											/* translators: %s: minimum order amount with currency */
											echo esc_html( sprintf( __( 'Min %s', 'unbelievable-salon-booking' ), number_format( $promo_code->min_order_amount, 2 ) . ' ' . $currency_symbol ) );
											?>
										</span>
									<?php endif; ?>
									<?php
									$has_rules = ! empty( $promo_code->first_time_only )
										|| ( ! empty( $promo_code->min_services ) && intval( $promo_code->min_services ) > 0 )
										|| ( ! empty( $promo_code->min_order_amount ) && floatval( $promo_code->min_order_amount ) > 0 );
									if ( ! $has_rules ) :
										?>
										<span class="unbsb-text-muted">&mdash;</span>
									<?php endif; ?>
								</td>
								<td>
									<span class="unbsb-status unbsb-status-<?php echo esc_attr( $promo_code->status ); ?>">
										<?php echo 'active' === $promo_code->status ? esc_html__( 'Active', 'unbelievable-salon-booking' ) : esc_html__( 'Inactive', 'unbelievable-salon-booking' ); ?>
									</span>
								</td>
								<td>
									<div class="unbsb-actions">
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-edit-promo-code" data-id="<?php echo esc_attr( $promo_code->id ); ?>" title="<?php esc_attr_e( 'Edit', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-btn-danger unbsb-delete-promo-code" data-id="<?php echo esc_attr( $promo_code->id ); ?>" title="<?php esc_attr_e( 'Delete', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-trash"></span>
										</button>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="unbsb-empty-state">
					<span class="dashicons dashicons-tag"></span>
					<p><?php esc_html_e( 'No promo codes added yet.', 'unbelievable-salon-booking' ); ?></p>
					<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-promo-code-empty">
						<?php esc_html_e( 'Add First Promo Code', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Promo Code Modal -->
<div id="unbsb-promo-code-modal" class="unbsb-modal unbsb-modal-promo-code" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-wide">
		<div class="unbsb-modal-header unbsb-modal-header-gradient">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-tag"></span>
				</div>
				<div>
					<h3 id="unbsb-promo-code-modal-title"><?php esc_html_e( 'New Promo Code', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle"><?php esc_html_e( 'Enter promo code details', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body unbsb-modal-body-sections">
			<form id="unbsb-promo-code-form">
				<input type="hidden" name="id" id="promo-id" value="">

				<div class="unbsb-modal-columns">
					<!-- Left Column - Main -->
					<div class="unbsb-modal-column unbsb-modal-column-main">
						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-tag"></span>
								<h4><?php esc_html_e( 'Code & Discount', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="promo-code"><?php esc_html_e( 'Code', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
									<input type="text" id="promo-code" name="code" placeholder="<?php esc_attr_e( 'e.g. WELCOME20', 'unbelievable-salon-booking' ); ?>" required style="font-family: monospace; text-transform: uppercase; letter-spacing: 1px;">
								</div>

								<div class="unbsb-form-group">
									<label for="promo-description"><?php esc_html_e( 'Description', 'unbelievable-salon-booking' ); ?></label>
									<textarea id="promo-description" name="description" rows="3" placeholder="<?php esc_attr_e( 'Short description about the promo code...', 'unbelievable-salon-booking' ); ?>"></textarea>
								</div>

								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="promo-discount-type"><?php esc_html_e( 'Discount Type', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<select id="promo-discount-type" name="discount_type" required>
											<option value="percentage"><?php esc_html_e( 'Percentage', 'unbelievable-salon-booking' ); ?></option>
											<option value="fixed_amount"><?php esc_html_e( 'Fixed Amount', 'unbelievable-salon-booking' ); ?></option>
											<option value="cheapest_free"><?php esc_html_e( 'Cheapest Free', 'unbelievable-salon-booking' ); ?></option>
										</select>
									</div>
									<div class="unbsb-form-group" id="promo-discount-value-group">
										<label for="promo-discount-value"><?php esc_html_e( 'Discount Value', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
										<div class="unbsb-input-with-suffix">
											<input type="number" id="promo-discount-value" name="discount_value" value="0" min="0" step="0.01" required>
											<span class="unbsb-input-suffix" id="promo-discount-value-suffix">%</span>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-admin-tools"></span>
								<h4><?php esc_html_e( 'Applicable To', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label><?php esc_html_e( 'Services', 'unbelievable-salon-booking' ); ?></label>
									<div style="margin-bottom: 8px;">
										<label class="unbsb-toggle-option" style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
											<input type="checkbox" id="promo-all-services" value="1" checked>
											<span><?php esc_html_e( 'All Services', 'unbelievable-salon-booking' ); ?></span>
										</label>
									</div>
									<div id="promo-services-list" class="unbsb-checkbox-list" style="max-height: 160px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; display: none;">
										<?php if ( ! empty( $services ) ) : ?>
											<?php foreach ( $services as $service ) : ?>
												<label style="display: flex; align-items: center; gap: 6px; padding: 4px 0; cursor: pointer;">
													<input type="checkbox" name="applicable_services[]" value="<?php echo esc_attr( $service->id ); ?>">
													<span><?php echo esc_html( $service->name ); ?></span>
												</label>
											<?php endforeach; ?>
										<?php else : ?>
											<p class="unbsb-text-muted" style="margin: 0; font-size: 12px;"><?php esc_html_e( 'No services available.', 'unbelievable-salon-booking' ); ?></p>
										<?php endif; ?>
									</div>
								</div>

								<div class="unbsb-form-group">
									<label><?php esc_html_e( 'Categories', 'unbelievable-salon-booking' ); ?></label>
									<div style="margin-bottom: 8px;">
										<label class="unbsb-toggle-option" style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
											<input type="checkbox" id="promo-all-categories" value="1" checked>
											<span><?php esc_html_e( 'All Categories', 'unbelievable-salon-booking' ); ?></span>
										</label>
									</div>
									<div id="promo-categories-list" class="unbsb-checkbox-list" style="max-height: 160px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; display: none;">
										<?php if ( ! empty( $categories ) ) : ?>
											<?php foreach ( $categories as $category ) : ?>
												<label style="display: flex; align-items: center; gap: 6px; padding: 4px 0; cursor: pointer;">
													<input type="checkbox" name="applicable_categories[]" value="<?php echo esc_attr( $category->id ); ?>">
													<span><?php echo esc_html( $category->name ); ?></span>
												</label>
											<?php endforeach; ?>
										<?php else : ?>
											<p class="unbsb-text-muted" style="margin: 0; font-size: 12px;"><?php esc_html_e( 'No categories available.', 'unbelievable-salon-booking' ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Right Column - Side -->
					<div class="unbsb-modal-column unbsb-modal-column-side">
						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-admin-settings"></span>
								<h4><?php esc_html_e( 'Conditions', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label class="unbsb-toggle-option" style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
										<input type="checkbox" id="promo-first-time-only" name="first_time_only" value="1">
										<span><?php esc_html_e( 'First Time Only', 'unbelievable-salon-booking' ); ?></span>
									</label>
									<small class="unbsb-help-text"><?php esc_html_e( 'Only for customers with no previous bookings', 'unbelievable-salon-booking' ); ?></small>
								</div>

								<div class="unbsb-form-group">
									<label for="promo-min-services"><?php esc_html_e( 'Min Services', 'unbelievable-salon-booking' ); ?></label>
									<input type="number" id="promo-min-services" name="min_services" value="0" min="0" step="1">
									<small class="unbsb-help-text"><?php esc_html_e( 'Minimum number of services in a booking', 'unbelievable-salon-booking' ); ?></small>
								</div>

								<div class="unbsb-form-group">
									<label for="promo-min-order-amount"><?php esc_html_e( 'Min Order Amount', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-input-with-suffix">
										<input type="number" id="promo-min-order-amount" name="min_order_amount" value="0" min="0" step="0.01">
										<span class="unbsb-input-suffix"><?php echo esc_html( $currency_symbol ); ?></span>
									</div>
								</div>
							</div>
						</div>

						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-chart-bar"></span>
								<h4><?php esc_html_e( 'Usage Limits', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="promo-max-uses"><?php esc_html_e( 'Max Uses', 'unbelievable-salon-booking' ); ?></label>
									<input type="number" id="promo-max-uses" name="max_uses" value="0" min="0" step="1">
									<small class="unbsb-help-text"><?php esc_html_e( '0 = unlimited', 'unbelievable-salon-booking' ); ?></small>
								</div>

								<div class="unbsb-form-group">
									<label for="promo-max-uses-per-customer"><?php esc_html_e( 'Max Uses Per Customer', 'unbelievable-salon-booking' ); ?></label>
									<input type="number" id="promo-max-uses-per-customer" name="max_uses_per_customer" value="0" min="0" step="1">
									<small class="unbsb-help-text"><?php esc_html_e( '0 = unlimited', 'unbelievable-salon-booking' ); ?></small>
								</div>
							</div>
						</div>

						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-calendar-alt"></span>
								<h4><?php esc_html_e( 'Validity', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="promo-start-date"><?php esc_html_e( 'Start Date', 'unbelievable-salon-booking' ); ?></label>
									<input type="date" id="promo-start-date" name="start_date" value="">
								</div>

								<div class="unbsb-form-group">
									<label for="promo-end-date"><?php esc_html_e( 'End Date', 'unbelievable-salon-booking' ); ?></label>
									<input type="date" id="promo-end-date" name="end_date" value="">
								</div>
							</div>
						</div>

						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="promo-status"><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-toggle-group">
										<label class="unbsb-toggle-option">
											<input type="radio" name="status" value="active" checked>
											<span class="unbsb-toggle-label unbsb-toggle-success">
												<span class="dashicons dashicons-yes-alt"></span>
												<?php esc_html_e( 'Active', 'unbelievable-salon-booking' ); ?>
											</span>
										</label>
										<label class="unbsb-toggle-option">
											<input type="radio" name="status" value="inactive">
											<span class="unbsb-toggle-label unbsb-toggle-muted">
												<span class="dashicons dashicons-hidden"></span>
												<?php esc_html_e( 'Inactive', 'unbelievable-salon-booking' ); ?>
											</span>
										</label>
									</div>
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
			<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-save-promo-code">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
