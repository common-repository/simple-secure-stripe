<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Service\Radar;

class ValueListItemService extends \SimpleSecureWP\SimpleSecureStripe\Stripe\Service\AbstractService
{
    /**
     * Returns a list of <code>ValueListItem</code> objects. The objects are sorted in
     * descending order by creation date, with the most recently created object
     * appearing first.
     *
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Collection<\Stripe\Radar\ValueListItem>
     *
     * @license MIT
     * Modified by sswp-bot on 26-December-2023 using Strauss.
     * @see https://github.com/BrianHenryIE/strauss
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/radar/value_list_items', $params, $opts);
    }

    /**
     * Creates a new <code>ValueListItem</code> object, which is added to the specified
     * parent value list.
     *
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\ValueListItem
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/radar/value_list_items', $params, $opts);
    }

    /**
     * Deletes a <code>ValueListItem</code> object, removing it from its parent value
     * list.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\ValueListItem
     */
    public function delete($id, $params = null, $opts = null)
    {
        return $this->request('delete', $this->buildPath('/v1/radar/value_list_items/%s', $id), $params, $opts);
    }

    /**
     * Retrieves a <code>ValueListItem</code> object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\ValueListItem
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/radar/value_list_items/%s', $id), $params, $opts);
    }
}
