<?php
/**
 * @version 1.0.0
 *
 * @var Gateways\Abstract_Gateway $gateway
 * @var array                     $tokens
 */

use SimpleSecureWP\SimpleSecureStripe\Gateways;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( ( $desc = $gateway->get_description() ) ): ?>
	<div class="sswps-gateway-desc<?php if ( $tokens ): ?> has_tokens<?php endif; ?>">
		<?php echo wp_kses_post( wpautop( wptexturize( $desc ) ) ); ?>
	</div>
<?php endif; ?>

<div class="<?php echo esc_attr( $gateway->id ); ?>-container sswps-gateway-container<?php if ( $tokens ): ?> has_tokens<?php endif; ?>">
	<?php if ( $tokens ): ?>
		<input
			type="radio" class="sswps-payment-type"
			id="<?php echo esc_attr( $gateway->id ); ?>_use_new"
			name="<?php echo esc_attr( $gateway->payment_type_key ); ?>" value="new"
		/>
		<label
			for="<?php echo esc_attr( $gateway->id ); ?>_use_new"
			class="sswps-label-payment-type"
		><?php echo $gateway->get_new_method_label() ?></label>
	<?php endif; ?>
	<div
		class="<?php echo esc_attr( $gateway->id ); ?>-new-method-container"
		<?php if ( $tokens ): ?> style="display: none" <?php endif; ?>>
		<?php sswps_get_template( 'checkout/' . $gateway->template_name, [ 'gateway' => $gateway ] ) ?>
	</div>
	<?php
	if ( $tokens ) :
		$gateway->saved_payment_methods( $tokens );
	endif;
	?>
	<?php if ( ( is_checkout() || is_checkout_pay_page() ) && $gateway->is_installment_available() ): ?>
		<?php sswps_get_template( 'installment-plans.php', [ 'installments' => null ] ) ?>
	<?php endif ?>
</div>