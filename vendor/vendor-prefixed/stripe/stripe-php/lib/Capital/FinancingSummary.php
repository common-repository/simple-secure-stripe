<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Capital;

/**
 * A financing object describes an account's current financing state. Used by Connect
 * platforms to read the state of Capital offered to their connected accounts.
 *
 * @property string $object The object type: financing_summary
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $details Additional information about the financing summary. Describes currency, advance amount, fee amount, withhold rate, remaining amount, paid amount, current repayment interval, repayment start date, and advance payout date.
 * @property null|string $financing_offer The Financing Offer ID this Financing Summary corresponds to
 * @property null|string $status Status of the Connected Account's financing. <a href="https://stripe.com/docs/api/capital/financing_summary">/v1/capital/financing_summary</a> will only return <code>details</code> for <code>paid_out</code> financing.
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class FinancingSummary extends \SimpleSecureWP\SimpleSecureStripe\Stripe\SingletonApiResource
{
    const OBJECT_NAME = 'capital.financing_summary';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\SingletonRetrieve;

    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_NONE = 'none';
}
