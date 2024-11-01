<?php

namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Stripe\ApplePayDomain;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use SimpleSecureWP\SimpleSecureStripe\Stripe\ErrorObject;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethod;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Service as StripeService;
use SimpleSecureWP\SimpleSecureStripe\Stripe\SetupIntent;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Source;
use SimpleSecureWP\SimpleSecureStripe\Stripe\StripeClient;
use SimpleSecureWP\SimpleSecureStripe\StripeIntegration;
use WC_Order;
use WP_Error;

/**
 * Gateway class that abstracts all API calls to Stripe.
 *
 * @author Simple & Secure WP
 * @package Stripe/Classes
 *
 * @property StripeService\AccountLinkService                        $accountLinks
 * @property StripeService\AccountService                            $accounts
 * @property StripeService\ApplePayDomainService                     $applePayDomains
 * @property StripeService\ApplicationFeeService                     $applicationFees
 * @property StripeService\BalanceService                            $balance
 * @property StripeService\BalanceTransactionService                 $balanceTransactions
 * @property StripeService\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property StripeService\ChargeService                             $charges
 * @property StripeService\Checkout\CheckoutServiceFactory           $checkout
 * @property StripeService\CountrySpecService                        $countrySpecs
 * @property StripeService\CouponService                             $coupons
 * @property StripeService\CreditNoteService                         $creditNotes
 * @property StripeService\CustomerService                           $customers
 * @property StripeService\DisputeService                            $disputes
 * @property StripeService\EphemeralKeyService                       $ephemeralKeys
 * @property StripeService\EventService                              $events
 * @property StripeService\ExchangeRateService                       $exchangeRates
 * @property StripeService\FileLinkService                           $fileLinks
 * @property StripeService\FileService                               $files
 * @property StripeService\InvoiceItemService                        $invoiceItems
 * @property StripeService\InvoiceService                            $invoices
 * @property StripeService\Issuing\IssuingServiceFactory             $issuing
 * @property StripeService\MandateService                            $mandates
 * @property StripeService\OrderReturnService                        $orderReturns
 * @property StripeService\OrderService                              $orders
 * @property StripeService\PaymentIntentService                      $paymentIntents
 * @property StripeService\PaymentMethodService                      $paymentMethods
 * @property StripeService\PayoutService                             $payouts
 * @property StripeService\PlanService                               $plans
 * @property StripeService\PriceService                              $prices
 * @property StripeService\ProductService                            $products
 * @property StripeService\Radar\RadarServiceFactory                 $radar
 * @property StripeService\RefundService                             $refunds
 * @property StripeService\Reporting\ReportingServiceFactory         $reporting
 * @property StripeService\ReviewService                             $reviews
 * @property StripeService\SetupIntentService                        $setupIntents
 * @property StripeService\Sigma\SigmaServiceFactory                 $sigma
 * @property StripeService\SkuService                                $skus
 * @property StripeService\SourceService                             $sources
 * @property StripeService\SubscriptionItemService                   $subscriptionItems
 * @property StripeService\SubscriptionScheduleService               $subscriptionSchedules
 * @property StripeService\SubscriptionService                       $subscriptions
 * @property StripeService\TaxRateService                            $taxRates
 * @property StripeService\Terminal\TerminalServiceFactory           $terminal
 * @property StripeService\TokenService                              $tokens
 * @property StripeService\TopupService                              $topups
 * @property StripeService\TransferService                           $transfers
 * @property StripeService\WebhookEndpointService                    $webhookEndpoints
 */
class Gateway {

	/**
	 *
	 * @since 1.0.0
	 * @var string mode (test, live)
	 */
	private $mode = null;

	private $messages = [];

	/**
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $secret_key = null;

	/**
	 *
	 * @var StripeClient
	 */
	private $client = null;

	public function __construct( $mode = null, $secret_key = null ) {
		if ( null !== $mode ) {
			$this->mode = $mode;
		}

		if ( null !== $secret_key ) {
			$this->secret_key = $secret_key;
		}

		$this->client = App::get( StripeIntegration\Client::class )->get( $mode );
	}

	public function __get( $key ) {
		return new API\Stripe( $this, $this->client, $key );
	}

	/**
	 *
	 * @since 1.0.0
	 *
	 * @param string $secret_key
	 *
	 * @param string $mode
	 *
	 * @return Gateway
	 */
	public static function load( $mode = null, $secret_key = null, $config = [] ) {
		$class = apply_filters( 'sswps/gateway_class', static::class );

		return new $class( $mode, $secret_key, $config );
	}

	/**
	 *
	 * @since 1.0.0
	 *
	 * @param string $mode
	 *
	 */
	public function set_mode( $mode ) {
		$this->mode = $mode;
	}

	public function get_api_options( $mode = '' ) {
		if ( empty( $mode ) && $this->mode != null ) {
			$mode             = $this->mode;
			$this->secret_key = sswps_get_secret_key( $mode );
		}
		$args = [ 'api_key' => $this->secret_key ? $this->secret_key : sswps_get_secret_key( $mode ) ];

		return apply_filters( 'sswps/api_options', $args );
	}

	/**
	 *
	 * @param mixed $err
	 *
	 * @return string
	 */
	private function get_error_message( $err ) {
		$message = '';
		if ( $err instanceof \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException ) {
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
		 * @param mixed  $err
		 *
		 * @param string $message
		 */
		return apply_filters( 'sswps/api_request_error_message', $message, $err );
	}

	/**
	 *
	 * @since 1.0.0
	 *
	 * @param string            $code
	 *
	 * @param ApiErrorException $e
	 */
	public function get_wp_error( $e, $code = 'stripe-error' ) {
		if ( ( $json_body = $e->getJsonBody() ) ) {
			$err = $json_body['error'];
		} else {
			$err = '';
		}

		$message = $this->get_error_message( $err );

		if ( ! $message ) {
			$message = $e->getMessage();
		}

		return apply_filters( 'sswps/api_get_wp_error', new WP_Error( $code, $message, $err ), $e, $code );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string|WC_Order $mode
	 *
	 * @return $this
	 */
	public function mode( $mode ) {
		if ( $mode instanceof WC_Order ) {
			$this->mode = sswps_order_mode( $mode );
		} elseif ( $mode instanceof \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource ) {
			if ( isset( $mode->livemode ) ) {
				$this->mode === $mode->livemode ? 'live' : 'test';
			}
		} else {
			$this->mode = $mode;
		}

		return $this;
	}

}
