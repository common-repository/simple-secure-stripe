<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards;

/**
 * A gift card represents a single gift card owned by a customer, including the
 * remaining balance, gift card code, and whether or not it is active.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property bool $active Whether this gift card can be used or not.
 * @property int $amount_available The amount of funds available for new transactions.
 * @property int $amount_held The amount of funds marked as held.
 * @property null|string $code Code used to redeem this gift card.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $created_by The related Stripe objects that created this gift card.
 * @property string $currency Three-letter <a href="https://www.iso.org/iso-4217-currency-codes.html">ISO currency code</a>, in lowercase. Must be a <a href="https://stripe.com/docs/currencies">supported currency</a>.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\Collection<\Stripe\GiftCards\Transaction> $transactions Transactions on this gift card.
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class Card extends \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource
{
    const OBJECT_NAME = 'gift_cards.card';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\All;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Create;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Retrieve;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Update;

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Card the validated card
     */
    public static function validate($params = null, $opts = null)
    {
        $url = static::classUrl() . '/validate';
        list($response, $opts) = static::_staticRequest('post', $url, $params, $opts);
        $obj = \SimpleSecureWP\SimpleSecureStripe\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }
}
