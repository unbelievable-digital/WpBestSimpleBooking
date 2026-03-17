<?php
/**
 * Staff Portal - My Performance Template
 *
 * Variables provided by render method:
 *   $staff           — staff object (->id, ->name)
 *   $metrics         — array: total_bookings, completed, cancelled, cancel_rate, total_revenue
 *   $top_services    — array of objects: ->name, ->booking_count, ->total_revenue
 *   $trend           — array of arrays: month, bookings, revenue, commission
 *   $currency_symbol — string like '₺'
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
				<span class="dashicons dashicons-chart-area"></span>
				<?php esc_html_e( 'My Performance', 'unbelievable-salon-booking' ); ?>
			</h1>
			<p class="unbsb-subtitle"><?php esc_html_e( 'Your booking statistics and performance overview', 'unbelievable-salon-booking' ); ?></p>
		</div>
	</div>

	<!-- Summary Cards -->
	<div class="unbsb-stats-grid unbsb-stats-grid-4" style="margin-bottom: 24px;">
		<!-- Bookings Count -->
		<div class="unbsb-stat-card unbsb-stat-gradient-primary">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $metrics['total_bookings'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Bookings', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<!-- Cancellation Rate -->
		<div class="unbsb-stat-card unbsb-stat-gradient-warning">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-dismiss"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $metrics['cancel_rate'] ); ?><small>%</small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Cancellation Rate', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<!-- Completed -->
		<div class="unbsb-stat-card unbsb-stat-gradient-success">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $metrics['completed'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Completed', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<!-- Revenue -->
		<div class="unbsb-stat-card unbsb-stat-gradient-info">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $metrics['total_revenue'], 2 ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Period Filter -->
	<div class="unbsb-card" style="margin-bottom: 24px;">
		<div class="unbsb-card-body" style="padding: 12px 24px;">
			<div class="unbsb-period-filter" id="unbsb-performance-filter" data-staff-id="<?php echo esc_attr( $staff->id ); ?>">
				<button type="button" class="unbsb-btn unbsb-btn-secondary active" data-period="this_month"><?php esc_html_e( 'This Month', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="last_month"><?php esc_html_e( 'Last Month', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="last_3_months"><?php esc_html_e( 'Last 3 Months', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="custom"><?php esc_html_e( 'Custom Range', 'unbelievable-salon-booking' ); ?></button>
				<div class="unbsb-date-range" id="unbsb-performance-date-range" style="display: none;">
					<input type="date" id="unbsb-performance-date-from">
					<span>&mdash;</span>
					<input type="date" id="unbsb-performance-date-to">
					<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-sm" id="unbsb-performance-apply-range"><?php esc_html_e( 'Apply', 'unbelievable-salon-booking' ); ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Services & Monthly Trend -->
	<div class="unbsb-earnings-layout">
		<!-- Left: Top Services -->
		<div class="unbsb-card unbsb-top-services">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-star-filled"></span>
					<?php esc_html_e( 'Top Services', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<?php if ( ! empty( $top_services ) ) : ?>
					<table class="unbsb-table unbsb-table-compact">
						<thead>
							<tr>
								<th style="width: 50px;">#</th>
								<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:center;"><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:right;"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$rank = 0;
							foreach ( array_slice( $top_services, 0, 5 ) as $svc ) :
								$rank++;
								?>
								<tr>
									<td>
										<span class="unbsb-service-rank"><?php echo esc_html( $rank ); ?></span>
									</td>
									<td><strong><?php echo esc_html( $svc->name ); ?></strong></td>
									<td style="text-align:center;"><?php echo esc_html( $svc->booking_count ); ?></td>
									<td style="text-align:right;"><?php echo esc_html( number_format( $svc->total_revenue, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="unbsb-empty-state unbsb-empty-state-small">
						<span class="dashicons dashicons-admin-tools"></span>
						<p><?php esc_html_e( 'No service data yet.', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Right: Monthly Trend -->
		<div class="unbsb-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-chart-line"></span>
					<?php esc_html_e( 'Monthly Trend', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<?php if ( ! empty( $trend ) ) : ?>
					<table class="unbsb-table unbsb-table-compact unbsb-trend-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Month', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:center;"><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:right;"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:right;"><?php esc_html_e( 'Commission', 'unbelievable-salon-booking' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $trend, 0, 6 ) as $row ) : ?>
								<tr>
									<td>
										<strong>
										<?php
										$month_date = DateTime::createFromFormat( 'Y-m', $row['month'] );
										if ( $month_date ) {
											echo esc_html( date_i18n( 'F Y', $month_date->getTimestamp() ) );
										} else {
											echo esc_html( $row['month'] );
										}
										?>
										</strong>
									</td>
									<td style="text-align:center;"><?php echo esc_html( $row['bookings'] ); ?></td>
									<td style="text-align:right;"><?php echo esc_html( number_format( $row['revenue'], 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
									<td style="text-align:right;"><?php echo esc_html( number_format( $row['commission'], 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="unbsb-empty-state unbsb-empty-state-small">
						<span class="dashicons dashicons-chart-line"></span>
						<p><?php esc_html_e( 'No trend data yet.', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
