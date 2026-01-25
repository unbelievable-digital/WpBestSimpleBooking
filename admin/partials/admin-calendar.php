<?php
/**
 * Admin Calendar Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ag-admin-wrap">
	<div class="ag-admin-header">
		<h1><?php esc_html_e( 'Takvim', 'appointment-general' ); ?></h1>
	</div>

	<div class="ag-card">
		<div class="ag-card-header">
			<div class="ag-calendar-controls">
				<div class="ag-calendar-nav">
					<button type="button" class="ag-btn ag-btn-icon" id="ag-cal-prev">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</button>
					<button type="button" class="ag-btn ag-btn-icon" id="ag-cal-today">
						<?php esc_html_e( 'Bugün', 'appointment-general' ); ?>
					</button>
					<button type="button" class="ag-btn ag-btn-icon" id="ag-cal-next">
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</div>
				<h2 id="ag-cal-title"></h2>
				<div class="ag-calendar-filters">
					<select id="ag-cal-staff">
						<option value=""><?php esc_html_e( 'Tüm Personel', 'appointment-general' ); ?></option>
						<?php foreach ( $staff as $s ) : ?>
							<option value="<?php echo esc_attr( $s->id ); ?>"><?php echo esc_html( $s->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<div class="ag-calendar-view-btns">
						<button type="button" class="ag-btn ag-btn-sm ag-cal-view active" data-view="month">
							<?php esc_html_e( 'Ay', 'appointment-general' ); ?>
						</button>
						<button type="button" class="ag-btn ag-btn-sm ag-cal-view" data-view="week">
							<?php esc_html_e( 'Hafta', 'appointment-general' ); ?>
						</button>
						<button type="button" class="ag-btn ag-btn-sm ag-cal-view" data-view="day">
							<?php esc_html_e( 'Gün', 'appointment-general' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="ag-card-body">
			<div id="ag-calendar"></div>
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

<script>
	var agStaff = <?php echo wp_json_encode( $staff ); ?>;
</script>
