<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections;

/**
 * A Financial Connections Session is the secure way to programmatically launch the client-side Stripe.js modal that lets your users link their accounts.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $account_holder The account holder for whom accounts are collected in this session.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Collection<\Stripe\FinancialConnections\Account> $accounts The accounts that were collected as part of this Session.
 * @property string $client_secret A value that will be passed to the client to launch the authentication flow.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $filters
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $limits
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $manual_entry
 * @property string[] $permissions Permissions requested for accounts collected during this session.
 * @property null|string[] $prefetch Data features requested to be retrieved upon account creation.
 * @property null|string $return_url For webview integrations only. Upon completing OAuth login in the native browser, the user will be redirected to this URL to return to your app.
 * @property null|string $status The current state of the session.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $status_details
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class Session extends \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource
{
    const OBJECT_NAME = 'financial_connections.session';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Create;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Retrieve;

    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
}
