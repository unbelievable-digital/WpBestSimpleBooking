<?php
/**
 * Admin Customers Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ag-admin-wrap">
	<div class="ag-admin-header">
		<h1><?php esc_html_e( 'Müşteriler', 'appointment-general' ); ?></h1>
	</div>

	<div class="ag-card">
		<div class="ag-card-body">
			<?php if ( ! empty( $customers ) ) : ?>
				<table class="ag-table ag-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Ad Soyad', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'E-posta', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Telefon', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Kayıt Tarihi', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'İşlemler', 'appointment-general' ); ?></th>
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
								<td><?php echo esc_html( $customer->phone ?: '-' ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'ag_date_format', 'd.m.Y' ), strtotime( $customer->created_at ) ) ); ?></td>
								<td>
									<button type="button" class="ag-btn ag-btn-sm ag-btn-secondary ag-view-customer-bookings" data-id="<?php echo esc_attr( $customer->id ); ?>">
										<?php esc_html_e( 'Randevuları Gör', 'appointment-general' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="ag-empty-state">
					<span class="dashicons dashicons-groups"></span>
					<p><?php esc_html_e( 'Henüz müşteri bulunmuyor.', 'appointment-general' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
