<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

/**
 * @version 1.0.0
 */

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

?>
<div class="sswps-paymentRequest-icons-container">
	<img
		class="sswps-paymentRequest-icon gpay"
		src="<?php echo esc_url( App::get( Plugin::class )->assets_url( 'img/googlepay_round_outline.svg' ) ); ?>" style="display: none"
	/>
	<img
		class="sswps-paymentRequest-icon microsoft-pay"
		src="<?php echo esc_url( App::get( Plugin::class )->assets_url( 'img/microsoft_pay.svg' ) ); ?>" style="display: none"
	/>
</div>