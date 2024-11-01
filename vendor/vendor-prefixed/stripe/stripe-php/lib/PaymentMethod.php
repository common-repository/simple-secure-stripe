<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe;

/**
 * PaymentMethod objects represent your customer's payment instruments.
 * You can use them with <a href="https://stripe.com/docs/payments/payment-intents">PaymentIntents</a> to collect payments or save them to
 * Customer objects to store instrument details for future payments.
 *
 * Related guides: <a href="https://stripe.com/docs/payments/payment-methods">Payment Methods</a> and <a href="https://stripe.com/docs/payments/more-payment-scenarios">More Payment Scenarios</a>.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $acss_debit
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $affirm
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $afterpay_clearpay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $alipay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $au_becs_debit
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $bacs_debit
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $bancontact
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $billing_details
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $blik
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $boleto
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $card
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $card_present
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $cashapp
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|string|\SimpleSecureWP\SimpleSecureStripe\Stripe\Customer $customer The ID of the Customer to which this PaymentMethod is saved. This will not be set when the PaymentMethod has not been saved to a Customer.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $customer_balance
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $eps
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $fpx
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $giropay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $grabpay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $ideal
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $interac_present
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $klarna
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $konbini
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $link
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $oxxo
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $p24
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $paynow
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $paypal
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $pix
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $promptpay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $radar_options Options to configure Radar. See <a href="https://stripe.com/docs/radar/radar-session">Radar Session</a> for more information.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $sepa_debit
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $sofort
 * @property string $type The type of the PaymentMethod. An additional hash is included on the PaymentMethod with a name matching this value. It contains additional information specific to the PaymentMethod type.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $us_bank_account
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $wechat_pay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $zip
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class PaymentMethod extends ApiResource
{
    const OBJECT_NAME = 'payment_method';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    const TYPE_ACSS_DEBIT = 'acss_debit';
    const TYPE_AFFIRM = 'affirm';
    const TYPE_AFTERPAY_CLEARPAY = 'afterpay_clearpay';
    const TYPE_ALIPAY = 'alipay';
    const TYPE_AU_BECS_DEBIT = 'au_becs_debit';
    const TYPE_BACS_DEBIT = 'bacs_debit';
    const TYPE_BANCONTACT = 'bancontact';
    const TYPE_BLIK = 'blik';
    const TYPE_BOLETO = 'boleto';
    const TYPE_CARD = 'card';
    const TYPE_CARD_PRESENT = 'card_present';
    const TYPE_CASHAPP = 'cashapp';
    const TYPE_CUSTOMER_BALANCE = 'customer_balance';
    const TYPE_EPS = 'eps';
    const TYPE_FPX = 'fpx';
    const TYPE_GIROPAY = 'giropay';
    const TYPE_GRABPAY = 'grabpay';
    const TYPE_IDEAL = 'ideal';
    const TYPE_INTERAC_PRESENT = 'interac_present';
    const TYPE_KLARNA = 'klarna';
    const TYPE_KONBINI = 'konbini';
    const TYPE_LINK = 'link';
    const TYPE_OXXO = 'oxxo';
    const TYPE_P24 = 'p24';
    const TYPE_PAYNOW = 'paynow';
    const TYPE_PAYPAL = 'paypal';
    const TYPE_PIX = 'pix';
    const TYPE_PROMPTPAY = 'promptpay';
    const TYPE_SEPA_DEBIT = 'sepa_debit';
    const TYPE_SOFORT = 'sofort';
    const TYPE_US_BANK_ACCOUNT = 'us_bank_account';
    const TYPE_WECHAT_PAY = 'wechat_pay';
    const TYPE_ZIP = 'zip';

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethod the attached payment method
     */
    public function attach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/attach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethod the detached payment method
     */
    public function detach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/detach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
