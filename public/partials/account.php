<?php
/**
 * Customer Account Template - Login, Register, My Bookings
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_logged_in    = is_user_logged_in();
$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );
$time_format     = get_option( 'unbsb_time_format', 'H:i' );

$status_labels = array(
	'pending'   => __( 'Pending', 'unbelievable-salon-booking' ),
	'confirmed' => __( 'Confirmed', 'unbelievable-salon-booking' ),
	'cancelled' => __( 'Cancelled', 'unbelievable-salon-booking' ),
	'completed' => __( 'Completed', 'unbelievable-salon-booking' ),
	'no_show'   => __( 'No Show', 'unbelievable-salon-booking' ),
);

$status_colors = array(
	'pending'   => '#f59e0b',
	'confirmed' => '#10b981',
	'cancelled' => '#ef4444',
	'completed' => '#6b7280',
	'no_show'   => '#8b5cf6',
);
?>

<div class="unbsb-account-wrapper">
	<?php if ( $is_logged_in ) : ?>
		<?php
		$current_user   = wp_get_current_user();
		$customer_model = new UNBSB_Customer();
		$customer       = $customer_model->get_by_user_id( $current_user->ID );

		if ( ! $customer ) {
			$customer = $customer_model->get_by_email( $current_user->user_email );
			if ( $customer && empty( $customer->user_id ) ) {
				$customer_model->link_user( $customer->id, $current_user->ID );
			}
		}

		$bookings = array();
		if ( $customer ) {
			$bookings = $customer_model->get_bookings( $customer->id );
		}
		?>

		<!-- Logged-in: My Account -->
		<div class="unbsb-account-header">
			<div class="unbsb-account-user">
				<div class="unbsb-account-avatar">
					<?php echo get_avatar( $current_user->ID, 48 ); ?>
				</div>
				<div class="unbsb-account-info">
					<h3><?php echo esc_html( $current_user->display_name ); ?></h3>
					<p><?php echo esc_html( $current_user->user_email ); ?></p>
				</div>
			</div>
			<a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="unbsb-btn unbsb-btn-secondary unbsb-btn-sm">
				<?php esc_html_e( 'Log Out', 'unbelievable-salon-booking' ); ?>
			</a>
		</div>

		<!-- My Bookings -->
		<div class="unbsb-my-bookings">
			<h3 class="unbsb-section-title"><?php esc_html_e( 'My Bookings', 'unbelievable-salon-booking' ); ?></h3>

			<?php if ( ! empty( $bookings ) ) : ?>
				<div class="unbsb-bookings-list">
					<?php foreach ( $bookings as $booking ) : ?>
						<div class="unbsb-booking-card">
							<div class="unbsb-booking-status-dot" style="background-color: <?php echo esc_attr( $status_colors[ $booking->status ] ?? '#6b7280' ); ?>"></div>
							<div class="unbsb-booking-details">
								<div class="unbsb-booking-main">
									<strong><?php echo esc_html( $booking->service_name ); ?></strong>
									<span class="unbsb-booking-staff"><?php echo esc_html( $booking->staff_name ); ?></span>
								</div>
								<div class="unbsb-booking-meta">
									<span class="unbsb-booking-date">
										<?php echo esc_html( date_i18n( $date_format, strtotime( $booking->booking_date ) ) ); ?>
										<?php echo esc_html( date_i18n( $time_format, strtotime( $booking->start_time ) ) ); ?>
									</span>
									<span class="unbsb-booking-status-badge" style="background-color: <?php echo esc_attr( $status_colors[ $booking->status ] ?? '#6b7280' ); ?>">
										<?php echo esc_html( $status_labels[ $booking->status ] ?? $booking->status ); ?>
									</span>
								</div>
							</div>
							<?php if ( ! empty( $booking->token ) && in_array( $booking->status, array( 'pending', 'confirmed' ), true ) ) : ?>
								<?php
								$manage_page = get_option( 'unbsb_manage_booking_page', '' );
								if ( $manage_page ) {
									$manage_url = add_query_arg( 'token', $booking->token, get_permalink( $manage_page ) );
								} else {
									$manage_url = add_query_arg( 'token', $booking->token, home_url( '/' ) );
								}
								?>
								<a href="<?php echo esc_url( $manage_url ); ?>" class="unbsb-btn unbsb-btn-sm unbsb-btn-outline">
									<?php esc_html_e( 'Manage', 'unbelievable-salon-booking' ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="unbsb-empty-message"><?php esc_html_e( 'You have no bookings yet.', 'unbelievable-salon-booking' ); ?></p>
			<?php endif; ?>
		</div>

	<?php else : ?>
		<!-- Not logged in: Login/Register tabs -->
		<div class="unbsb-auth-wrapper">
			<div class="unbsb-auth-tabs">
				<button type="button" class="unbsb-auth-tab active" data-tab="login">
					<?php esc_html_e( 'Login', 'unbelievable-salon-booking' ); ?>
				</button>
				<button type="button" class="unbsb-auth-tab" data-tab="register">
					<?php esc_html_e( 'Register', 'unbelievable-salon-booking' ); ?>
				</button>
			</div>

			<!-- Login Form -->
			<div class="unbsb-auth-panel active" id="unbsb-login-panel">
				<form id="unbsb-login-form" class="unbsb-auth-form">
					<?php wp_nonce_field( 'unbsb_auth_nonce', 'unbsb_auth_nonce' ); ?>

					<div class="unbsb-form-group">
						<label for="unbsb-login-email"><?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?></label>
						<input type="email" id="unbsb-login-email" name="email" required placeholder="<?php esc_attr_e( 'example@email.com', 'unbelievable-salon-booking' ); ?>">
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb-login-password"><?php esc_html_e( 'Password', 'unbelievable-salon-booking' ); ?></label>
						<input type="password" id="unbsb-login-password" name="password" required>
					</div>

					<div class="unbsb-auth-message" id="unbsb-login-message" style="display: none;"></div>

					<button type="submit" class="unbsb-btn unbsb-btn-primary unbsb-btn-full">
						<?php esc_html_e( 'Login', 'unbelievable-salon-booking' ); ?>
					</button>
				</form>
			</div>

			<!-- Register Form -->
			<div class="unbsb-auth-panel" id="unbsb-register-panel" style="display: none;">
				<form id="unbsb-register-form" class="unbsb-auth-form">
					<?php wp_nonce_field( 'unbsb_auth_nonce', 'unbsb_auth_nonce_register' ); ?>

					<div class="unbsb-form-group">
						<label for="unbsb-register-name"><?php esc_html_e( 'Full Name', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
						<input type="text" id="unbsb-register-name" name="name" required placeholder="<?php esc_attr_e( 'Your full name', 'unbelievable-salon-booking' ); ?>">
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb-register-email"><?php esc_html_e( 'Email', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
						<input type="email" id="unbsb-register-email" name="email" required placeholder="<?php esc_attr_e( 'example@email.com', 'unbelievable-salon-booking' ); ?>">
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb-register-phone"><?php esc_html_e( 'Phone', 'unbelievable-salon-booking' ); ?></label>
						<input type="tel" id="unbsb-register-phone" name="phone" placeholder="<?php esc_attr_e( '+1 XXX XXX XXXX', 'unbelievable-salon-booking' ); ?>">
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb-register-password"><?php esc_html_e( 'Password', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
						<input type="password" id="unbsb-register-password" name="password" required minlength="6">
					</div>

					<div class="unbsb-form-group">
						<label for="unbsb-register-password2"><?php esc_html_e( 'Confirm Password', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
						<input type="password" id="unbsb-register-password2" name="password_confirm" required minlength="6">
					</div>

					<div class="unbsb-auth-message" id="unbsb-register-message" style="display: none;"></div>

					<button type="submit" class="unbsb-btn unbsb-btn-primary unbsb-btn-full">
						<?php esc_html_e( 'Register', 'unbelievable-salon-booking' ); ?>
					</button>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>
