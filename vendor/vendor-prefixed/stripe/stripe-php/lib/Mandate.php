<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe;

/**
 * A Mandate is a record of the permission a customer has given you to debit their payment method.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $customer_acceptance
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $multi_use
 * @property null|string $on_behalf_of The account (if any) for which the mandate is intended.
 * @property string|\SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethod $payment_method ID of the payment method associated with this mandate.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $payment_method_details
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $single_use
 * @property string $status The status of the mandate, which indicates whether it can be used to initiate a payment.
 * @property string $type The type of the mandate.
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class Mandate extends ApiResource
{
    const OBJECT_NAME = 'mandate';

    use ApiOperations\Retrieve;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';

    const TYPE_MULTI_USE = 'multi_use';
    const TYPE_SINGLE_USE = 'single_use';
}
