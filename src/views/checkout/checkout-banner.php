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
 * @var Gateways\Abstract_Gateway[] $gateways
 */
use SimpleSecureWP\SimpleSecureStripe\Gateways;
?>
<div class="sswps-banner-checkout">
    <fieldset>
        <legend class="banner-title"><?php esc_html_e('Express Checkout', 'simple-secure-stripe')?></legend>
        <ul class="sswps_checkout_banner_gateways" style="list-style: none">
		    <?php foreach( $gateways as $gateway ) : ?>
                <li class="sswps-checkout-banner-gateway banner_payment_method_<?php echo esc_attr( $gateway->id ); ?>">

                </li>
		    <?php endforeach; ?>
        </ul>
    </fieldset>
    <span class="banner-divider"><?php esc_html_e('OR', 'simple-secure-stripe')?></span>
</div>