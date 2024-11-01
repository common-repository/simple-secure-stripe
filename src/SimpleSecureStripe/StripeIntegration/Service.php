<?php

namespace SimpleSecureWP\SimpleSecureStripe\StripeIntegration;

use InvalidArgumentException;
use ReflectionMethod;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\InvalidArgumentException as StripeInvalidArgumentException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\UnexpectedValueException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AbstractService;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Util\ApiVersion;
use WP_Error;

class Service {
	/**
	 * StripeIntegration client.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Stripe service.
	 *
	 * @var AbstractService
	 */
	protected $service;

	/**
	 * Service string.
	 *
	 * @var string
	 */
	protected $service_string;

	/**
	 * Stripe mode.
	 *
	 * @var string
	 */
	protected $mode;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Client $client  The StripeIntegration client.
	 * @param string $service The Stripe service to use.
	 * @param string $mode    The Stripe mode to use.
	 */
	public function __construct( Client $client, string $service, string $mode ) {
		$this->client         = $client;
		$this->service_string = $service;
		$this->service        = $this->client->get( $mode )->__get( $service );
		$this->mode           = $mode;
	}

	/**
	 * Wrap Stripe API operations and catch errors.
	 *
	 * @since 1.0.0
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
			$args = apply_filters( 'sswps/api_request_args', $args, $this->service_string, $method );

			return $this->service->{$method}( ...$args );
		} catch ( ApiErrorException $e ) {
			return $this->client->get_wp_error( $e, $this->service_string . '-error' );
		} catch ( UnexpectedValueException|StripeInvalidArgumentException $e ) {
			return new WP_Error( 'sswps-stripe-error', $e->getMessage(), $e );
		}
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

		$api_options = [
			'api_key' => $this->client->get_secret_key( $this->mode ),
		];

		/**
		 * Filters the API options.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $api_options The API options.
		 * @param Client  $client      The StripeIntegration client.
		 * @param string  $mode        The Stripe mode.
		 * @param Service $service     The StripeIntegration service.
		 */
		$api_options = apply_filters( 'sswps/api_options', $api_options, $this->client, $this->mode, $this );

		// merge options
		$args[ $num_args - 1 ] = wp_parse_args( $args[ $num_args - 1 ], $api_options );

		return $args;
	}
}