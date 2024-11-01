<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use WC_Order;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class Konbini extends Abstract_Local_Payment {

	protected string $payment_method_type = 'konbini';

	public bool $synchronous = false;

	public bool $is_voucher_payment = true;

	use Payment\Traits\Local_Intent {
		get_payment_intent_checkout_params as get_payment_intent_checkout_params_v1;
	}

	public function __construct() {
		$this->local_payment_type = 'konbini';
		$this->currencies         = [ 'JPY' ];
		$this->countries          = $this->limited_countries = [ 'JP' ];
		$this->id                 = 'sswps_konbini';
		$this->tab_title          = __( 'Konbini', 'simple-secure-stripe' );
		$this->method_title       = __( 'Konbini', 'simple-secure-stripe' );
		$this->method_description = __( 'Konbini gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/konbini.svg' );
		parent::__construct();
	}

	public function get_local_payment_settings() {
		return array_merge( parent::get_local_payment_settings(), [
			'expiration_days' => [
				'title'       => __( 'Expiration Days', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => '3',
				'options'     => array_reduce( range( 0, 60 ), function( $carry, $item ) {
					$carry[ $item ] = sprintf( _n( '%s day', '%s days', $item, 'simple-secure-stripe' ), $item );

					return $carry;
				}, [] ),
				'desc_tip'    => true,
				'description' => __( 'The number of days before the Boleto voucher expires.', 'simple-secure-stripe' ),
			],
			'email_link'      => [
				'title'       => __( 'Voucher Link In Email', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'desc_tip'    => true,
				'description' => __( 'If enabled, the voucher link will be included in the order on-hold email sent to the customer.', 'simple-secure-stripe' ),
			],
		] );
	}

	public function add_stripe_order_args( &$args, $order ) {
		$args['payment_method_options'] = [
			'konbini' => [
				'confirmation_number' => $this->sanitize_confirmation_number( $order->get_billing_phone() ),
				'expires_after_days'  => $this->get_option( 'expiration_days', 3 ),
			],
		];
	}

	/**
	 * @since 1.0.0
	 *
	 * @param $value
	 *
	 * @return array|string|string[]|null
	 */
	private function sanitize_confirmation_number( $value ) {
		return preg_replace( '/[^\d]/', '', $value );
	}

	/**
	 * @param WC_Order $order
	 */
	public function process_voucher_order_status( WC_Order $order ) {
		if ( $this->is_active( 'email_link' ) ) {
			add_filter( 'woocommerce_email_additional_content_customer_on_hold_order', [ $this, 'add_customer_voucher_email_content' ], 10, 2 );
		}
		$order->update_status( 'on-hold' );
	}

	/**
	 * @param string    $content
	 * @param \WC_Order $order
	 */
	public function add_customer_voucher_email_content( $content, $order ) {
		if ( $order && $order->get_payment_method() === $this->id ) {
			if ( ( $intent_id = $order->get_meta( Constants::PAYMENT_INTENT_ID ) ) ) {
				$payment_intent = $this->gateway->mode( $order )->paymentIntents->retrieve( $intent_id );
				$link = isset( $payment_intent->next_action->konbini_display_details->hosted_voucher_url ) ? $payment_intent->next_action->konbini_display_details->hosted_voucher_url : null;
				if ( $link ) {
					/* translators: 1 - open link tag, 2 - close link tag */
					$content .= '<p>' . sprintf( __( 'Please click %1$shere%2$s to view your Konbini voucher.', 'simple-secure-stripe' ), '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">', '</a>' ) . '</p>';
				}
			}
		}

		return $content;
	}

	/**
	 * @param WC_Order|null $order
	 *
	 * @return string
	 */
	public function get_return_url( $order = null ) {
		if ( $this->processing_payment && $order ) {
			return add_query_arg( [
				Constants::VOUCHER_PAYMENT => $this->id,
				'order-id'                 => $order->get_id(),
				'order-key'                => $order->get_order_key(),
			], wc_get_checkout_url() );
		}

		return parent::get_return_url( $order );
	}

	protected function get_payment_intent_checkout_params( $intent, $order, $type ) {
		$params                        = $this->get_payment_intent_checkout_params_v1( $intent, $order, $type );
		$params['billing_phone']       = $this->sanitize_confirmation_number( $order->get_billing_phone() );
		$params['confirmation_number'] = rand( 10000000000, 99999999999 );

		return $params;
	}

	public function get_local_payment_description() {
		$this->local_payment_description = sswps_get_template_html( 'checkout/konbini-instructions.php', [ 'button_text' => $this->order_button_text ] );

		return parent::get_local_payment_description();
	}

	public function validate_local_payment_available( $currency, $billing_country, $total ) {
		return 120 <= $total && $total <= 300000;
	}

}
