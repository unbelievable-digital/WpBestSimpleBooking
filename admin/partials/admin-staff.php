<?php
/**
 * Admin Staff Template
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
		<h1><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></h1>
		<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-staff">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'New Staff', 'unbelievable-salon-booking' ); ?>
		</button>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body">
			<?php if ( ! empty( $staff ) ) : ?>
				<div class="unbsb-staff-grid">
					<?php foreach ( $staff as $staff_member ) : ?>
						<div class="unbsb-staff-card" data-id="<?php echo esc_attr( $staff_member->id ); ?>">
							<div class="unbsb-staff-avatar">
								<?php if ( $staff_member->avatar_url ) : ?>
									<img src="<?php echo esc_url( $staff_member->avatar_url ); ?>" alt="<?php echo esc_attr( $staff_member->name ); ?>">
								<?php else : ?>
									<span class="unbsb-avatar-placeholder">
										<?php echo esc_html( mb_substr( $staff_member->name, 0, 1 ) ); ?>
									</span>
								<?php endif; ?>
								<span class="unbsb-staff-status-dot unbsb-status-<?php echo esc_attr( $staff_member->status ); ?>"></span>
							</div>
							<div class="unbsb-staff-info">
								<h3><?php echo esc_html( $staff_member->name ); ?></h3>
								<?php if ( $staff_member->email ) : ?>
									<p class="unbsb-staff-email"><?php echo esc_html( $staff_member->email ); ?></p>
								<?php endif; ?>
								<?php if ( $staff_member->phone ) : ?>
									<p class="unbsb-staff-phone"><?php echo esc_html( $staff_member->phone ); ?></p>
								<?php endif; ?>
							</div>
							<div class="unbsb-staff-actions">
								<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-secondary unbsb-edit-hours" data-id="<?php echo esc_attr( $staff_member->id ); ?>" data-name="<?php echo esc_attr( $staff_member->name ); ?>">
									<span class="dashicons dashicons-clock"></span>
									<?php esc_html_e( 'Hours', 'unbelievable-salon-booking' ); ?>
								</button>
								<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-edit-staff" data-id="<?php echo esc_attr( $staff_member->id ); ?>">
									<span class="dashicons dashicons-edit"></span>
								</button>
								<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-btn-danger unbsb-delete-staff" data-id="<?php echo esc_attr( $staff_member->id ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="unbsb-empty-state">
					<span class="dashicons dashicons-businessman"></span>
					<p><?php esc_html_e( 'No staff added yet.', 'unbelievable-salon-booking' ); ?></p>
					<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-staff-empty">
						<?php esc_html_e( 'Add First Staff', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
/**
 * Render a single service checkbox with custom price/duration fields.
 *
 * @param object $service         Service object.
 * @param string $currency_symbol Currency symbol.
 */
