<?php
/**
 * Admin Categories Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<h1><?php esc_html_e( 'Categories', 'unbelievable-salon-booking' ); ?></h1>
		<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-category">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'New Category', 'unbelievable-salon-booking' ); ?>
		</button>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body">
			<?php if ( ! empty( $categories ) ) : ?>
				<table class="unbsb-table unbsb-table-striped" id="unbsb-categories-table">
					<thead>
						<tr>
							<th style="width: 40px;"></th>
							<th><?php esc_html_e( 'Category Name', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Service Count', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Order', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $categories as $category ) : ?>
							<tr data-id="<?php echo esc_attr( $category->id ); ?>">
								<td>
									<span class="unbsb-color-dot" style="background-color: <?php echo esc_attr( $category->color ); ?>"></span>
								</td>
								<td>
									<strong><?php echo esc_html( $category->name ); ?></strong>
									<?php if ( $category->description ) : ?>
										<div class="unbsb-text-small unbsb-text-muted">
											<?php echo esc_html( wp_trim_words( $category->description, 10 ) ); ?>
										</div>
									<?php endif; ?>
								</td>
								<td>
									<span class="unbsb-badge"><?php echo esc_html( $category->service_count ); ?></span>
								</td>
								<td><?php echo esc_html( $category->sort_order ); ?></td>
								<td>
									<span class="unbsb-status unbsb-status-<?php echo esc_attr( $category->status ); ?>">
										<?php echo 'active' === $category->status ? esc_html__( 'Active', 'unbelievable-salon-booking' ) : esc_html__( 'Inactive', 'unbelievable-salon-booking' ); ?>
									</span>
								</td>
								<td>
									<div class="unbsb-actions">
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-edit-category" data-id="<?php echo esc_attr( $category->id ); ?>" title="<?php esc_attr_e( 'Edit', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-btn-danger unbsb-delete-category" data-id="<?php echo esc_attr( $category->id ); ?>" data-service-count="<?php echo esc_attr( $category->service_count ); ?>" title="<?php esc_attr_e( 'Delete', 'unbelievable-salon-booking' ); ?>">
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
					<span class="dashicons dashicons-category"></span>
					<p><?php esc_html_e( 'No categories added yet.', 'unbelievable-salon-booking' ); ?></p>
					<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-category-empty">
						<?php esc_html_e( 'Add First Category', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Category Modal -->
<div id="unbsb-category-modal" class="unbsb-modal unbsb-modal-category" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-compact">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-category">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-category"></span>
				</div>
				<div>
					<h3 id="unbsb-category-modal-title"><?php esc_html_e( 'New Category', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle"><?php esc_html_e( 'Add a category to group services', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body">
			<form id="unbsb-category-form">
				<input type="hidden" name="id" id="category-id" value="">

				<div class="unbsb-form-group">
					<label for="category-name"><?php esc_html_e( 'Category Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
					<input type="text" id="category-name" name="name" placeholder="<?php esc_attr_e( 'e.g. Hair Care, Manicure, Massage', 'unbelievable-salon-booking' ); ?>" required>
				</div>

				<div class="unbsb-form-group">
					<label for="category-description"><?php esc_html_e( 'Description', 'unbelievable-salon-booking' ); ?></label>
					<textarea id="category-description" name="description" rows="2" placeholder="<?php esc_attr_e( 'Short description about the category...', 'unbelievable-salon-booking' ); ?>"></textarea>
				</div>

				<div class="unbsb-form-row-3">
					<div class="unbsb-form-group">
						<label for="category-color"><?php esc_html_e( 'Color', 'unbelievable-salon-booking' ); ?></label>
						<div class="unbsb-color-picker-wrap">
							<input type="color" id="category-color" name="color" value="#3788d8">
							<span class="unbsb-color-label" id="category-color-label">#3788d8</span>
						</div>
					</div>
					<div class="unbsb-form-group">
						<label for="category-sort-order"><?php esc_html_e( 'Sort Order', 'unbelievable-salon-booking' ); ?></label>
						<input type="number" id="category-sort-order" name="sort_order" value="0" min="0">
						<small class="unbsb-help-text"><?php esc_html_e( 'Lower value first', 'unbelievable-salon-booking' ); ?></small>
					</div>
					<div class="unbsb-form-group">
						<label><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></label>
						<div class="unbsb-toggle-group unbsb-toggle-group-vertical">
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

				<div class="unbsb-color-presets">
					<span class="unbsb-presets-label"><?php esc_html_e( 'Quick Colors:', 'unbelievable-salon-booking' ); ?></span>
					<button type="button" class="unbsb-color-preset" data-color="#3788d8" style="background-color: #3788d8"></button>
					<button type="button" class="unbsb-color-preset" data-color="#10b981" style="background-color: #10b981"></button>
					<button type="button" class="unbsb-color-preset" data-color="#f59e0b" style="background-color: #f59e0b"></button>
					<button type="button" class="unbsb-color-preset" data-color="#ef4444" style="background-color: #ef4444"></button>
					<button type="button" class="unbsb-color-preset" data-color="#8b5cf6" style="background-color: #8b5cf6"></button>
					<button type="button" class="unbsb-color-preset" data-color="#ec4899" style="background-color: #ec4899"></button>
					<button type="button" class="unbsb-color-preset" data-color="#14b8a6" style="background-color: #14b8a6"></button>
					<button type="button" class="unbsb-color-preset" data-color="#64748b" style="background-color: #64748b"></button>
				</div>
			</form>
		</div>
		<div class="unbsb-modal-footer">
			<button type="button" class="unbsb-btn unbsb-btn-ghost unbsb-modal-close">
				<span class="dashicons dashicons-no-alt"></span>
				<?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?>
			</button>
			<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" id="unbsb-save-category">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
