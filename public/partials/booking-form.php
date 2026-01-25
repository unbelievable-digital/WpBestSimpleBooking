<?php
/**
 * Booking Form Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol     = get_option( 'ag_currency_symbol', '₺' );
$currency_position   = get_option( 'ag_currency_position', 'after' );
$company_name        = get_option( 'ag_company_name', get_bloginfo( 'name' ) );
$flow_mode           = get_option( 'ag_booking_flow_mode', 'service_first' );
$multi_service       = 'yes' === get_option( 'ag_enable_multi_service', 'no' );
$input_type          = $multi_service ? 'checkbox' : 'radio';

// Adım yapılandırması akış moduna göre.
$steps_config = array(
	'service_first' => array(
		array( 'key' => 'service', 'label' => __( 'Hizmet', 'appointment-general' ) ),
		array( 'key' => 'staff', 'label' => __( 'Personel', 'appointment-general' ) ),
		array( 'key' => 'datetime', 'label' => __( 'Tarih/Saat', 'appointment-general' ) ),
		array( 'key' => 'info', 'label' => __( 'Bilgiler', 'appointment-general' ) ),
	),
	'staff_first' => array(
		array( 'key' => 'staff', 'label' => __( 'Personel', 'appointment-general' ) ),
		array( 'key' => 'service', 'label' => __( 'Hizmet', 'appointment-general' ) ),
		array( 'key' => 'datetime', 'label' => __( 'Tarih/Saat', 'appointment-general' ) ),
		array( 'key' => 'info', 'label' => __( 'Bilgiler', 'appointment-general' ) ),
	),
	'service_only' => array(
		array( 'key' => 'service', 'label' => __( 'Hizmet', 'appointment-general' ) ),
		array( 'key' => 'datetime', 'label' => __( 'Tarih/Saat', 'appointment-general' ) ),
		array( 'key' => 'info', 'label' => __( 'Bilgiler', 'appointment-general' ) ),
	),
	'staff_only' => array(
		array( 'key' => 'staff', 'label' => __( 'Personel', 'appointment-general' ) ),
		array( 'key' => 'datetime', 'label' => __( 'Tarih/Saat', 'appointment-general' ) ),
		array( 'key' => 'info', 'label' => __( 'Bilgiler', 'appointment-general' ) ),
	),
);

$current_steps = isset( $steps_config[ $flow_mode ] ) ? $steps_config[ $flow_mode ] : $steps_config['service_first'];
$total_steps   = count( $current_steps );

// Adım numaralarını key'e göre bul.
$step_numbers = array();
foreach ( $current_steps as $index => $step ) {
	$step_numbers[ $step['key'] ] = $index + 1;
}

// Hangi adımların aktif olduğunu belirle.
$has_service_step = isset( $step_numbers['service'] );
$has_staff_step   = isset( $step_numbers['staff'] );
?>

<div class="ag-booking-wrapper" id="ag-booking-form">
	<div class="ag-booking-container">
		<!-- Progress Steps -->
		<div class="ag-progress">
			<?php foreach ( $current_steps as $index => $step ) : ?>
				<?php if ( $index > 0 ) : ?>
					<div class="ag-progress-line"></div>
				<?php endif; ?>
				<div class="ag-progress-step<?php echo 0 === $index ? ' active' : ''; ?>" data-step="<?php echo esc_attr( $index + 1 ); ?>" data-step-key="<?php echo esc_attr( $step['key'] ); ?>">
					<span class="ag-step-number"><?php echo esc_html( $index + 1 ); ?></span>
					<span class="ag-step-label"><?php echo esc_html( $step['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>

		<form id="ag-booking-wizard" class="ag-booking-form">
			<!-- Honeypot -->
			<input type="text" name="website" class="ag-honeypot" tabindex="-1" autocomplete="off">

			<!-- Service Selection Step -->
			<?php if ( $has_service_step ) : ?>
			<div class="ag-step" data-step="<?php echo esc_attr( $step_numbers['service'] ); ?>" data-step-key="service"<?php echo 1 !== $step_numbers['service'] ? ' style="display: none;"' : ''; ?>>
				<h3 class="ag-step-title">
					<?php if ( $multi_service ) : ?>
						<?php esc_html_e( 'Hizmet Seçin (Birden fazla seçebilirsiniz)', 'appointment-general' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Hizmet Seçin', 'appointment-general' ); ?>
					<?php endif; ?>
				</h3>

				<?php if ( ! empty( $services ) ) : ?>
					<?php if ( ! empty( $categories ) ) : ?>
					<!-- Kategori Filtresi -->
					<div class="ag-category-filter" id="ag-category-filter">
						<button type="button" class="ag-filter-btn active" data-category="all">
							<?php esc_html_e( 'Tümü', 'appointment-general' ); ?>
						</button>
						<?php foreach ( $categories as $category ) : ?>
							<button type="button" class="ag-filter-btn" data-category="<?php echo esc_attr( $category->id ); ?>" style="--filter-color: <?php echo esc_attr( $category->color ); ?>">
								<?php echo esc_html( $category->name ); ?>
							</button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<div class="ag-service-list<?php echo $multi_service ? ' ag-multi-service' : ''; ?>" id="ag-service-list">
						<?php foreach ( $services as $service ) : ?>
							<label class="ag-service-item" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-category-id="<?php echo esc_attr( $service->category_id ?: '0' ); ?>" data-price="<?php echo esc_attr( $service->price ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>">
								<input type="<?php echo esc_attr( $input_type ); ?>" name="<?php echo $multi_service ? 'service_ids[]' : 'service_id'; ?>" value="<?php echo esc_attr( $service->id ); ?>"<?php echo $multi_service ? '' : ' required'; ?>>
								<div class="ag-service-card">
									<span class="ag-service-color" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
									<div class="ag-service-info">
										<strong class="ag-service-name"><?php echo esc_html( $service->name ); ?></strong>
										<?php if ( $service->description ) : ?>
											<p class="ag-service-desc"><?php echo esc_html( $service->description ); ?></p>
										<?php endif; ?>
										<div class="ag-service-meta">
											<span class="ag-service-duration">
												<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>
												<?php echo esc_html( $service->duration ); ?> <?php esc_html_e( 'dk', 'appointment-general' ); ?>
											</span>
											<span class="ag-service-price">
												<?php if ( 'before' === $currency_position ) : ?>
													<?php echo esc_html( $currency_symbol . number_format( $service->price, 0 ) ); ?>
												<?php else : ?>
													<?php echo esc_html( number_format( $service->price, 0 ) . ' ' . $currency_symbol ); ?>
												<?php endif; ?>
											</span>
										</div>
									</div>
									<span class="ag-service-check">
										<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
									</span>
								</div>
							</label>
						<?php endforeach; ?>
					</div>

					<?php if ( $multi_service ) : ?>
					<!-- Seçilen Hizmetler Özeti -->
					<div class="ag-selected-services-summary" id="ag-selected-summary" style="display: none;">
						<div class="ag-summary-header">
							<strong><?php esc_html_e( 'Seçilen Hizmetler', 'appointment-general' ); ?></strong>
						</div>
						<div class="ag-summary-totals">
							<span class="ag-total-duration">
								<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>
								<?php esc_html_e( 'Toplam:', 'appointment-general' ); ?> <span id="ag-total-duration">0</span> <?php esc_html_e( 'dk', 'appointment-general' ); ?>
							</span>
							<span class="ag-total-price">
								<?php if ( 'before' === $currency_position ) : ?>
									<?php echo esc_html( $currency_symbol ); ?><span id="ag-total-price">0</span>
								<?php else : ?>
									<span id="ag-total-price">0</span> <?php echo esc_html( $currency_symbol ); ?>
								<?php endif; ?>
							</span>
						</div>
					</div>
					<?php endif; ?>
				<?php else : ?>
					<p class="ag-empty"><?php esc_html_e( 'Henüz hizmet bulunmuyor.', 'appointment-general' ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<!-- Staff Selection Step -->
			<?php if ( $has_staff_step ) : ?>
			<div class="ag-step" data-step="<?php echo esc_attr( $step_numbers['staff'] ); ?>" data-step-key="staff" style="display: none;">
				<h3 class="ag-step-title"><?php esc_html_e( 'Personel Seçin', 'appointment-general' ); ?></h3>

				<div class="ag-staff-list" id="ag-staff-list">
					<!-- AJAX ile doldurulacak -->
				</div>
			</div>
			<?php endif; ?>

			<!-- Date & Time Selection Step -->
			<div class="ag-step" data-step="<?php echo esc_attr( $step_numbers['datetime'] ); ?>" data-step-key="datetime" style="display: none;">
				<h3 class="ag-step-title"><?php esc_html_e( 'Tarih ve Saat Seçin', 'appointment-general' ); ?></h3>

				<div class="ag-datetime-grid">
					<div class="ag-calendar-wrapper">
						<div class="ag-calendar-header">
							<button type="button" class="ag-cal-nav" id="ag-prev-month">
								<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12l4.58-4.59z"/></svg>
							</button>
							<span class="ag-calendar-title" id="ag-calendar-title"></span>
							<button type="button" class="ag-cal-nav" id="ag-next-month">
								<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6-6-6z"/></svg>
							</button>
						</div>
						<div class="ag-calendar-body" id="ag-calendar-body">
							<!-- JS ile doldurulacak -->
						</div>
						<input type="hidden" name="booking_date" id="booking-date" required>
					</div>

					<div class="ag-time-wrapper">
						<h4 class="ag-time-title"><?php esc_html_e( 'Müsait Saatler', 'appointment-general' ); ?></h4>
						<div class="ag-time-slots" id="ag-time-slots">
							<p class="ag-time-hint"><?php esc_html_e( 'Önce bir tarih seçin', 'appointment-general' ); ?></p>
						</div>
						<input type="hidden" name="start_time" id="start-time" required>
					</div>
				</div>
			</div>

			<!-- Customer Info Step -->
			<div class="ag-step" data-step="<?php echo esc_attr( $step_numbers['info'] ); ?>" data-step-key="info" style="display: none;">
				<h3 class="ag-step-title"><?php esc_html_e( 'Bilgilerinizi Girin', 'appointment-general' ); ?></h3>

				<div class="ag-customer-form">
					<div class="ag-form-group">
						<label for="customer-name"><?php esc_html_e( 'Ad Soyad', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="text" name="customer_name" id="customer-name" required placeholder="<?php esc_attr_e( 'Adınız ve soyadınız', 'appointment-general' ); ?>">
					</div>

					<div class="ag-form-row">
						<div class="ag-form-group">
							<label for="customer-email"><?php esc_html_e( 'E-posta', 'appointment-general' ); ?> <span class="required">*</span></label>
							<input type="email" name="customer_email" id="customer-email" required placeholder="<?php esc_attr_e( 'ornek@email.com', 'appointment-general' ); ?>">
						</div>
						<div class="ag-form-group">
							<label for="customer-phone"><?php esc_html_e( 'Telefon', 'appointment-general' ); ?></label>
							<input type="tel" name="customer_phone" id="customer-phone" placeholder="<?php esc_attr_e( '05XX XXX XX XX', 'appointment-general' ); ?>">
						</div>
					</div>

					<div class="ag-form-group">
						<label for="customer-notes"><?php esc_html_e( 'Notlar', 'appointment-general' ); ?></label>
						<textarea name="notes" id="customer-notes" rows="3" placeholder="<?php esc_attr_e( 'Eklemek istediğiniz notlar (opsiyonel)', 'appointment-general' ); ?>"></textarea>
					</div>
				</div>

				<!-- Summary -->
				<div class="ag-booking-summary">
					<h4><?php esc_html_e( 'Randevu Özeti', 'appointment-general' ); ?></h4>
					<div class="ag-summary-content" id="ag-booking-summary">
						<!-- JS ile doldurulacak -->
					</div>
				</div>
			</div>

			<!-- Success Message -->
			<div class="ag-step ag-success-step" data-step="<?php echo esc_attr( $total_steps + 1 ); ?>" data-step-key="success" style="display: none;">
				<div class="ag-success-icon">
					<svg viewBox="0 0 24 24" width="64" height="64"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
				</div>
				<h3 class="ag-success-title"><?php esc_html_e( 'Randevunuz Alındı!', 'appointment-general' ); ?></h3>
				<p class="ag-success-message"><?php esc_html_e( 'Randevu bilgileriniz e-posta adresinize gönderilecektir.', 'appointment-general' ); ?></p>
				<div class="ag-success-details" id="ag-success-details">
					<!-- JS ile doldurulacak -->
				</div>
			</div>

			<!-- Navigation Buttons -->
			<div class="ag-form-actions" id="ag-form-actions">
				<button type="button" class="ag-btn ag-btn-secondary" id="ag-prev-btn" style="display: none;">
					<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
					<?php esc_html_e( 'Geri', 'appointment-general' ); ?>
				</button>
				<button type="button" class="ag-btn ag-btn-primary" id="ag-next-btn" disabled>
					<?php esc_html_e( 'Devam', 'appointment-general' ); ?>
					<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8-8-8z"/></svg>
				</button>
				<button type="submit" class="ag-btn ag-btn-primary ag-btn-submit" id="ag-submit-btn" style="display: none;">
					<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
					<?php esc_html_e( 'Randevu Al', 'appointment-general' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>

<script>
	var agServicesData = <?php echo wp_json_encode( $services ); ?>;
	var agFlowConfig = {
		mode: <?php echo wp_json_encode( $flow_mode ); ?>,
		steps: <?php echo wp_json_encode( $current_steps ); ?>,
		stepNumbers: <?php echo wp_json_encode( $step_numbers ); ?>,
		totalSteps: <?php echo (int) $total_steps; ?>,
		hasServiceStep: <?php echo $has_service_step ? 'true' : 'false'; ?>,
		hasStaffStep: <?php echo $has_staff_step ? 'true' : 'false'; ?>,
		multiService: <?php echo $multi_service ? 'true' : 'false'; ?>
	};
</script>