function unbsb_render_service_checkbox( $service, $currency_symbol ) {
	?>
	<div class="unbsb-service-checkbox-wrap" data-service-id="<?php echo esc_attr( $service->id ); ?>">
		<label class="unbsb-service-checkbox">
			<input type="checkbox" name="services[]" value="<?php echo esc_attr( $service->id ); ?>" data-price="<?php echo esc_attr( $service->price ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>">
			<span class="unbsb-service-checkbox-content">
				<span class="unbsb-service-checkbox-color" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
				<span class="unbsb-service-checkbox-name"><?php echo esc_html( $service->name ); ?></span>
				<span class="unbsb-service-checkbox-duration"><?php echo esc_html( $service->duration ); ?> <?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></span>
			</span>
			<span class="unbsb-service-checkbox-check">
				<span class="dashicons dashicons-yes"></span>
			</span>
		</label>
		<div class="unbsb-service-custom-fields" style="display: none;">
			<div class="unbsb-custom-field">
				<label><?php esc_html_e( 'Custom Price', 'unbelievable-salon-booking' ); ?></label>
				<div class="unbsb-custom-field-input">
					<input type="number" name="service_prices[<?php echo esc_attr( $service->id ); ?>]" step="0.01" min="0" placeholder="<?php echo esc_attr( $service->price ); ?>">
					<span class="unbsb-custom-field-suffix"><?php echo esc_html( $currency_symbol ); ?></span>
				</div>
			</div>
			<div class="unbsb-custom-field">
				<label><?php esc_html_e( 'Custom Duration', 'unbelievable-salon-booking' ); ?></label>
				<div class="unbsb-custom-field-input">
					<input type="number" name="service_durations[<?php echo esc_attr( $service->id ); ?>]" step="1" min="1" placeholder="<?php echo esc_attr( $service->duration ); ?>">
					<span class="unbsb-custom-field-suffix"><?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>

<!-- Staff Modal -->
<div id="unbsb-staff-modal" class="unbsb-modal unbsb-modal-staff" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-wide">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-staff">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-businessman"></span>
				</div>
				<div>
					<h3 id="unbsb-staff-modal-title"><?php esc_html_e( 'New Staff', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle"><?php esc_html_e( 'Enter staff details', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body unbsb-modal-body-sections">
			<form id="unbsb-staff-form">
				<input type="hidden" name="id" id="staff-id" value="">

				<div class="unbsb-modal-columns">
					<!-- Left Column - Basic Info -->
					<div class="unbsb-modal-column unbsb-modal-column-main">
						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-admin-users"></span>
								<h4><?php esc_html_e( 'Personal Information', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label for="staff-name"><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
									<input type="text" id="staff-name" name="name" placeholder="<?php esc_attr_e( 'e.g. John Doe', 'unbelievable-salon-booking' ); ?>" required>
								</div>

								<div class="unbsb-form-row-2">
									<div class="unbsb-form-group">
										<label for="staff-email">
											<span class="dashicons dashicons-email" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-primary);"></span>
											<?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?>
										</label>
										<input type="email" id="staff-email" name="email" placeholder="example@email.com">
									</div>
									<div class="unbsb-form-group">
										<label for="staff-phone">
											<span class="dashicons dashicons-phone" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-success);"></span>
											<?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?>
										</label>
										<input type="tel" id="staff-phone" name="phone" placeholder="0532 xxx xx xx">
									</div>
								</div>

								<div class="unbsb-form-group">
									<label for="staff-bio"><?php esc_html_e( 'Biography', 'unbelievable-salon-booking' ); ?></label>
									<textarea id="staff-bio" name="bio" rows="3" placeholder="<?php esc_attr_e( 'A short introduction...', 'unbelievable-salon-booking' ); ?>"></textarea>
								</div>
							</div>
						</div>

						<div class="unbsb-form-section">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-admin-tools"></span>
								<h4><?php esc_html_e( 'Offered Services', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<?php if ( ! empty( $services ) ) : ?>
									<?php
									// Group services by category.
									$grouped       = array();
									$uncategorized = array();
									foreach ( $services as $service ) {
										if ( ! empty( $service->category_id ) ) {
											$grouped[ $service->category_id ][] = $service;
										} else {
											$uncategorized[] = $service;
										}
									}

									// Build category lookup.
									$category_map = array();
									if ( ! empty( $categories ) ) {
										foreach ( $categories as $cat ) {
											$category_map[ $cat->id ] = $cat;
										}
									}
									?>
									<div class="unbsb-service-category-groups">
										<?php foreach ( $grouped as $cat_id => $cat_services ) :
											$cat       = isset( $category_map[ $cat_id ] ) ? $category_map[ $cat_id ] : null;
											$cat_name  = $cat ? $cat->name : __( 'Uncategorized', 'unbelievable-salon-booking' );
											$cat_color = $cat ? $cat->color : '#94a3b8';
											$total     = count( $cat_services );
										?>
											<div class="unbsb-service-category-group" data-category-id="<?php echo esc_attr( $cat_id ); ?>">
												<div class="unbsb-service-category-header">
													<span class="unbsb-category-color" style="background-color: <?php echo esc_attr( $cat_color ); ?>"></span>
													<input type="checkbox" class="unbsb-category-toggle-all">
													<span class="unbsb-category-name"><?php echo esc_html( $cat_name ); ?></span>
													<span class="unbsb-category-count">0/<?php echo esc_html( $total ); ?></span>
													<span class="dashicons dashicons-arrow-down-alt2 unbsb-category-collapse-icon"></span>
												</div>
												<div class="unbsb-service-category-body">
													<div class="unbsb-service-checkbox-grid">
														<?php foreach ( $cat_services as $service ) :
															unbsb_render_service_checkbox( $service, $currency_symbol );
														endforeach; ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>

										<?php if ( ! empty( $uncategorized ) ) :
											$total = count( $uncategorized );
										?>
											<div class="unbsb-service-category-group" data-category-id="0">
												<div class="unbsb-service-category-header">
													<span class="unbsb-category-color" style="background-color: #94a3b8"></span>
													<input type="checkbox" class="unbsb-category-toggle-all">
													<span class="unbsb-category-name"><?php esc_html_e( 'Uncategorized', 'unbelievable-salon-booking' ); ?></span>
													<span class="unbsb-category-count">0/<?php echo esc_html( $total ); ?></span>
													<span class="dashicons dashicons-arrow-down-alt2 unbsb-category-collapse-icon"></span>
												</div>
												<div class="unbsb-service-category-body">
													<div class="unbsb-service-checkbox-grid">
														<?php foreach ( $uncategorized as $service ) :
															unbsb_render_service_checkbox( $service, $currency_symbol );
														endforeach; ?>
													</div>
												</div>
											</div>
										<?php endif; ?>
									</div>
								<?php else : ?>
									<div class="unbsb-empty-inline">
										<span class="dashicons dashicons-info"></span>
										<?php esc_html_e( 'No services added yet. Please add services first.', 'unbelievable-salon-booking' ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<!-- Right Column - Additional Settings -->
					<div class="unbsb-modal-column unbsb-modal-column-side">
						<div class="unbsb-form-section unbsb-form-section-alt">
							<div class="unbsb-form-section-header">
								<span class="dashicons dashicons-admin-settings"></span>
								<h4><?php esc_html_e( 'Settings', 'unbelievable-salon-booking' ); ?></h4>
							</div>
							<div class="unbsb-form-section-body">
								<div class="unbsb-form-group">
									<label><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></label>
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

								<hr class="unbsb-divider">

								<!-- Compensation -->
								<div class="unbsb-form-group">
									<label><?php esc_html_e( 'Compensation', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-salary-type-options">
										<label class="unbsb-salary-type-option">
											<input type="radio" name="salary_type" value="percentage" checked>
											<span><?php esc_html_e( 'Percentage', 'unbelievable-salon-booking' ); ?></span>
										</label>
										<label class="unbsb-salary-type-option">
											<input type="radio" name="salary_type" value="fixed">
											<span><?php esc_html_e( 'Fixed Salary', 'unbelievable-salon-booking' ); ?></span>
										</label>
										<label class="unbsb-salary-type-option">
											<input type="radio" name="salary_type" value="mix">
											<span><?php esc_html_e( 'Mix', 'unbelievable-salon-booking' ); ?></span>
										</label>
									</div>
								</div>

								<div class="unbsb-salary-fields">
									<div class="unbsb-form-group unbsb-salary-field-percentage" id="unbsb-salary-percentage-field">
										<label for="staff-salary-percentage"><?php esc_html_e( 'Commission Rate', 'unbelievable-salon-booking' ); ?></label>
										<div class="unbsb-input-with-suffix">
											<input type="number" id="staff-salary-percentage" name="salary_percentage" min="0" max="100" step="1" placeholder="40" value="">
											<span class="unbsb-input-suffix">%</span>
										</div>
									</div>

									<div class="unbsb-form-group unbsb-salary-field-fixed" id="unbsb-salary-fixed-field" style="display: none;">
										<label for="staff-salary-fixed"><?php esc_html_e( 'Monthly Salary', 'unbelievable-salon-booking' ); ?></label>
										<div class="unbsb-input-with-suffix">
											<input type="number" id="staff-salary-fixed" name="salary_fixed" min="0" step="0.01" placeholder="2000.00" value="">
											<span class="unbsb-input-suffix"><?php echo esc_html( get_option( 'unbsb_currency_symbol', '₺' ) ); ?></span>
										</div>
									</div>
								</div>

								<hr class="unbsb-divider">

								<!-- WordPress Account -->
								<div class="unbsb-form-group">
									<label><?php esc_html_e( 'WordPress Account', 'unbelievable-salon-booking' ); ?></label>
									<div class="unbsb-wp-account-section" id="unbsb-wp-account-section">
										<!-- State: No account linked -->
										<div class="unbsb-wp-account-unlinked" id="unbsb-wp-account-unlinked">
											<div class="unbsb-wp-account-status">
												<span class="dashicons dashicons-admin-users" style="color: var(--unbsb-text-muted);"></span>
												<span class="unbsb-wp-account-label"><?php esc_html_e( 'No account linked', 'unbelievable-salon-booking' ); ?></span>
											</div>
											<div class="unbsb-wp-account-actions">
												<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-primary" id="unbsb-create-wp-account">
													<?php esc_html_e( 'Create Account', 'unbelievable-salon-booking' ); ?>
												</button>
												<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-outline" id="unbsb-link-wp-account-btn">
													<?php esc_html_e( 'Link Existing', 'unbelievable-salon-booking' ); ?>
												</button>
											</div>
										</div>

										<!-- State: Link existing - search form -->
										<div class="unbsb-wp-account-search" id="unbsb-wp-account-search" style="display: none;">
											<div class="unbsb-wp-account-search-input">
												<input type="text" id="unbsb-wp-user-search" placeholder="<?php esc_attr_e( 'Search by email or username...', 'unbelievable-salon-booking' ); ?>" autocomplete="off">
												<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-ghost" id="unbsb-cancel-link-search">
													<span class="dashicons dashicons-no-alt"></span>
												</button>
											</div>
											<div class="unbsb-wp-user-results" id="unbsb-wp-user-results" style="display: none;"></div>
										</div>

										<!-- State: Account linked -->
										<div class="unbsb-wp-account-linked" id="unbsb-wp-account-linked" style="display: none;">
											<div class="unbsb-wp-account-info">
												<span class="dashicons dashicons-yes-alt" style="color: var(--unbsb-success);"></span>
												<span class="unbsb-wp-account-user" id="unbsb-wp-account-user"></span>
											</div>
											<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-danger unbsb-btn-outline" id="unbsb-unlink-wp-account">
												<?php esc_html_e( 'Unlink', 'unbelievable-salon-booking' ); ?>
											</button>
										</div>
									</div>
								</div>

								<hr class="unbsb-divider">

								<div class="unbsb-info-box">
									<span class="dashicons dashicons-info-outline"></span>
									<p><?php esc_html_e( 'You can edit working hours from the "Hours" button after saving the staff member.', 'unbelievable-salon-booking' ); ?></p>
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
			<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-save-staff">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>

<!-- Working Hours Modal -->
<div id="unbsb-hours-modal" class="unbsb-modal unbsb-modal-hours" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-medium">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-hours">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-clock"></span>
				</div>
				<div>
					<h3 id="unbsb-hours-modal-title"><?php esc_html_e( 'Working Hours', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-hours-staff-name"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body">
			<form id="unbsb-hours-form">
				<input type="hidden" name="staff_id" id="hours-staff-id" value="">

				<div class="unbsb-hours-grid">
					<?php
					$days = array(
						1 => __( 'Monday', 'unbelievable-salon-booking' ),
						2 => __( 'Tuesday', 'unbelievable-salon-booking' ),
						3 => __( 'Wednesday', 'unbelievable-salon-booking' ),
						4 => __( 'Thursday', 'unbelievable-salon-booking' ),
						5 => __( 'Friday', 'unbelievable-salon-booking' ),
						6 => __( 'Saturday', 'unbelievable-salon-booking' ),
						0 => __( 'Sunday', 'unbelievable-salon-booking' ),
					);
					foreach ( $days as $day_num => $day_name ) :
						$is_weekend = ( 0 === $day_num || 6 === $day_num );
						?>
						<div class="unbsb-hours-day <?php echo $is_weekend ? 'unbsb-hours-day-weekend' : ''; ?>">
							<div class="unbsb-hours-day-header">
								<label class="unbsb-hours-day-toggle">
									<input type="checkbox" name="hours[<?php echo esc_attr( $day_num ); ?>][is_working]" value="1" <?php echo 0 !== $day_num ? 'checked' : ''; ?> class="unbsb-hours-working-toggle">
									<span class="unbsb-hours-day-name"><?php echo esc_html( $day_name ); ?></span>
									<span class="unbsb-hours-day-status">
										<span class="unbsb-hours-status-on"><?php esc_html_e( 'Open', 'unbelievable-salon-booking' ); ?></span>
										<span class="unbsb-hours-status-off"><?php esc_html_e( 'Closed', 'unbelievable-salon-booking' ); ?></span>
									</span>
								</label>
							</div>
							<div class="unbsb-hours-day-times">
								<div class="unbsb-hours-time-group">
									<input type="text" name="hours[<?php echo esc_attr( $day_num ); ?>][start_time]" value="09:00" class="unbsb-hours-time-input unbsb-time-input" placeholder="09:00" maxlength="5" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" inputmode="numeric" autocomplete="off">
									<span class="unbsb-hours-time-separator">—</span>
									<input type="text" name="hours[<?php echo esc_attr( $day_num ); ?>][end_time]" value="18:00" class="unbsb-hours-time-input unbsb-time-input" placeholder="18:00" maxlength="5" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" inputmode="numeric" autocomplete="off">
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="unbsb-hours-quick-actions">
					<span class="unbsb-quick-action-label"><?php esc_html_e( 'Quick Set:', 'unbelievable-salon-booking' ); ?></span>
					<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-outline" id="unbsb-hours-set-weekdays">
						<?php esc_html_e( 'Weekdays (09-18)', 'unbelievable-salon-booking' ); ?>
					</button>
					<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-outline" id="unbsb-hours-set-all">
						<?php esc_html_e( 'All Days', 'unbelievable-salon-booking' ); ?>
					</button>
					<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-outline" id="unbsb-hours-clear-all">
						<?php esc_html_e( 'Clear', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			</form>
		</div>
		<div class="unbsb-modal-footer">
			<button type="button" class="unbsb-btn unbsb-btn-ghost unbsb-modal-close">
				<span class="dashicons dashicons-no-alt"></span>
				<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
			</button>
			<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-save-hours">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
