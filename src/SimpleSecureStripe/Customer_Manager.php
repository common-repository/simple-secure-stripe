<?php

namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethod;
use SimpleSecureWP\SimpleSecureStripe\Tokens;
use WC_Customer;
use WP_Error;

/**
 * Class that manages customer creation and custom updates.
 *
 * @since 1.0.0
 * @package Stripe/Classes
 * @author Simple & Secure WP
 *
 */
class Customer_Manager {

	public function __construct() {
		add_action( 'woocommerce_checkout_update_customer', array( $this, 'checkout_update_customer' ), 10, 2 );
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
	}

	/**
	 * Return whether or not the plugin should create a Stripe customer if the user has an account with the store.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function should_create_when_account_exists() {
		return App::get( Settings\Advanced::class )->get_option( 'customer_creation' ) === 'account_creation';
	}

	/**
	 * Returns true if the plugin should create a Stripe customer when the payment is being processed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function should_create_when_payment() {
		return App::get( Settings\Advanced::class )->get_option( 'customer_creation' ) === 'payment';
	}

	/**
	 *
	 * @param WC_Customer $customer
	 * @param array       $data
	 */
	public function checkout_update_customer( $customer, $data ) {
		if ( $this->user_has_id( $customer ) ) {
			if ( $this->should_update_customer( $customer ) ) {
				$result = $this->update_customer( $customer );
				if ( is_wp_error( $result ) ) {
					// add info to log. This error isn't added to wc_add_notice because we don't want a user update to
					// interfere with payment being processed.
					/* translators: error message provided by Stripe */
					sswps_log_error( sprintf( __( 'Error saving customer. Reason: %s', 'simple-secure-stripe' ), $result->get_error_message() ) );
				}
			}
		} else {
			// create the customer
			if ( isset( $data['payment_method'] ) && strpos( $data['payment_method'], 'sswps_' ) !== false ) {
				if ( WC()->session ) {
					$customer_id = WC()->session->get( Constants::STRIPE_CUSTOMER_ID );
					if ( $customer_id ) {
						// customer ID is no longer needed since it's being added to the user.
						unset( WC()->session->{Constants::STRIPE_CUSTOMER_ID} );

						return sswps_save_customer( $customer_id, $customer->get_id() );
					}
				}
				// Create the customer since this is a Stripe payment.
				$response = $this->create_customer( $customer );
				if ( ! is_wp_error( $response ) ) {
					sswps_save_customer( $response->id, $customer->get_id() );
				} else {
					/* translators: error message provided by Stripe */
					wc_add_notice( sprintf( __( 'Error saving customer. Reason: %s', 'simple-secure-stripe' ), $response->get_error_message() ), 'error' );
				}
			}
		}
	}

	/**
	 *
	 * @param WC_Customer $customer
	 * @param string|null $mode
	 *
	 * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Customer|WP_Error
	 */
	public function create_customer( $customer, $mode = null ) {
		return Gateway::load( $mode )->customers->create( apply_filters( 'sswps/customer_args', $this->get_customer_args( $customer ) ) );
	}

	/**
	 *
	 * @param WC_Customer $customer
	 */
	public function update_customer( $customer ) {
		return Gateway::load()->customers->update(
			sswps_get_customer_id( $customer->get_id() ),
			apply_filters( 'sswps/update_customer_args', $this->get_customer_args( $customer, 'update' ) )
		);
	}

	/**
	 * Check if the Stripe customer has been created for the WC user.
	 * If there is no Stripe customer then
	 * create one and save it to the WC user.
	 */
	public function wp_loaded() {
		$customer = WC()->customer;
		if ( $customer && $customer->get_id() ) {
			if ( ! $this->user_has_id( $customer ) ) {
				if ( $this->should_create_when_account_exists() ) {
					if ( WC()->session ) {
						$customer_id = WC()->session->get( Constants::STRIPE_CUSTOMER_ID );
						if ( $customer_id ) {
							// customer ID is no longer needed since it's being added to the user.
							unset( WC()->session->{Constants::STRIPE_CUSTOMER_ID} );

							return sswps_save_customer( $customer_id, $customer->get_id() );
						}
					}
					$response = $this->create_customer( $customer );
					if ( ! is_wp_error( $response ) ) {
						sswps_save_customer( $response->id, $customer->get_id() );
					}
				}
			}
		}
	}

