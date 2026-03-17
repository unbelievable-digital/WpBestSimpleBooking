<?php
/**
 * Admin Dashboard Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );
$time_format     = get_option( 'unbsb_time_format', 'H:i' );
?>

<div class="unbsb-admin-wrap unbsb-dashboard">
	<div class="unbsb-admin-header">
		<div>
			<h1><?php esc_html_e( 'Dashboard', 'unbelievable-salon-booking' ); ?></h1>
			<p class="unbsb-subtitle"><?php echo esc_html( date_i18n( 'l, j F Y' ) ); ?></p>
		</div>
		<div class="unbsb-header-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-new-booking' ) ); ?>" class="unbsb-btn unbsb-btn-primary">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'New Booking', 'unbelievable-salon-booking' ); ?>
			</a>
		</div>
	</div>

	<!-- Stat Cards -->
	<div class="unbsb-stats-grid unbsb-stats-grid-4">
		<div class="unbsb-stat-card unbsb-stat-gradient-primary">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $stats['total_bookings'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Bookings', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card unbsb-stat-gradient-warning">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-clock"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $stats['pending_bookings'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Pending', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card unbsb-stat-gradient-success">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $stats['today_bookings'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Today', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card unbsb-stat-gradient-info">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $monthly_total, 0, ',', '.' ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Monthly Revenue', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Revenue Summary + Extra Stats -->
	<div class="unbsb-stats-grid unbsb-stats-grid-4">
		<div class="unbsb-stat-card">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $revenue_summary['today'], 0, ',', '.' ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Today\'s Revenue', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $revenue_summary['week'], 0, ',', '.' ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Weekly Revenue', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-tag"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $avg_booking_value, 0, ',', '.' ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Avg. Booking Value', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-dismiss"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $cancellation_rate ); ?>%</span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Cancellation Rate', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Customer Stats -->
	<div class="unbsb-stats-grid unbsb-stats-grid-3">
		<div class="unbsb-stat-card">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $customer_stats['total'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Total Customers', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-admin-users"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $customer_stats['new_month'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'New This Month', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>

		<div class="unbsb-stat-card">
			<div class="unbsb-stat-icon">
				<span class="dashicons dashicons-heart"></span>
			</div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $customer_stats['returning'] ); ?></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Returning Customers', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Charts Row -->
	<div class="unbsb-charts-grid">
		<!-- Weekly Bookings -->
		<div class="unbsb-card unbsb-chart-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-chart-bar"></span>
					<?php esc_html_e( 'Last 7 Days', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<canvas id="weeklyChart" height="280"></canvas>
			</div>
		</div>

		<!-- Monthly Revenue -->
		<div class="unbsb-card unbsb-chart-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-chart-line"></span>
					<?php esc_html_e( 'Monthly Revenue', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<canvas id="revenueChart" height="280"></canvas>
			</div>
		</div>

		<!-- Service Distribution -->
		<div class="unbsb-card unbsb-chart-card unbsb-chart-card-small">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-chart-pie"></span>
					<?php esc_html_e( 'Popular Services', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body unbsb-chart-body-center">
				<?php if ( ! empty( $service_labels ) ) : ?>
					<canvas id="servicesChart" height="220"></canvas>
				<?php else : ?>
					<div class="unbsb-empty-chart">
						<span class="dashicons dashicons-chart-pie"></span>
						<p><?php esc_html_e( 'No data yet', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Top Services & Staff Performance -->
	<div class="unbsb-dashboard-grid unbsb-dashboard-grid-2">
		<!-- Top 5 Services -->
		<div class="unbsb-card">
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
								<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:center;"><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:right;"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $top_services as $svc ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $svc->name ); ?></strong></td>
									<td style="text-align:center;"><?php echo esc_html( $svc->booking_count ); ?></td>
									<td style="text-align:right;"><?php echo esc_html( number_format( $svc->total_revenue, 0, ',', '.' ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="unbsb-empty-state unbsb-empty-state-small">
						<span class="dashicons dashicons-admin-tools"></span>
						<p><?php esc_html_e( 'No data yet', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Staff Performance (this month) -->
		<div class="unbsb-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-businessman"></span>
					<?php esc_html_e( 'Staff Performance', 'unbelievable-salon-booking' ); ?>
				</h2>
				<span class="unbsb-badge unbsb-badge-secondary"><?php esc_html_e( 'This Month', 'unbelievable-salon-booking' ); ?></span>
			</div>
			<div class="unbsb-card-body">
				<?php if ( ! empty( $staff_performance ) ) : ?>
					<table class="unbsb-table unbsb-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:center;"><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
								<th style="text-align:right;"><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $staff_performance as $sp ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $sp->name ); ?></strong></td>
									<td style="text-align:center;"><?php echo esc_html( $sp->booking_count ); ?></td>
									<td style="text-align:right;"><?php echo esc_html( number_format( $sp->total_revenue, 0, ',', '.' ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="unbsb-empty-state unbsb-empty-state-small">
						<span class="dashicons dashicons-businessman"></span>
						<p><?php esc_html_e( 'No data yet', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Revenue & Metrics Row -->
	<div class="unbsb-stats-grid unbsb-stats-grid-4" style="margin-bottom: 24px;">
		<div class="unbsb-stat-card unbsb-stat-gradient-success">
			<div class="unbsb-stat-icon"><span class="dashicons dashicons-money-alt"></span></div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $revenue_summary['today'], 0, ',', '.' ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Today\'s Revenue', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
		<div class="unbsb-stat-card unbsb-stat-gradient-primary">
			<div class="unbsb-stat-icon"><span class="dashicons dashicons-chart-area"></span></div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $revenue_summary['week'], 0, ',', '.' ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Weekly Revenue', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
		<div class="unbsb-stat-card unbsb-stat-gradient-warning">
			<div class="unbsb-stat-icon"><span class="dashicons dashicons-tag"></span></div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( number_format( $avg_booking_value, 0, ',', '.' ) ); ?><small><?php echo esc_html( $currency_symbol ); ?></small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Avg Booking Value', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
		<div class="unbsb-stat-card <?php echo $cancellation_rate > 20 ? 'unbsb-stat-gradient-danger' : 'unbsb-stat-gradient-info'; ?>">
			<div class="unbsb-stat-icon"><span class="dashicons dashicons-dismiss"></span></div>
			<div class="unbsb-stat-content">
				<span class="unbsb-stat-number"><?php echo esc_html( $cancellation_rate ); ?><small>%</small></span>
				<span class="unbsb-stat-label"><?php esc_html_e( 'Cancellation Rate', 'unbelievable-salon-booking' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Content Row -->
	<div class="unbsb-dashboard-grid">
		<!-- Today's Bookings -->
		<div class="unbsb-card unbsb-today-bookings">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-calendar"></span>
					<?php esc_html_e( 'Today\'s Bookings', 'unbelievable-salon-booking' ); ?>
				</h2>
				<span class="unbsb-badge unbsb-badge-primary"><?php echo esc_html( count( $today_bookings_list ) ); ?></span>
			</div>
			<div class="unbsb-card-body unbsb-card-body-scroll">
				<?php if ( ! empty( $today_bookings_list ) ) : ?>
					<div class="unbsb-timeline">
						<?php foreach ( $today_bookings_list as $booking ) : ?>
							<div class="unbsb-timeline-item unbsb-timeline-<?php echo esc_attr( $booking->status ); ?>">
								<div class="unbsb-timeline-time">
									<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->start_time ) ) ); ?>
								</div>
								<div class="unbsb-timeline-content">
									<div class="unbsb-timeline-header">
										<strong><?php echo esc_html( $booking->customer_name ); ?></strong>
										<span class="unbsb-status unbsb-status-<?php echo esc_attr( $booking->status ); ?>">
											<?php echo esc_html( unbsb_get_status_label( $booking->status ) ); ?>
										</span>
									</div>
									<div class="unbsb-timeline-details">
										<span><span class="dashicons dashicons-admin-tools"></span> <?php echo esc_html( $booking->service_name ); ?></span>
										<?php if ( ! empty( $booking->staff_name ) ) : ?>
											<span><span class="dashicons dashicons-businessman"></span> <?php echo esc_html( $booking->staff_name ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="unbsb-empty-state unbsb-empty-state-small">
						<span class="dashicons dashicons-coffee"></span>
						<p><?php esc_html_e( 'No bookings today', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Recent Bookings -->
		<div class="unbsb-card unbsb-recent-bookings">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-list-view"></span>
					<?php esc_html_e( 'Recent Bookings', 'unbelievable-salon-booking' ); ?>
				</h2>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-bookings' ) ); ?>" class="unbsb-link">
					<?php esc_html_e( 'All', 'unbelievable-salon-booking' ); ?> &rarr;
				</a>
			</div>
			<div class="unbsb-card-body">
				<?php if ( ! empty( $recent_bookings ) ) : ?>
					<table class="unbsb-table unbsb-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Customer', 'unbelievable-salon-booking' ); ?></th>
								<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
								<th><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?></th>
								<th><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $recent_bookings, 0, 8 ) as $booking ) : ?>
								<tr>
									<td>
										<strong><?php echo esc_html( $booking->customer_name ); ?></strong>
									</td>
									<td><?php echo esc_html( $booking->service_name ); ?></td>
									<td>
										<span class="unbsb-text-nowrap"><?php echo esc_html( date_i18n( 'd M', strtotime( $booking->booking_date ) ) ); ?></span>
										<small class="unbsb-text-muted"><?php echo esc_html( date_i18n( $time_format, strtotime( $booking->start_time ) ) ); ?></small>
									</td>
									<td>
										<span class="unbsb-status unbsb-status-<?php echo esc_attr( $booking->status ); ?>">
											<?php echo esc_html( unbsb_get_status_label( $booking->status ) ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="unbsb-empty-state">
						<span class="dashicons dashicons-calendar-alt"></span>
						<p><?php esc_html_e( 'No bookings yet.', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Popular Services -->
		<div class="unbsb-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-star-filled"></span>
					<?php esc_html_e( 'Popular Services', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<?php if ( ! empty( $top_services ) ) : ?>
					<table class="unbsb-table unbsb-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
								<th><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
								<th><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $top_services as $svc ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $svc->name ); ?></strong></td>
									<td><?php echo esc_html( $svc->booking_count ); ?></td>
									<td><?php echo esc_html( number_format( $svc->total_revenue, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="unbsb-empty-state unbsb-empty-state-small">
						<span class="dashicons dashicons-admin-tools"></span>
						<p><?php esc_html_e( 'No data yet', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Staff Performance -->
		<div class="unbsb-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-groups"></span>
					<?php esc_html_e( 'Staff Performance', 'unbelievable-salon-booking' ); ?>
				</h2>
			<span class="unbsb-badge unbsb-badge-muted"><?php esc_html_e( 'This Month', 'unbelievable-salon-booking' ); ?></span>
			</div>
			<div class="unbsb-card-body">
				<?php if ( ! empty( $staff_performance ) ) : ?>
					<table class="unbsb-table unbsb-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></th>
								<th><?php esc_html_e( 'Bookings', 'unbelievable-salon-booking' ); ?></th>
								<th><?php esc_html_e( 'Revenue', 'unbelievable-salon-booking' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $staff_performance as $sp ) : ?>
								<tr>
									<td>
										<div style="display: flex; align-items: center; gap: 8px;">
											<?php if ( $sp->avatar_url ) : ?>
												<img src="<?php echo esc_url( $sp->avatar_url ); ?>" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
											<?php endif; ?>
											<strong><?php echo esc_html( $sp->name ); ?></strong>
										</div>
									</td>
									<td><?php echo esc_html( $sp->booking_count ); ?></td>
									<td><?php echo esc_html( number_format( $sp->total_revenue, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="unbsb-empty-state unbsb-empty-state-small">
						<span class="dashicons dashicons-businessman"></span>
						<p><?php esc_html_e( 'No data yet', 'unbelievable-salon-booking' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Quick Access -->
		<div class="unbsb-card unbsb-quick-links">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-admin-links"></span>
					<?php esc_html_e( 'Quick Access', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<div class="unbsb-quick-links-grid">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-services' ) ); ?>" class="unbsb-quick-link">
						<span class="dashicons dashicons-admin-tools"></span>
						<span><?php esc_html_e( 'Services', 'unbelievable-salon-booking' ); ?></span>
						<small><?php echo esc_html( $stats['total_services'] ); ?></small>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-staff' ) ); ?>" class="unbsb-quick-link">
						<span class="dashicons dashicons-businessman"></span>
						<span><?php esc_html_e( 'Staff', 'unbelievable-salon-booking' ); ?></span>
						<small><?php echo esc_html( $stats['total_staff'] ); ?></small>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-customers' ) ); ?>" class="unbsb-quick-link">
						<span class="dashicons dashicons-groups"></span>
						<span><?php esc_html_e( 'Customers', 'unbelievable-salon-booking' ); ?></span>
						<small><?php echo esc_html( $stats['total_customers'] ); ?></small>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-calendar' ) ); ?>" class="unbsb-quick-link">
						<span class="dashicons dashicons-calendar"></span>
						<span><?php esc_html_e( 'Calendar', 'unbelievable-salon-booking' ); ?></span>
						<small><?php esc_html_e( 'View', 'unbelievable-salon-booking' ); ?></small>
					</a>
				</div>

				<div class="unbsb-shortcode-box">
					<div class="unbsb-shortcode-label"><?php esc_html_e( 'Shortcode:', 'unbelievable-salon-booking' ); ?></div>
					<code>[unbsb_booking_form]</code>
					<button type="button" class="unbsb-copy-btn" data-copy="[unbsb_booking_form]" title="<?php esc_attr_e( 'Copy', 'unbelievable-salon-booking' ); ?>">
						<span class="dashicons dashicons-clipboard"></span>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>


<?php
/**
 * Get status label
 *
 * @param string $status Status.
 * @return string
 */
function unbsb_get_status_label( $status ) {
	$labels = array(
		'pending'   => __( 'Pending', 'unbelievable-salon-booking' ),
		'confirmed' => __( 'Confirmed', 'unbelievable-salon-booking' ),
		'cancelled' => __( 'Cancelled', 'unbelievable-salon-booking' ),
		'completed' => __( 'Completed', 'unbelievable-salon-booking' ),
		'no_show'   => __( 'No Show', 'unbelievable-salon-booking' ),
	);

	return $labels[ $status ] ?? $status;
}
?>
