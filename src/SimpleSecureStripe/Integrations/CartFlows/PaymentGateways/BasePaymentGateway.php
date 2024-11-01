<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\CartFlows\PaymentGateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Customer_Manager;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Integrations\CartFlows\Constants as CartFlowsConstants;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;

/**
 * Class BasePaymentGateway
 *
 * @package SimpleSecureWP\CartFlows\Stripe\PaymentGateways
 */
class BasePaymentGateway {

	protected $name;

	protected $supports_api_refund = true;

	/**
	 * @var Payment\Abstract_Payment
	 */
	protected $payment_client;

	/**
	 * @var Gateways\Abstract_Gateway
	 */
	protected $payment_method;

	private $logger;

	final public function __construct( \Cartflows_Logger $logger ) {
		$this->logger = $logger;
	}

	public static function get_instance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new static( wcf()->logger() );
		}

		return $instance;
	}

	public function is_api_refund() {
		return $this->supports_api_refund;
	}

	public function init_payment_client( $payment_method ) {
		$this->payment_method = WC()->payment_gateways()->payment_gateways()[ $payment_method ];
		$this->payment_client = Payment\Factory::load( 'payment_intent', $this->payment_method, \SimpleSecureWP\SimpleSecureStripe\Gateway::load() );
	}

	/**
	 * @param WC_Order $order
	 * @param array     $product
	 */
	public function process_offer_payment( WC_Order $order, array $product ) {
		$this->init_payment_client( $order->get_payment_method() );

		$this->payment_method->set_payment_method_token( $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ) );

		if ( ( $payment_intent = $order->get_meta( CartFlowsConstants::CARTFLOWS_PAYMENT_INTENT_ID . $product['step_id'] ) ) ) {
			$intent = $this->payment_client->get_gateway()->paymentIntents->retrieve( $payment_intent );
		} else {
			// If customer doesn't exist on order, create a customer ID and attach payment method.
			$customer_id = $order->get_meta( Constants::CUSTOMER_ID );
			if ( ! $customer_id && ! is_user_logged_in() ) {
				$result = $this->create_customer( $order );
				if ( is_wp_error( $result ) ) {
					$this->logger->log( sprintf( 'Error processing cartflows payment. Reason: %s', $result->get_error_message() ) );

					return false;
				}
			}

			$intent = $this->create_payment_intent( $order, $product );
		}

		if ( is_wp_error( $intent ) ) {
			return false;
		}

		$order->update_meta_data( CartFlowsConstants::CARTFLOWS_PAYMENT_INTENT_ID . $product['step_id'], $intent->id );
		$order->save();

		// check if intent needs confirmation
		if ( $intent->status === Constants::REQUIRES_CONFIRMATION ) {
			$intent = $this->payment_client->get_gateway()->paymentIntents->confirm( $intent->id );
			if ( is_wp_error( $intent ) ) {
				return false;
			}
		}

		if ( $intent->status === Constants::REQUIRES_ACTION ) {
			// send json response so Stripe can handle 3DS
			wp_send_json( [
				'status'   => 'success',
				'redirect' => $this->payment_method->get_payment_intent_checkout_url( $intent, $order ),
			] );
		}

		if ( in_array( $intent->status, [ Constants::SUCCEEDED, Constants::REQUIRES_CAPTURE ] ) ) {
			$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $intent->charges->data[0]->id );
			$order->save();

			return true;
		}

		return true;
	}

	/**
	 * @param WC_Order $order
	 * @param array     $product_data
	 */
	private function create_payment_intent( $order, $product_data ) {
		$customer_id = $order->get_customer_id();
		$args        = [
			'amount'               => Utils\Currency::add_number_precision( $product_data['price'], $order->get_currency() ),
			'currency'             => $order->get_currency(),
			'description'          => sprintf( __( '%1$s - Order %2$s - One Time offer', 'simple-secure-stripe' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() ),
			'payment_method'       => $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ),
			'confirmation_method'  => $this->payment_method->get_confirmation_method( $order ),
			'capture_method'       => $this->payment_method->get_option( 'charge_type' ) === 'capture' ? 'automatic' : 'manual',
			'confirm'              => false,
			'payment_method_types' => [ $this->payment_method->get_payment_method_type() ],
			'customer'             => $customer_id ? sswps_get_customer_id( $customer_id ) : $order->get_meta( Constants::CUSTOMER_ID ),
		];
		$this->payment_client->add_order_shipping_address( $args, $order );
		$this->payment_client->add_order_metadata( $args, $order );

		$args = apply_filters( 'sswps/payment_intent_args', $args, $order, $this->payment_client );

		return $this->payment_client->get_gateway()->paymentIntents->create( $args );
	}

	/**
	 * @param WC_Order $order
	 * @param array     $offer_data
	 */
	public function process_offer_refund( WC_Order $order, array $offer_data ) {
		$this->init_payment_client( $order->get_payment_method() );
		$mode   = sswps_order_mode( $order );
		$refund = $this->payment_client->get_gateway()->refunds->mode( $mode )->create( [
			'charge'   => $offer_data['transaction_id'],
			'amount'   => Utils\Currency::add_number_precision( $offer_data['refund_amount'], $order->get_currency() ),
			'metadata' => [
				'order_id'    => $order->get_id(),
				'created_via' => 'woocommerce',
			],
		] );
		if ( is_wp_error( $refund ) ) {
			return false;
		}

		return $refund->id;
	}

	/**
	 * @param WC_Order $order
	 */
	private function create_customer( WC_Order $order ) {
		$result = App::get( Customer_Manager::class )->create_customer( WC()->customer );
		if ( ! is_wp_error( $result ) ) {
			$order->update_meta_data( Constants::CUSTOMER_ID, $result->id );
			$order->save();

			// save the payment method.
			$result = $this->payment_method->create_payment_method( $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ), $result->id );
		}

		return $result;
	}
}