	/**
	 *
	 * @param WC_Customer $customer
	 */
	private function user_has_id( $customer ) {
		$id = sswps_get_customer_id( $customer->get_id() );

		// this customer may have an ID from another plugin. Check that too.
		if ( empty( $id ) ) {
			$id = get_user_option( Constants::STRIPE_CUSTOMER_ID, $customer->get_id() );
			if ( ! empty( $id ) && is_string( $id ) ) {
				// validate that this customer exists in the Stripe gateway
				$response = Gateway::load()->customers->retrieve( $id );
				// id exists so save customer ID to this plugin's format.
				sswps_save_customer( $id, $customer->get_id() );

				// load this customer's payment methods
				$this->sync_payment_methods( $id, $customer->get_id() );
			}
		}

		return ! empty( $id );
	}

	/**
	 * Syncs the WC database payment methods with the payment methods stored in Stripe.
	 *
	 * @param string $customer_id
	 * @param int    $user_id
	 *
	 * @since 1.0.0
	 */
	public static function sync_payment_methods( $customer_id, $user_id, $mode = '' ) {
		$payment_methods = Gateway::load( $mode )->paymentMethods->all( array(
			'customer' => $customer_id,
			'type'     => 'card',
		) );

		foreach ( $payment_methods->data as $payment_method ) {
			/**
			 * @var PaymentMethod $payment_method
			 */
			if ( ! Tokens\Abstract_Token::token_exists( $payment_method->id, $user_id ) ) {
				$payment_gateways = WC()->payment_gateways()->payment_gateways();
				$gateway_id       = 'sswps_cc';
				if ( isset( $payment_method->card->wallet->type ) ) {
					switch ( $payment_method->card->wallet->type ) {
						case 'google_pay':
							$gateway_id = 'sswps_googlepay';
							break;
						case 'apple_pay':
							$gateway_id = 'sswps_applepay';
							break;
					}
				}
				/**
				 *
				 * @var Gateways\CC $payment_gateway
				 */
				$payment_gateway = $payment_gateways[ $gateway_id ];
				$token = $payment_gateway->get_payment_token( $payment_method->id, $payment_method );
				$token->set_environment( $payment_method->livemode ? 'live' : 'test' );
				$token->set_user_id( $user_id );
				$token->set_customer_id( $customer_id );
				$token->save();
			}
		}
	}

	/**
	 * Returns true if the customer should be updated in Stripe.
	 *
	 * @param WC_Customer $customer
	 *
	 * @return bool
	 */
	private function should_update_customer( $customer ) {
		$changes = $customer->get_changes();
		if ( ! empty( $changes['billing'] ) ) {
			return ! empty( array_intersect_key( $changes['billing'], array_flip( $this->get_attribute_keys() ) ) );
		}

		return false;
	}

	/**
	 * Returns an array of user attributes.
	 *
	 * @return array
	 */
	private function get_attribute_keys() {
		return apply_filters( 'sswps/get_customer_attribute_keys', array(
			'first_name',
			'last_name',
			'email',
			'address_1',
			'address_2',
			'country',
			'state',
			'postcode'
		) );
	}

	/**
	 * Return an array of args used to create or update a customer.
	 *
	 * @param WC_Customer $customer
	 * @param string      $context
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_customer_args( $customer, $context = 'create' ) {
		$args = array(
			'email'   => $customer->get_email(),
			'name'    => sprintf( '%s %s', $customer->get_first_name(), $customer->get_last_name() ),
			'phone'   => $customer->get_billing_phone(),
			'address' => array(
				'city'        => $customer->get_billing_city(),
				'country'     => $customer->get_billing_country(),
				'line1'       => $customer->get_billing_address_1(),
				'line2'       => $customer->get_billing_address_2(),
				'postal_code' => $customer->get_billing_postcode(),
				'state'       => $customer->get_billing_state()
			)
		);
		if ( 'create' === $context ) {
			$args['metadata'] = array(
				'user_id'  => $customer->get_id(),
				'username' => $customer->get_username(),
				'website'  => get_site_url(),
			);
		}

		return $args;
	}

}
