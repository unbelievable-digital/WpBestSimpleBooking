<?php
/**
 * Admin Staff Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ag-admin-wrap">
	<div class="ag-admin-header">
		<h1><?php esc_html_e( 'Personel', 'appointment-general' ); ?></h1>
		<button type="button" class="ag-btn ag-btn-primary" id="ag-add-staff">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'Yeni Personel', 'appointment-general' ); ?>
		</button>
	</div>

	<div class="ag-card">
		<div class="ag-card-body">
			<?php if ( ! empty( $staff ) ) : ?>
				<div class="ag-staff-grid">
					<?php foreach ( $staff as $s ) : ?>
						<div class="ag-staff-card" data-id="<?php echo esc_attr( $s->id ); ?>">
							<div class="ag-staff-avatar">
								<?php if ( $s->avatar_url ) : ?>
									<img src="<?php echo esc_url( $s->avatar_url ); ?>" alt="<?php echo esc_attr( $s->name ); ?>">
								<?php else : ?>
									<span class="ag-avatar-placeholder">
										<?php echo esc_html( mb_substr( $s->name, 0, 1 ) ); ?>
									</span>
								<?php endif; ?>
								<span class="ag-staff-status-dot ag-status-<?php echo esc_attr( $s->status ); ?>"></span>
							</div>
							<div class="ag-staff-info">
								<h3><?php echo esc_html( $s->name ); ?></h3>
								<?php if ( $s->email ) : ?>
									<p class="ag-staff-email"><?php echo esc_html( $s->email ); ?></p>
								<?php endif; ?>
								<?php if ( $s->phone ) : ?>
									<p class="ag-staff-phone"><?php echo esc_html( $s->phone ); ?></p>
								<?php endif; ?>
							</div>
							<div class="ag-staff-actions">
								<button type="button" class="ag-btn ag-btn-sm ag-btn-secondary ag-edit-hours" data-id="<?php echo esc_attr( $s->id ); ?>" data-name="<?php echo esc_attr( $s->name ); ?>">
									<span class="dashicons dashicons-clock"></span>
									<?php esc_html_e( 'Saatler', 'appointment-general' ); ?>
								</button>
								<button type="button" class="ag-btn ag-btn-sm ag-btn-icon ag-edit-staff" data-id="<?php echo esc_attr( $s->id ); ?>">
									<span class="dashicons dashicons-edit"></span>
								</button>
								<button type="button" class="ag-btn ag-btn-sm ag-btn-icon ag-btn-danger ag-delete-staff" data-id="<?php echo esc_attr( $s->id ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="ag-empty-state">
					<span class="dashicons dashicons-businessman"></span>
					<p><?php esc_html_e( 'Henüz personel eklenmemiş.', 'appointment-general' ); ?></p>
					<button type="button" class="ag-btn ag-btn-primary" id="ag-add-staff-empty">
						<?php esc_html_e( 'İlk Personeli Ekle', 'appointment-general' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Staff Modal -->
<div id="ag-staff-modal" class="ag-modal" style="display: none;">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content">
		<div class="ag-modal-header">
			<h3 id="ag-staff-modal-title"><?php esc_html_e( 'Yeni Personel', 'appointment-general' ); ?></h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body">
			<form id="ag-staff-form">
				<input type="hidden" name="id" id="staff-id" value="">

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="staff-name"><?php esc_html_e( 'Ad Soyad', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="text" id="staff-name" name="name" required>
					</div>
				</div>

				<div class="ag-form-row ag-form-row-2">
					<div class="ag-form-group">
						<label for="staff-email"><?php esc_html_e( 'E-posta', 'appointment-general' ); ?></label>
						<input type="email" id="staff-email" name="email">
					</div>
					<div class="ag-form-group">
						<label for="staff-phone"><?php esc_html_e( 'Telefon', 'appointment-general' ); ?></label>
						<input type="tel" id="staff-phone" name="phone">
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="staff-bio"><?php esc_html_e( 'Biyografi', 'appointment-general' ); ?></label>
						<textarea id="staff-bio" name="bio" rows="3"></textarea>
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label><?php esc_html_e( 'Sunduğu Hizmetler', 'appointment-general' ); ?></label>
						<div class="ag-checkbox-group">
							<?php foreach ( $services as $service ) : ?>
								<label class="ag-checkbox-label">
									<input type="checkbox" name="services[]" value="<?php echo esc_attr( $service->id ); ?>">
									<span class="ag-color-dot" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
									<?php echo esc_html( $service->name ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="staff-status"><?php esc_html_e( 'Durum', 'appointment-general' ); ?></label>
						<select id="staff-status" name="status">
							<option value="active"><?php esc_html_e( 'Aktif', 'appointment-general' ); ?></option>
							<option value="inactive"><?php esc_html_e( 'Pasif', 'appointment-general' ); ?></option>
						</select>
					</div>
				</div>
			</form>
		</div>
		<div class="ag-modal-footer">
			<button type="button" class="ag-btn ag-btn-secondary ag-modal-close"><?php esc_html_e( 'İptal', 'appointment-general' ); ?></button>
			<button type="button" class="ag-btn ag-btn-primary" id="ag-save-staff"><?php esc_html_e( 'Kaydet', 'appointment-general' ); ?></button>
		</div>
	</div>
</div>

<!-- Working Hours Modal -->
<div id="ag-hours-modal" class="ag-modal" style="display: none;">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content ag-modal-lg">
		<div class="ag-modal-header">
			<h3 id="ag-hours-modal-title"><?php esc_html_e( 'Çalışma Saatleri', 'appointment-general' ); ?></h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body">
			<form id="ag-hours-form">
				<input type="hidden" name="staff_id" id="hours-staff-id" value="">

				<table class="ag-table ag-hours-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Gün', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Çalışıyor', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Başlangıç', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Bitiş', 'appointment-general' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$days = array(
							1 => __( 'Pazartesi', 'appointment-general' ),
							2 => __( 'Salı', 'appointment-general' ),
							3 => __( 'Çarşamba', 'appointment-general' ),
							4 => __( 'Perşembe', 'appointment-general' ),
							5 => __( 'Cuma', 'appointment-general' ),
							6 => __( 'Cumartesi', 'appointment-general' ),
							0 => __( 'Pazar', 'appointment-general' ),
						);
						foreach ( $days as $day_num => $day_name ) :
							?>
							<tr>
								<td><strong><?php echo esc_html( $day_name ); ?></strong></td>
								<td>
									<label class="ag-switch">
										<input type="checkbox" name="hours[<?php echo esc_attr( $day_num ); ?>][is_working]" value="1" <?php echo 0 !== $day_num ? 'checked' : ''; ?>>
										<span class="ag-switch-slider"></span>
									</label>
								</td>
								<td>
									<input type="time" name="hours[<?php echo esc_attr( $day_num ); ?>][start_time]" value="09:00">
								</td>
								<td>
									<input type="time" name="hours[<?php echo esc_attr( $day_num ); ?>][end_time]" value="18:00">
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</form>
		</div>
		<div class="ag-modal-footer">
			<button type="button" class="ag-btn ag-btn-secondary ag-modal-close"><?php esc_html_e( 'İptal', 'appointment-general' ); ?></button>
			<button type="button" class="ag-btn ag-btn-primary" id="ag-save-hours"><?php esc_html_e( 'Kaydet', 'appointment-general' ); ?></button>
		</div>
	</div>
</div>

<script>
	var agStaff = <?php echo wp_json_encode( $staff ); ?>;
	var agServices = <?php echo wp_json_encode( $services ); ?>;
</script>
