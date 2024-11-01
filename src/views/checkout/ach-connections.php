<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var Gateways\ACH $gateway
 */
use SimpleSecureWP\SimpleSecureStripe\Gateways;
?>
<div id="sswps-ach-container">
	<p class="sswps-ach__mandate">
		<?php echo esc_html( $gateway->get_mandate_text() ) ?>
	</p>
</div>
