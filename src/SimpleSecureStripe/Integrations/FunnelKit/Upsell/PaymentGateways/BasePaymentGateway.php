<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Upsell\PaymentGateways;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Customer_Manager;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use WC_Customer;
use WC_Order;
use WFOCU_Logger;

/**
 * Class BaseGateway
 *
 * @package SimpleSecureWP\WooFunnels\Stripe\Upsell\PaymentGateways
 */
class BasePaymentGateway extends \WFOCU_Gateway {

	public $refund_supported = true;

	/**
	 * @var WFOCU_Logger
	 */
	private $logger;

	private $client;

	private $payment;

	final public function __construct( Gateway $client, Payment\Abstract_Payment $payment, WFOCU_Logger $logger ) {
		parent::__construct();
		$this->client  = $client;
		$this->payment = $payment;
		$this->logger  = $logger;
		$this->initialize();
	}

	public static function get_instance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new static( Gateway::load(), new Payment\Intent( null, null ), WFOCU_Core()->log );
		}

		return $instance;
	}

	public function initialize() {
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return bool|void
	 */
	public function process_charge( $order ) {
		$this->handle_client_error();
		$this->initialize_actions();
		try {
			$intent = Request::get_sanitized_var( '_payment_intent' );

			// check if payment intent exists.
			if ( $intent ) {
				$intent = $this->client->paymentIntents->retrieve( $intent );
			} else {
				// If there is no customer ID, create one
				$user_id     = $order->get_customer_id();
				$customer_id = $order->get_meta( Constants::CUSTOMER_ID );
				if ( ! $customer_id && ! $user_id ) {
					$this->create_stripe_customer( WC()->customer, $order );
				} elseif ( $user_id ) {
					$order->update_meta_data( Constants::CUSTOMER_ID, sswps_get_customer_id( $user_id ) );
					$order->save();
				}
				// create the payment intent
				$intent = $this->create_payment_intent( $order );
			}
			if ( $intent->status === Constants::REQUIRES_PAYMENT_METHOD ) {
				$intent = $this->client->paymentIntents->update( $intent->id, [ 'payment_method' => $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ) ] );
			}
			if ( $intent->status === Constants::REQUIRES_CONFIRMATION ) {
				$intent = $this->client->paymentIntents->confirm( $intent->id );
			}

			if ( $intent->status === Constants::REQUIRES_ACTION ) {
				// send back response
				return \wp_send_json( [
					'success' => true,
					'data'    => [ 'redirect_url' => $this->get_payment_intent_redirect_url( $intent ) ]
				] );
			}
			$charge = $intent->charges->data[0];
			WFOCU_Core()->data->set( '_transaction_id', $charge->id );
			$this->update_payment_balance( $charge, $order );

			return $this->handle_result( true );
		} catch ( Exception $e ) {
			/* translators: %s: error message */
			$this->logger->log( sprintf( __( 'Error processing upsell. Reason: %s', 'simple-secure-stripe' ), $e->getMessage() ) );
			throw new \WFOCU_Payment_Gateway_Exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function process_refund_offer( $order ) {
		$charge = Request::get_sanitized_var( 'txn_id' );
		$charge = $charge === null ? false : wc_clean( $charge );
		$amount = Request::get_sanitized_var( 'amt' );
		$amount = $amount === null ? false : round( $amount, 2 );
		$mode   = sswps_order_mode( $order );
		$result = $this->client->mode( $mode )->refunds->create( [
			'charge'   => $charge,
			'amount'   => Utils\Currency::add_number_precision( $amount, $order->get_currency() ),
			'metadata' => array(
				'order_id'    => $order->get_id(),
				'created_via' => 'woocommerce'
			),
			'expand'   => App::get( Settings\Advanced::class )->is_fee_enabled() ? [ 'charge.balance_transaction', 'charge.refunds.data.balance_transaction' ] : []
		] );

		/* translators: %s: transaction ID */
		$this->logger->log( sprintf( __( 'Charge %s refunded n Stripe.', 'simple-secure-stripe' ), $charge ) );
		if ( isset( $result->charge->balance_transaction ) ) {
			$pb              = Utils\Misc::get_payment_balance( $order );
			$payment_balance = Utils\Misc::add_balance_transaction_to_order( $result->charge, $order );
			$pb->net         -= $payment_balance->refunded;
			$pb->save();
		}

		return (bool) $result->id;
	}

	public function get_transaction_link( $transaction_id, $order_id ) {
		$order = wc_get_order( $order_id );
		$mode  = sswps_order_mode( $order );
		$url   = 'https://dashboard.stripe.com/payments/%s';
		if ( $mode === 'test' ) {
			$url = 'https://dashboard.stripe.com/test/payments/%s';
		}

		return sprintf( $url, $transaction_id );
	}

	public function handle_client_error() {
		$package = \WFOCU_Core()->data->get( '_upsell_package' );
		if ( $package && isset( $package['_client_error'] ) ) {
			/* translators: %s: error message */
			$this->logger->log( sprintf( __( 'Stripe client error: %s', 'simple-secure-stripe' ), sanitize_text_field( $package['_client_error'] ) ) );
		}
	}

	/**
	 * @param WC_Customer $customer
	 *
	 * @throws Exception
	 */
	private function create_stripe_customer( WC_Customer $customer, WC_Order $order ) {
		$result = App::get( Customer_Manager::class )->create_customer( $customer );
		if ( ! is_wp_error( $result ) ) {
			$order->update_meta_data( Constants::CUSTOMER_ID, $result->id );
			$order->save();

			// now that we have a customer created, attach the payment method
			$payment_method = $order->get_meta( Constants::PAYMENT_METHOD_TOKEN );

			return $this->client->paymentMethods->attach( $payment_method, [ 'customer' => $result->id ] );
		}

		throw new Exception( $result->get_error_message() );
	}

	private function create_payment_intent( WC_Order $order ) {
		$package        = WFOCU_Core()->data->get( '_upsell_package' );
		$payment_method = $this->get_wc_gateway();
		$params         = array(
			'amount'               => Utils\Currency::add_number_precision( $package['total'], $order->get_currency() ),
			/* translators: 1: site name, 2: order number. */
			'description'          => sprintf( __( '%1$s - Order %2$s - One Time offer', 'simple-secure-stripe' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() ),
			'payment_method'       => $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ),
			'confirmation_method'  => 'automatic', //$payment_method->get_confirmation_method( $order ),
			'capture_method'       => $payment_method->get_option( 'charge_type' ) === 'capture' ? 'automatic' : 'manual',
			'confirm'              => false,
			'payment_method_types' => [ $payment_method->get_payment_method_type() ], /* @phpstan-ignore-line */
			'customer'             => $order->get_meta( Constants::CUSTOMER_ID )
		);
		$this->payment->add_order_metadata( $params, $order );
		$this->payment->add_order_currency( $params, $order );
		$this->payment->add_order_shipping_address( $params, $order );

		$params = apply_filters( 'sswps/funnelkit_upsell_create_payment_intent', $params, $order, $this->client );

		$result = $this->client->mode( $order )->paymentIntents->create( $params );

		return $result;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function has_token( $order ) {
		$payment_token = $order->get_meta( Constants::PAYMENT_METHOD_TOKEN );

		return ! empty( $payment_token );
	}

	/**
	 * @param PaymentIntent $intent
	 *
	 * @return string
	 */
	protected function get_payment_intent_redirect_url( PaymentIntent $intent ) {
		return sprintf( '#response=%s', rawurlencode( base64_encode(
			wp_json_encode( [
					'payment_intent' => $intent->id,
					'client_secret'  => $intent->client_secret
				]
			) ) ) );
	}

	public function initialize_actions() {
		if ( App::get( Settings\Advanced::class ) && App::get( Settings\Advanced::class )->is_fee_enabled() ) {
			add_filter( 'sswps/api_request_args', array( $this, 'add_balance_transaction' ), 10, 3 );
		}
	}

	public function add_balance_transaction( $args, $property, $method ) {
		if ( $property === 'paymentIntents' ) {
			if ( \in_array( $method, array( 'create', 'confirm', 'update', 'retrieve' ) ) ) {
				$data = null;
				switch ( $method ) {
					case 'create':
						$data = &$args[0];
						break;
					case 'update':
					case 'confirm':
					case 'retrieve':
						$data = &$args[1];
						break;
				}
				$data             = ! \is_array( $data ) ? array() : $data;
				$data['expand']   = ! isset( $data['expand'] ) ? array() : $data['expand'];
				$data['expand'][] = 'charges.data.balance_transaction';
			}
		}

		return $args;
	}

	/**
	 * @param Charge $charge
	 * @param WC_Order      $order
	 *
	 * @return void
	 */
	public function update_payment_balance( $charge, $order ) {
		if ( $charge && isset( $charge->balance_transaction ) && is_object( $charge->balance_transaction ) ) {
			$order_behavior = WFOCU_Core()->funnels->get_funnel_option( 'order_behavior' );
			$use_main_order = $order_behavior === 'batching';
			// If this is a merged order, update the existing payment balance
			if ( $use_main_order ) {
				$payment_balance       = Utils\Misc::create_payment_balance_from_balance_transaction( $charge->balance_transaction, $order );
				$payment_balance2      = Utils\Misc::get_payment_balance( $order );
				$payment_balance2->net += $payment_balance->net;
				$payment_balance2->fee += $payment_balance->fee;
				$payment_balance2->save();
			} else {
				// This code is called if a new order is created for the Upsell
				add_action( 'wfocu_offer_new_order_created_' . $this->get_key(), function ( $order ) use ( $charge ) {
					$payment_balance = Utils\Misc::create_payment_balance_from_balance_transaction( $charge->balance_transaction, $order );
					$payment_balance->save();
				} );
			}
		}
	}

	/**
	 * @param WC_Order $order
	 * @param array     $charge_ids
	 *
	 * @return void
	 * @throws ApiErrorException
	 */
	public function process_refund_success( $order, $charge_ids ) {
		$pb = Utils\Misc::get_payment_balance( $order );
		foreach ( $charge_ids as $id ) {
			$charge          = $this->client->mode( $order )->charges->retrieve( $id, [ 'expand' => [ 'balance_transaction' ] ] );
			$payment_balance = Utils\Misc::add_balance_transaction_to_order( $charge, $order );
			$pb->net         += $payment_balance->net;
			$pb->fee         += $payment_balance->fee;
		}
		$pb->save();
	}


}