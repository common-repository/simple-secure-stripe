<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var Gateways\Abstract_Local_Payment $gateway
 */
use SimpleSecureWP\SimpleSecureStripe\Gateways;
?>
<div id="sswps_local_payment_<?php echo esc_attr( $gateway->id ); ?>" data-active="<?php echo esc_attr( $gateway->is_local_payment_available() ); ?>">
	<?php sswps_get_template( 'offsite-notice.php', array( 'text' => $gateway->order_button_text, 'title' => $gateway->get_title() ) ) ?>
</div>
