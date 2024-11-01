<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

use SimpleSecureWP\SimpleSecureStripe\Gateways\Abstract_Gateway;

/**
 * @var array            $tokens
 * @var Abstract_Gateway $gateway
 * @version 1.0.0
 *
 */
?>
<input type="radio" class="sswps-payment-type" checked="checked" id="<?php echo esc_attr( $gateway->id ); ?>_use_saved" name="<?php echo esc_attr( $gateway->payment_type_key ); ?>" value="saved"/>
<label for="<?php echo esc_attr( $gateway->id ); ?>_use_saved" class="sswps-label-payment-type"><?php echo esc_html( $gateway->get_saved_methods_label() ); ?></label>
<div class="sswps-saved-methods-container <?php echo esc_attr( $gateway->id ); ?>-saved-methods-container">
	<select class="sswps-saved-methods" id="<?php echo esc_attr( $gateway->saved_method_key ); ?>" name="<?php echo esc_attr( $gateway->saved_method_key ); ?>">
		<?php foreach ( $tokens as $token ) : ?>
			<option class="sswps-saved-method <?php echo esc_attr( $token->get_html_classes() ); ?>" value="<?php echo esc_attr( $token->get_token() ); ?>"><?php echo $token->get_payment_method_title( $gateway->get_option( 'method_format' ) ); ?></option>
		<?php endforeach; ?>
	</select>
</div>