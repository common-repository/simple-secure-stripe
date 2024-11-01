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
 * @var WP_User $user Current user.
 * @var bool     $signed_up Whether or not the user has signed up.
 * @var \SimpleSecureWP\SimpleSecureStripe\Admin\Views\Welcome $this
 */

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
$form_fields = App::get( API::class )->form_fields;
?>
<div class="sswps-admin__page">
	<?php $this->template( 'components/header', [ 'hide_pill' => true ] ); ?>
	<div class="sswps-admin__page-content">
		<div class="sswps-admin__content-box sswps-admin__content-box--stripe">
			<header>
				<h2><?php esc_html_e( 'Configure Stripe', 'simple-secure-stripe' ); ?></h2>
				<?php if ( ! empty( $account_id ) ) : ?>
					<div>
						<?php $this->template( 'components/pill' ); ?>
					</div>
				<?php endif; ?>
			</header>
			<p>
				<?php esc_html_e( 'A simple and secure way to accept Credit Cards, Apple Pay, Google Pay, SEPA, and more!', 'simple-secure-stripe' ); ?>
			</p>

			<div class="sswps-admin__content-box-actions">
				<?php if ( empty( $account_id ) ) : ?>
					<?php echo App::get( API::class )->generate_stripe_connect_html( 'stripe_connect', $form_fields['stripe_connect'] ); ?>
				<?php endif; ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=sswps_api' ) ); ?>" class="sswp-button--secondary"><?php esc_html_e( 'Settings', 'simple-secure-stripe' ); ?></a>
			</div>
		</div>
		<div class="sswps-admin__content-box sswps-admin__content-box--docs">
			<header>
				<h2><?php esc_html_e( 'Documentation', 'simple-secure-stripe' ); ?></h2>
			</header>
			<ul>
				<li><a href="https://sswp.io/ars3m"><?php esc_html_e( 'Getting started', 'simple-secure-stripe' ); ?></a></li>
				<li><a href="https://sswp.io/j18sw"><?php esc_html_e( 'Creating a webhook', 'simple-secure-stripe' ); ?></a></li>
				<li><a href="https://sswp.io/tb30v"><?php esc_html_e( 'Contact support', 'simple-secure-stripe' ); ?></a></li>
			</ul>
		</div>
	</div>
</div>