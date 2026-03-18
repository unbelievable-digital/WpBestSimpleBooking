<?php
/**
 * Booking Form Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol   = get_option( 'unbsb_currency_symbol', '₺' );
$currency_position = get_option( 'unbsb_currency_position', 'after' );
$company_name      = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
$flow_mode         = get_option( 'unbsb_booking_flow_mode', 'service_first' );
$multi_service     = 'yes' === get_option( 'unbsb_enable_multi_service', 'no' );
$input_type        = $multi_service ? 'checkbox' : 'radio';

// Step configuration based on flow mode.
$steps_config = array(
	'service_first' => array(
		array(
			'key'   => 'service',
			'label' => __( 'Service', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'staff',
			'label' => __( 'Staff', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'datetime',
			'label' => __( 'Date/Time', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'info',
			'label' => __( 'Details', 'unbelievable-salon-booking' ),
		),
	),
	'staff_first'   => array(
		array(
			'key'   => 'staff',
			'label' => __( 'Staff', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'service',
			'label' => __( 'Service', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'datetime',
			'label' => __( 'Date/Time', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'info',
			'label' => __( 'Details', 'unbelievable-salon-booking' ),
		),
	),
	'service_only'  => array(
		array(
			'key'   => 'service',
			'label' => __( 'Service', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'datetime',
			'label' => __( 'Date/Time', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'info',
			'label' => __( 'Details', 'unbelievable-salon-booking' ),
		),
	),
	'staff_only'    => array(
		array(
			'key'   => 'staff',
			'label' => __( 'Staff', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'datetime',
			'label' => __( 'Date/Time', 'unbelievable-salon-booking' ),
		),
		array(
			'key'   => 'info',
			'label' => __( 'Details', 'unbelievable-salon-booking' ),
		),
	),
);

$current_steps = isset( $steps_config[ $flow_mode ] ) ? $steps_config[ $flow_mode ] : $steps_config['service_first'];
$total_steps   = count( $current_steps );

// Find step numbers by key.
$step_numbers = array();
foreach ( $current_steps as $index => $step ) {
	$step_numbers[ $step['key'] ] = $index + 1;
}

// Determine which steps are active.
$has_service_step = isset( $step_numbers['service'] );
$has_staff_step   = isset( $step_numbers['staff'] );
?>

<div class="unbsb-booking-wrapper" id="unbsb-booking-form">
	<div class="unbsb-booking-container">
		<!-- Progress Steps -->
		<div class="unbsb-progress">
			<?php foreach ( $current_steps as $index => $step ) : ?>
				<?php if ( $index > 0 ) : ?>
					<div class="unbsb-progress-line"></div>
				<?php endif; ?>
				<div class="unbsb-progress-step<?php echo 0 === $index ? ' active' : ''; ?>" data-step="<?php echo esc_attr( $index + 1 ); ?>" data-step-key="<?php echo esc_attr( $step['key'] ); ?>">
					<span class="unbsb-step-number"><?php echo esc_html( $index + 1 ); ?></span>
					<span class="unbsb-step-label"><?php echo esc_html( $step['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>

		<form id="unbsb-booking-wizard" class="unbsb-booking-form">
			<!-- Honeypot -->
			<input type="text" name="website" class="unbsb-honeypot" tabindex="-1" autocomplete="off">

			<!-- Service Selection Step -->
			<?php if ( $has_service_step ) : ?>
			<div class="unbsb-step" data-step="<?php echo esc_attr( $step_numbers['service'] ); ?>" data-step-key="service"<?php echo 1 !== $step_numbers['service'] ? ' style="display: none;"' : ''; ?>>
				<h3 class="unbsb-step-title">
					<?php if ( $multi_service ) : ?>
						<?php esc_html_e( 'Select Services (You can select multiple)', 'unbelievable-salon-booking' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Select a Service', 'unbelievable-salon-booking' ); ?>
					<?php endif; ?>
				</h3>

				<?php if ( ! empty( $services ) ) : ?>
					<?php if ( ! empty( $categories ) ) : ?>
					<!-- Category Filter -->
					<div class="unbsb-filter-wrapper" id="unbsb-filter-wrapper">
						<button type="button" class="unbsb-filter-arrow unbsb-filter-arrow-left" id="unbsb-filter-arrow-left" aria-label="<?php esc_attr_e( 'Scroll left', 'unbelievable-salon-booking' ); ?>">
							<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12l4.58-4.59z"/></svg>
						</button>
						<div class="unbsb-category-filter" id="unbsb-category-filter">
							<button type="button" class="unbsb-filter-btn active" data-category="all">
								<span class="unbsb-filter-icon">
									<svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M4 8h4V4H4v4zm6 12h4v-4h-4v4zm-6 0h4v-4H4v4zm0-6h4v-4H4v4zm6 0h4v-4h-4v4zm6-10v4h4V4h-4zm-6 4h4V4h-4v4zm6 6h4v-4h-4v4zm0 6h4v-4h-4v4z"/></svg>
								</span>
								<?php esc_html_e( 'All', 'unbelievable-salon-booking' ); ?>
							</button>
							<?php foreach ( $categories as $category ) : ?>
								<button type="button" class="unbsb-filter-btn" data-category="<?php echo esc_attr( $category->id ); ?>" style="--filter-color: <?php echo esc_attr( $category->color ); ?>">
									<span class="unbsb-filter-dot"></span>
									<?php echo esc_html( $category->name ); ?>
								</button>
							<?php endforeach; ?>
						</div>
						<button type="button" class="unbsb-filter-arrow unbsb-filter-arrow-right" id="unbsb-filter-arrow-right" aria-label="<?php esc_attr_e( 'Scroll right', 'unbelievable-salon-booking' ); ?>">
							<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6-6-6z"/></svg>
						</button>
						<div class="unbsb-filter-swipe-hint" id="unbsb-filter-swipe-hint">
							<svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M18.89 12.44l-4.53-4.53c-.29-.29-.77-.29-1.06 0l-.07.07c-.29.29-.29.77 0 1.06l3.18 3.18H4c-.55 0-1 .45-1 1s.45 1 1 1h12.41l-3.18 3.18c-.29.29-.29.77 0 1.06l.07.07c.29.29.77.29 1.06 0l4.53-4.53c.28-.29.28-.77 0-1.06z" opacity="0.5"/><path fill="currentColor" d="M9.71 17.46l3.18-3.18c.29-.29.29-.77 0-1.06l-3.18-3.18c-.29-.29-.77-.29-1.06 0l-.07.07c-.29.29-.29.77 0 1.06L10.76 13H4c-.55 0-1-.45-1-1" opacity="0"/></svg>
						</div>
						<div class="unbsb-filter-dots" id="unbsb-filter-dots"></div>
					</div>
					<?php endif; ?>

					<?php
					// Group services by category for multi-service mode.
					$grouped_services      = array();
					$uncategorized_services = array();
					$categories_map        = array();
					if ( ! empty( $categories ) ) {
						foreach ( $categories as $cat ) {
							$categories_map[ $cat->id ] = $cat;
						}
					}
					foreach ( $services as $svc ) {
						if ( ! empty( $svc->category_id ) && isset( $categories_map[ $svc->category_id ] ) ) {
							$grouped_services[ $svc->category_id ][] = $svc;
						} else {
							$uncategorized_services[] = $svc;
						}
					}
					?>

					<div class="unbsb-service-list<?php echo $multi_service ? ' unbsb-multi-service' : ''; ?>" id="unbsb-service-list">
						<?php if ( $multi_service && ! empty( $categories ) ) : ?>
							<?php foreach ( $grouped_services as $cat_id => $cat_services ) : ?>
								<?php $cat = $categories_map[ $cat_id ]; ?>
								<div class="unbsb-service-category-group" data-category-id="<?php echo esc_attr( $cat_id ); ?>">
									<div class="unbsb-service-category-header">
										<span class="unbsb-category-dot" style="background-color: <?php echo esc_attr( $cat->color ); ?>"></span>
										<span class="unbsb-category-title"><?php echo esc_html( $cat->name ); ?></span>
										<span class="unbsb-category-selected-count" style="display:none">0</span>
									</div>
									<div class="unbsb-service-category-items">
										<?php foreach ( $cat_services as $service ) : ?>
											<?php
											$has_discount    = ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price );
											$effective_price = $has_discount ? $service->discounted_price : $service->price;
											?>
											<label class="unbsb-service-item" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-category-id="<?php echo esc_attr( $cat_id ); ?>" data-price="<?php echo esc_attr( $effective_price ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>">
												<?php include __DIR__ . '/service-card-inner.php'; ?>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
							<?php if ( ! empty( $uncategorized_services ) ) : ?>
								<div class="unbsb-service-category-group" data-category-id="0">
									<div class="unbsb-service-category-header">
										<span class="unbsb-category-dot" style="background-color: #94a3b8"></span>
										<span class="unbsb-category-title"><?php esc_html_e( 'Uncategorized', 'unbelievable-salon-booking' ); ?></span>
										<span class="unbsb-category-selected-count" style="display:none">0</span>
									</div>
									<div class="unbsb-service-category-items">
										<?php foreach ( $uncategorized_services as $service ) : ?>
											<?php
											$has_discount    = ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price );
											$effective_price = $has_discount ? $service->discounted_price : $service->price;
											?>
											<label class="unbsb-service-item" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-category-id="0" data-price="<?php echo esc_attr( $effective_price ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>">
												<?php include __DIR__ . '/service-card-inner.php'; ?>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php elseif ( ! empty( $categories ) && ! empty( $grouped_services ) ) : ?>
							<?php foreach ( $grouped_services as $cat_id => $cat_services ) : ?>
								<?php $cat = $categories_map[ $cat_id ]; ?>
								<div class="unbsb-service-category-group" data-category-id="<?php echo esc_attr( $cat_id ); ?>">
									<div class="unbsb-service-category-header">
										<span class="unbsb-category-dot" style="background-color: <?php echo esc_attr( $cat->color ); ?>"></span>
										<span class="unbsb-category-title"><?php echo esc_html( $cat->name ); ?></span>
									</div>
									<div class="unbsb-service-category-items">
										<?php foreach ( $cat_services as $service ) : ?>
											<?php
											$has_discount    = ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price );
											$effective_price = $has_discount ? $service->discounted_price : $service->price;
											?>
											<label class="unbsb-service-item" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-category-id="<?php echo esc_attr( $cat_id ); ?>" data-price="<?php echo esc_attr( $effective_price ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>">
												<?php include __DIR__ . '/service-card-inner.php'; ?>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
							<?php if ( ! empty( $uncategorized_services ) ) : ?>
								<?php foreach ( $uncategorized_services as $service ) : ?>
									<?php
									$has_discount    = ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price );
									$effective_price = $has_discount ? $service->discounted_price : $service->price;
									?>
									<label class="unbsb-service-item" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-category-id="0" data-price="<?php echo esc_attr( $effective_price ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>">
										<?php include __DIR__ . '/service-card-inner.php'; ?>
									</label>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php else : ?>
							<?php foreach ( $services as $service ) : ?>
								<?php
								$has_discount    = ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price );
								$effective_price = $has_discount ? $service->discounted_price : $service->price;
								?>
								<label class="unbsb-service-item" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-category-id="<?php echo esc_attr( ! empty( $service->category_id ) ? $service->category_id : '0' ); ?>" data-price="<?php echo esc_attr( $effective_price ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>">
									<?php include __DIR__ . '/service-card-inner.php'; ?>
								</label>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

					<?php if ( $multi_service ) : ?>
					<!-- Selected Services Summary -->
					<div class="unbsb-selected-services-summary" id="unbsb-selected-summary" style="display: none;">
						<div class="unbsb-summary-header">
							<strong><?php esc_html_e( 'Selected Services', 'unbelievable-salon-booking' ); ?></strong>
						</div>
						<div class="unbsb-summary-totals">
							<span class="unbsb-total-duration">
								<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>
								<?php esc_html_e( 'Total:', 'unbelievable-salon-booking' ); ?> <span id="unbsb-total-duration">0</span> <?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?>
							</span>
							<span class="unbsb-total-price">
								<?php if ( 'before' === $currency_position ) : ?>
									<?php echo esc_html( $currency_symbol ); ?><span id="unbsb-total-price">0</span>
								<?php else : ?>
									<span id="unbsb-total-price">0</span> <?php echo esc_html( $currency_symbol ); ?>
								<?php endif; ?>
							</span>
						</div>
					</div>
					<?php endif; ?>
				<?php else : ?>
					<p class="unbsb-empty"><?php esc_html_e( 'No services available yet.', 'unbelievable-salon-booking' ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<!-- Staff Selection Step -->
			<?php if ( $has_staff_step ) : ?>
			<div class="unbsb-step" data-step="<?php echo esc_attr( $step_numbers['staff'] ); ?>" data-step-key="staff" style="display: none;">
				<h3 class="unbsb-step-title"><?php esc_html_e( 'Select Staff', 'unbelievable-salon-booking' ); ?></h3>

				<div class="unbsb-staff-list" id="unbsb-staff-list">
					<!-- Populated via AJAX -->
				</div>
			</div>
			<?php endif; ?>

			<!-- Date & Time Selection Step -->
			<div class="unbsb-step" data-step="<?php echo esc_attr( $step_numbers['datetime'] ); ?>" data-step-key="datetime" style="display: none;">
				<h3 class="unbsb-step-title"><?php esc_html_e( 'Select Date and Time', 'unbelievable-salon-booking' ); ?></h3>

				<div class="unbsb-datetime-grid">
					<div class="unbsb-calendar-wrapper">
						<div class="unbsb-calendar-header">
							<button type="button" class="unbsb-cal-nav" id="unbsb-prev-month">
								<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12l4.58-4.59z"/></svg>
							</button>
							<span class="unbsb-calendar-title" id="unbsb-calendar-title"></span>
							<button type="button" class="unbsb-cal-nav" id="unbsb-next-month">
								<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6-6-6z"/></svg>
							</button>
						</div>
						<div class="unbsb-calendar-body" id="unbsb-calendar-body">
							<!-- Populated via JS -->
						</div>
						<input type="hidden" name="booking_date" id="booking-date" required>
						<input type="hidden" name="staff_id" id="staff-id" value="">
					</div>

					<div class="unbsb-time-wrapper">
						<h4 class="unbsb-time-title"><?php esc_html_e( 'Available Times', 'unbelievable-salon-booking' ); ?></h4>
						<div class="unbsb-time-slots" id="unbsb-time-slots">
							<p class="unbsb-time-hint"><?php esc_html_e( 'Select a date first', 'unbelievable-salon-booking' ); ?></p>
						</div>
						<input type="hidden" name="start_time" id="start-time" required>
					</div>
				</div>
			</div>

			<!-- Customer Info Step -->
			<div class="unbsb-step" data-step="<?php echo esc_attr( $step_numbers['info'] ); ?>" data-step-key="info" style="display: none;">
				<h3 class="unbsb-step-title"><?php esc_html_e( 'Enter Your Information', 'unbelievable-salon-booking' ); ?></h3>

				<div class="unbsb-customer-form">
					<?php
					$prefill_name  = '';
					$prefill_email = '';
					$prefill_phone = '';

					if ( is_user_logged_in() ) {
						$current_user  = wp_get_current_user();
						$prefill_name  = $current_user->display_name;
						$prefill_email = $current_user->user_email;

						$customer_model_prefill = new UNBSB_Customer();
						$customer_prefill       = $customer_model_prefill->get_by_user_id( $current_user->ID );
						if ( ! $customer_prefill ) {
							$customer_prefill = $customer_model_prefill->get_by_email( $current_user->user_email );
						}
						if ( $customer_prefill && ! empty( $customer_prefill->phone ) ) {
							$prefill_phone = $customer_prefill->phone;
						}
					}
					?>
					<div class="unbsb-form-group">
						<label for="customer-name"><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
						<input type="text" name="customer_name" id="customer-name" required placeholder="<?php esc_attr_e( 'Your full name', 'unbelievable-salon-booking' ); ?>" value="<?php echo esc_attr( $prefill_name ); ?>">
					</div>

					<div class="unbsb-form-row">
						<div class="unbsb-form-group">
							<label for="customer-email"><?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
							<input type="email" name="customer_email" id="customer-email" required placeholder="<?php esc_attr_e( 'example@email.com', 'unbelievable-salon-booking' ); ?>" value="<?php echo esc_attr( $prefill_email ); ?>">
						</div>
						<div class="unbsb-form-group">
							<label for="customer-phone"><?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?></label>
							<input type="tel" name="customer_phone" id="customer-phone" placeholder="<?php esc_attr_e( '+1 XXX XXX XXXX', 'unbelievable-salon-booking' ); ?>" value="<?php echo esc_attr( $prefill_phone ); ?>">
						</div>
					</div>

					<div class="unbsb-form-group">
						<label for="customer-notes"><?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?></label>
						<textarea name="notes" id="customer-notes" rows="3" placeholder="<?php esc_attr_e( 'Any additional notes (optional)', 'unbelievable-salon-booking' ); ?>"></textarea>
					</div>
				</div>

				<!-- Promo Code -->
				<div class="unbsb-promo-code-section">
					<div class="unbsb-promo-code-input-row">
						<div class="unbsb-form-group unbsb-promo-input-group">
							<label for="unbsb-promo-code-input"><?php esc_html_e( 'Promo Code', 'unbelievable-salon-booking' ); ?></label>
							<div class="unbsb-promo-input-wrapper">
								<input type="text" id="unbsb-promo-code-input" placeholder="<?php esc_attr_e( 'Enter promo code', 'unbelievable-salon-booking' ); ?>" autocomplete="off" style="text-transform: uppercase; letter-spacing: 1px; font-family: monospace;">
								<button type="button" id="unbsb-apply-promo" class="unbsb-promo-apply-btn"><?php esc_html_e( 'Apply', 'unbelievable-salon-booking' ); ?></button>
							</div>
						</div>
					</div>
					<div id="unbsb-promo-code-message" class="unbsb-promo-message" style="display: none;"></div>
					<input type="hidden" name="promo_code" id="unbsb-promo-code-hidden" value="">
				</div>

				<!-- CAPTCHA -->
				<?php if ( class_exists( 'UNBSB_Captcha' ) && UNBSB_Captcha::is_enabled() ) : ?>
				<div class="unbsb-captcha-wrapper">
					<?php echo UNBSB_Captcha::render_widget(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php endif; ?>

				<!-- Summary -->
				<div class="unbsb-booking-summary">
					<h4><?php esc_html_e( 'Booking Summary', 'unbelievable-salon-booking' ); ?></h4>
					<div class="unbsb-summary-content" id="unbsb-booking-summary">
						<!-- Populated via JS -->
					</div>
				</div>
			</div>

			<!-- Success Message -->
			<div class="unbsb-step unbsb-success-step" data-step="<?php echo esc_attr( $total_steps + 1 ); ?>" data-step-key="success" style="display: none;">
				<div class="unbsb-success-icon">
					<svg viewBox="0 0 24 24" width="64" height="64"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
				</div>
				<h3 class="unbsb-success-title"><?php esc_html_e( 'Your Booking is Received!', 'unbelievable-salon-booking' ); ?></h3>
				<p class="unbsb-success-message"><?php esc_html_e( 'Your booking details will be sent to your email address.', 'unbelievable-salon-booking' ); ?></p>
				<div class="unbsb-success-details" id="unbsb-success-details">
					<!-- Populated via JS -->
				</div>
			</div>

			<!-- Navigation Buttons -->
			<div class="unbsb-form-actions" id="unbsb-form-actions">
				<button type="button" class="unbsb-btn unbsb-btn-secondary" id="unbsb-prev-btn" style="display: none;">
					<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
					<?php esc_html_e( 'Back', 'unbelievable-salon-booking' ); ?>
				</button>
				<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-next-btn" disabled>
					<?php esc_html_e( 'Continue', 'unbelievable-salon-booking' ); ?>
					<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8-8-8z"/></svg>
				</button>
				<button type="submit" class="unbsb-btn unbsb-btn-primary unbsb-btn-submit" id="unbsb-submit-btn" style="display: none;">
					<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
					<?php esc_html_e( 'Book Now', 'unbelievable-salon-booking' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
