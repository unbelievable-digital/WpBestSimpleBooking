<?php
/**
 * Service Card Inner Template
 *
 * Shared partial for rendering a single service card's inner HTML.
 * Expects $service, $has_discount, $effective_price, $input_type,
 * $multi_service, $currency_symbol, $currency_position to be in scope.
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="<?php echo esc_attr( $input_type ); ?>" name="<?php echo $multi_service ? 'service_ids[]' : 'service_id'; ?>" value="<?php echo esc_attr( $service->id ); ?>"<?php echo $multi_service ? '' : ' required'; ?>>
<div class="unbsb-service-card">
	<span class="unbsb-service-check">
		<svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
	</span>
	<span class="unbsb-service-color" style="background-color: <?php echo esc_attr( $service->color ); ?>"></span>
	<div class="unbsb-service-info">
		<div class="unbsb-service-name-row">
			<strong class="unbsb-service-name"><?php echo esc_html( $service->name ); ?></strong>
			<span class="unbsb-service-duration">
				<svg viewBox="0 0 24 24" width="12" height="12"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>
				<?php echo esc_html( $service->duration ); ?> <?php esc_html_e( 'min', 'unbelievable-salon-booking' ); ?>
			</span>
		</div>
		<?php if ( $service->description ) : ?>
			<p class="unbsb-service-desc"><?php echo esc_html( $service->description ); ?></p>
		<?php endif; ?>
	</div>
	<div class="unbsb-service-price-area">
		<?php if ( $has_discount ) : ?>
			<span class="unbsb-price-original-top">
				<?php if ( 'before' === $currency_position ) : ?>
					<?php echo esc_html( $currency_symbol . number_format( $service->price, 0 ) ); ?>
				<?php else : ?>
					<?php echo esc_html( number_format( $service->price, 0 ) . ' ' . $currency_symbol ); ?>
				<?php endif; ?>
			</span>
			<span class="unbsb-price-current unbsb-price-discounted-big">
				<?php if ( 'before' === $currency_position ) : ?>
					<?php echo esc_html( $currency_symbol . number_format( $service->discounted_price, 0 ) ); ?>
				<?php else : ?>
					<?php echo esc_html( number_format( $service->discounted_price, 0 ) ); ?><small><?php echo esc_html( ' ' . $currency_symbol ); ?></small>
				<?php endif; ?>
			</span>
			<span class="unbsb-discount-badge">
				<?php
				$discount_pct = round( ( ( floatval( $service->price ) - floatval( $service->discounted_price ) ) / floatval( $service->price ) ) * 100 );
				/* translators: %d: discount percentage */
				echo esc_html( sprintf( __( '-%d%%', 'unbelievable-salon-booking' ), $discount_pct ) );
				?>
			</span>
		<?php else : ?>
			<span class="unbsb-price-current">
				<?php if ( 'before' === $currency_position ) : ?>
					<?php echo esc_html( $currency_symbol . number_format( $service->price, 0 ) ); ?>
				<?php else : ?>
					<?php echo esc_html( number_format( $service->price, 0 ) ); ?><small><?php echo esc_html( ' ' . $currency_symbol ); ?></small>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</div>
</div>
