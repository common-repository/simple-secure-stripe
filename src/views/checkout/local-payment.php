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
 * @var Gateways\Abstract_Local_Payment $gateway
 */

use SimpleSecureWP\SimpleSecureStripe\Gateways;

?>
	<div id="sswps_local_payment_<?php echo esc_attr( $gateway->id ); ?>" data-active="<?php echo esc_attr( $gateway->is_local_payment_available() ); ?>">

	</div>
<?php
$desc = $gateway->get_local_payment_description();
?>
<?php if ( $desc ) : ?>
	<p class="sswps-local-desc <?php echo esc_attr( $gateway->id ); ?>"><?php echo $desc ?></p>
<?php endif; ?>
<?php if ( $gateway->show_save_payment_method_html() ): ?>
	<?php sswps_get_template( 'save-payment-method.php', [ 'gateway' => $gateway ] ) ?>
<?php endif;