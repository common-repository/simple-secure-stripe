<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury;

/**
 * Encodes whether a FinancialAccount has access to a particular Feature, with a <code>status</code> enum and associated <code>status_details</code>.
 * Stripe or the platform can control Features via the requested field.
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $card_issuing Toggle settings for enabling/disabling a feature
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $deposit_insurance Toggle settings for enabling/disabling a feature
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $financial_addresses Settings related to Financial Addresses features on a Financial Account
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $inbound_transfers InboundTransfers contains inbound transfers features for a FinancialAccount.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $intra_stripe_flows Toggle settings for enabling/disabling a feature
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $outbound_payments Settings related to Outbound Payments features on a Financial Account
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $outbound_transfers OutboundTransfers contains outbound transfers features for a FinancialAccount.
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class FinancialAccountFeatures extends \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource
{
    const OBJECT_NAME = 'treasury.financial_account_features';
}
