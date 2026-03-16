<?php
/**
 * Staff List Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_services = 'yes' === $atts['show_services'];
?>

<div class="unbsb-staff-wrapper">
	<?php if ( ! empty( $staff ) ) : ?>
		<div class="unbsb-staff-grid-public">
			<?php foreach ( $staff as $staff_member ) : ?>
				<div class="unbsb-staff-card-public">
					<div class="unbsb-staff-avatar-public">
						<?php if ( $staff_member->avatar_url ) : ?>
							<img src="<?php echo esc_url( $staff_member->avatar_url ); ?>" alt="<?php echo esc_attr( $staff_member->name ); ?>">
						<?php else : ?>
							<span class="unbsb-avatar-letter"><?php echo esc_html( mb_substr( $staff_member->name, 0, 1 ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="unbsb-staff-content-public">
						<h3 class="unbsb-staff-name"><?php echo esc_html( $staff_member->name ); ?></h3>
						<?php if ( $staff_member->bio ) : ?>
							<p class="unbsb-staff-bio"><?php echo esc_html( $staff_member->bio ); ?></p>
						<?php endif; ?>
						<?php if ( $show_services ) : ?>
							<?php
							$staff_services = $service_model->get_by_staff( $staff_member->id );
							if ( ! empty( $staff_services ) ) :
								?>
								<div class="unbsb-staff-services">
									<?php foreach ( $staff_services as $service ) : ?>
										<span class="unbsb-staff-service-tag" style="border-color: <?php echo esc_attr( $service->color ); ?>">
											<?php echo esc_html( $service->name ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="unbsb-empty-message"><?php esc_html_e( 'No staff available yet.', 'unbelievable-salon-booking' ); ?></p>
	<?php endif; ?>
</div>
