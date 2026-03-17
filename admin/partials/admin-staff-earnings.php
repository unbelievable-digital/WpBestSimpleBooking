<?php
/**
 * Staff Portal - My Earnings Template
 *
 * Variables provided by render method:
 *   $staff           — staff object (->id, ->name)
 *   $summary         — array with keys: total_earnings, total_paid, remaining_balance, this_month_earnings
 *   $currency_symbol — string like '$'
 *   $date_format     — string like 'd.m.Y'
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="unbsb-admin-wrap">
	<!-- Page Header -->
	<div class="unbsb-admin-header">
		<div>
			<h1>
				<span class="dashicons dashicons-money-alt"></span>
				<?php esc_html_e( 'My Earnings', 'unbelievable-salon-booking' ); ?>
			</h1>
			<p class="unbsb-subtitle"><?php esc_html_e( 'Your earnings summary and payment history', 'unbelievable-salon-booking' ); ?></p>
		</div>
	</div>

	<!-- Summary Cards -->
	<div class="unbsb-stats-grid unbsb-stats-grid-3" style="margin-bottom: 24px;">
		<!-- Total Earnings -->
		<div class="unbsb-stat-card unbsb-stat-gradient-success">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-chart-line"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $summary['total_earnings'], 2 ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Earnings', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<!-- Total Paid -->
		<div class="unbsb-stat-card unbsb-stat-gradient-info">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $summary['total_paid'], 2 ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Paid', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<!-- Remaining Balance -->
		<div class="unbsb-stat-card <?php echo $summary['remaining_balance'] > 0 ? 'unbsb-stat-gradient-danger' : 'unbsb-stat-gradient-primary'; ?>">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $summary['remaining_balance'], 2 ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Remaining Balance', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Period Filter -->
	<div class="unbsb-card" style="margin-bottom: 24px;">
		<div class="unbsb-card-body" style="padding: 12px 24px;">
			<div class="unbsb-period-filter" id="unbsb-earnings-filter" data-staff-id="<?php echo esc_attr( $staff->id ); ?>">
				<button type="button" class="unbsb-btn unbsb-btn-secondary active" data-period="this_month"><?php esc_html_e( 'This Month', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="last_month"><?php esc_html_e( 'Last Month', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="last_3_months"><?php esc_html_e( 'Last 3 Months', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="custom"><?php esc_html_e( 'Custom Range', 'unbelievable-salon-booking' ); ?></button>
				<div class="unbsb-date-range" id="unbsb-earnings-date-range" style="display: none;">
					<input type="date" id="unbsb-earnings-date-from">
					<span>&mdash;</span>
					<input type="date" id="unbsb-earnings-date-to">
					<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-sm" id="unbsb-earnings-apply-range"><?php esc_html_e( 'Apply', 'unbelievable-salon-booking' ); ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Earnings Detail -->
	<div class="unbsb-card" style="margin-bottom: 24px;">
		<div class="unbsb-card-header">
			<h2>
				<span class="dashicons dashicons-chart-bar"></span>
				<?php esc_html_e( 'Earnings Detail', 'unbelievable-salon-booking' ); ?>
			</h2>
		</div>
		<div class="unbsb-card-body">
			<div id="unbsb-earnings-table-wrap">
				<table class="unbsb-table unbsb-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Booking', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Booking Total', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Commission', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody id="unbsb-earnings-tbody">
						<!-- Populated via JS -->
					</tbody>
				</table>
				<div class="unbsb-empty-state" id="unbsb-earnings-empty" style="display: none;">
					<span class="dashicons dashicons-chart-line"></span>
					<p><?php esc_html_e( 'No earnings found for this period.', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<!-- Payments History -->
	<div class="unbsb-card">
		<div class="unbsb-card-header">
			<h2>
				<span class="dashicons dashicons-money-alt"></span>
				<?php esc_html_e( 'Payments Received', 'unbelievable-salon-booking' ); ?>
			</h2>
		</div>
		<div class="unbsb-card-body">
			<div id="unbsb-payments-table-wrap">
				<table class="unbsb-table unbsb-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Method', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Note', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Recorded By', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody id="unbsb-payments-tbody">
						<!-- Populated via JS -->
					</tbody>
				</table>
				<div class="unbsb-empty-state" id="unbsb-payments-empty" style="display: none;">
					<span class="dashicons dashicons-money-alt"></span>
					<p><?php esc_html_e( 'No payments recorded yet.', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
