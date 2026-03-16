<?php
/**
 * Admin Services Template
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
		<h1><?php esc_html_e( 'Services', 'unbelievable-salon-booking' ); ?></h1>
		<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-service">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'New Service', 'unbelievable-salon-booking' ); ?>
		</button>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body">
			<?php if ( ! empty( $services ) ) : ?>
				<table class="unbsb-table unbsb-table-striped" id="unbsb-services-table">
					<thead>
						<tr>
							<th style="width: 40px;"></th>
							<th><?php esc_html_e( 'Service Name', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Category', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Duration', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Price', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $services as $service ) : ?>
							<tr data-id="<?php echo esc_attr( $service->id ); ?>">
								<td>
									<span class="unbsb-color-dot" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
								</td>
								<td>
									<strong><?php echo esc_html( $service->name ); ?></strong>
									<?php if ( $service->description ) : ?>
										<div class="unbsb-text-small unbsb-text-muted">
											<?php echo esc_html( wp_trim_words( $service->description, 10 ) ); ?>
										</div>
									<?php endif; ?>
								</td>
								<td>
									<?php if ( ! empty( $service->category_name ) ) : ?>
										<span class="unbsb-category-badge" style="background-color: <?php echo esc_attr( $service->category_color ); ?>20; color: <?php echo esc_attr( $service->category_color ); ?>; border: 1px solid <?php echo esc_attr( $service->category_color ); ?>40;">
											<?php echo esc_html( $service->category_name ); ?>
										</span>
									<?php else : ?>
										<span class="unbsb-text-muted">-</span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $service->duration ); ?> <?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></td>
								<td>
								<?php if ( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) ) : ?>
									<span class="unbsb-price-original"><?php echo esc_html( number_format( $service->price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></span>
									<span class="unbsb-price-discounted"><?php echo esc_html( number_format( $service->discounted_price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></span>
								<?php else : ?>
									<?php echo esc_html( number_format( $service->price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?>
								<?php endif; ?>
							</td>
								<td>
									<span class="unbsb-status unbsb-status-<?php echo esc_attr( $service->status ); ?>">
										<?php echo 'active' === $service->status ? esc_html__( 'Active', 'unbelievable-salon-booking' ) : esc_html__( 'Inactive', 'unbelievable-salon-booking' ); ?>
									</span>
								</td>
								<td>
									<div class="unbsb-actions">
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-edit-service" data-id="<?php echo esc_attr( $service->id ); ?>" title="<?php esc_attr_e( 'Edit', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-btn-danger unbsb-delete-service" data-id="<?php echo esc_attr( $service->id ); ?>" title="<?php esc_attr_e( 'Delete', 'unbelievable-salon-booking' ); ?>">
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
					<span class="dashicons dashicons-admin-tools"></span>
					<p><?php esc_html_e( 'No services added yet.', 'unbelievable-salon-booking' ); ?></p>
					<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-service-empty">
						<?php esc_html_e( 'Add First Service', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Service Modal -->
<div id="unbsb-service-modal" class="unbsb-modal unbsb-modal-service" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-wide">
		<div class="unbsb-modal-header unbsb-modal-header-gradient">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-admin-tools"></span>
				</div>
				<div>
					<h3 id="unbsb-service-modal-title"><?php esc_html_e( 'New Service', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle"><?php esc_html_e( 'Enter service details', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body unbsb-modal-body-sections">
			<form id="unbsb-service-form">
				<input type="hidden" name="id" id="service-id" value="">

				<div class="unbsb-modal-columns">
					<!-- Left Column - Basic Info -->
					<div class="unbsb-modal-column unbsb-modal-column-main">
						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-edit"></span>
								<h4><?php esc_html_e( 'Basic Information', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="service-name"><?php esc_html_e( 'Service Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
									<input type="text" id="service-name" name="name" placeholder="<?php esc_attr_e( 'e.g. Haircut', 'unbelievable-salon-booking' ); ?>" required>
								</div>

								<div class="unbsb-form-group">
									<label for="service-category"><?php esc_html_e( 'Category', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-select-with-action">
										<select id="service-category" name="category_id">
											<option value=""><?php esc_html_e( '-- Select Category --', 'unbelievable-salon-booking' ); ?></option>
											<?php foreach ( $categories as $category ) : ?>
												<option value="<?php echo esc_attr( $category->id ); ?>"><?php echo esc_html( $category->name ); ?></option>
											<?php endforeach; ?>
										</select>
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-outline" id="unbsb-add-category-inline" title="<?php esc_attr_e( 'Add New Category', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-plus-alt2"></span>
										</button>
									</div>
									<div id="unbsb-new-category-form" class="unbsb-inline-form" style="display: none;">
										<div class="unbsb-inline-form-row">
											<input type="text" id="new-category-name" placeholder="<?php esc_attr_e( 'Category name', 'unbelievable-salon-booking' ); ?>">
											<input type="color" id="new-category-color" value="#3788d8" title="<?php esc_attr_e( 'Color', 'unbelievable-salon-booking' ); ?>">
											<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-success" id="unbsb-save-category-inline">
												<span class="dashicons dashicons-yes"></span>
											</button>
											<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-ghost" id="unbsb-cancel-category-inline">
												<span class="dashicons dashicons-no-alt"></span>
											</button>
										</div>
									</div>
								</div>

								<div class="unbsb-form-group">
									<label for="service-description"><?php esc_html_e( 'Description', 'unbelievable-salon-booking' ); ?></label>
									<textarea id="service-description" name="description" rows="3" placeholder="<?php esc_attr_e( 'Short description about the service...', 'unbelievable-salon-booking' ); ?>"></textarea>
								</div>
							</div>
						</div>

						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-clock"></span>
								<h4><?php esc_html_e( 'Duration & Price', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="service-duration">
											<span class="dashicons dashicons-clock" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-primary);"></span>
											<?php esc_html_e( 'Duration', 'unbelievable-salon-booking' ); ?> <span class="required">*</span>
										</label>
										<div class="unbsb-input-with-suffix">
											<input type="number" id="service-duration" name="duration" value="30" min="5" step="5" required>
											<span class="unbsb-input-suffix"><?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></span>
										</div>
									</div>
									<div class="unbsb-form-group">
										<label for="service-price">
											<span class="dashicons dashicons-money-alt" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-success);"></span>
											<?php esc_html_e( 'Price', 'unbelievable-salon-booking' ); ?>
										</label>
										<div class="unbsb-input-with-suffix">
											<input type="number" id="service-price" name="price" value="0" min="0" step="0.01">
											<span class="unbsb-input-suffix"><?php echo esc_html( $currency_symbol ); ?></span>
										</div>
									</div>
								</div>

								<div class="unbsb-form-group">
									<label for="service-discounted-price">
										<span class="dashicons dashicons-tag" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-danger, #ef4444);"></span>
										<?php esc_html_e( 'Discounted Price', 'unbelievable-salon-booking' ); ?>
									</label>
									<div class="unbsb-input-with-suffix">
										<input type="number" id="service-discounted-price" name="discounted_price" value="" min="0" step="0.01" placeholder="<?php esc_attr_e( 'Leave empty for no discount', 'unbelievable-salon-booking' ); ?>">
										<span class="unbsb-input-suffix"><?php echo esc_html( $currency_symbol ); ?></span>
									</div>
									<small class="unbsb-help-text"><?php esc_html_e( 'Leave empty for no discount. Must be less than the regular price.', 'unbelievable-salon-booking' ); ?></small>
								</div>
							</div>
						</div>
					</div>

					<!-- Right Column - Additional Settings -->
					<div class="unbsb-modal-column unbsb-modal-column-side">
						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-admin-settings"></span>
								<h4><?php esc_html_e( 'Additional Settings', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="service-buffer-before"><?php esc_html_e( 'Buffer Before', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-input-with-suffix">
										<input type="number" id="service-buffer-before" name="buffer_before" value="0" min="0" step="5">
										<span class="unbsb-input-suffix"><?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></span>
									</div>
									<small class="unbsb-help-text"><?php esc_html_e( 'Preparation time', 'unbelievable-salon-booking' ); ?></small>
								</div>

								<div class="unbsb-form-group">
									<label for="service-buffer-after"><?php esc_html_e( 'Buffer After', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-input-with-suffix">
										<input type="number" id="service-buffer-after" name="buffer_after" value="0" min="0" step="5">
										<span class="unbsb-input-suffix"><?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></span>
									</div>
									<small class="unbsb-help-text"><?php esc_html_e( 'Cleanup time', 'unbelievable-salon-booking' ); ?></small>
								</div>

								<hr class="unbsb-divider">

								<div class="unbsb-form-group">
									<label for="service-color"><?php esc_html_e( 'Color', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-color-picker-wrap">
										<input type="color" id="service-color" name="color" value="#3788d8">
										<span class="unbsb-color-label" id="service-color-label">#3788d8</span>
									</div>
								</div>

								<div class="unbsb-form-group">
									<label for="service-status"><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></label>
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
			<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-save-service">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
