<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;

use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;
use SimpleSecureWP\SimpleSecureStripe\Assets\Assets;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Assets\Api as AssetsApi;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException;

class WeChatPayment extends AbstractStripeLocalPayment {

	protected $name = 'sswps_wechat';

	public function __construct( AssetsApi $assets_api ) {
		parent::__construct( $assets_api );
	}

	public function init() {
		parent::init();
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', [ $this, 'update_redirect_url' ], 1000, 2 );
	}

	public function get_payment_method_script_handles() {
		wp_enqueue_script( 'sswps-qrcode', App::get( Assets::class )->assets_url( 'js/frontend/qrcode.js' ), [] );

		return parent::get_payment_method_script_handles();
	}

	public function get_payment_method_data() {
		return array_merge( parent::get_payment_method_data(), [
			'qrSize' => $this->payment_method->get_option( 'qr_size' ),
		] );
	}

	/**
	 * Update the redirect url for live mode so that the Redirect_Hanlder can process
	 * the live payment.
	 *
	 * @param PaymentContext $context
	 * @param PaymentResult  $result
	 *
	 * @throws ApiErrorException
	 */
	public function update_redirect_url( PaymentContext $context, PaymentResult $result ) {
		if ( $context->order->get_payment_method() === $this->name && sswps_mode() === 'live' ) {
			/**
			 * @var Gateways\Abstract_Gateway $payment_method
			 */
			$payment_method = $context->get_payment_method_instance();
			$source_id      = $context->order->get_meta( Constants::SOURCE_ID );
			$source         = Gateway::load( sswps_order_mode( $context->order ) )->sources->retrieve( $source_id );
			$redirect       = add_query_arg( [
				'source'        => $source_id,
				'client_secret' => $source->client_secret,
			], $payment_method->get_local_payment_return_url( $context->order ) );
			$result->set_redirect_url( $redirect );
		}
	}
}