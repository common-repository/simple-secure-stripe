<?php

namespace SimpleSecureWP\SimpleSecureStripe\StripeIntegration;

use InvalidArgumentException;
use SimpleSecureWP\SimpleSecureStripe\Admin;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Stripe\ErrorObject;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Service as StripeService;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Stripe as StripeOfficial;
use SimpleSecureWP\SimpleSecureStripe\Stripe\StripeClient;
use WP_Error;

class Client {
	/**
	 * @var string
	 */
	public static $version = '2022-08-01';

	/**
	 * The Stripe appPartnerId.
	 *
	 * @var ?string
	 */
	protected $app_partner_id = null;

	/**
	 * @var StripeClient
	 */
	protected $live_client;

	/**
	 * Stripe API messages.
	 *
	 * @var array<string, mixed>
	 */
	protected $messages = [];

	/**
	 * The Stripe mode: live or test.
	 *
	 * @var string
	 */
	protected $mode = 'live';

	/**
	 * @var StripeClient
	 */
	protected $test_client;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->live_client = new StripeClient( $this->get_config( Constants::LIVE ) );
		$this->test_client = new StripeClient( $this->get_config( Constants::TEST ) );
		StripeOfficial::setAppInfo( 'WordPress simple-secure-stripe', Plugin::VERSION, 'https://wordpress.org/plugins/simple-secure-stripe/', $this->app_partner_id );
	}

	/**
	 * Get the Stripe client.
	 *
	 * @since 1.0.0
	 *
	 * @param ?string $mode The mode to get the client for. Either 'live' or 'test'.
	 *
	 * @return StripeClient
	 */
	public function get( $mode = Constants::LIVE ) : StripeClient {
		return $mode === Constants::LIVE || $mode === null ? $this->live_client : $this->test_client;
	}

	/**
	 * Get the Stripe client config.
	 *
	 * @since 1.0.0
	 *
	 * @param string $mode The mode to get the client config for. Either 'live' or 'test'.
	 *
	 * @return mixed|null
	 */
	public function get_config( string $mode = Constants::LIVE ) {
		/**
		 * Filter the Stripe client config.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $config The config.
		 * @param string $mode   The mode: 'live' or 'test'.
		 * @param Client $client The client.
		 */
		return apply_filters( 'sswps/client_config', [ 'stripe_version' => static::$version ], $mode, $this );
	}

	/**
	 *
	 * @param mixed $err
	 *
	 * @return string
	 */
	protected function get_error_message( $err ) {
		$message = '';
		if ( $err instanceof ApiErrorException ) {
			$err = $err->getError();
		}
		if ( is_array( $err ) || $err instanceof ErrorObject ) {
			$this->messages = ! $this->messages ? sswps_get_error_messages() : $this->messages;
			$keys           = [];

			if ( isset( $err['code'] ) ) {
				$keys[] = $err['code'];
				if ( $err['code'] === 'card_declined' ) {
					if ( isset( $err['decline_code'] ) ) {
						$keys[] = $err['decline_code'];
					}
				}
			}

			while ( ! empty( $keys ) ) {
				$key = array_pop( $keys );
				if ( isset( $this->messages[ $key ] ) ) {
					$message = $this->messages[ $key ];
					break;
				}
			}

			if ( empty( $message ) && isset( $err['message'] ) ) {
				$message = $err['message'];
			}
		}

		if ( is_string( $err ) ) {
			$message = $err;
		}

		/**
		 * @since 1.0.0
		 *
		 * @param string $message
		 * @param mixed  $err
		 */
		return apply_filters( 'sswps/api_request_error_message', $message, $err );
	}

	/**
	 * Return the secret key for the provided mode.
	 * If no mode given, the key for the active mode is returned.
	 *
	 * @since   1.0.0
	 *
	 * @param string $mode Stripe mode: live or test.
	 */
	public function get_secret_key( string $mode = Constants::LIVE ) {
		if ( $mode !== Constants::LIVE && $mode !== Constants::TEST ) {
			/* translators: 1 - "live", 2 - "test" (unless otherwise overridden) */
			throw new InvalidArgumentException( sprintf( __( 'Mode can only be "%1$s" or "%2$s".', 'simple-secure-stripe' ), Constants::LIVE, Constants::TEST ) );
		}

		$key = App::get( Admin\Settings\API::class )->get_option( "secret_key_{$mode}" );

		return apply_filters( 'sswps/get_secret_key', $key, $mode, $this );
	}

	/**
	 *
	 * @since 1.0.0
	 *
	 * @param ApiErrorException $e
	 * @param string            $code
	 */
	public function get_wp_error( $e, $code = 'stripe-error' ) {
		$err       = '';
		$json_body = $e->getJsonBody();

		if ( $json_body ) {
			$err = $json_body['error'];
		}

		$message = $this->get_error_message( $err );

		if ( ! $message ) {
			$message = $e->getMessage();
		}

		return apply_filters( 'sswps/api_get_wp_error', new WP_Error( $code, $message, $err ), $e, $code );
	}

	/**
	 * Get a service object for the given mode.
	 *
	 * @since 1.0.0
	 *
	 * @param string $service Stripe AbstractService.
	 *                        accountLinks          StripeService\AccountLinkService
	 *                        accounts              StripeService\AccountService
	 *                        applePayDomains       StripeService\ApplePayDomainService
	 *                        applicationFees       StripeService\ApplicationFeeService
	 *                        balance               StripeService\BalanceService
	 *                        balanceTransactions   StripeService\BalanceTransactionService
	 *                        billingPortal         StripeService\BillingPortal\BillingPortalServiceFactory
	 *                        charges               StripeService\ChargeService
	 *                        checkout              StripeService\Checkout\CheckoutServiceFactory
	 *                        countrySpecs          StripeService\CountrySpecService
	 *                        coupons               StripeService\CouponService
	 *                        creditNotes           StripeService\CreditNoteService
	 *                        customers             StripeService\CustomerService
	 *                        disputes              StripeService\DisputeService
	 *                        ephemeralKeys         StripeService\EphemeralKeyService
	 *                        events                StripeService\EventService
	 *                        exchangeRates         StripeService\ExchangeRateService
	 *                        fileLinks             StripeService\FileLinkService
	 *                        files                 StripeService\FileService
	 *                        invoiceItems          StripeService\InvoiceItemService
	 *                        invoices              StripeService\InvoiceService
	 *                        issuing               StripeService\Issuing\IssuingServiceFactory
	 *                        mandates              StripeService\MandateService
	 *                        orderReturns          StripeService\OrderReturnService
	 *                        orders                StripeService\OrderService
	 *                        paymentIntents        StripeService\PaymentIntentService
	 *                        paymentMethods        StripeService\PaymentMethodService
	 *                        payouts               StripeService\PayoutService
	 *                        plans                 StripeService\PlanService
	 *                        prices                StripeService\PriceService
	 *                        products              StripeService\ProductService
	 *                        radar                 StripeService\Radar\RadarServiceFactory
	 *                        refunds               StripeService\RefundService
	 *                        reporting             StripeService\Reporting\ReportingServiceFactory
	 *                        reviews               StripeService\ReviewService
	 *                        setupIntents          StripeService\SetupIntentService
	 *                        sigma                 StripeService\Sigma\SigmaServiceFactory
	 *                        skus                  StripeService\SkuService
	 *                        sources               StripeService\SourceService
	 *                        subscriptionItems     StripeService\SubscriptionItemService
	 *                        subscriptionSchedules StripeService\SubscriptionScheduleService
	 *                        subscriptions         StripeService\SubscriptionService
	 *                        taxRates              StripeService\TaxRateService
	 *                        terminal              StripeService\Terminal\TerminalServiceFactory
	 *                        tokens                StripeService\TokenService
	 *                        topups                StripeService\TopupService
	 *                        transfers             StripeService\TransferService
	 *                        webhookEndpoints      StripeService\WebhookEndpointService
	 * @param string $mode    Stripe mode.
	 *
	 * @return Service
	 */
	public static function service( string $service, string $mode = Constants::LIVE ) : Service {
		$service_key = "{$mode}_{$service}";

		if ( App::has( $service_key ) ) {
			return App::get( $service_key );
		}

		$service_object = new Service( App::get( static::class ), $service, $mode );
		App::singleton( $service_key, $service_object );

		return App::get( $service_key );
	}

	/**
	 * Sets a Stripe client.
	 *
	 * @param StripeClient $client
	 * @param ?string      $mode
	 *
	 * @return void
	 */
	public function set_client( StripeClient $client, $mode = Constants::LIVE ) {
		if ( $mode === Constants::LIVE || $mode === null ) {
			$this->live_client = $client;
		} else {
			$this->test_client = $client;
		}
	}
}