<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use WC_Order;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class Boleto extends Abstract_Local_Payment {

	protected string $payment_method_type = 'boleto';

	public bool $synchronous = false;

	public bool $is_voucher_payment = true;

	use Payment\Traits\Local_Intent;

	public function __construct() {
		$this->local_payment_type = 'boleto';
		$this->currencies         = [ 'BRL' ];
		$this->countries          = $this->limited_countries = [ 'BR' ];
		$this->id                 = 'sswps_boleto';
		$this->tab_title          = __( 'Boleto', 'simple-secure-stripe' );
		$this->method_title       = __( 'Boleto', 'simple-secure-stripe' );
		$this->method_description = __( 'Boleto gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/boleto.svg' );
		parent::__construct();
		$this->template_name = 'boleto.php';
	}

	public function get_local_payment_settings() {
		return array_merge( parent::get_local_payment_settings(), [
			'expiration_days' => [
				'title'       => __( 'Expiration Days', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => '3',
				'options'     => array_reduce( range( 0, 14 ), function( $carry, $item ) {
					/* translators: %s - number of days for expiration */
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

	public function validate_fields() {
		$regex = '/^(\w{3}\.){2}\w{3}-\w{2}$|^(\w{11}|\w{14})$|^\w{2}\.\w{3}\.\w{3}\/\w{4}-\w{2}$/';
		$tax_id = Request::get_sanitized_var( 'sswps_boleto_tax_id' );
		if ( empty( $tax_id ) || ! preg_match_all( $regex, $tax_id ) ) {
			wc_add_notice( __( 'Please enter a valid CPF / CNPJ', 'simple-secure-stripe' ), 'error' );
			return false;
		}

		return true;
	}

	public function add_stripe_order_args( &$args, $order ) {
		$args['payment_method_options'] = [
			'boleto' => [
				'expires_after_days' => $this->get_option( 'expiration_days', 3 ),
			],
		];
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
	 * @param WC_Order $order
	 */
	public function add_customer_voucher_email_content( $content, $order ) {
		if ( $order && $order->get_payment_method() === $this->id ) {
			if ( ( $intent_id = $order->get_meta( Constants::PAYMENT_INTENT_ID ) ) ) {
				$payment_intent = $this->gateway->mode( $order )->paymentIntents->retrieve( $intent_id );
				$link = isset( $payment_intent->next_action->boleto_display_details->hosted_voucher_url ) ? $payment_intent->next_action->boleto_display_details->hosted_voucher_url : null;
				if ( $link ) {
					/* translators: 1 - open link, 2 - close link */
					$content .= '<p>' . sprintf( __( 'Please click %1$shere%2$s to view your Boleto voucher.', 'simple-secure-stripe' ), '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">', '</a>' ) . '</p>';
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

}