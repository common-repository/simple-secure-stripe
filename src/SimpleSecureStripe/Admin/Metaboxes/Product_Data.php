<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin\Metaboxes;

use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Product_Gateway_Option;

class Product_Data {

	private $gateways = [];

	private $options = [];

	public function product_data_tabs( $tabs ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$tabs['stripe'] = array(
				'label'    => __( 'Stripe Settings', 'simple-secure-stripe' ),
				'target'   => 'sswps_product_data',
				'class'    => array( 'hide_if_external' ),
				'priority' => 100,
			);
		}

		return $tabs;
	}

	public function output_panel() {
		global $product_object;

		$this->init_gateways( $product_object );
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$gateways = $this->get_payment_gateways();
			include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/metaboxes/product-data.php';
		}
	}

	private function init_gateways( $product ) {
		$order = $product->get_meta( Constants::PRODUCT_GATEWAY_ORDER );
		$order = ! $order ? [] : $order;
		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( $gateway->supports( 'sswps_product_checkout' ) ) {
				if ( isset( $order[ $gateway->id ] ) ) {
					$this->gateways[ $order[ $gateway->id ] ] = $gateway;
				} else {
					$this->gateways[] = $gateway;
				}
				$this->options[ $gateway->id ] = new Product_Gateway_Option( $product, $gateway );
			}
		}
		ksort( $this->gateways );
	}

	public function get_product_option( $gateway_id ) {
		return $this->options[ $gateway_id ];
	}

	private function get_payment_gateways() {
		$gateways = [];
		foreach ( $this->gateways as $gateway ) {
			$gateways[ $gateway->id ] = $gateway;
		}

		return $gateways;
	}

	/**
	 *
	 * @param \WC_Product $product
	 */
	public function save( $product ) {
		$update_product = Request::get_sanitized_var( 'sswps_update_product' );
		// only update the settings if something has been changed.
		if ( empty( $update_product ) ) {
			return;
		}
		$loop  = 0;
		$order = [];
		$this->init_gateways( $product );
		$payment_gateways = $this->get_payment_gateways();
		$gateway_order    = Request::get_sanitized_var( 'sswps_gateway_order', [] );
		$capture_type     = Request::get_sanitized_var( 'sswps_capture_type', [] );
		$button_position  = Request::get_sanitized_var( Constants::BUTTON_POSITION );

		if ( ! empty( $gateway_order ) ) {
			foreach ( $gateway_order as $i => $gateway ) {
				$order[ $gateway ] = $loop;
				if ( ! empty( $capture_type ) ) {
					$this->get_product_option( $gateway )->set_option( 'charge_type', wc_clean( $capture_type[ $i ] ) );
					$this->get_product_option( $gateway )->save();
				}
				$loop ++;
			}
		}
		if ( ! empty( $button_position ) ) {
			$product->update_meta_data( Constants::BUTTON_POSITION, wc_clean( $button_position ) );
		}
		$product->update_meta_data( Constants::PRODUCT_GATEWAY_ORDER, $order );
	}

}
