<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Upsell;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Upsell\PaymentGateways\BasePaymentGateway;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\AssetsApi;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use WC_Order;

class PaymentGateways {

	private $assets;

	public function __construct( AssetsApi $assets ) {
		$this->assets = $assets;
		new LinkIntegration();
		$this->initialize();
	}

	private function initialize() {
		add_action( 'init', [ $this, 'initialize_gateways' ] );
		add_filter( 'wfocu_wc_get_supported_gateways', [ $this, 'add_supported_gateways' ] );
		add_filter( 'sswps/force_save_payment_method', [ $this, 'maybe_set_save_payment_method' ], 10, 3 );
		add_action( 'sswps/order_payment_complete', [ $this, 'maybe_setup_upsell' ], 10, 2 );
		add_action( 'wfocu_offer_new_order_created_before_complete', [ $this, 'add_new_order_data' ] );
		add_action( 'wfocu_footer_before_print_scripts', [ $this, 'add_scripts' ] );
		add_filter( 'wfocu_localized_data', [ $this, 'add_data' ] );
		add_filter( 'wfocu_subscriptions_get_supported_gateways', [ $this, 'get_subscription_gateways' ] );
		add_action( 'sswps/process_refund_success', [ $this, 'process_refund_success' ] );
		add_action( 'wfocu_subscription_created_for_upsell', [ $this, 'update_subscription_meta' ], 10, 3 );
	}

	public function initialize_gateways() {
		foreach ( $this->get_payment_gateways() as $clazz ) {
			call_user_func( [ $clazz, 'get_instance' ] );
		}
	}

	public function add_supported_gateways( $gateways ) {
		return array_merge( $gateways, $this->get_payment_gateways() );
	}

	public function get_subscription_gateways( $gateways ) {
		return array_merge( $gateways, array_keys( $this->get_payment_gateways() ) );
	}

	private function get_payment_gateways() {
		return [
			'sswps_cc'              => 'SimpleSecureWP\WooFunnels\Stripe\Upsell\PaymentGateways\CreditCardGateway',
			'sswps_googlepay'       => 'SimpleSecureWP\WooFunnels\Stripe\Upsell\PaymentGateways\GooglePayGateway',
			'sswps_applepay'        => 'SimpleSecureWP\WooFunnels\Stripe\Upsell\PaymentGateways\ApplePayGateway',
			'sswps_payment_request' => 'SimpleSecureWP\WooFunnels\Stripe\Upsell\PaymentGateways\PaymentRequestGateway'
		];
	}

	private function is_supported_gateway( $id ) {
		return isset( $this->get_payment_gateways()[ $id ] );
	}

	/**
	 * @param $id
	 *
	 * @return bool|\WFOCU_Gateway|\WFOCU_Gateways
	 */
	public function get_wfocu_payment_gateway( $id ) {
		return WFOCU_Core()->gateways->get_integration( $id );
	}

	/**
	 * @param                            $bool
	 * @param WC_Order                  $order
	 * @param Gateways\Abstract_Gateway $payment_method
	 *
	 * @return bool
	 */
	public function maybe_set_save_payment_method( $bool, WC_Order $order, Gateways\Abstract_Gateway $payment_method ) {
		if ( ! $bool ) {
			$payment_gateway = $this->get_wfocu_payment_gateway( $order->get_payment_method() );
			if ( $payment_gateway && $payment_gateway->should_tokenize() && ! $payment_method->use_saved_source() ) {
				$bool = true;
			}
		}

		return $bool;
	}

	/**
	 * Maybe setup the WooFunnels upsell if the charge has not been captured.
	 *
	 * @param Charge $charge
	 * @param WC_Order      $order
	 */
	public function maybe_setup_upsell( Charge $charge, WC_Order $order ) {
		$payment_method = $order->get_payment_method();
		if ( ! $charge->captured && $this->is_supported_gateway( $payment_method ) ) {
			$payment_gateway = $this->get_wfocu_payment_gateway( $payment_method );
			if ( $payment_gateway && $payment_gateway->should_tokenize() ) {
				WFOCU_Core()->public->maybe_setup_upsell( $order->get_id() );
			}
		}
	}

