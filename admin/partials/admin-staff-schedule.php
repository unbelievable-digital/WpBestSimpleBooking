<?php
/**
 * Admin Staff Schedule Page
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Personelleri al.
$staff_model = new AG_Staff();
$all_staff   = $staff_model->get_active();

// Haftanın günleri.
$days_of_week = array(
	1 => __( 'Pazartesi', 'appointment-general' ),
	2 => __( 'Salı', 'appointment-general' ),
	3 => __( 'Çarşamba', 'appointment-general' ),
	4 => __( 'Perşembe', 'appointment-general' ),
	5 => __( 'Cuma', 'appointment-general' ),
	6 => __( 'Cumartesi', 'appointment-general' ),
	0 => __( 'Pazar', 'appointment-general' ),
);
?>

<div class="ag-schedule-page">
	<!-- Header -->
	<div class="ag-settings-header">
		<div class="ag-settings-header-content">
			<div class="ag-settings-header-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="ag-settings-header-text">
				<h1><?php esc_html_e( 'Çalışma Takvimi', 'appointment-general' ); ?></h1>
				<p><?php esc_html_e( 'Personel çalışma saatlerini, molalarını ve izinlerini yönetin.', 'appointment-general' ); ?></p>
			</div>
		</div>
	</div>

	<?php if ( empty( $all_staff ) ) : ?>
		<div class="ag-card" style="margin-top: 24px;">
			<div class="ag-card-body" style="text-align: center; padding: 60px 20px;">
				<span class="dashicons dashicons-admin-users" style="font-size: 48px; color: #d1d5db; margin-bottom: 15px;"></span>
				<h3><?php esc_html_e( 'Henüz personel eklenmemiş', 'appointment-general' ); ?></h3>
				<p style="color: #6b7280;"><?php esc_html_e( 'Çalışma takvimi ayarlamak için önce personel eklemelisiniz.', 'appointment-general' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-staff' ) ); ?>" class="ag-btn ag-btn-primary" style="margin-top: 15px;">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Personel Ekle', 'appointment-general' ); ?>
				</a>
			</div>
		</div>
	<?php else : ?>

	<!-- Personel Seçimi -->
	<div class="ag-schedule-staff-selector">
		<label for="ag-schedule-staff"><?php esc_html_e( 'Personel Seçin:', 'appointment-general' ); ?></label>
		<select id="ag-schedule-staff" class="ag-select-large">
			<?php foreach ( $all_staff as $staff ) : ?>
				<option value="<?php echo esc_attr( $staff->id ); ?>"><?php echo esc_html( $staff->name ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<!-- Ana İçerik Grid -->
	<div class="ag-schedule-grid">
		<!-- Sol: Haftalık Program -->
		<div class="ag-schedule-weekly">
			<div class="ag-card">
				<div class="ag-card-header">
					<h3>
						<span class="dashicons dashicons-clock"></span>
						<?php esc_html_e( 'Haftalık Çalışma Programı', 'appointment-general' ); ?>
					</h3>
				</div>
				<div class="ag-card-body" style="padding: 0;">
					<div class="ag-days-list" id="ag-days-list">
						<?php foreach ( $days_of_week as $day_num => $day_name ) : ?>
						<div class="ag-day-item" data-day="<?php echo esc_attr( $day_num ); ?>">
							<div class="ag-day-header">
								<label class="ag-day-toggle">
									<input type="checkbox" class="ag-day-working" data-day="<?php echo esc_attr( $day_num ); ?>">
									<span class="ag-day-name"><?php echo esc_html( $day_name ); ?></span>
								</label>
								<span class="ag-day-status"></span>
							</div>
							<div class="ag-day-content">
								<div class="ag-day-times">
									<div class="ag-time-row">
										<label><?php esc_html_e( 'Çalışma:', 'appointment-general' ); ?></label>
										<input type="text" class="ag-time-input ag-time-start" data-day="<?php echo esc_attr( $day_num ); ?>" value="09:00" placeholder="09:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
										<span class="ag-time-separator">-</span>
										<input type="text" class="ag-time-input ag-time-end" data-day="<?php echo esc_attr( $day_num ); ?>" value="18:00" placeholder="18:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
									</div>
								</div>
								<div class="ag-day-breaks">
									<div class="ag-breaks-header">
										<span><?php esc_html_e( 'Molalar', 'appointment-general' ); ?></span>
										<button type="button" class="ag-add-break-btn" data-day="<?php echo esc_attr( $day_num ); ?>">
											<span class="dashicons dashicons-plus-alt2"></span>
										</button>
									</div>
									<div class="ag-breaks-list" data-day="<?php echo esc_attr( $day_num ); ?>">
										<!-- Molalar JavaScript ile doldurulacak -->
									</div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="ag-card-footer">
					<button type="button" class="ag-btn ag-btn-primary" id="ag-save-schedule">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Programı Kaydet', 'appointment-general' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Sağ: İzin Takvimi -->
		<div class="ag-schedule-calendar-wrap">
			<div class="ag-card">
				<div class="ag-card-header">
					<h3>
						<span class="dashicons dashicons-calendar"></span>
						<?php esc_html_e( 'İzin Takvimi', 'appointment-general' ); ?>
					</h3>
				</div>
				<div class="ag-card-body">
					<div class="ag-schedule-calendar">
						<div class="ag-calendar-nav">
							<button type="button" class="ag-calendar-prev">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
							</button>
							<span class="ag-calendar-month-year"></span>
							<button type="button" class="ag-calendar-next">
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</button>
						</div>
						<div class="ag-calendar-weekdays">
							<span><?php esc_html_e( 'Pzt', 'appointment-general' ); ?></span>
							<span><?php esc_html_e( 'Sal', 'appointment-general' ); ?></span>
							<span><?php esc_html_e( 'Çar', 'appointment-general' ); ?></span>
							<span><?php esc_html_e( 'Per', 'appointment-general' ); ?></span>
							<span><?php esc_html_e( 'Cum', 'appointment-general' ); ?></span>
							<span><?php esc_html_e( 'Cmt', 'appointment-general' ); ?></span>
							<span><?php esc_html_e( 'Paz', 'appointment-general' ); ?></span>
						</div>
						<div class="ag-calendar-days" id="ag-calendar-days">
							<!-- Günler JavaScript ile doldurulacak -->
						</div>
					</div>
					<div class="ag-calendar-legend">
						<span class="ag-legend-item">
							<span class="ag-legend-dot ag-legend-holiday"></span>
							<?php esc_html_e( 'İzinli', 'appointment-general' ); ?>
						</span>
						<span class="ag-legend-item">
							<span class="ag-legend-dot ag-legend-today"></span>
							<?php esc_html_e( 'Bugün', 'appointment-general' ); ?>
						</span>
					</div>
				</div>
			</div>

			<!-- İzin Listesi -->
			<div class="ag-card" style="margin-top: 20px;">
				<div class="ag-card-header">
					<h3>
						<span class="dashicons dashicons-dismiss"></span>
						<?php esc_html_e( 'Kayıtlı İzinler', 'appointment-general' ); ?>
					</h3>
				</div>
				<div class="ag-card-body" style="padding: 0;">
					<div class="ag-holidays-list" id="ag-holidays-list">
						<!-- İzinler JavaScript ile doldurulacak -->
						<div class="ag-holidays-empty">
							<span class="dashicons dashicons-yes-alt"></span>
							<p><?php esc_html_e( 'Kayıtlı izin bulunmuyor', 'appointment-general' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php endif; ?>
</div>

<!-- İzin Ekleme Modal -->
<div id="ag-holiday-modal" class="ag-modal">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content ag-modal-sm">
		<div class="ag-modal-header">
			<h3>
				<span class="dashicons dashicons-calendar"></span>
				<?php esc_html_e( 'İzin Ekle', 'appointment-general' ); ?>
			</h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body">
			<input type="hidden" id="ag-holiday-date">
			<div class="ag-holiday-date-display">
				<span class="dashicons dashicons-calendar-alt"></span>
				<span id="ag-holiday-date-text"></span>
			</div>
			<div class="ag-form-group">
				<label for="ag-holiday-reason"><?php esc_html_e( 'Sebep (opsiyonel)', 'appointment-general' ); ?></label>
				<input type="text" id="ag-holiday-reason" placeholder="<?php esc_attr_e( 'Örn: Yıllık izin, Hastalık...', 'appointment-general' ); ?>">
			</div>
		</div>
		<div class="ag-modal-footer">
			<button type="button" class="ag-btn ag-btn-secondary ag-modal-cancel">
				<?php esc_html_e( 'İptal', 'appointment-general' ); ?>
			</button>
			<button type="button" class="ag-btn ag-btn-primary" id="ag-add-holiday-btn">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'İzin Ekle', 'appointment-general' ); ?>
			</button>
		</div>
	</div>
</div>
