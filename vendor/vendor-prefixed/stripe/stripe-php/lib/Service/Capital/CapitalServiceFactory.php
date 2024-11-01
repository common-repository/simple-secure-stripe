<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Capital;

/**
 * Service factory class for API resources in the Capital namespace.
 *
 * @property FinancingOfferService $financingOffers
 * @property FinancingSummaryService $financingSummary
 * @property FinancingTransactionService $financingTransactions
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class CapitalServiceFactory extends \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = [
        'financingOffers' => FinancingOfferService::class,
        'financingSummary' => FinancingSummaryService::class,
        'financingTransactions' => FinancingTransactionService::class,
    ];

    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
