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
<div class="sswps-paynow-instructions">
    <ol>
        <li>
            <?php
            /* translators: %s: button. */
            printf( esc_html__( 'Click %1$s and you will be shown a QR code.', 'simple-secure-stripe' ), '<b>' . esc_html( $button_text ) . '</b>' ) ;
            ?>
        </li>
        <li>
            <?php esc_html_e( 'Scan the QR code using an app from participating banks and participating non-bank financial institutions.', 'simple-secure-stripe' ) ?>
        </li>
        <li>
            <?php esc_html_e( 'The authentication process may take several moments. Once confirmed, you will be redirected to the order received page.', 'simple-secure-stripe' ) ?>
        </li>
    </ol>
</div>
