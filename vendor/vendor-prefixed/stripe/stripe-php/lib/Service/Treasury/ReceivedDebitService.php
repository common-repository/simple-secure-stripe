<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Treasury;

class ReceivedDebitService extends \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AbstractService
{
    /**
     * Returns a list of ReceivedDebits.
     *
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Collection<\Stripe\Treasury\ReceivedDebit>
     *
     * @license MIT
     * Modified by sswp-bot on 26-December-2023 using Strauss.
     * @see https://github.com/BrianHenryIE/strauss
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/treasury/received_debits', $params, $opts);
    }

    /**
     * Retrieves the details of an existing ReceivedDebit by passing the unique
     * ReceivedDebit ID from the ReceivedDebit list.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\ReceivedDebit
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/treasury/received_debits/%s', $id), $params, $opts);
    }
}
