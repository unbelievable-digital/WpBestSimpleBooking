<?php
/**
 * Admin Calendar Template (FullCalendar)
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<h1><?php esc_html_e( 'Calendar', 'unbelievable-salon-booking' ); ?></h1>
		<div class="unbsb-calendar-filters">
			<select id="unbsb-cal-staff">
				<option value=""><?php esc_html_e( 'All Staff', 'unbelievable-salon-booking' ); ?></option>
				<?php foreach ( $staff as $staff_member ) : ?>
					<option value="<?php echo esc_attr( $staff_member->id ); ?>"><?php echo esc_html( $staff_member->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="unbsb-card">
		<div class="unbsb-card-body unbsb-fc-wrap">
			<div id="unbsb-calendar"></div>
		</div>
	</div>
</div>

<!-- Booking Detail Modal -->
<div id="unbsb-cal-booking-modal" class="unbsb-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-compact">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-booking">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Booking Details', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-cal-booking-id"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body" id="unbsb-cal-booking-detail">
			<!-- Populated via JS -->
		</div>
	</div>
</div>
