<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var Charge $charge
 * @var WC_Order $order
 */

use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;

?>
<?php if ( ! $order->has_status( 'cancelled' ) ) : ?>
	<?php if ( ( $charge->status === 'pending' && ! $charge->captured ) || ( $charge->status === 'succeeded' && ! $charge->captured ) ) : ?>
		<div class="charge-actions">
			<h2><?php esc_html_e( 'Actions', 'simple-secure-stripe' ); ?></h2>
			<div>
				<input
					type="text" class="wc_input_price" name="capture_amount"
					value="<?php echo esc_attr( $order->get_total( 'raw' ) ); ?>"
					placeholder="<?php esc_attr_e( 'capture amount', 'simple-secure-stripe' ); ?>"
				/>
				<button class="button button-secondary do-api-capture"><?php esc_html_e( 'Capture', 'simple-secure-stripe' ); ?></button>
				<button class="button button-secondary do-api-cancel"><?php esc_html_e( 'Void', 'simple-secure-stripe' ); ?></button>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
<div class="data-container">
	<div class="charge-data column-6">
		<h3><?php esc_html_e( 'Charge Data', 'simple-secure-stripe' ); ?></h3>
		<div class="metadata">
			<label><?php esc_html_e( 'Mode', 'simple-secure-stripe' ); ?></label>:&nbsp;
			<?php $charge->livemode ? esc_html_e( 'Live', 'simple-secure-stripe' ) : esc_html_e( 'Test', 'simple-secure-stripe' ); ?>
		</div>
		<div class="metadata">
			<label><?php esc_html_e( 'Status', 'simple-secure-stripe' ); ?></label>:&nbsp;
			<?php echo esc_html( $charge->status ); ?>
		</div>
		<?php if ( ( $payment_intent_id = $order->get_meta( Constants::PAYMENT_INTENT_ID, true ) ) ) : ?>
			<div class="metadata">
				<label><?php esc_html_e( 'Payment Intent', 'simple-secure-stripe' ); ?></label>:&nbsp;
				<?php echo esc_html( $payment_intent_id ); ?>
			</div>
		<?php endif; ?>
		<?php if ( isset( $charge->customer ) ) : ?>
			<div class="metadata">
				<label><?php esc_html_e( 'Customer', 'simple-secure-stripe' ); ?></label>:&nbsp;
				<?php echo esc_html( $charge->customer ); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="payment-data column-6">
		<h3><?php esc_html_e( 'Payment Method', 'simple-secure-stripe' ); ?></h3>
		<div class="metadata">
			<label><?php esc_html_e( 'Title', 'simple-secure-stripe' ); ?></label>:&nbsp;
			<?php echo esc_html( $order->get_payment_method_title() ); ?>
		</div>
		<div class="metadata">
			<label><?php esc_html_e( 'Type', 'simple-secure-stripe' ); ?></label>:&nbsp;
			<?php echo esc_html( $charge->payment_method_details->offsetGet( 'type' ) ); ?>
		</div>
		<?php if ( $charge->payment_method_details->offsetGet( 'card' ) !== null ) : ?>
			<div class="metadata">
				<label><?php esc_html_e( 'Exp', 'simple-secure-stripe' ); ?>:&nbsp;</label>
				<?php printf( '%02d / %s', $charge->payment_method_details->offsetGet( 'card' )->exp_month, $charge->payment_method_details->offsetGet( 'card' )->exp_year ); ?>
			</div>
			<div class="metadata">
				<label><?php esc_html_e( 'Fingerprint', 'simple-secure-stripe' ); ?>:&nbsp;</label>
				<?php echo esc_html( $charge->payment_method_details->offsetGet( 'card' )->fingerprint ); ?>
			</div>
			<div class="metadata">
				<label><?php esc_html_e( 'CVC check', 'simple-secure-stripe' ); ?>:&nbsp;</label>
				<?php echo esc_html( $charge->payment_method_details->offsetGet( 'card' )->checks->cvc_check ); ?>
			</div>
			<div class="metadata">
				<label><?php esc_html_e( 'Postal check', 'simple-secure-stripe' ); ?>:&nbsp;</label>
				<?php echo esc_html( $charge->payment_method_details->offsetGet( 'card' )->checks->address_postal_code_check ); ?>
			</div>
			<div class="metadata">
				<label><?php esc_html_e( 'Street check', 'simple-secure-stripe' ); ?>:&nbsp;</label>
				<?php echo esc_html( $charge->payment_method_details->offsetGet( 'card' )->checks->address_line1_check ); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="payment-data column-6">
		<h3><?php esc_html_e( 'Risk Data', 'simple-secure-stripe' ); ?></h3>
		<?php if ( $charge->outcome->offsetGet( 'risk_score' ) !== null ) { ?>
			<div class="metadata">
				<label><?php esc_html_e( 'Score', 'simple-secure-stripe' ); ?></label>
				<?php echo esc_html( $charge->outcome->offsetGet( 'risk_score' ) ); ?>
			</div>
		<?php } ?>
		<div class="metadata">
			<label><?php esc_html_e( 'Level', 'simple-secure-stripe' ); ?></label>
			<?php echo esc_html( $charge->outcome->offsetGet( 'risk_score' ) ); ?>
		</div>
	</div>
</div>
