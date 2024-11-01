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
	<?php woocommerce_form_field( 'sswps_boleto_tax_id', array(
		'type'        => 'text',
		'label'       => __( 'CPF / CNPJ' ),
		'placeholder' => __( 'Enter your CPF/CNPJ', 'simple-secure-stripe' ),
		'required'    => true
	) ) ?>
	<?php if ( sswps_mode() === 'test' ): ?>
        <div class="sswps-boleto__description">
            <p><?php esc_html_e( 'Test mode values', 'simple-secure-stripe' ) ?></p>
            <div>
                <label>CPF:</label>&nbsp;<span>000.000.000-00</span>
            </div>
            <div>
                <label>CNPJ:</label>&nbsp;<span>00.000.000/0000-00</span>
            </div>
        </div>
	<?php else: ?>
        <div class="sswps-boleto__description">
            <p><?php esc_html_e( 'Accepted formats', 'simple-secure-stripe' ) ?></p>
            <div>
                <label>CPF:</label>&nbsp;<span><?php esc_html_e( 'XXX.XXX.XXX-XX or XXXXXXXXXXX', 'simple-secure-stripe' ) ?></span>
            </div>
            <div>
                <label>CNPJ:</label>&nbsp;<span><?php esc_html_e( 'XX.XXX.XXX/XXXX-XX or XXXXXXXXXXXXXX', 'simple-secure-stripe' ) ?></span>
            </div>
        </div>
	<?php endif; ?>
</div>