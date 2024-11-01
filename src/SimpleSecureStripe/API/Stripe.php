<?php

namespace SimpleSecureWP\SimpleSecureStripe\API;

use InvalidArgumentException;
use ReflectionMethod;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AbstractService;
use SimpleSecureWP\SimpleSecureStripe\Stripe\StripeClient;
use WP_Error;

/**
 * Used as a wrapper for API requests to Stripe.
 * Allows method chaining so things like mode can
 * be set intuitively.
 *
 * @since   1.0.0
 * @author  Simple & Secure WP
 * @package Stripe/Classes
 */
class Stripe {

	/**
	 *
	 * @var StripeClient
	 */
	private $client;

	/**
	 *
	 * @var string
	 */
	private $property;

	/**
	 *
	 * @var AbstractService
	 */
	private $service;

	/**
	 *
	 * @var Gateway
	 */
	private $gateway;

	/**
	 *
	 * @var string
	 */
	private $mode = '';

	/**
	 *
	 * @param Gateway      $gateway
	 * @param StripeClient $client
	 * @param string       $property
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $gateway, $client, $property ) {
		$this->client   = $client;
		$this->property = $property;
		$this->gateway  = $gateway;

		$service = $this->client->__get( $property );

		if ( ! $service ) {
			throw new InvalidArgumentException( sprintf( 'Property %s is not a valid entry', $property ) );
		}

		$this->service = $service;
	}

	/**
	 * Wrapper for Stripe API operations.
	 * This way, all exceptions can be caught gracefully.
	 *
	 * @param string $method
	 * @param array  $args
	 *
	 * @throws InvalidArgumentException
	 */
	public function __call( $method, $args ) {
		if ( ! method_exists( $this->service, $method ) ) {
			throw new InvalidArgumentException( sprintf( 'Method %s does not exist for class %s.', $method, get_class( $this->service ) ) );
		}
		$args = $this->parse_args( $args, $method );
		try {
			/**
			 * Filters arguments before they are sent to the service for an API request.
			 *
			 * @since 1.0.0
			 *
			 * @param array  $args     The array of arguments that will be passed to the service method.
			 * @param string $property The name of the service being called.
			 * @param string $method   The method of the service. Ex: create, delete, retrieve
			 */
			$args = apply_filters( 'sswps/api_request_args', $args, $this->property, $method );

			return $this->service->{$method}( ...$args );
		} catch ( \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException $e ) {
			return $this->gateway->get_wp_error( $e, $this->property . '-error' );
		} catch ( \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\UnexpectedValueException|\SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\InvalidArgumentException $e ) {
			return new WP_Error( 'stripe-error', $e->getMessage(), $e );
		}
	}

	/**
	 *
	 * @param string $mode
	 *
	 * @return $this
	 */
	public function mode( $mode ) {
		$this->mode = $mode;

		return $this;
	}

	/**
	 * Given an array of arguments, add method defaults to the array of args based on their existance.
	 *
	 * @param array  $args
	 * @param string $method
	 */
	private function parse_args( $args, $method ) {
		$reflection_method = new ReflectionMethod( get_class( $this->service ), $method );
		$num_args          = $reflection_method->getNumberOfParameters();

		// loop through each
		foreach ( $reflection_method->getParameters() as $parameter ) {
			if ( ! isset( $args[ $parameter->getPosition() ] ) && $parameter->isOptional() ) {
				$args[ $parameter->getPosition() ] = $parameter->getDefaultValue();
			}
		}
		// merge options
		$args[ $num_args - 1 ] = wp_parse_args( $args[ $num_args - 1 ], $this->gateway->get_api_options( $this->mode ) );

		return $args;
	}

}
