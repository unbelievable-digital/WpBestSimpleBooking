<?php
/**
 * Admin Calendar Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<h1><?php esc_html_e( 'Calendar', 'unbelievable-salon-booking' ); ?></h1>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-header">
			<div class="unbsb-calendar-controls">
				<div class="unbsb-calendar-nav">
					<button type="button" class="unbsb-btn unbsb-btn-icon" id="unbsb-cal-prev">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</button>
					<button type="button" class="unbsb-btn unbsb-btn-icon" id="unbsb-cal-today">
						<?php esc_html_e( 'Today', 'unbelievable-salon-booking' ); ?>
					</button>
					<button type="button" class="unbsb-btn unbsb-btn-icon" id="unbsb-cal-next">
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</div>
				<h2 id="unbsb-cal-title"></h2>
				<div class="unbsb-calendar-filters">
					<select id="unbsb-cal-staff">
						<option value=""><?php esc_html_e( 'All Staff', 'unbelievable-salon-booking' ); ?></option>
						<?php foreach ( $staff as $staff_member ) : ?>
							<option value="<?php echo esc_attr( $staff_member->id ); ?>"><?php echo esc_html( $staff_member->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<div class="unbsb-calendar-view-btns">
						<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-cal-view active" data-view="month">
							<?php esc_html_e( 'Month', 'unbelievable-salon-booking' ); ?>
						</button>
						<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-cal-view" data-view="week">
							<?php esc_html_e( 'Week', 'unbelievable-salon-booking' ); ?>
						</button>
						<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-cal-view" data-view="day">
							<?php esc_html_e( 'Day', 'unbelievable-salon-booking' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="unbsb-card-body">
			<div id="unbsb-calendar"></div>
		</div>
	</div>
</div>

<!-- Booking Detail Modal -->
<div id="unbsb-booking-modal" class="unbsb-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content">
		<div class="unbsb-modal-header">
			<h3><?php esc_html_e( 'Booking Details', 'unbelievable-salon-booking' ); ?></h3>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body" id="unbsb-booking-detail">
			<!-- Populated via AJAX -->
		</div>
	</div>
</div>
