<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

use SimpleSecureWP\SimpleSecureStripe\Gateways;

/**
 * @var Gateways\ACH $gateway
 * @version 1.0.0
 */
?>
<div id="sswps-plaid-container">
	<?php if ( 'sandbox' === sswps_mode() ) : ?>
		<p><?php esc_html_e( 'sandbox testing credentials', 'simple-secure-stripe' ); ?>:</p>
		<p><strong><?php esc_html_e( 'username', 'simple-secure-stripe' ); ?></strong>:&nbsp;user_good</p>
		<p><strong><?php esc_html_e( 'password', 'simple-secure-stripe' ); ?></strong>:&nbsp;pass_good</p>
		<p><strong><?php esc_html_e( 'pin', 'simple-secure-stripe' ); ?></strong>:&nbsp;credential_good&nbsp;(<?php esc_html_e( 'when required', 'simple-secure-stripe' ); ?>)</p>
	<?php endif; ?>
</div>