<?php
/**
 * Admin Services Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'ag_currency_symbol', '₺' );
?>

<div class="ag-admin-wrap">
	<div class="ag-admin-header">
		<h1><?php esc_html_e( 'Hizmetler', 'appointment-general' ); ?></h1>
		<button type="button" class="ag-btn ag-btn-primary" id="ag-add-service">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'Yeni Hizmet', 'appointment-general' ); ?>
		</button>
	</div>

	<div class="ag-card">
		<div class="ag-card-body">
			<?php if ( ! empty( $services ) ) : ?>
				<table class="ag-table ag-table-striped" id="ag-services-table">
					<thead>
						<tr>
							<th style="width: 40px;"></th>
							<th><?php esc_html_e( 'Hizmet Adı', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Kategori', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Süre', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Ücret', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Durum', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'İşlemler', 'appointment-general' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $services as $service ) : ?>
							<tr data-id="<?php echo esc_attr( $service->id ); ?>">
								<td>
									<span class="ag-color-dot" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
								</td>
								<td>
									<strong><?php echo esc_html( $service->name ); ?></strong>
									<?php if ( $service->description ) : ?>
										<div class="ag-text-small ag-text-muted">
											<?php echo esc_html( wp_trim_words( $service->description, 10 ) ); ?>
										</div>
									<?php endif; ?>
								</td>
								<td>
									<?php if ( ! empty( $service->category_name ) ) : ?>
										<span class="ag-category-badge" style="background-color: <?php echo esc_attr( $service->category_color ); ?>20; color: <?php echo esc_attr( $service->category_color ); ?>; border: 1px solid <?php echo esc_attr( $service->category_color ); ?>40;">
											<?php echo esc_html( $service->category_name ); ?>
										</span>
									<?php else : ?>
										<span class="ag-text-muted">-</span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $service->duration ); ?> <?php esc_html_e( 'dk', 'appointment-general' ); ?></td>
								<td><?php echo esc_html( number_format( $service->price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
								<td>
									<span class="ag-status ag-status-<?php echo esc_attr( $service->status ); ?>">
										<?php echo 'active' === $service->status ? esc_html__( 'Aktif', 'appointment-general' ) : esc_html__( 'Pasif', 'appointment-general' ); ?>
									</span>
								</td>
								<td>
									<div class="ag-actions">
										<button type="button" class="ag-btn ag-btn-sm ag-btn-icon ag-edit-service" data-id="<?php echo esc_attr( $service->id ); ?>" title="<?php esc_attr_e( 'Düzenle', 'appointment-general' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="ag-btn ag-btn-sm ag-btn-icon ag-btn-danger ag-delete-service" data-id="<?php echo esc_attr( $service->id ); ?>" title="<?php esc_attr_e( 'Sil', 'appointment-general' ); ?>">
											<span class="dashicons dashicons-trash"></span>
										</button>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="ag-empty-state">
					<span class="dashicons dashicons-admin-tools"></span>
					<p><?php esc_html_e( 'Henüz hizmet eklenmemiş.', 'appointment-general' ); ?></p>
					<button type="button" class="ag-btn ag-btn-primary" id="ag-add-service-empty">
						<?php esc_html_e( 'İlk Hizmeti Ekle', 'appointment-general' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Service Modal -->
<div id="ag-service-modal" class="ag-modal" style="display: none;">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content">
		<div class="ag-modal-header">
			<h3 id="ag-service-modal-title"><?php esc_html_e( 'Yeni Hizmet', 'appointment-general' ); ?></h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body">
			<form id="ag-service-form">
				<input type="hidden" name="id" id="service-id" value="">

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="service-name"><?php esc_html_e( 'Hizmet Adı', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="text" id="service-name" name="name" required>
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="service-category"><?php esc_html_e( 'Kategori', 'appointment-general' ); ?></label>
						<select id="service-category" name="category_id">
							<option value=""><?php esc_html_e( '-- Kategori Seçin --', 'appointment-general' ); ?></option>
							<?php foreach ( $categories as $category ) : ?>
								<option value="<?php echo esc_attr( $category->id ); ?>"><?php echo esc_html( $category->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="service-description"><?php esc_html_e( 'Açıklama', 'appointment-general' ); ?></label>
						<textarea id="service-description" name="description" rows="3"></textarea>
					</div>
				</div>

				<div class="ag-form-row ag-form-row-2">
					<div class="ag-form-group">
						<label for="service-duration"><?php esc_html_e( 'Süre (dakika)', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="number" id="service-duration" name="duration" value="30" min="5" step="5" required>
					</div>
					<div class="ag-form-group">
						<label for="service-price"><?php esc_html_e( 'Ücret', 'appointment-general' ); ?> (<?php echo esc_html( $currency_symbol ); ?>)</label>
						<input type="number" id="service-price" name="price" value="0" min="0" step="0.01">
					</div>
				</div>

				<div class="ag-form-row ag-form-row-2">
					<div class="ag-form-group">
						<label for="service-buffer-before"><?php esc_html_e( 'Öncesi Tampon (dk)', 'appointment-general' ); ?></label>
						<input type="number" id="service-buffer-before" name="buffer_before" value="0" min="0" step="5">
						<small class="ag-help-text"><?php esc_html_e( 'Randevudan önce hazırlık süresi', 'appointment-general' ); ?></small>
					</div>
					<div class="ag-form-group">
						<label for="service-buffer-after"><?php esc_html_e( 'Sonrası Tampon (dk)', 'appointment-general' ); ?></label>
						<input type="number" id="service-buffer-after" name="buffer_after" value="0" min="0" step="5">
						<small class="ag-help-text"><?php esc_html_e( 'Randevudan sonra temizlik süresi', 'appointment-general' ); ?></small>
					</div>
				</div>

				<div class="ag-form-row ag-form-row-2">
					<div class="ag-form-group">
						<label for="service-color"><?php esc_html_e( 'Renk', 'appointment-general' ); ?></label>
						<input type="color" id="service-color" name="color" value="#3788d8">
					</div>
					<div class="ag-form-group">
						<label for="service-status"><?php esc_html_e( 'Durum', 'appointment-general' ); ?></label>
						<select id="service-status" name="status">
							<option value="active"><?php esc_html_e( 'Aktif', 'appointment-general' ); ?></option>
							<option value="inactive"><?php esc_html_e( 'Pasif', 'appointment-general' ); ?></option>
						</select>
					</div>
				</div>
			</form>
		</div>
		<div class="ag-modal-footer">
			<button type="button" class="ag-btn ag-btn-secondary ag-modal-close"><?php esc_html_e( 'İptal', 'appointment-general' ); ?></button>
			<button type="button" class="ag-btn ag-btn-primary" id="ag-save-service"><?php esc_html_e( 'Kaydet', 'appointment-general' ); ?></button>
		</div>
	</div>
</div>

<script>
	var agServices = <?php echo wp_json_encode( $services ); ?>;
	var agCategories = <?php echo wp_json_encode( $categories ); ?>;
</script>
