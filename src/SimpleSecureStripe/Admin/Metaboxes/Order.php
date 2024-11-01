<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin\Metaboxes;

use SimpleSecureWP\SimpleSecureStripe\Admin;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\REST;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;
use WC_Payment_Token;
use WC_Payment_Tokens;
use WP_Post;

/**
 *
 * @package Stripe/Admin
 * @author Simple & Secure WP
 *
 */
class Order {

	/**
	 *
	 * @param string           $post_type
	 * @param WP_Post|WC_Order $post
	 */
	public function add_meta_boxes( $post_type, $post ) {
		// only add meta box if shop_order and Stripe gateway was used.
		if (
			( Utils\FeaturesUtil::is_custom_order_tables_enabled() && $post instanceof WC_Order )
			|| $post_type === 'shop_order'
			|| apply_filters( 'sswps/show_admin_metaboxes', false, $post )
		) {
			add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'pay_order_section' ] );

			$order          = $post instanceof WC_Order ? $post : wc_get_order( $post->ID );
			$payment_method = $order->get_payment_method();
			if ( $payment_method ) {
				$gateways = WC()->payment_gateways()->payment_gateways();
				if ( isset( $gateways[ $payment_method ] ) ) {
					$gateway = WC()->payment_gateways()->payment_gateways()[ $payment_method ];
					if ( $gateway instanceof Gateways\Abstract_Gateway ) {
						add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'charge_data_view' ] );
						add_action( 'woocommerce_admin_order_totals_after_total', [ $this, 'stripe_fee_view' ] );
					}
				}
			}
			$this->enqueue_scripts();
		}
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function charge_data_view( $order ) {
		if ( ( $transaction_id = $order->get_transaction_id() ) ) {
			include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/metaboxes/order-charge-data.php';
		}
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function pay_order_section( $order ) {
		if (
			( $order->get_type() === 'shop_order'
				&& $order->has_status(
					apply_filters( 'sswps/pay_order_statuses', [
						'pending',
						'failed',
						'auto-draft',
					], $order )
				)
			)
			|| apply_filters( 'sswps/show_pay_order_section', false, $order )
		) {
			include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/metaboxes/order-pay.php';
			$payment_methods = [];
			foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
				if ( $gateway instanceof Gateways\Abstract_Gateway ) {
					$payment_methods = array_merge( $payment_methods, WC_Payment_Tokens::get_customer_tokens( $order->get_user_id(), $gateway->id ) );
				}
			}
			wp_enqueue_script( 'sswps-elements', 'https://js.stripe.com/v3/', [], Plugin::VERSION, true );
			wp_localize_script(
				'sswps-elements',
				'sswps_order_pay_params',
				[
					'api_key'         => sswps_get_publishable_key(),
					'payment_methods' => array_map(
						function( $payment_method ) {
							return $payment_method->__toString();
						},
						$payment_methods
					),
					'order_status'    => $order->get_status(),
					'messages'        => [
						'order_status' => __( 'You must create the order before payment can be processed.', 'simple-secure-stripe' ),
					],
				]
			);
			wp_enqueue_script( 'sswps-admin-modals', App::get( Plugin::class )->assets_url( 'js/admin/modals.js' ), [
				'wc-backbone-modal',
				'jquery-blockui',
			], Plugin::VERSION, true );
		}
	}

	public function stripe_fee_view( $order_id ) {
		if ( App::get( Admin\Settings\Advanced::class )->is_active( 'stripe_fee' ) ) {
			$order = wc_get_order( $order_id );
			$fee   = Utils\Misc::display_fee( $order );
			$net   = Utils\Misc::display_net( $order );
			if ( $fee && $net ) {
				?>
				<tr>
					<td class="label sswps-fee"><?php esc_html_e( 'Stripe Fee', 'simple-secure-stripe' ) ?>:</td>
					<td width="1%"></td>
					<td><?php echo wp_kses_post( $fee ); ?></td>
				</tr>
				<tr>
					<td class="label sswps-net"><?php esc_html_e( 'Net payout', 'simple-secure-stripe' ) ?></td>
					<td width="1%"></td>
					<td class="total"><?php echo wp_kses_post( $net ); ?></td>
				</tr>
				<?php
			}
		}
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'sswps-order-metabox', App::get( Plugin::class )->assets_url( 'js/admin/meta-boxes-order.js' ), [
			'jquery',
			'jquery-blockui',
		], \SimpleSecureWP\SimpleSecureStripe\Plugin::VERSION, true );

		wp_localize_script(
			'sswps-order-metabox',
			'sswps_order_metabox_params',
			[
				'_wpnonce' => wp_create_nonce( 'wp_rest' ),
				'routes'   => [
					'charge_view'     => REST\API::get_admin_endpoint( App::get( REST\Order_Actions::class )->rest_uri( 'charge-view' ) ),
					'capture'         => REST\API::get_admin_endpoint( App::get( REST\Order_Actions::class )->rest_uri( 'capture' ) ),
					'void'            => REST\API::get_admin_endpoint( App::get( REST\Order_Actions::class )->rest_uri( 'void' ) ),
					'pay'             => REST\API::get_admin_endpoint( App::get( REST\Order_Actions::class )->rest_uri( 'pay' ) ),
					'payment_methods' => REST\API::get_admin_endpoint( App::get( REST\Order_Actions::class )->rest_uri( 'customer-payment-methods' ) ),
				],
				'messages' => [
					'capture_amount' => __( 'If the capture amount is less than the order total, make sure you edit your order line items to reflect the new capture amount.', 'simple-secure-stripe' ),
				],
			]
		);
	}
}
