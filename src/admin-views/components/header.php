<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var Template $this Template.
 */

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings\API;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Template;

App::get( API::class )->init_form_fields();
$mode = sswps_mode();
$account_id = App::get( API::class )->get_option( 'account_id' );
$webhook_created = App::get( API::class )->get_option( 'webhook_created' );
$webhook_secret = App::get( API::class )->get_option( 'webhook_secret' );
$test_publishable_key = App::get( API::class )->get_option( 'publishable_key_test' );
$test_secret_key = App::get( API::class )->get_option( 'secret_key_test' );
?>
<div class="sswps-admin__settings-header">
	<div class="sswps-admin__settings-header-logo-group">
		<img class="sswps-admin__settings-header-logo" src="<?php echo esc_url( App::get( Plugin::class )->assets_url() . 'img/logo.svg' ); ?>" title="<?php esc_attr_e( 'Simple & Secure WP', 'simple-secure-stripe' ); ?>"/>
		<span class="sswps-admin__settings-header-logo-separator"><?php esc_html_e( '+', 'simple-secure-stripe' ) ?></span>
		<img src="<?php echo esc_url( App::get( Plugin::class )->assets_url() . 'img/stripe_logo.svg' ); ?>" class="sswps-admin__settings-header-stripe" alt="<?php esc_attr_e( 'Stripe', 'simple-secure-stripe' ); ?>"/>
	</div>

	<nav class="sswps-admin__settings-header-nav">
		<?php if ( empty( $hide_pill ) ) : ?>
			<?php $this->template( 'components/pill' ); ?>
		<?php endif; ?>
		<a href="https://sswp.io/about" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'About', 'simple-secure-stripe' ); ?></a> | <a href="https://sswp.io/stripe" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'simple-secure-stripe' ); ?></a> | <a href="https://sswp.io/support" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'simple-secure-stripe' ); ?></a>
	</nav>
</div>
