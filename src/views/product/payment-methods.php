<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @version 1.0.0
 *
 * @var string $position
 * @var array $gateways
 */
?>
<div class="sswps-clear"></div>
<div class="sswps-product-checkout-container <?php echo esc_attr( $position );?>">
	<ul class="sswps_product_payment_methods" style="list-style: none">
		<?php foreach($gateways as $gateway):?>
			<li class="payment_method_<?php echo esc_attr($gateway->id)?>">
				<div class="payment-box">
					<?php $gateway->product_fields()?>
				</div>
			</li>
		<?php endforeach;?>
	</ul>
</div>