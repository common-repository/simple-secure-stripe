<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var Gateways\CC $gateway
 * @version 1.0.0
 *
 */
use SimpleSecureWP\SimpleSecureStripe\Gateways;
?>
<?php if ( $gateway->is_custom_form_active() ): ?>
    <div id="sswps-cc-custom-form">
		<?php sswps_get_template( $gateway->get_custom_form_template(), [ 'gateway' => $gateway ] ) ?>
    </div>
<?php else: ?>
    <div id="sswps-card-element" class="<?php echo esc_attr( $gateway->get_option( 'form_type' ) ); ?>-type"></div>
<?php endif; ?>
<?php if ( $gateway->show_save_payment_method_html() ): ?>
	<?php sswps_get_template( 'save-payment-method.php', [ 'gateway' => $gateway ] ) ?>
<?php endif; ?>