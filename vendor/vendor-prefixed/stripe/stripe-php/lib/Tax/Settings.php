<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Tax;

/**
 * You can use Tax <code>Settings</code> to manage configurations used by Stripe Tax calculations.
 *
 * Related guide: <a href="https://stripe.com/docs/tax/settings-api">Using the Settings API</a>
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $defaults
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $head_office The place where your business is located.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property string $status The <code>active</code> status indicates you have all required settings to calculate tax. A status can transition out of <code>active</code> when new required settings are introduced.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $status_details
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class Settings extends \SimpleSecureWP\SimpleSecureStripe\Stripe\SingletonApiResource
{
    const OBJECT_NAME = 'tax.settings';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\SingletonRetrieve;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Update;

    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
}
