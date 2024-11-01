<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Tax;

/**
 * Tax forms are legal documents which are delivered to one or more tax authorities for information reporting purposes.
 *
 * Related guide: <a href="https://stripe.com/docs/connect/tax-reporting">US tax reporting for Connect platforms</a>
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|string|\SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Form $corrected_by The form that corrects this form, if any.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject[] $filing_statuses A list of tax filing statuses. Note that a filing status will only be included if the form has been filed directly with the jurisdiction’s tax authority.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $payee
 * @property string $type The type of the tax form. An additional hash is included on the tax form with a name matching this value. It contains additional information specific to the tax form type.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $us_1099_k
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $us_1099_misc
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $us_1099_nec
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class Form extends \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource
{
    const OBJECT_NAME = 'tax.form';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\All;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Retrieve;

    const TYPE_US_1099_K = 'us_1099_k';
    const TYPE_US_1099_MISC = 'us_1099_misc';
    const TYPE_US_1099_NEC = 'us_1099_nec';

    /**
     * @param callable $readBodyChunkCallable
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return void
     */
    public function pdf($readBodyChunkCallable, $params = null, $opts = null)
    {
        $opts = \SimpleSecureWP\SimpleSecureStripe\Stripe\Util\RequestOptions::parse($opts);
        if (!isset($opts->apiBase)) {
            $opts->apiBase = \SimpleSecureWP\SimpleSecureStripe\Stripe\Stripe::$apiUploadBase;
        }
        $url = $this->instanceUrl() . '/pdf';
        $this->_requestStream('get', $url, $readBodyChunkCallable, $params, $opts);
    }
}
