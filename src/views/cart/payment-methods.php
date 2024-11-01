<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

use SimpleSecureWP\SimpleSecureStripe\Gateways;

/**
 * @var mixed                       $after
 * @var int                         $cart_total
 * @var Gateways\Abstract_Gateway[] $gateways
 * @version 1.0.0
 */
?>
<div class="sswps-cart-checkout-container" <?php if ( $cart_total == 0 ) : ?>style="display: none"<?php endif; ?>>
	<ul class="sswps_cart_payment_methods" style="list-style: none">
		<?php if ( $after ): ?>
			<li class="sswps-payment-method or">
				<p class="sswps-cart-or">
					&mdash;&nbsp;<?php esc_html_e( 'or', 'simple-secure-stripe' ) ?>&nbsp;&mdash;
				</p>
			</li>
		<?php endif; ?>
		<?php foreach ( $gateways as $gateway ): ?>
			<li
				class="sswps-payment-method payment_method_<?php echo esc_attr( $gateway->id ) ?>"
			>
				<div class="payment-box">
					<?php $gateway->cart_fields() ?>
				</div>
			</li>
		<?php endforeach; ?>
		<?php if ( ! $after ): ?>
			<li class="sswps-payment-method or">
				<p class="sswps-cart-or">
					&mdash;&nbsp;<?php esc_html_e( 'or', 'simple-secure-stripe' ) ?>&nbsp;&mdash;
				</p>
			</li>
		<?php endif; ?>
	</ul>
</div>
