<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Service\GiftCards;

class TransactionService extends \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AbstractService
{
    /**
     * List gift card transactions for a gift card.
     *
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Collection<\Stripe\GiftCards\Transaction>
     *
     * @license MIT
     * Modified by sswp-bot on 26-December-2023 using Strauss.
     * @see https://github.com/BrianHenryIE/strauss
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/gift_cards/transactions', $params, $opts);
    }

    /**
     * Cancel a gift card transaction.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Transaction
     */
    public function cancel($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/gift_cards/transactions/%s/cancel', $id), $params, $opts);
    }

    /**
     * Confirm a gift card transaction.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Transaction
     */
    public function confirm($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/gift_cards/transactions/%s/confirm', $id), $params, $opts);
    }

    /**
     * Create a gift card transaction.
     *
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Transaction
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/gift_cards/transactions', $params, $opts);
    }

    /**
     * Retrieves the gift card transaction.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Transaction
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/gift_cards/transactions/%s', $id), $params, $opts);
    }

    /**
     * Update a gift card transaction.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Transaction
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/gift_cards/transactions/%s', $id), $params, $opts);
    }
}
