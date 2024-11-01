<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections;

/**
 * A historical balance for the account on a particular day. It may be sourced from a balance snapshot provided by a financial institution, or inferred using transactions data.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $as_of The time for which this balance was calculated, measured in seconds since the Unix epoch. If the balance was computed by Stripe and not provided directly by a financial institution, it will always be 23:59:59 UTC.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $current <p>The balances owed to (or by) the account holder.</p><p>Each key is a three-letter <a href="https://www.iso.org/iso-4217-currency-codes.html">ISO currency code</a>, in lowercase.</p><p>Each value is a integer amount. A positive amount indicates money owed to the account holder. A negative amount indicates money owed by the account holder.</p>
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class InferredBalance extends \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource
{
    const OBJECT_NAME = 'financial_connections.account_inferred_balance';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\All;
}
