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
 * @var string $button_text
 */

?>
<div class="sswps-konbini-instructions">
    <ol>
        <li>
            <?php
            /* translators: %s: button. */
            printf( esc_html__( 'Click %1$s and you will be presented with your Konbini payment code and confirmation number.', 'simple-secure-stripe' ), '<b>' . esc_html( $button_text ) . '</b>' );
            ?>
        </li>
        <li>
            <?php esc_html_e( 'Your order email will contain a link to your Konbini voucher which has your payment code and confirmation number.', 'simple-secure-stripe' ) ?>
        </li>
        <li>
            <?php esc_html_e( 'At the convenience store, provide the payment code and confirmation number to the payment machine or cashier.', 'simple-secure-stripe' ) ?>
        </li>
        <li>
            <?php esc_html_e( 'After the payment is complete, keep the receipt for your records.', 'simple-secure-stripe' ) ?>
        </li>
    </ol>
</div>
