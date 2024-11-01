<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var Gateways\Klarna $gateway
 * @version 1.0.0
 *
 */
use SimpleSecureWP\SimpleSecureStripe\Gateways;
$payment_options = $gateway->get_option( 'payment_categories' );
?>
<?php if ( sswps_mode() === 'test' ): ?>
    <div class="sswps-klarna__testmode">
        <label><?php esc_html_e( 'Test mode sms', 'simple-secure-stripe' ); ?>:</label>&nbsp;<span>123456</span>
    </div>
<?php endif; ?>
<div id="sswps_local_payment_<?php echo esc_attr( $gateway->id ); ?>" data-active="<?php echo esc_attr( $gateway->is_local_payment_available() ); ?>"></div>