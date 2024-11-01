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
 * @var Template $this Template.
 * @var Abstract_Settings|Abstract_Gateway $settings The settings object.
 * @var string $sub_template The sub template to load.
 */

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings\Abstract_Settings;
use SimpleSecureWP\SimpleSecureStripe\Gateways\Abstract_Gateway;
use SimpleSecureWP\SimpleSecureStripe\Template;

$errors = $settings->get_errors();

$hide_local_payments_nav_for = [
	'sswps_ach',
	'sswps_applepay',
	'sswps_cc',
	'sswps_googlepay',
	'sswps_payment_request',
];
?>
<input type="hidden" id="sswps_prefix" name="sswps_prefix" value="<?php echo esc_attr( $settings->get_prefix() ); ?>"/>

<?php if ( $errors ) : ?>
	<div id="woocommerce_errors" class="error notice inline is-dismissible">
		<?php foreach ( $errors as $error ) : ?>
			<p><?php echo wp_kses_post( $error ); ?></p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php $this->template( 'components/header' ); ?>
<?php $this->template( 'settings/nav' ); ?>
<?php if ( $settings instanceof Abstract_Gateway && ! in_array( $settings->id, $hide_local_payments_nav_for ) ) : ?>
	<?php $this->template( 'settings/local-payments-nav' ); ?>
<?php endif; ?>

<div class="sswps-admin__settings <?php echo esc_attr( $sub_template ); ?>">
	<?php $settings->admin_options(); ?>
</div>