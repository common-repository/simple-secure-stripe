<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\CartFlows;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class PaymentsApi {

	public function __construct() {
		add_filter( 'cartflows_offer_supported_payment_gateways', array( $this, 'add_payment_gateways' ) );
		add_filter( 'sswps/force_save_payment_method', array( $this, 'maybe_force_save_payment_method' ), 10, 3 );
		add_filter( 'cartflows_offer_js_localize', array( $this, 'enqueue_scripts' ) );
	}

	public function add_payment_gateways( $supported_gateways ) {
		$ids = array( 'sswps_cc', 'sswps_googlepay', 'sswps_applepay', 'sswps_payment_request' );
		foreach ( $ids as $id ) {
			$supported_gateways[ $id ] = array(
				'path'  => dirname( __FILE__ ) . '/PaymentGateways/BasePaymentGateway.php',
				'class' => '\SimpleSecureWP\CartFlows\Stripe\PaymentGateways\BasePaymentGateway'
			);
		}

		return $supported_gateways;
	}

	/**
	 * @param $bool
	 * @param $order
	 * @param $payment_method
	 *
	 * @return bool
	 */
	public function maybe_force_save_payment_method( bool $bool, \WC_Order $order, Gateways\Abstract_Gateway $payment_method ) {
		// validate that next step is an offer
		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();
		if ( Main::cartflows_pro_enabled() && $checkout_id && $flow_id ) {
			$wcf_step_obj      = wcf_pro_get_step( $checkout_id );
			$next_step_id      = $wcf_step_obj->get_next_step_id();
			$wcf_next_step_obj = wcf_pro_get_step( $next_step_id );
			// todo eventually remove check for Payment\Intent so sources can be supported.
			if ( $next_step_id && $wcf_next_step_obj->is_offer_page() && ! $payment_method->use_saved_source() && $payment_method->payment_object instanceof Payment\Intent ) {
				$bool = true;
			}
		}

		return $bool;
	}

	/**
	 * @param array $localize
	 */
	public function enqueue_scripts( $localize ) {
		if ( in_array( $localize['payment_method'], $this->get_payment_method_ids() ) ) {
			$localize['stripeData'] = array(
				'key'       => sswps_get_publishable_key(),
				'accountId' => sswps_get_account_id(),
				'version'   => Plugin::VERSION,
				'mode'      => sswps_mode(),
				'msg'       => __( 'Processing Order...', 'simple-secure-stripe' ),
				'timeout'   => 3000
			);
			// enqueue cartflows script
			$assets_url = plugin_dir_url( __DIR__ ) . 'dist/';
			wp_enqueue_script( 'sswps-cartflows', $assets_url . 'sswps-cartflows.js', ['jquery', 'wp-api-fetch', 'wp-polyfill'], Plugin::VERSION, true );
		}

		return $localize;
	}

	private function get_payment_method_ids() {
		/**
		 * @since 1.0.0
		 */
		return apply_filters( 'sswps/cartflows_get_payment_method_ids', array(
			'sswps_cc',
			'sswps_applepay',
			'sswps_googlepay',
			'sswps_payment_request'
		) );
	}

}