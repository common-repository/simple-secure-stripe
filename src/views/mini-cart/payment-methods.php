<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
use SimpleSecureWP\SimpleSecureStripe\Gateways;
/**
 * @version 1.0.0
 * @var Gateways\Abstract_Gateway[] $gateways
 */
?>
<input type="hidden" class="sswps_mini_cart_payment_methods"/>
<?php foreach ( $gateways as $gateway ) : ?>
	<?php $gateway->mini_cart_fields() ?>
<?php endforeach;