	public function add_new_order_data( WC_Order $order ) {
		$payment_method = $order->get_payment_method();
		if ( $this->is_supported_gateway( $payment_method ) ) {
			$order->update_meta_data( Constants::MODE, sswps_mode() );
		}
	}

	public function add_data( $data ) {
		$data['stripeData'] = [
			'publishableKey' => sswps_get_publishable_key(),
			'account'        => sswps_get_account_id()
		];

		return $data;
	}

	public function add_scripts() {
		if ( ! \WFOCU_Core()->public->if_is_offer() || WFOCU_Core()->public->if_is_preview() ) {
			return true;
		}
		$order = WFOCU_Core()->data->get_current_order();
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		$payment_method = $order->get_payment_method();

		if ( in_array( $payment_method, array_keys( $this->get_payment_gateways() ) ) ) {
			$this->assets->enqueue_script( 'sswps-funnelkit-upsell', 'dist/sswps-funnelkit-upsell.js' );
			$this->assets->do_script_items( 'sswps-funnelkit-upsell' );
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return void
	 */
	public function process_refund_success( $order ) {
		$funnel_id = $order->get_meta( '_wfocu_funnel_id' );
		if ( $funnel_id && App::get( Settings\Advanced::class )->is_fee_enabled() ) {
			/**
			 * @var BasePaymentGateway $payment_method
			 */
			$payment_method = $this->get_wfocu_payment_gateway( $order->get_payment_method() );
			if ( $payment_method ) {
				$charges = $this->get_charges_for_upsell( $order );
				if ( ! empty( $charges ) ) {
					$payment_method->process_refund_success( $order, $charges );
				}
			}
		}
	}

	private function get_charges_for_upsell( $order ) {
		$charges     = [];
		$session_ids = WFOCU_Core()->track->query_results( array(
			'data'          => array(
				'id' => array(
					'type'     => 'col',
					'function' => '',
					'name'     => 'session_id',
				),
			),
			'where'         => array(
				array(
					'key'      => 'events.order_id',
					'value'    => $order->get_id(),
					'operator' => '=',
				),
			),
			'query_type'    => 'get_results',
			'session_table' => true,
			'nocache'       => true,
		) );
		if ( is_array( $session_ids ) && count( $session_ids ) > 0 ) {
			$session_ids = end( $session_ids );
			if ( isset( $session_ids->session_id ) ) {
				$session_id = $session_ids->session_id;

				$eventsdb  = WFOCU_Core()->track->query_results( array(
					'where'      => array(
						array(
							'key'      => 'events.sess_id',
							'value'    => $session_id,
							'operator' => '=',
						),

					),
					'query_type' => 'get_results',
					'order_by'   => 'events.timestamp',
					'order'      => 'ASC',
					'nocache'    => true,

				) );
				$event_ids = wc_list_pluck( $eventsdb, 'id' );
				$eventmeta = WFOCU_Core()->track->get_meta( $event_ids );
				if ( $eventmeta ) {
					foreach ( $eventmeta as $meta ) {
						if ( $meta['meta_key'] === '_transaction_id' && strpos( $meta['meta_value'], 'ch_' ) !== false ) {
							$charges[] = $meta['meta_value'];
						}
					}
				}
			}
		}

		return $charges;
	}

	/**
	 * @param \WC_Subscription $subscription
	 * @param string           $product_hash
	 * @param \WC_Order        $order
	 *
	 * @return void
	 */
	public function update_subscription_meta( $subscription, $product_hash, $order ) {
		if ( $this->is_supported_gateway( $subscription->get_payment_method() ) ) {
			$subscription->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ) );
			$subscription->update_meta_data( Constants::CUSTOMER_ID, $order->get_meta( Constants::CUSTOMER_ID ) );
			$subscription->save();
		}
	}
}