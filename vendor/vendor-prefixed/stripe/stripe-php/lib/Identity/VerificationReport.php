<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Identity;

/**
 * A VerificationReport is the result of an attempt to collect and verify data from a user.
 * The collection of verification checks performed is determined from the <code>type</code> and <code>options</code>
 * parameters used. You can find the result of each verification check performed in the
 * appropriate sub-resource: <code>document</code>, <code>id_number</code>, <code>selfie</code>.
 *
 * Each VerificationReport contains a copy of any data collected by the user as well as
 * reference IDs which can be used to access collected images through the <a href="https://stripe.com/docs/api/files">FileUpload</a>
 * API. To configure and create VerificationReports, use the
 * <a href="https://stripe.com/docs/api/identity/verification_sessions">VerificationSession</a> API.
 *
 * Related guides: <a href="https://stripe.com/docs/identity/verification-sessions#results">Accessing verification results</a>.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $document Result from a document check
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $id_number Result from an id_number check
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $options
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $selfie Result from a selfie check
 * @property null|string $type Type of report.
 * @property null|string $verification_session ID of the VerificationSession that created this report.
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class VerificationReport extends \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource
{
    const OBJECT_NAME = 'identity.verification_report';

    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\All;
    use \SimpleSecureWP\SimpleSecureStripe\Stripe\ApiOperations\Retrieve;

    const TYPE_DOCUMENT = 'document';
    const TYPE_ID_NUMBER = 'id_number';
}
