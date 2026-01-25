<?php
/**
 * Booking Management Template - Müşteri randevu yönetimi
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Token kontrolü.
$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

if ( empty( $token ) ) {
	echo '<div class="ag-manage-error">';
	echo '<p>' . esc_html__( 'Geçersiz randevu linki.', 'appointment-general' ) . '</p>';
	echo '</div>';
	return;
}

// Booking Manager yükle.
if ( ! class_exists( 'AG_Booking_Manager' ) ) {
	require_once AG_PLUGIN_DIR . 'includes/class-ag-booking-manager.php';
}

$booking_manager = new AG_Booking_Manager();
$booking_model   = new AG_Booking();

// Randevuyu getir.
$booking = $booking_model->get_by_token( $token );

if ( ! $booking ) {
	echo '<div class="ag-manage-error">';
	echo '<p>' . esc_html__( 'Randevu bulunamadı.', 'appointment-general' ) . '</p>';
	echo '</div>';
	return;
}

// Detaylı bilgileri getir.
$booking = $booking_model->get_with_details( $booking->id );

// Ayarları al.
$date_format     = get_option( 'ag_date_format', 'd.m.Y' );
$time_format     = get_option( 'ag_time_format', 'H:i' );
$currency_symbol = get_option( 'ag_currency_symbol', '₺' );
$company_name    = get_option( 'ag_company_name', get_bloginfo( 'name' ) );

// İptal ve değiştirme kontrolü.
$can_cancel     = $booking_manager->can_cancel( $booking );
$can_reschedule = $booking_manager->can_reschedule( $booking );

// Durum renkleri.
$status_colors = array(
	'pending'   => '#f59e0b',
	'confirmed' => '#10b981',
	'cancelled' => '#ef4444',
	'completed' => '#6b7280',
	'no_show'   => '#8b5cf6',
);

$status_labels = array(
	'pending'   => __( 'Beklemede', 'appointment-general' ),
	'confirmed' => __( 'Onaylandı', 'appointment-general' ),
	'cancelled' => __( 'İptal Edildi', 'appointment-general' ),
	'completed' => __( 'Tamamlandı', 'appointment-general' ),
	'no_show'   => __( 'Gelmedi', 'appointment-general' ),
);

$formatted_date = date_i18n( $date_format, strtotime( $booking->booking_date ) );
$formatted_time = date_i18n( $time_format, strtotime( $booking->start_time ) );
?>

<div class="ag-manage-booking" data-token="<?php echo esc_attr( $token ); ?>">
	<div class="ag-manage-header">
		<h2><?php esc_html_e( 'Randevu Detayları', 'appointment-general' ); ?></h2>
		<p class="ag-company-name"><?php echo esc_html( $company_name ); ?></p>
	</div>

	<!-- Durum Kartı -->
	<div class="ag-status-card" style="border-left-color: <?php echo esc_attr( $status_colors[ $booking->status ] ?? '#6b7280' ); ?>;">
		<div class="ag-status-badge" style="background-color: <?php echo esc_attr( $status_colors[ $booking->status ] ?? '#6b7280' ); ?>;">
			<?php echo esc_html( $status_labels[ $booking->status ] ?? $booking->status ); ?>
		</div>
		<div class="ag-booking-id">
			<?php
			/* translators: %d: Booking ID */
			printf( esc_html__( 'Randevu No: #%d', 'appointment-general' ), $booking->id );
			?>
		</div>
	</div>

	<!-- Randevu Bilgileri -->
	<div class="ag-manage-card">
		<div class="ag-info-row">
			<span class="ag-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
					<line x1="16" y1="2" x2="16" y2="6"></line>
					<line x1="8" y1="2" x2="8" y2="6"></line>
					<line x1="3" y1="10" x2="21" y2="10"></line>
				</svg>
			</span>
			<div class="ag-info-content">
				<span class="ag-info-label"><?php esc_html_e( 'Tarih ve Saat', 'appointment-general' ); ?></span>
				<span class="ag-info-value"><?php echo esc_html( $formatted_date . ' - ' . $formatted_time ); ?></span>
			</div>
		</div>

		<div class="ag-info-row">
			<span class="ag-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
					<line x1="7" y1="7" x2="7.01" y2="7"></line>
				</svg>
			</span>
			<div class="ag-info-content">
				<span class="ag-info-label"><?php esc_html_e( 'Hizmet', 'appointment-general' ); ?></span>
				<span class="ag-info-value"><?php echo esc_html( $booking->service_name ); ?></span>
			</div>
		</div>

		<div class="ag-info-row">
			<span class="ag-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
					<circle cx="12" cy="7" r="4"></circle>
				</svg>
			</span>
			<div class="ag-info-content">
				<span class="ag-info-label"><?php esc_html_e( 'Personel', 'appointment-general' ); ?></span>
				<span class="ag-info-value"><?php echo esc_html( $booking->staff_name ); ?></span>
			</div>
		</div>

		<div class="ag-info-row">
			<span class="ag-info-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<line x1="12" y1="1" x2="12" y2="23"></line>
					<path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
				</svg>
			</span>
			<div class="ag-info-content">
				<span class="ag-info-label"><?php esc_html_e( 'Ücret', 'appointment-general' ); ?></span>
				<span class="ag-info-value"><?php echo esc_html( $booking->price . ' ' . $currency_symbol ); ?></span>
			</div>
		</div>
	</div>

	<?php if ( 'cancelled' !== $booking->status && 'completed' !== $booking->status ) : ?>
	<!-- Aksiyon Butonları -->
	<div class="ag-manage-actions">
		<?php if ( $can_reschedule['can_reschedule'] ) : ?>
		<button type="button" class="ag-btn ag-btn-primary ag-btn-reschedule" id="ag-open-reschedule">
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
				<line x1="16" y1="2" x2="16" y2="6"></line>
				<line x1="8" y1="2" x2="8" y2="6"></line>
				<line x1="3" y1="10" x2="21" y2="10"></line>
			</svg>
			<?php esc_html_e( 'Randevuyu Değiştir', 'appointment-general' ); ?>
		</button>
		<?php elseif ( ! empty( $can_reschedule['reason'] ) ) : ?>
		<div class="ag-notice ag-notice-warning">
			<p><?php echo esc_html( $can_reschedule['reason'] ); ?></p>
		</div>
		<?php endif; ?>

		<?php if ( $can_cancel['can_cancel'] ) : ?>
		<button type="button" class="ag-btn ag-btn-danger ag-btn-cancel" id="ag-open-cancel">
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="12" cy="12" r="10"></circle>
				<line x1="15" y1="9" x2="9" y2="15"></line>
				<line x1="9" y1="9" x2="15" y2="15"></line>
			</svg>
			<?php esc_html_e( 'Randevuyu İptal Et', 'appointment-general' ); ?>
		</button>
		<?php elseif ( ! empty( $can_cancel['reason'] ) ) : ?>
		<div class="ag-notice ag-notice-warning">
			<p><?php echo esc_html( $can_cancel['reason'] ); ?></p>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- Yeniden Planlama Modalı -->
	<div class="ag-modal" id="ag-reschedule-modal" style="display: none;">
		<div class="ag-modal-overlay"></div>
		<div class="ag-modal-content">
			<div class="ag-modal-header">
				<h3><?php esc_html_e( 'Randevuyu Değiştir', 'appointment-general' ); ?></h3>
				<button type="button" class="ag-modal-close">&times;</button>
			</div>
			<div class="ag-modal-body">
				<form id="ag-reschedule-form">
					<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
					<input type="hidden" name="staff_id" value="<?php echo esc_attr( $booking->staff_id ); ?>">
					<input type="hidden" name="service_id" value="<?php echo esc_attr( $booking->service_id ); ?>">

					<div class="ag-form-group">
						<label for="ag-reschedule-date"><?php esc_html_e( 'Yeni Tarih', 'appointment-general' ); ?></label>
						<input type="date" id="ag-reschedule-date" name="new_date" required
							min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>"
							max="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+' . get_option( 'ag_booking_future_days', 30 ) . ' days' ) ) ); ?>">
					</div>

					<div class="ag-form-group">
						<label for="ag-reschedule-time"><?php esc_html_e( 'Yeni Saat', 'appointment-general' ); ?></label>
						<select id="ag-reschedule-time" name="new_time" required disabled>
							<option value=""><?php esc_html_e( 'Önce tarih seçin', 'appointment-general' ); ?></option>
						</select>
					</div>

					<div class="ag-form-actions">
						<button type="button" class="ag-btn ag-btn-secondary ag-modal-cancel">
							<?php esc_html_e( 'Vazgeç', 'appointment-general' ); ?>
						</button>
						<button type="submit" class="ag-btn ag-btn-primary" id="ag-submit-reschedule">
							<?php esc_html_e( 'Randevuyu Güncelle', 'appointment-general' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- İptal Modalı -->
	<div class="ag-modal" id="ag-cancel-modal" style="display: none;">
		<div class="ag-modal-overlay"></div>
		<div class="ag-modal-content">
			<div class="ag-modal-header">
				<h3><?php esc_html_e( 'Randevuyu İptal Et', 'appointment-general' ); ?></h3>
				<button type="button" class="ag-modal-close">&times;</button>
			</div>
			<div class="ag-modal-body">
				<div class="ag-cancel-warning">
					<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
					<p><?php esc_html_e( 'Bu işlem geri alınamaz. Randevunuz iptal edilecektir.', 'appointment-general' ); ?></p>
				</div>

				<form id="ag-cancel-form">
					<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">

					<div class="ag-form-group">
						<label for="ag-cancel-reason"><?php esc_html_e( 'İptal Nedeni (opsiyonel)', 'appointment-general' ); ?></label>
						<textarea id="ag-cancel-reason" name="reason" rows="3" placeholder="<?php esc_attr_e( 'Neden iptal etmek istiyorsunuz?', 'appointment-general' ); ?>"></textarea>
					</div>

					<div class="ag-form-actions">
						<button type="button" class="ag-btn ag-btn-secondary ag-modal-cancel">
							<?php esc_html_e( 'Vazgeç', 'appointment-general' ); ?>
						</button>
						<button type="submit" class="ag-btn ag-btn-danger" id="ag-submit-cancel">
							<?php esc_html_e( 'Evet, İptal Et', 'appointment-general' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<style>
.ag-manage-booking {
	max-width: 500px;
	margin: 0 auto;
	padding: 20px;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

.ag-manage-header {
	text-align: center;
	margin-bottom: 24px;
}

.ag-manage-header h2 {
	margin: 0 0 8px 0;
	font-size: 24px;
	font-weight: 600;
	color: #1f2937;
}

.ag-company-name {
	margin: 0;
	color: #6b7280;
	font-size: 14px;
}

.ag-status-card {
	background: #fff;
	border-radius: 12px;
	padding: 16px;
	margin-bottom: 16px;
	border-left: 4px solid #6b7280;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.ag-status-badge {
	color: #fff;
	font-size: 12px;
	font-weight: 600;
	padding: 6px 12px;
	border-radius: 20px;
	text-transform: uppercase;
}

.ag-booking-id {
	color: #6b7280;
	font-size: 13px;
}

.ag-manage-card {
	background: #fff;
	border-radius: 12px;
	padding: 16px;
	margin-bottom: 16px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.ag-info-row {
	display: flex;
	align-items: center;
	padding: 12px 0;
	border-bottom: 1px solid #f3f4f6;
}

.ag-info-row:last-child {
	border-bottom: none;
}

.ag-info-icon {
	width: 40px;
	height: 40px;
	background: #f3f4f6;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 12px;
	color: #6b7280;
}

.ag-info-content {
	flex: 1;
}

.ag-info-label {
	display: block;
	font-size: 12px;
	color: #9ca3af;
	margin-bottom: 2px;
}

.ag-info-value {
	font-size: 15px;
	font-weight: 500;
	color: #1f2937;
}

.ag-manage-actions {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.ag-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	padding: 14px 24px;
	font-size: 15px;
	font-weight: 500;
	border: none;
	border-radius: 10px;
	cursor: pointer;
	transition: all 0.2s ease;
	width: 100%;
}

.ag-btn-primary {
	background: #3b82f6;
	color: #fff;
}

.ag-btn-primary:hover {
	background: #2563eb;
}

.ag-btn-secondary {
	background: #f3f4f6;
	color: #374151;
}

.ag-btn-secondary:hover {
	background: #e5e7eb;
}

.ag-btn-danger {
	background: #fee2e2;
	color: #dc2626;
}

.ag-btn-danger:hover {
	background: #fecaca;
}

.ag-notice {
	padding: 12px 16px;
	border-radius: 8px;
	font-size: 14px;
}

.ag-notice-warning {
	background: #fef3c7;
	color: #92400e;
}

.ag-notice p {
	margin: 0;
}

/* Modal Styles */
.ag-modal {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 9999;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 20px;
}

.ag-modal-overlay {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0,0,0,0.5);
}

.ag-modal-content {
	position: relative;
	background: #fff;
	border-radius: 16px;
	width: 100%;
	max-width: 400px;
	max-height: 90vh;
	overflow-y: auto;
	animation: modalSlideIn 0.2s ease;
}

@keyframes modalSlideIn {
	from {
		opacity: 0;
		transform: translateY(20px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.ag-modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 16px 20px;
	border-bottom: 1px solid #e5e7eb;
}

.ag-modal-header h3 {
	margin: 0;
	font-size: 18px;
	font-weight: 600;
	color: #1f2937;
}

.ag-modal-close {
	background: none;
	border: none;
	font-size: 24px;
	color: #9ca3af;
	cursor: pointer;
	line-height: 1;
	padding: 0;
}

.ag-modal-close:hover {
	color: #6b7280;
}

.ag-modal-body {
	padding: 20px;
}

.ag-form-group {
	margin-bottom: 16px;
}

.ag-form-group label {
	display: block;
	font-size: 14px;
	font-weight: 500;
	color: #374151;
	margin-bottom: 6px;
}

.ag-form-group input,
.ag-form-group select,
.ag-form-group textarea {
	width: 100%;
	padding: 12px;
	font-size: 15px;
	border: 1px solid #d1d5db;
	border-radius: 8px;
	box-sizing: border-box;
}

.ag-form-group input:focus,
.ag-form-group select:focus,
.ag-form-group textarea:focus {
	outline: none;
	border-color: #3b82f6;
	box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.ag-form-actions {
	display: flex;
	gap: 12px;
	margin-top: 20px;
}

.ag-form-actions .ag-btn {
	flex: 1;
}

.ag-cancel-warning {
	text-align: center;
	padding: 20px;
	background: #fef2f2;
	border-radius: 12px;
	margin-bottom: 20px;
}

.ag-cancel-warning p {
	margin: 12px 0 0 0;
	color: #991b1b;
	font-size: 14px;
}

.ag-manage-error {
	max-width: 400px;
	margin: 40px auto;
	padding: 24px;
	text-align: center;
	background: #fef2f2;
	border-radius: 12px;
}

.ag-manage-error p {
	margin: 0;
	color: #991b1b;
}

/* Loading State */
.ag-btn.loading {
	opacity: 0.7;
	pointer-events: none;
}

.ag-btn.loading::after {
	content: "";
	display: inline-block;
	width: 16px;
	height: 16px;
	border: 2px solid transparent;
	border-top-color: currentColor;
	border-radius: 50%;
	animation: spin 0.6s linear infinite;
	margin-left: 8px;
}

@keyframes spin {
	to { transform: rotate(360deg); }
}
</style>

<script>
(function() {
	'use strict';

	var token = '<?php echo esc_js( $token ); ?>';
	var restUrl = '<?php echo esc_url( rest_url( 'ag/v1/' ) ); ?>';

	// Modal açma/kapama.
	var rescheduleBtn = document.getElementById('ag-open-reschedule');
	var cancelBtn = document.getElementById('ag-open-cancel');
	var rescheduleModal = document.getElementById('ag-reschedule-modal');
	var cancelModal = document.getElementById('ag-cancel-modal');

	function openModal(modal) {
		if (modal) {
			modal.style.display = 'flex';
			document.body.style.overflow = 'hidden';
		}
	}

	function closeModal(modal) {
		if (modal) {
			modal.style.display = 'none';
			document.body.style.overflow = '';
		}
	}

	if (rescheduleBtn) {
		rescheduleBtn.addEventListener('click', function() {
			openModal(rescheduleModal);
		});
	}

	if (cancelBtn) {
		cancelBtn.addEventListener('click', function() {
			openModal(cancelModal);
		});
	}

	// Modal kapatma butonları.
	document.querySelectorAll('.ag-modal-close, .ag-modal-cancel, .ag-modal-overlay').forEach(function(el) {
		el.addEventListener('click', function() {
			closeModal(rescheduleModal);
			closeModal(cancelModal);
		});
	});

	// ESC ile modal kapatma.
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			closeModal(rescheduleModal);
			closeModal(cancelModal);
		}
	});

	// Tarih değiştiğinde slotları getir.
	var dateInput = document.getElementById('ag-reschedule-date');
	var timeSelect = document.getElementById('ag-reschedule-time');
	var staffId = document.querySelector('input[name="staff_id"]');
	var serviceId = document.querySelector('input[name="service_id"]');

	if (dateInput && timeSelect) {
		dateInput.addEventListener('change', function() {
			var date = this.value;
			if (!date) return;

			timeSelect.disabled = true;
			timeSelect.innerHTML = '<option value=""><?php echo esc_js( __( 'Yükleniyor...', 'appointment-general' ) ); ?></option>';

			fetch(restUrl + 'bookings/' + token + '/available-slots?date=' + date)
				.then(function(response) { return response.json(); })
				.then(function(data) {
					if (data.success && data.data.length > 0) {
						timeSelect.innerHTML = '<option value=""><?php echo esc_js( __( 'Saat seçin', 'appointment-general' ) ); ?></option>';
						data.data.forEach(function(slot) {
							var option = document.createElement('option');
							option.value = slot.start;
							option.textContent = slot.start + ' - ' + slot.end;
							timeSelect.appendChild(option);
						});
						timeSelect.disabled = false;
					} else {
						timeSelect.innerHTML = '<option value=""><?php echo esc_js( __( 'Müsait slot yok', 'appointment-general' ) ); ?></option>';
					}
				})
				.catch(function(error) {
					timeSelect.innerHTML = '<option value=""><?php echo esc_js( __( 'Hata oluştu', 'appointment-general' ) ); ?></option>';
				});
		});
	}

	// Yeniden planlama formu.
	var rescheduleForm = document.getElementById('ag-reschedule-form');
	if (rescheduleForm) {
		rescheduleForm.addEventListener('submit', function(e) {
			e.preventDefault();

			var submitBtn = document.getElementById('ag-submit-reschedule');
			submitBtn.classList.add('loading');

			var formData = {
				new_date: dateInput.value,
				new_time: timeSelect.value
			};

			fetch(restUrl + 'bookings/' + token + '/reschedule', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(formData)
			})
			.then(function(response) { return response.json(); })
			.then(function(data) {
				submitBtn.classList.remove('loading');
				if (data.success) {
					alert(data.message || '<?php echo esc_js( __( 'Randevunuz güncellendi!', 'appointment-general' ) ); ?>');
					window.location.reload();
				} else {
					alert(data.message || '<?php echo esc_js( __( 'Bir hata oluştu.', 'appointment-general' ) ); ?>');
				}
			})
			.catch(function(error) {
				submitBtn.classList.remove('loading');
				alert('<?php echo esc_js( __( 'Bir hata oluştu.', 'appointment-general' ) ); ?>');
			});
		});
	}

	// İptal formu.
	var cancelForm = document.getElementById('ag-cancel-form');
	if (cancelForm) {
		cancelForm.addEventListener('submit', function(e) {
			e.preventDefault();

			var submitBtn = document.getElementById('ag-submit-cancel');
			submitBtn.classList.add('loading');

			var reason = document.getElementById('ag-cancel-reason').value;

			fetch(restUrl + 'bookings/' + token + '/cancel', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({ reason: reason })
			})
			.then(function(response) { return response.json(); })
			.then(function(data) {
				submitBtn.classList.remove('loading');
				if (data.success) {
					alert(data.message || '<?php echo esc_js( __( 'Randevunuz iptal edildi!', 'appointment-general' ) ); ?>');
					window.location.reload();
				} else {
					alert(data.message || '<?php echo esc_js( __( 'Bir hata oluştu.', 'appointment-general' ) ); ?>');
				}
			})
			.catch(function(error) {
				submitBtn.classList.remove('loading');
				alert('<?php echo esc_js( __( 'Bir hata oluştu.', 'appointment-general' ) ); ?>');
			});
		});
	}
})();
</script>
