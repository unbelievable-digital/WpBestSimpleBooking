<?php
/**
 * Admin Staff View — Combined earnings, performance, and payments for a single staff member
 *
 * Variables provided by render method:
 *   $staff           — staff object (->id, ->name, ->email, ->phone, ->avatar_url, ->salary_type, ->salary_percentage, ->salary_fixed)
 *   $summary         — array: total_earnings, total_paid, remaining_balance, this_month_earnings
 *   $metrics         — array: total_bookings, completed, cancelled, cancel_rate, total_revenue
 *   $top_services    — array of objects: ->name, ->booking_count, ->total_revenue
 *   $trend           — array of arrays: month, bookings, revenue, commission
 *   $currency_symbol — string
 *   $date_format     — string
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
		<div style="display: flex; align-items: center; gap: 16px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-staff' ) ); ?>" class="unbsb-btn unbsb-btn-secondary unbsb-btn-sm">
				<span class="dashicons dashicons-arrow-left-alt2"></span>
			</a>
			<div style="display: flex; align-items: center; gap: 12px;">
				<?php if ( $staff->avatar_url ) : ?>
					<img src="<?php echo esc_url( $staff->avatar_url ); ?>" alt="<?php echo esc_attr( $staff->name ); ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
				<?php else : ?>
					<span class="unbsb-avatar-placeholder" style="width: 48px; height: 48px; font-size: 20px; display: flex; align-items: center; justify-content: center; border-radius: 50; background: linear-gradient(135deg, var(--unbsb-primary), #1d4ed8); color: #fff;">
						<?php echo esc_html( mb_substr( $staff->name, 0, 1 ) ); ?>
					</span>
				<?php endif; ?>
				<div>
					<h1 style="margin: 0;"><?php echo esc_html( $staff->name ); ?></h1>
					<p class="unbsb-subtitle" style="margin: 0;">
						<?php
						$salary_label = '';
						if ( 'percentage' === $staff->salary_type ) {
							/* translators: %s: percentage value */
							$salary_label = sprintf( __( 'Commission: %s%%', 'unbelievable-salon-booking' ), $staff->salary_percentage );
						} elseif ( 'fixed' === $staff->salary_type ) {
							/* translators: %s: fixed salary amount */
							$salary_label = sprintf( __( 'Fixed Salary: %s', 'unbelievable-salon-booking' ), number_format( $staff->salary_fixed, 2 ) . ' ' . $currency_symbol );
						} elseif ( 'mix' === $staff->salary_type ) {
							/* translators: 1: percentage value, 2: fixed amount */
							$salary_label = sprintf( __( 'Commission: %1$s%% + Fixed: %2$s', 'unbelievable-salon-booking' ), $staff->salary_percentage, number_format( $staff->salary_fixed, 2 ) . ' ' . $currency_symbol );
						}
						echo esc_html( $salary_label );
						?>
					</p>
				</div>
			</div>
		</div>
		<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-staff-payment" data-id="<?php echo esc_attr( $staff->id ); ?>" data-name="<?php echo esc_attr( $staff->name ); ?>">
			<span class="dashicons dashicons-money-alt"></span>
			<?php esc_html_e( 'Record Payment', 'unbelievable-salon-booking' ); ?>
		</button>
	</div>

	<!-- Summary Cards (4 + 3 = 7 cards in 2 rows) -->
	<!-- Row 1: Performance -->
	<div class="unbsb-stats-grid unbsb-stats-grid-4" style="margin-bottom: 16px;">
		<div class="unbsb-stat-card unbsb-stat-gradient-primary">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number unbsb-perf-value" data-metric="total_bookings"><?php echo esc_html( $metrics['total_bookings'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Bookings', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
		<div class="unbsb-stat-card unbsb-stat-gradient-warning">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-dismiss"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number unbsb-perf-value" data-metric="cancel_rate"><?php echo esc_html( $metrics['cancel_rate'] ); ?>%</span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Cancellation Rate', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
		<div class="unbsb-stat-card unbsb-stat-gradient-success">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number unbsb-perf-value" data-metric="completed"><?php echo esc_html( $metrics['completed'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Completed', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
		<div class="unbsb-stat-card unbsb-stat-gradient-info">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number unbsb-perf-value" data-metric="total_revenue"><?php echo esc_html( number_format( $metrics['total_revenue'], 2 ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Row 2: Earnings -->
	<div class="unbsb-stats-grid unbsb-stats-grid-3" style="margin-bottom: 24px;">
		<div class="unbsb-stat-card unbsb-stat-gradient-success">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-chart-line"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $summary['total_earnings'], 2 ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Earnings', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
		<div class="unbsb-stat-card unbsb-stat-gradient-info">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $summary['total_paid'], 2 ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Paid', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
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
			<div class="unbsb-period-filter" id="unbsb-staffview-filter" data-staff-id="<?php echo esc_attr( $staff->id ); ?>">
				<button type="button" class="unbsb-btn unbsb-btn-secondary active" data-period="this_month"><?php esc_html_e( 'This Month', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="last_month"><?php esc_html_e( 'Last Month', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="last_3_months"><?php esc_html_e( 'Last 3 Months', 'unbelievable-salon-booking' ); ?></button>
				<button type="button" class="unbsb-btn unbsb-btn-secondary" data-period="custom"><?php esc_html_e( 'Custom Range', 'unbelievable-salon-booking' ); ?></button>
				<div class="unbsb-date-range" id="unbsb-staffview-date-range" style="display: none;">
					<input type="date" id="unbsb-staffview-date-from">
					<span>&mdash;</span>
					<input type="date" id="unbsb-staffview-date-to">
					<button type="button" class="unbsb-btn unbsb-btn-primary unbsb-btn-sm" id="unbsb-staffview-apply-range"><?php esc_html_e( 'Apply', 'unbelievable-salon-booking' ); ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Services & Monthly Trend -->
	<div class="unbsb-earnings-layout" style="margin-bottom: 24px;">
		<!-- Left: Top Services -->
		<div class="unbsb-card unbsb-top-services">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-star-filled"></span>
					<?php esc_html_e( 'Top Services', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<div id="unbsb-staffview-top-services-wrap">
					<?php if ( ! empty( $top_services ) ) : ?>
						<table class="unbsb-table unbsb-table-compact" id="unbsb-staffview-top-services-table">
							<thead>
								<tr>
									<th style="width: 50px;">#</th>
									<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
									<th style="text-align:center;"><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
									<th style="text-align:right;"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
								</tr>
							</thead>
							<tbody id="unbsb-staffview-top-services-tbody">
								<?php
								$rank = 0;
								foreach ( array_slice( $top_services, 0, 5 ) as $svc ) :
									$rank++;
									?>
									<tr>
										<td><span class="unbsb-service-rank"><?php echo esc_html( $rank ); ?></span></td>
										<td><strong><?php echo esc_html( $svc->name ); ?></strong></td>
										<td style="text-align:center;"><?php echo esc_html( $svc->booking_count ); ?></td>
										<td style="text-align:right;"><?php echo esc_html( number_format( $svc->total_revenue, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<div class="unbsb-empty-state unbsb-empty-state-small" id="unbsb-staffview-top-services-empty">
							<span class="dashicons dashicons-admin-tools"></span>
							<p><?php esc_html_e( 'No service data yet.', 'unbelievable-salon-booking' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
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
				<div id="unbsb-staffview-trend-wrap">
					<?php if ( ! empty( $trend ) ) : ?>
						<table class="unbsb-table unbsb-table-compact unbsb-trend-table" id="unbsb-staffview-trend-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Month', 'unbelievable-salon-booking' ); ?></th>
									<th style="text-align:center;"><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
									<th style="text-align:right;"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
									<th style="text-align:right;"><?php esc_html_e( 'Commission', 'unbelievable-salon-booking' ); ?></th>
								</tr>
							</thead>
							<tbody id="unbsb-staffview-trend-tbody">
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
						<div class="unbsb-empty-state unbsb-empty-state-small" id="unbsb-staffview-trend-empty">
							<span class="dashicons dashicons-chart-line"></span>
							<p><?php esc_html_e( 'No trend data yet.', 'unbelievable-salon-booking' ); ?></p>
						</div>
					<?php endif; ?>
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
			<div id="unbsb-staffview-earnings-table-wrap">
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
					<tbody id="unbsb-staffview-earnings-tbody">
						<!-- Populated via JS -->
					</tbody>
				</table>
				<div class="unbsb-empty-state" id="unbsb-staffview-earnings-empty" style="display: none;">
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
				<?php esc_html_e( 'Payments History', 'unbelievable-salon-booking' ); ?>
			</h2>
		</div>
		<div class="unbsb-card-body">
			<div id="unbsb-staffview-payments-table-wrap">
				<table class="unbsb-table unbsb-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Method', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Note', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Recorded By', 'unbelievable-salon-booking' ); ?></th>
							<th style="width: 50px;"></th>
						</tr>
					</thead>
					<tbody id="unbsb-staffview-payments-tbody">
						<!-- Populated via JS -->
					</tbody>
				</table>
				<div class="unbsb-empty-state" id="unbsb-staffview-payments-empty" style="display: none;">
					<span class="dashicons dashicons-money-alt"></span>
					<p><?php esc_html_e( 'No payments recorded yet.', 'unbelievable-salon-booking' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
