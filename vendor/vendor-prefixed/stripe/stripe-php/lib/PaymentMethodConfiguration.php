<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe;

/**
 * An object detailing payment method configurations.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $acss_debit
 * @property bool $active Whether the configuration can be used for new payments.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $affirm
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $afterpay_clearpay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $alipay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $apple_pay
 * @property null|string $application The Connect application associated with this configuration.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $au_becs_debit
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $bacs_debit
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $bancontact
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $blik
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $boleto
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $card
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $cartes_bancaires
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $cashapp
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $eps
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $fpx
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $giropay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $google_pay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $grabpay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $id_bank_transfer
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $ideal
 * @property bool $is_default The default configuration is used whenever no payment method configuration is specified.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $jcb
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $klarna
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $konbini
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $link
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $multibanco
 * @property string $name Configuration name.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $netbanking
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $oxxo
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $p24
 * @property null|string $parent The configuration's parent configuration.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $pay_by_bank
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $paynow
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $paypal
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $promptpay
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $sepa_debit
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $sofort
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $upi
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $us_bank_account
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $wechat_pay
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class PaymentMethodConfiguration extends ApiResource
{
    const OBJECT_NAME = 'payment_method_configuration';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
