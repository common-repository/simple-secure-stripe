<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var string $button_type
 * @var string $style
 * @var string $type
 * @version 1.0.0
 * @package Stripe/Templates
 */
?>
<button
	class="apple-pay-button <?php echo esc_attr( $style ) ?>"
	style="<?php echo '-apple-pay-button-style: ' . esc_attr( $button_type ) . '; -apple-pay-button-type:' . apply_filters( 'sswps/applepay_button_type', esc_attr( $type ) ) ?>"
></button>