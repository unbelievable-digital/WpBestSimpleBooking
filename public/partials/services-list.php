<?php
/**
 * Services List Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_symbol   = get_option( 'unbsb_currency_symbol', '₺' );
$currency_position = get_option( 'unbsb_currency_position', 'after' );
?>

<div class="unbsb-services-wrapper">
	<?php if ( ! empty( $services ) ) : ?>
		<div class="unbsb-services-grid">
			<?php foreach ( $services as $service ) : ?>
				<div class="unbsb-service-card-public">
					<span class="unbsb-service-color-bar" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
					<div class="unbsb-service-content">
						<h3 class="unbsb-service-title"><?php echo esc_html( $service->name ); ?></h3>
						<?php if ( $service->description ) : ?>
							<p class="unbsb-service-description"><?php echo esc_html( $service->description ); ?></p>
						<?php endif; ?>
						<?php $has_discount = ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ); ?>
						<div class="unbsb-service-footer">
							<span class="unbsb-service-duration-badge">
								<?php echo esc_html( $service->duration ); ?> <?php esc_html_e( 'minutes', 'unbelievable-salon-booking' ); ?>
							</span>
							<?php if ( $has_discount ) : ?>
								<span class="unbsb-service-price-badge unbsb-has-discount">
									<span class="unbsb-price-original">
										<?php if ( 'before' === $currency_position ) : ?>
											<?php echo esc_html( $currency_symbol . number_format( $service->price, 0 ) ); ?>
										<?php else : ?>
											<?php echo esc_html( number_format( $service->price, 0 ) . ' ' . $currency_symbol ); ?>
										<?php endif; ?>
									</span>
									<span class="unbsb-price-discounted">
										<?php if ( 'before' === $currency_position ) : ?>
											<?php echo esc_html( $currency_symbol . number_format( $service->discounted_price, 0 ) ); ?>
										<?php else : ?>
											<?php echo esc_html( number_format( $service->discounted_price, 0 ) . ' ' . $currency_symbol ); ?>
										<?php endif; ?>
									</span>
								</span>
							<?php else : ?>
								<span class="unbsb-service-price-badge">
									<?php if ( 'before' === $currency_position ) : ?>
										<?php echo esc_html( $currency_symbol . number_format( $service->price, 0 ) ); ?>
									<?php else : ?>
										<?php echo esc_html( number_format( $service->price, 0 ) . ' ' . $currency_symbol ); ?>
									<?php endif; ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="unbsb-empty-message"><?php esc_html_e( 'No services available yet.', 'unbelievable-salon-booking' ); ?></p>
	<?php endif; ?>
</div>
