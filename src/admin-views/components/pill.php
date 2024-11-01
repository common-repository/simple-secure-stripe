<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings\API;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

App::get( API::class )->init_form_fields();
$mode = sswps_mode();
$account_id = App::get( API::class )->get_option( 'account_id' );
$webhook_created = App::get( API::class )->get_option( 'webhook_created' );
$webhook_secret = App::get( API::class )->get_option( 'webhook_secret' );
$test_publishable_key = App::get( API::class )->get_option( 'publishable_key_test' );
$test_secret_key = App::get( API::class )->get_option( 'secret_key_test' );

if (
	empty( $account_id )
	|| empty( $webhook_created )
	|| $webhook_created === 'no'
	|| empty( $webhook_secret )
	|| $webhook_secret === 'no'
	|| (
		$mode === 'test'
		&& ( empty( $test_publishable_key ) || empty( $test_secret_key ) )
	)
) : ?>
	<span class="sswps-mode__pill sswps-mode__pill--setup-needed"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=sswps_api' ) ); ?>"><?php esc_html_e( 'Setup Needed', 'simple-secure-stripe' ) ?></a></span>
<?php elseif ( $mode === 'live' ) : ?>
	<span class="sswps-mode__pill sswps-mode__pill--live"><?php esc_html_e( 'Live Mode', 'simple-secure-stripe' ) ?></span>
<?php else: ?>
	<span class="sswps-mode__pill sswps-mode__pill--test"><?php esc_html_e( 'Test Mode', 'simple-secure-stripe' ) ?></span>
<?php endif;
