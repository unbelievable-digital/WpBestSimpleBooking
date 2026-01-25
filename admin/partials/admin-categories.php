<?php
/**
 * Admin Categories Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ag-admin-wrap">
	<div class="ag-admin-header">
		<h1><?php esc_html_e( 'Kategoriler', 'appointment-general' ); ?></h1>
		<button type="button" class="ag-btn ag-btn-primary" id="ag-add-category">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'Yeni Kategori', 'appointment-general' ); ?>
		</button>
	</div>

	<div class="ag-card">
		<div class="ag-card-body">
			<?php if ( ! empty( $categories ) ) : ?>
				<table class="ag-table ag-table-striped" id="ag-categories-table">
					<thead>
						<tr>
							<th style="width: 40px;"></th>
							<th><?php esc_html_e( 'Kategori Adı', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Hizmet Sayısı', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Sıra', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Durum', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'İşlemler', 'appointment-general' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $categories as $category ) : ?>
							<tr data-id="<?php echo esc_attr( $category->id ); ?>">
								<td>
									<span class="ag-color-dot" style="background-color: <?php echo esc_attr( $category->color ); ?>"></span>
								</td>
								<td>
									<strong><?php echo esc_html( $category->name ); ?></strong>
									<?php if ( $category->description ) : ?>
										<div class="ag-text-small ag-text-muted">
											<?php echo esc_html( wp_trim_words( $category->description, 10 ) ); ?>
										</div>
									<?php endif; ?>
								</td>
								<td>
									<span class="ag-badge"><?php echo esc_html( $category->service_count ); ?></span>
								</td>
								<td><?php echo esc_html( $category->sort_order ); ?></td>
								<td>
									<span class="ag-status ag-status-<?php echo esc_attr( $category->status ); ?>">
										<?php echo 'active' === $category->status ? esc_html__( 'Aktif', 'appointment-general' ) : esc_html__( 'Pasif', 'appointment-general' ); ?>
									</span>
								</td>
								<td>
									<div class="ag-actions">
										<button type="button" class="ag-btn ag-btn-sm ag-btn-icon ag-edit-category" data-id="<?php echo esc_attr( $category->id ); ?>" title="<?php esc_attr_e( 'Düzenle', 'appointment-general' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="ag-btn ag-btn-sm ag-btn-icon ag-btn-danger ag-delete-category" data-id="<?php echo esc_attr( $category->id ); ?>" data-service-count="<?php echo esc_attr( $category->service_count ); ?>" title="<?php esc_attr_e( 'Sil', 'appointment-general' ); ?>">
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
					<span class="dashicons dashicons-category"></span>
					<p><?php esc_html_e( 'Henüz kategori eklenmemiş.', 'appointment-general' ); ?></p>
					<button type="button" class="ag-btn ag-btn-primary" id="ag-add-category-empty">
						<?php esc_html_e( 'İlk Kategoriyi Ekle', 'appointment-general' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Category Modal -->
<div id="ag-category-modal" class="ag-modal" style="display: none;">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content">
		<div class="ag-modal-header">
			<h3 id="ag-category-modal-title"><?php esc_html_e( 'Yeni Kategori', 'appointment-general' ); ?></h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body">
			<form id="ag-category-form">
				<input type="hidden" name="id" id="category-id" value="">

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="category-name"><?php esc_html_e( 'Kategori Adı', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="text" id="category-name" name="name" required>
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group">
						<label for="category-description"><?php esc_html_e( 'Açıklama', 'appointment-general' ); ?></label>
						<textarea id="category-description" name="description" rows="3"></textarea>
					</div>
				</div>

				<div class="ag-form-row ag-form-row-3">
					<div class="ag-form-group">
						<label for="category-color"><?php esc_html_e( 'Renk', 'appointment-general' ); ?></label>
						<input type="color" id="category-color" name="color" value="#3788d8">
					</div>
					<div class="ag-form-group">
						<label for="category-sort-order"><?php esc_html_e( 'Sıra', 'appointment-general' ); ?></label>
						<input type="number" id="category-sort-order" name="sort_order" value="0" min="0">
					</div>
					<div class="ag-form-group">
						<label for="category-status"><?php esc_html_e( 'Durum', 'appointment-general' ); ?></label>
						<select id="category-status" name="status">
							<option value="active"><?php esc_html_e( 'Aktif', 'appointment-general' ); ?></option>
							<option value="inactive"><?php esc_html_e( 'Pasif', 'appointment-general' ); ?></option>
						</select>
					</div>
				</div>
			</form>
		</div>
		<div class="ag-modal-footer">
			<button type="button" class="ag-btn ag-btn-secondary ag-modal-close"><?php esc_html_e( 'İptal', 'appointment-general' ); ?></button>
			<button type="button" class="ag-btn ag-btn-primary" id="ag-save-category"><?php esc_html_e( 'Kaydet', 'appointment-general' ); ?></button>
		</div>
	</div>
</div>

<script>
	var agCategories = <?php echo wp_json_encode( $categories ); ?>;
</script>
