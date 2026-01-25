<?php
/**
 * Admin Bookings Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'ag_currency_symbol', '₺' );
$date_format     = get_option( 'ag_date_format', 'd.m.Y' );
$time_format     = get_option( 'ag_time_format', 'H:i' );

$statuses = array(
	''          => __( 'Tüm Durumlar', 'appointment-general' ),
	'pending'   => __( 'Beklemede', 'appointment-general' ),
	'confirmed' => __( 'Onaylandı', 'appointment-general' ),
	'cancelled' => __( 'İptal', 'appointment-general' ),
	'completed' => __( 'Tamamlandı', 'appointment-general' ),
	'no_show'   => __( 'Gelmedi', 'appointment-general' ),
);
?>

<div class="ag-admin-wrap">
	<div class="ag-admin-header">
		<h1><?php esc_html_e( 'Randevular', 'appointment-general' ); ?></h1>
		<button type="button" class="ag-btn ag-btn-primary" id="ag-add-booking">
			<span class="dashicons dashicons-plus-alt"></span>
			<?php esc_html_e( 'Yeni Randevu', 'appointment-general' ); ?>
		</button>
	</div>

	<div class="ag-card">
		<div class="ag-card-header">
			<h2><?php esc_html_e( 'Filtrele', 'appointment-general' ); ?></h2>
		</div>
		<div class="ag-card-body">
			<form method="get" class="ag-filter-form">
				<input type="hidden" name="page" value="ag-bookings">

				<div class="ag-filter-row">
					<div class="ag-filter-field">
						<label for="status"><?php esc_html_e( 'Durum', 'appointment-general' ); ?></label>
						<select name="status" id="status">
							<?php foreach ( $statuses as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="ag-filter-field">
						<label for="staff_id"><?php esc_html_e( 'Personel', 'appointment-general' ); ?></label>
						<select name="staff_id" id="staff_id">
							<option value=""><?php esc_html_e( 'Tüm Personel', 'appointment-general' ); ?></option>
							<?php foreach ( $staff as $s ) : ?>
								<option value="<?php echo esc_attr( $s->id ); ?>" <?php selected( $staff_id, $s->id ); ?>>
									<?php echo esc_html( $s->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="ag-filter-field">
						<label for="date_from"><?php esc_html_e( 'Başlangıç Tarihi', 'appointment-general' ); ?></label>
						<input type="date" name="date_from" id="date_from" value="<?php echo esc_attr( $date_from ); ?>">
					</div>

					<div class="ag-filter-field">
						<label for="date_to"><?php esc_html_e( 'Bitiş Tarihi', 'appointment-general' ); ?></label>
						<input type="date" name="date_to" id="date_to" value="<?php echo esc_attr( $date_to ); ?>">
					</div>

					<div class="ag-filter-actions">
						<button type="submit" class="ag-btn ag-btn-primary">
							<?php esc_html_e( 'Filtrele', 'appointment-general' ); ?>
						</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-bookings' ) ); ?>" class="ag-btn ag-btn-secondary">
							<?php esc_html_e( 'Temizle', 'appointment-general' ); ?>
						</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="ag-card">
		<div class="ag-card-body">
			<?php if ( ! empty( $bookings ) ) : ?>
				<table class="ag-table ag-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Müşteri', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Hizmet', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Personel', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Tarih/Saat', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Ücret', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'Durum', 'appointment-general' ); ?></th>
							<th><?php esc_html_e( 'İşlemler', 'appointment-general' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $bookings as $booking ) : ?>
							<tr data-id="<?php echo esc_attr( $booking->id ); ?>">
								<td>#<?php echo esc_html( $booking->id ); ?></td>
								<td>
									<strong><?php echo esc_html( $booking->customer_name ); ?></strong>
									<div class="ag-text-small">
										<?php echo esc_html( $booking->customer_email ); ?>
										<?php if ( $booking->customer_phone ) : ?>
											<br><?php echo esc_html( $booking->customer_phone ); ?>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<span class="ag-service-badge" style="border-left-color: <?php echo esc_attr( $booking->service_color ?? '#3788d8' ); ?>">
										<?php echo esc_html( $booking->service_name ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $booking->staff_name ); ?></td>
								<td>
									<strong><?php echo esc_html( date_i18n( $date_format, strtotime( $booking->booking_date ) ) ); ?></strong>
									<div class="ag-text-small">
										<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->start_time ) ) ); ?> -
										<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->end_time ) ) ); ?>
									</div>
								</td>
								<td>
									<?php echo esc_html( number_format( $booking->price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?>
								</td>
								<td>
									<select class="ag-status-select" data-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php foreach ( $statuses as $value => $label ) : ?>
											<?php if ( '' !== $value ) : ?>
												<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $booking->status, $value ); ?>>
													<?php echo esc_html( $label ); ?>
												</option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<div class="ag-actions">
										<button type="button" class="ag-btn ag-btn-sm ag-btn-icon ag-view-booking" data-id="<?php echo esc_attr( $booking->id ); ?>" title="<?php esc_attr_e( 'Görüntüle', 'appointment-general' ); ?>">
											<span class="dashicons dashicons-visibility"></span>
										</button>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="ag-empty-state">
					<span class="dashicons dashicons-calendar-alt"></span>
					<p><?php esc_html_e( 'Randevu bulunamadı.', 'appointment-general' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Booking Detail Modal -->
<div id="ag-booking-modal" class="ag-modal" style="display: none;">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content">
		<div class="ag-modal-header">
			<h3><?php esc_html_e( 'Randevu Detayı', 'appointment-general' ); ?></h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body" id="ag-booking-detail">
			<!-- AJAX ile doldurulacak -->
		</div>
	</div>
</div>

<!-- Add Booking Modal -->
<div id="ag-add-booking-modal" class="ag-modal" style="display: none;">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content ag-modal-lg">
		<div class="ag-modal-header">
			<h3><?php esc_html_e( 'Yeni Randevu Oluştur', 'appointment-general' ); ?></h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body">
			<form id="ag-add-booking-form">
				<div class="ag-form-row">
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-staff"><?php esc_html_e( 'Personel', 'appointment-general' ); ?> <span class="required">*</span></label>
						<select id="booking-staff" name="staff_id" required>
							<option value=""><?php esc_html_e( 'Personel Seçin', 'appointment-general' ); ?></option>
							<?php foreach ( $staff as $s ) : ?>
								<option value="<?php echo esc_attr( $s->id ); ?>">
									<?php echo esc_html( $s->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-service"><?php esc_html_e( 'Hizmet', 'appointment-general' ); ?> <span class="required">*</span></label>
						<select id="booking-service" name="service_id" required>
							<option value=""><?php esc_html_e( 'Hizmet Seçin', 'appointment-general' ); ?></option>
							<?php foreach ( $services as $service ) : ?>
								<option value="<?php echo esc_attr( $service->id ); ?>" data-duration="<?php echo esc_attr( $service->duration ); ?>" data-price="<?php echo esc_attr( $service->price ); ?>">
									<?php echo esc_html( $service->name ); ?> (<?php echo esc_html( $service->duration ); ?> dk - <?php echo esc_html( number_format( $service->price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?>)
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-date"><?php esc_html_e( 'Tarih', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="date" id="booking-date" name="booking_date" required min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
					</div>
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-time"><?php esc_html_e( 'Saat', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="time" id="booking-time" name="start_time" required>
					</div>
				</div>

				<div class="ag-form-section-title"><?php esc_html_e( 'Müşteri Bilgileri', 'appointment-general' ); ?></div>

				<div class="ag-form-row">
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-customer-name"><?php esc_html_e( 'Ad Soyad', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="text" id="booking-customer-name" name="customer_name" required>
					</div>
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-customer-email"><?php esc_html_e( 'E-posta', 'appointment-general' ); ?> <span class="required">*</span></label>
						<input type="email" id="booking-customer-email" name="customer_email" required>
					</div>
				</div>

				<div class="ag-form-row">
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-customer-phone"><?php esc_html_e( 'Telefon', 'appointment-general' ); ?></label>
						<input type="tel" id="booking-customer-phone" name="customer_phone">
					</div>
					<div class="ag-form-group ag-form-group-half">
						<label for="booking-status"><?php esc_html_e( 'Durum', 'appointment-general' ); ?></label>
						<select id="booking-status" name="status">
							<option value="pending"><?php esc_html_e( 'Beklemede', 'appointment-general' ); ?></option>
							<option value="confirmed"><?php esc_html_e( 'Onaylandı', 'appointment-general' ); ?></option>
						</select>
					</div>
				</div>

				<div class="ag-form-group">
					<label for="booking-notes"><?php esc_html_e( 'Notlar', 'appointment-general' ); ?></label>
					<textarea id="booking-notes" name="notes" rows="3"></textarea>
				</div>

				<div class="ag-form-group">
					<label for="booking-internal-notes"><?php esc_html_e( 'Dahili Notlar', 'appointment-general' ); ?> <span class="ag-text-small">(<?php esc_html_e( 'Sadece admin görür', 'appointment-general' ); ?>)</span></label>
					<textarea id="booking-internal-notes" name="internal_notes" rows="2"></textarea>
				</div>
			</form>
		</div>
		<div class="ag-modal-footer">
			<button type="button" class="ag-btn ag-btn-secondary ag-modal-close"><?php esc_html_e( 'İptal', 'appointment-general' ); ?></button>
			<button type="button" class="ag-btn ag-btn-primary" id="ag-save-booking"><?php esc_html_e( 'Randevu Oluştur', 'appointment-general' ); ?></button>
		</div>
	</div>
</div>
