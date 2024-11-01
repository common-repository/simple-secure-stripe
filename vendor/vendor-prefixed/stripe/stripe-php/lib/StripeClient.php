<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AccountLinkService $accountLinks
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AccountSessionService $accountSessions
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AccountService $accounts
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\ApplePayDomainService $applePayDomains
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\ApplicationFeeService $applicationFees
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Apps\AppsServiceFactory $apps
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\BalanceService $balance
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\BalanceTransactionService $balanceTransactions
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Capital\CapitalServiceFactory $capital
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\ChargeService $charges
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\CountrySpecService $countrySpecs
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\CouponService $coupons
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\CreditNoteService $creditNotes
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\CustomerSessionService $customerSessions
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\CustomerService $customers
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\DisputeService $disputes
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\EventService $events
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\ExchangeRateService $exchangeRates
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\FileLinkService $fileLinks
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\FileService $files
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\FinancialConnections\FinancialConnectionsServiceFactory $financialConnections
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\GiftCards\GiftCardsServiceFactory $giftCards
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Identity\IdentityServiceFactory $identity
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\InvoiceItemService $invoiceItems
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\InvoiceService $invoices
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\MandateService $mandates
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\OAuthService $oauth
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\OrderService $orders
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PaymentIntentService $paymentIntents
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PaymentLinkService $paymentLinks
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PaymentMethodConfigurationService $paymentMethodConfigurations
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PaymentMethodService $paymentMethods
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PayoutService $payouts
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PlanService $plans
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PriceService $prices
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\ProductService $products
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\PromotionCodeService $promotionCodes
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\QuotePhaseService $quotePhases
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\QuoteService $quotes
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Radar\RadarServiceFactory $radar
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\RefundService $refunds
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\ReviewService $reviews
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\SetupAttemptService $setupAttempts
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\SetupIntentService $setupIntents
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\ShippingRateService $shippingRates
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\SourceService $sources
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\SubscriptionItemService $subscriptionItems
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\SubscriptionService $subscriptions
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Tax\TaxServiceFactory $tax
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\TaxCodeService $taxCodes
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\TaxRateService $taxRates
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\TestHelpers\TestHelpersServiceFactory $testHelpers
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\TokenService $tokens
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\TopupService $topups
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\TransferService $transfers
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Treasury\TreasuryServiceFactory $treasury
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\WebhookEndpointService $webhookEndpoints
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        return $this->getService($name);
    }

    public function getService($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->getService($name);
    }
}
