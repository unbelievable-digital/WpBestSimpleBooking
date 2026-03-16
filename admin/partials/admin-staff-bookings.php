<?php
/**
 * Staff Portal - My Bookings Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );
$time_format     = get_option( 'unbsb_time_format', 'H:i' );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filtering.
$filter = isset( $_GET['filter'] ) ? sanitize_text_field( wp_unslash( $_GET['filter'] ) ) : 'today';
?>

<div class="unbsb-admin-wrap">
	<div class="unbsb-admin-header">
		<div>
			<h1><?php esc_html_e( 'My Bookings', 'unbelievable-salon-booking' ); ?></h1>
			<p class="unbsb-subtitle"><?php esc_html_e( 'Your upcoming and past appointments', 'unbelievable-salon-booking' ); ?></p>
		</div>
		<div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=unbsb-staff-new-booking' ) ); ?>" class="unbsb-btn unbsb-btn-primary">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'New Booking', 'unbelievable-salon-booking' ); ?>
			</a>
		</div>
	</div>

	<!-- Date Filter -->
	<div class="unbsb-card">
		<div class="unbsb-card-body" style="padding: 12px 24px;">
			<div class="unbsb-sp-date-filter" id="unbsb-sp-date-filter">
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'today' ) ); ?>" class="unbsb-sp-filter-btn <?php echo 'today' === $filter ? 'active' : ''; ?>">
					<span class="dashicons dashicons-calendar"></span>
					<?php esc_html_e( 'Today', 'unbelievable-salon-booking' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'week' ) ); ?>" class="unbsb-sp-filter-btn <?php echo 'week' === $filter ? 'active' : ''; ?>">
					<span class="dashicons dashicons-calendar-alt"></span>
					<?php esc_html_e( 'This Week', 'unbelievable-salon-booking' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'month' ) ); ?>" class="unbsb-sp-filter-btn <?php echo 'month' === $filter ? 'active' : ''; ?>">
					<span class="dashicons dashicons-calendar-alt"></span>
					<?php esc_html_e( 'This Month', 'unbelievable-salon-booking' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'filter', 'all' ) ); ?>" class="unbsb-sp-filter-btn <?php echo 'all' === $filter ? 'active' : ''; ?>">
					<span class="dashicons dashicons-list-view"></span>
					<?php esc_html_e( 'All', 'unbelievable-salon-booking' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Bookings Table -->
	<div class="unbsb-card">
		<div class="unbsb-card-body">
			<?php if ( ! empty( $bookings ) ) : ?>
				<table class="unbsb-table unbsb-table-striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date/Time', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Service', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Price', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Status', 'unbelievable-salon-booking' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'unbelievable-salon-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $bookings as $booking ) : ?>
							<tr data-id="<?php echo esc_attr( $booking->id ); ?>">
								<td>
									<strong><?php echo esc_html( date_i18n( $date_format, strtotime( $booking->booking_date ) ) ); ?></strong>
									<div class="unbsb-text-small">
										<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->start_time ) ) ); ?> -
										<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->end_time ) ) ); ?>
									</div>
								</td>
								<td>
									<strong><?php echo esc_html( $booking->customer_name ); ?></strong>
									<?php if ( $booking->customer_phone ) : ?>
										<div class="unbsb-text-small"><?php echo esc_html( $booking->customer_phone ); ?></div>
									<?php endif; ?>
								</td>
								<td>
									<span class="unbsb-service-badge" style="border-left-color: <?php echo esc_attr( $booking->service_color ?? '#3788d8' ); ?>">
										<?php echo esc_html( $booking->service_name ); ?>
									</span>
								</td>
								<td>
									<?php echo esc_html( number_format( $booking->price, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?>
								</td>
								<td>
									<span class="unbsb-status unbsb-status-<?php echo esc_attr( $booking->status ); ?>">
										<?php
										$status_labels = array(
											'pending'   => __( 'Pending', 'unbelievable-salon-booking' ),
											'confirmed' => __( 'Confirmed', 'unbelievable-salon-booking' ),
											'cancelled' => __( 'Cancelled', 'unbelievable-salon-booking' ),
											'completed' => __( 'Completed', 'unbelievable-salon-booking' ),
											'no_show'   => __( 'No Show', 'unbelievable-salon-booking' ),
										);
										echo esc_html( $status_labels[ $booking->status ] ?? $booking->status );
										?>
									</span>
								</td>
								<td>
									<div class="unbsb-actions">
										<?php if ( 'pending' === $booking->status ) : ?>
											<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-success unbsb-sp-confirm-booking" data-id="<?php echo esc_attr( $booking->id ); ?>" title="<?php esc_attr_e( 'Confirm', 'unbelievable-salon-booking' ); ?>">
												<span class="dashicons dashicons-yes-alt"></span>
											</button>
											<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-danger unbsb-sp-reject-booking" data-id="<?php echo esc_attr( $booking->id ); ?>" title="<?php esc_attr_e( 'Reject', 'unbelievable-salon-booking' ); ?>">
												<span class="dashicons dashicons-dismiss"></span>
											</button>
										<?php endif; ?>
										<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-icon unbsb-sp-view-booking" data-id="<?php echo esc_attr( $booking->id ); ?>" title="<?php esc_attr_e( 'View', 'unbelievable-salon-booking' ); ?>">
											<span class="dashicons dashicons-visibility"></span>
										</button>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="unbsb-empty-state">
					<span class="dashicons dashicons-calendar-alt"></span>
					<p><?php esc_html_e( 'No bookings found for this period.', 'unbelievable-salon-booking' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Booking Detail Modal -->
<div id="unbsb-sp-booking-modal" class="unbsb-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content unbsb-modal-medium">
		<div class="unbsb-modal-header unbsb-modal-header-gradient unbsb-modal-header-booking">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Booking Details', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-sp-booking-id"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body" id="unbsb-sp-booking-detail">
			<!-- Populated via AJAX -->
		</div>
	</div>
</div>
