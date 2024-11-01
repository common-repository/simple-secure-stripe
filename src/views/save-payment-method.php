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
<div class="sswps-save-source"
     <?php if ( ! is_user_logged_in() && ! WC()->checkout()->is_registration_required() ): ?>style="display:none"<?php endif ?>>
	<label class="checkbox">
		<input type="checkbox" id="<?php echo $gateway->save_source_key ?>" name="<?php echo $gateway->save_source_key ?>" value="yes"/>
		<span class="save-source-checkbox"></span>
	</label>
	<label class="save-source-label"><?php echo esc_html( $gateway->get_save_payment_method_label() ) ?></label>
</div>