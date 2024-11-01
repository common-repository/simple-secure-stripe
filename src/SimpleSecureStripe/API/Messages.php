<?php

namespace SimpleSecureWP\SimpleSecureStripe\API;

use SimpleSecureWP\SimpleSecureStripe\Checker;
use WP_Error;

class Messages {
	private $messages;

	/**
	 * @param WP_Error $error
	 */
	public function filter_error_message( $error ) {
		if ( ! $error ) {
			return $error;
		}

		if ( ! Checker::is_frontend_request() ) {
			return $error;
		}

		$data = $error->get_error_data();

		if ( ! $data ) {
			return $error;
		}

		if ( ! isset( $data['code'] ) ) {
			return $error;
		}

		$code = $data['code'];

		if ( isset( $data['param'] ) ) {
			$code = $code . ':' . $data['param'];
		}

		if ( ! $this->has_code( $code ) ) {
			return $error;
		}

		$message = $this->get_messages()[ $code ];
		if ( is_callable( $message ) ) {
			$message = $message( $error, $data );
		}
		$error = new WP_Error( $code, $message, $data );

		return $error;
	}

	private function has_code( $key ) {
		return array_key_exists( $key, $this->get_messages() );
	}

	private function get_messages() {
		if ( ! $this->messages ) {
			$this->messages = [
				'resource_missing:customer'       => static function( $error, $data ) {
					if ( current_user_can( 'manage_woocommerce' ) ) {
						return sprintf( '%s. %s', $error->get_error_message(), __( 'This customer ID does not exist in your Stripe account. To resolve, navigate to the Edit Profile page in the WordPress Admin and delete the user\'s Stripe customer ID.', 'simple-secure-stripe' ) );
					}

					return sprintf( '%s. %s', $error->get_error_message(), __( 'This customer ID does not exist in the merchant\'s Stripe account. Please contact us and we\'ll update your account.', 'simple-secure-stripe' ) );
				},
				'resource_missing:payment_method' => static function( $error, $data ) {
					if ( current_user_can( 'manage_woocommerce' ) ) {
						return sprintf(
							'%s. %s', $error->get_error_message(),
							__(
								'This payment method does not exist in your Stripe account. This usually happens when you change the Stripe account the plugin is connected to. Please choose a different payment method.',
								'simple-secure-stripe'
							)
						);
					}

					return sprintf( '%s. %s', $error->get_error_message(), __( 'The selected payment method is invalid. Please select a different payment method.', 'simple-secure-stripe' ) );
				},
			];
			$this->messages = apply_filters( 'sswps/get_api_error_messages', $this->messages );
		}

		return $this->messages;
	}

}