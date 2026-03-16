<?php
/**
 * Admin Customers Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<h1><?php esc_html_e( 'Customers', 'unbelievable-salon-booking' ); ?></h1>
		<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-customer">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'New Customer', 'unbelievable-salon-booking' ); ?>
		</button>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body">
			<?php if ( ! empty( $customers ) ) : ?>
				<table class="unbsb-table unbsb-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Registration Date', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $customers as $customer ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $customer->name ); ?></strong></td>
								<td>
									<a href="mailto:<?php echo esc_attr( $customer->email ); ?>">
										<?php echo esc_html( $customer->email ); ?>
									</a>
								</td>
								<td><?php echo esc_html( ! empty( $customer->phone ) ? $customer->phone : '-' ); ?></td>
								<td><?php echo esc_html( ! empty( $customer->notes ) ? wp_trim_words( $customer->notes, 8, '...' ) : '-' ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'unbsb_date_format', 'd.m.Y' ), strtotime( $customer->created_at ) ) ); ?></td>
								<td>
									<div class="unbsb-btn-group">
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-secondary unbsb-edit-customer"
											data-id="<?php echo esc_attr( $customer->id ); ?>"
											data-name="<?php echo esc_attr( $customer->name ); ?>"
											data-email="<?php echo esc_attr( $customer->email ); ?>"
											data-phone="<?php echo esc_attr( $customer->phone ); ?>"
											data-notes="<?php echo esc_attr( $customer->notes ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-danger unbsb-delete-customer" data-id="<?php echo esc_attr( $customer->id ); ?>">
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
					<span class="dashicons dashicons-groups"></span>
					<p><?php esc_html_e( 'No customers found yet.', 'unbelievable-salon-booking' ); ?></p>
					<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-add-customer-empty">
						<?php esc_html_e( 'Add First Customer', 'unbelievable-salon-booking' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Customer Modal -->
<div id="unbsb-customer-modal" class="unbsb-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content">
		<div class="unbsb-modal-header unbsb-modal-header-gradient">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-admin-users"></span>
				</div>
				<div>
					<h3 id="unbsb-customer-modal-title"><?php esc_html_e( 'New Customer', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle"><?php esc_html_e( 'Enter customer details', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body">
			<form id="unbsb-customer-form">
				<input type="hidden" name="id" id="customer-id" value="">

				<div class="unbsb-form-group">
					<label for="customer-name"><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
					<input type="text" id="customer-name" name="name" required>
				</div>

				<div class="unbsb-form-row-2">
					<div class="unbsb-form-group">
						<label for="customer-email">
							<span class="dashicons dashicons-email" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-primary);"></span>
							<?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?> <span class="required">*</span>
						</label>
						<input type="email" id="customer-email" name="email" required>
					</div>
					<div class="unbsb-form-group">
						<label for="customer-phone">
							<span class="dashicons dashicons-phone" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; color: var(--unbsb-success);"></span>
							<?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?>
						</label>
						<input type="tel" id="customer-phone" name="phone">
					</div>
				</div>

				<div class="unbsb-form-group">
					<label for="customer-notes"><?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?></label>
					<textarea id="customer-notes" name="notes" rows="3" placeholder="<?php esc_attr_e( 'Internal notes about this customer...', 'unbelievable-salon-booking' ); ?>"></textarea>
				</div>
			</form>
		</div>
		<div class="unbsb-modal-footer">
			<button type="button" class="unbsb-btn unbsb-btn-secondary unbsb-modal-close-btn"><?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?></button>
			<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-save-customer">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
