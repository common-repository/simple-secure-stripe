<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var array   $payment_methods
 * @var WP_User $user
 */

?>
<div class="sswps-user-info">
	<h2><?php esc_html_e( 'Stripe Customer ID\'s', 'simple-secure-stripe' ); ?></h2>
	<p><?php esc_html_e( 'If you change a customer ID, the customer\'s payment methods will be imported from your Stripe account.', 'simple-secure-stripe'  ); ?></p>
	<p><?php esc_html_e( 'If you remove a customer ID, the customer\'s payment methods will be removed from the WC payment methods table.', 'simple-secure-stripe'  ); ?></p>
	<table class="form-table">
		<tbody>
		<tr>
			<th><?php esc_html_e( 'Live ID', 'simple-secure-stripe' ); ?></th>
			<td><input
					type="text"
					id="sswps_live_id"
					name="sswps_live_id"
					value="<?php echo esc_attr( sswps_get_customer_id( $user->ID, 'live' ) ); ?>"
				/>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Test ID', 'simple-secure-stripe' ); ?></th>
			<td><input
					type="text"
					id="sswps_test_id"
					name="sswps_test_id"
					value="<?php echo esc_attr( sswps_get_customer_id( $user->ID, 'test' ) ); ?>"
				/>
			</td>
		</tr>
		</tbody>
	</table>
	<h2><?php esc_html_e( 'Stripe Live Payment Methods', 'simple-secure-stripe' ); ?></h2>
	<?php if ( $payment_methods['live'] ) : ?>
		<table class="sswps-payment-methods">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Payment Gateway', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Payment Method', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Token', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'simple-secure-stripe' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $payment_methods['live'] as $token ) : ?>
				<tr>
					<td><?php echo esc_html( $token->get_gateway_id() ); ?></td>
					<td><?php echo esc_html( $token->get_payment_method_title() ); ?></td>
					<td><?php echo esc_html( $token->get_token() ); ?></td>
					<td><input
							type="checkbox" name="payment_methods[live][]"
							value="<?php echo esc_attr( $token->get_id() . ':' . $token->get_token() ); ?>"
						/></td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<th><?php esc_html_e( 'Action', 'simple-secure-stripe' ); ?></th>
				<td><select name="live_payment_method_actions">
						<option value="none" selected><?php esc_html_e( 'No Action', 'simple-secure-stripe' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Delete', 'simple-secure-stripe' ); ?></option>
					</select></td>
			</tr>
			</tbody>
		</table>
	<?php else : ?>
		<?php esc_html_e( 'No live payment methods saved', 'simple-secure-stripe' ); ?>
	<?php endif; ?>
	<h2><?php esc_html_e( 'Stripe Test Payment Methods', 'simple-secure-stripe' ); ?></h2>
	<?php if ( $payment_methods['test'] ) : ?>
		<table class="sswps-payment-methods">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Payment Gateway', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Payment Method', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Token', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'simple-secure-stripe' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $payment_methods['test'] as $token ) : ?>
				<tr>
					<td><?php echo $token->get_gateway_id(); ?></td>
					<td><?php echo $token->get_payment_method_title(); ?></td>
					<td><?php echo $token->get_token(); ?></td>
					<td><input
							type="checkbox" name="payment_methods[test][]"
							value="<?php echo esc_attr( $token->get_id() . ':' . $token->get_token() ); ?>"
						/></td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<th><?php esc_html_e( 'Action', 'simple-secure-stripe' ); ?></th>
				<td><select name="test_payment_method_actions">
						<option value="none" selected><?php esc_html_e( 'No Action', 'simple-secure-stripe' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Delete', 'simple-secure-stripe' ); ?></option>
					</select></td>
			</tr>
			</tbody>
		</table>
	<?php else : ?>
		<?php esc_html_e( 'No test payment methods saved', 'simple-secure-stripe' ); ?>
	<?php endif; ?>
	<?php
	/* translators: 1: open strong tag, 2: close strong tag. */
	printf( esc_html__( '%1$sNote:%2$s Payment methods will be deleted from your WordPress site and within Stripe.', 'simple-secure-stripe' ), '<strong>', '</strong>' );
	?>
</div>
