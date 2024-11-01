<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal;

/**
 * A Configurations object represents how features should be configured for terminal readers.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $bbpos_wisepos_e
 * @property null|bool $is_account_default Whether this Configuration is the default for your account
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $tipping
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $verifone_p400
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class Configuration extends \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.configuration';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\All;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Create;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Delete;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Retrieve;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Update;
}
