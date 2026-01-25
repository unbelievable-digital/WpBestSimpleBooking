<?php
/**
 * Staff List Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_services = 'yes' === $atts['show_services'];
?>

<div class="ag-staff-wrapper">
	<?php if ( ! empty( $staff ) ) : ?>
		<div class="ag-staff-grid-public">
			<?php foreach ( $staff as $s ) : ?>
				<div class="ag-staff-card-public">
					<div class="ag-staff-avatar-public">
						<?php if ( $s->avatar_url ) : ?>
							<img src="<?php echo esc_url( $s->avatar_url ); ?>" alt="<?php echo esc_attr( $s->name ); ?>">
						<?php else : ?>
							<span class="ag-avatar-letter"><?php echo esc_html( mb_substr( $s->name, 0, 1 ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="ag-staff-content-public">
						<h3 class="ag-staff-name"><?php echo esc_html( $s->name ); ?></h3>
						<?php if ( $s->bio ) : ?>
							<p class="ag-staff-bio"><?php echo esc_html( $s->bio ); ?></p>
						<?php endif; ?>
						<?php if ( $show_services ) : ?>
							<?php
							$staff_services = $service_model->get_by_staff( $s->id );
							if ( ! empty( $staff_services ) ) :
								?>
								<div class="ag-staff-services">
									<?php foreach ( $staff_services as $service ) : ?>
										<span class="ag-staff-service-tag" style="border-color: <?php echo esc_attr( $service->color ); ?>">
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
		<p class="ag-empty-message"><?php esc_html_e( 'Henüz personel bulunmuyor.', 'appointment-general' ); ?></p>
	<?php endif; ?>
</div>
