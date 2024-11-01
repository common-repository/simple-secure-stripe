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
 * @var Gateways\Blik $gateway
 */
use SimpleSecureWP\SimpleSecureStripe\Gateways;
?>
<div id="sswps_local_payment_<?php echo esc_attr( $gateway->id ); ?>" data-active="<?php echo esc_attr( $gateway->is_local_payment_available() ); ?>">
    <ol>
        <li><?php esc_html_e( 'Request your 6-digit code from your banking application.', 'simple-secure-stripe' ) ?></li>
        <li><?php
        /* translators: %1$s: button */
        printf( esc_html__( 'Enter the code into the input fields below. Click %1$s once you have entered the code.', 'simple-secure-stripe' ), '<b>' . $gateway->order_button_text . '</b>' );
        ?></li>
        <li><?php esc_html_e( 'You will receive a notification on your mobile device asking you to authorize the payment.', 'simple-secure-stripe' ); ?></li>
    </ol>
    <div class="sswps-blik-code-container">
        <p>
			<?php esc_html_e( 'Please enter your 6 digit BLIK code.', 'simple-secure-stripe' ) ?>
        </p>
        <div class="sswps-blik-code">
			<?php foreach ( range( 0, 5 ) as $idx ): ?>
				<?php woocommerce_form_field( 'blik_code_' . $idx, array(
					'type'              => 'text',
					'maxlength'         => 1,
					'input_class'       => array( 'blik-code' ),
					'custom_attributes' => array( 'data-blik_index' => $idx )
				) ) ?>
			<?php endforeach; ?>
        </div>
    </div>
    <div class="blik-timer-container" style="display: none">
        <div>
            <p>
				<?php esc_html_e( 'Your transaction will expire in:', 'simple-secure-stripe' ) ?>
            </p>
            <span id="blik_timer"></span>
        </div>
    </div>
</div>

