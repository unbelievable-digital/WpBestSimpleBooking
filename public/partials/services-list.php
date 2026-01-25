<?php
/**
 * Services List Template
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol   = get_option( 'ag_currency_symbol', '₺' );
$currency_position = get_option( 'ag_currency_position', 'after' );
?>

<div class="ag-services-wrapper">
	<?php if ( ! empty( $services ) ) : ?>
		<div class="ag-services-grid">
			<?php foreach ( $services as $service ) : ?>
				<div class="ag-service-card-public">
					<span class="ag-service-color-bar" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
					<div class="ag-service-content">
						<h3 class="ag-service-title"><?php echo esc_html( $service->name ); ?></h3>
						<?php if ( $service->description ) : ?>
							<p class="ag-service-description"><?php echo esc_html( $service->description ); ?></p>
						<?php endif; ?>
						<div class="ag-service-footer">
							<span class="ag-service-duration-badge">
								<?php echo esc_html( $service->duration ); ?> <?php esc_html_e( 'dakika', 'appointment-general' ); ?>
							</span>
							<span class="ag-service-price-badge">
								<?php if ( 'before' === $currency_position ) : ?>
									<?php echo esc_html( $currency_symbol . number_format( $service->price, 0 ) ); ?>
								<?php else : ?>
									<?php echo esc_html( number_format( $service->price, 0 ) . ' ' . $currency_symbol ); ?>
								<?php endif; ?>
							</span>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="ag-empty-message"><?php esc_html_e( 'Henüz hizmet bulunmuyor.', 'appointment-general' ); ?></p>
	<?php endif; ?>
</div>
