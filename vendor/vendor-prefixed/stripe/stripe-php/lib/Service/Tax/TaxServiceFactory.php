<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Tax;

/**
 * Service factory class for API resources in the Tax namespace.
 *
 * @property CalculationService $calculations
 * @property FormService $forms
 * @property RegistrationService $registrations
 * @property SettingsService $settings
 * @property TransactionService $transactions
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class TaxServiceFactory extends \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = [
        'calculations' => CalculationService::class,
        'forms' => FormService::class,
        'registrations' => RegistrationService::class,
        'settings' => SettingsService::class,
        'transactions' => TransactionService::class,
    ];

    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
