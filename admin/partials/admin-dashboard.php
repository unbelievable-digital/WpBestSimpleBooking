<?php
/**
 * Admin Dashboard Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'ag_currency_symbol', '₺' );
$date_format     = get_option( 'ag_date_format', 'd.m.Y' );
$time_format     = get_option( 'ag_time_format', 'H:i' );
?>

<div class="ag-admin-wrap">
	<div class="ag-admin-header">
		<h1><?php esc_html_e( 'Randevu Sistemi', 'appointment-general' ); ?></h1>
		<p class="ag-subtitle"><?php esc_html_e( 'Kontrol Paneli', 'appointment-general' ); ?></p>
	</div>

	<div class="ag-stats-grid">
		<div class="ag-stat-card ag-stat-primary">
			<div class="ag-stat-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="ag-stat-content">
				<span class="ag-stat-number"><?php echo esc_html( $stats['total_bookings'] ); ?></span>
				<span class="ag-stat-label"><?php esc_html_e( 'Toplam Randevu', 'appointment-general' ); ?></span>
			</div>
		</div>

		<div class="ag-stat-card ag-stat-warning">
			<div class="ag-stat-icon">
				<span class="dashicons dashicons-clock"></span>
			</div>
			<div class="ag-stat-content">
				<span class="ag-stat-number"><?php echo esc_html( $stats['pending_bookings'] ); ?></span>
				<span class="ag-stat-label"><?php esc_html_e( 'Bekleyen Randevu', 'appointment-general' ); ?></span>
			</div>
		</div>

		<div class="ag-stat-card ag-stat-success">
			<div class="ag-stat-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="ag-stat-content">
				<span class="ag-stat-number"><?php echo esc_html( $stats['today_bookings'] ); ?></span>
				<span class="ag-stat-label"><?php esc_html_e( 'Bugünkü Randevu', 'appointment-general' ); ?></span>
			</div>
		</div>

		<div class="ag-stat-card ag-stat-info">
			<div class="ag-stat-icon">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<div class="ag-stat-content">
				<span class="ag-stat-number"><?php echo esc_html( $stats['total_customers'] ); ?></span>
				<span class="ag-stat-label"><?php esc_html_e( 'Toplam Müşteri', 'appointment-general' ); ?></span>
			</div>
		</div>
	</div>

	<div class="ag-dashboard-grid">
		<div class="ag-card ag-recent-bookings">
			<div class="ag-card-header">
				<h2><?php esc_html_e( 'Son Randevular', 'appointment-general' ); ?></h2>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-bookings' ) ); ?>" class="ag-link">
					<?php esc_html_e( 'Tümünü Gör', 'appointment-general' ); ?> &rarr;
				</a>
			</div>
			<div class="ag-card-body">
				<?php if ( ! empty( $recent_bookings ) ) : ?>
					<table class="ag-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Müşteri', 'appointment-general' ); ?></th>
								<th><?php esc_html_e( 'Hizmet', 'appointment-general' ); ?></th>
								<th><?php esc_html_e( 'Tarih', 'appointment-general' ); ?></th>
								<th><?php esc_html_e( 'Durum', 'appointment-general' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $recent_bookings as $booking ) : ?>
								<tr>
									<td>
										<strong><?php echo esc_html( $booking->customer_name ); ?></strong>
										<small class="ag-text-muted"><?php echo esc_html( $booking->customer_email ); ?></small>
									</td>
									<td><?php echo esc_html( $booking->service_name ); ?></td>
									<td>
										<?php echo esc_html( date_i18n( $date_format, strtotime( $booking->booking_date ) ) ); ?>
										<small class="ag-text-muted"><?php echo esc_html( date_i18n( $time_format, strtotime( $booking->start_time ) ) ); ?></small>
									</td>
									<td>
										<span class="ag-status ag-status-<?php echo esc_attr( $booking->status ); ?>">
											<?php echo esc_html( ag_get_status_label( $booking->status ) ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="ag-empty-state">
						<span class="dashicons dashicons-calendar-alt"></span>
						<p><?php esc_html_e( 'Henüz randevu bulunmuyor.', 'appointment-general' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="ag-card ag-quick-links">
			<div class="ag-card-header">
				<h2><?php esc_html_e( 'Hızlı Erişim', 'appointment-general' ); ?></h2>
			</div>
			<div class="ag-card-body">
				<div class="ag-quick-links-grid">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-services' ) ); ?>" class="ag-quick-link">
						<span class="dashicons dashicons-admin-tools"></span>
						<span><?php esc_html_e( 'Hizmetler', 'appointment-general' ); ?></span>
						<small><?php echo esc_html( $stats['total_services'] ); ?> <?php esc_html_e( 'hizmet', 'appointment-general' ); ?></small>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-staff' ) ); ?>" class="ag-quick-link">
						<span class="dashicons dashicons-businessman"></span>
						<span><?php esc_html_e( 'Personel', 'appointment-general' ); ?></span>
						<small><?php echo esc_html( $stats['total_staff'] ); ?> <?php esc_html_e( 'personel', 'appointment-general' ); ?></small>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-calendar' ) ); ?>" class="ag-quick-link">
						<span class="dashicons dashicons-calendar"></span>
						<span><?php esc_html_e( 'Takvim', 'appointment-general' ); ?></span>
						<small><?php esc_html_e( 'Randevuları görüntüle', 'appointment-general' ); ?></small>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ag-settings' ) ); ?>" class="ag-quick-link">
						<span class="dashicons dashicons-admin-settings"></span>
						<span><?php esc_html_e( 'Ayarlar', 'appointment-general' ); ?></span>
						<small><?php esc_html_e( 'Yapılandırma', 'appointment-general' ); ?></small>
					</a>
				</div>
			</div>
		</div>

		<div class="ag-card ag-shortcode-info">
			<div class="ag-card-header">
				<h2><?php esc_html_e( 'Shortcode', 'appointment-general' ); ?></h2>
			</div>
			<div class="ag-card-body">
				<p><?php esc_html_e( 'Randevu formunu sayfanıza eklemek için aşağıdaki shortcode\'u kullanın:', 'appointment-general' ); ?></p>
				<div class="ag-shortcode-box">
					<code>[ag_booking_form]</code>
					<button type="button" class="ag-copy-btn" data-copy="[ag_booking_form]">
						<span class="dashicons dashicons-clipboard"></span>
					</button>
				</div>
				<p class="ag-help-text"><?php esc_html_e( 'Kopyalamak için butona tıklayın.', 'appointment-general' ); ?></p>
			</div>
		</div>
	</div>
</div>

<?php
/**
 * Durum etiketini getir
 *
 * @param string $status Durum.
 * @return string
 */
function ag_get_status_label( $status ) {
	$labels = array(
		'pending'   => __( 'Beklemede', 'appointment-general' ),
		'confirmed' => __( 'Onaylandı', 'appointment-general' ),
		'cancelled' => __( 'İptal', 'appointment-general' ),
		'completed' => __( 'Tamamlandı', 'appointment-general' ),
		'no_show'   => __( 'Gelmedi', 'appointment-general' ),
	);

	return $labels[ $status ] ?? $status;
}
?>
