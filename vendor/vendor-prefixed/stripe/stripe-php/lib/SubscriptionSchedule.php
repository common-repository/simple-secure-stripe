<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe;

/**
 * A subscription schedule allows you to create and manage the lifecycle of a subscription by predefining expected changes.
 *
 * Related guide: <a href="https://stripe.com/docs/billing/subscriptions/subscription-schedules">Subscription schedules</a>
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|string|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $application ID of the Connect Application that created the schedule.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $applies_to
 * @property null|string $billing_behavior Configures when the subscription schedule generates prorations for phase transitions. Possible values are <code>prorate_on_next_phase</code> or <code>prorate_up_front</code> with the default being <code>prorate_on_next_phase</code>. <code>prorate_on_next_phase</code> will apply phase changes and generate prorations at transition time.<code>prorate_up_front</code> will bill for all phases within the current billing cycle up front.
 * @property null|int $canceled_at Time at which the subscription schedule was canceled. Measured in seconds since the Unix epoch.
 * @property null|int $completed_at Time at which the subscription schedule was completed. Measured in seconds since the Unix epoch.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $current_phase Object representing the start and end dates for the current phase of the subscription schedule, if it is <code>active</code>.
 * @property string|\SimpleSecureWP\SimpleSecureStripe\Stripe\Customer $customer ID of the customer who owns the subscription schedule.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $default_settings
 * @property string $end_behavior Behavior of the subscription schedule and underlying subscription when it ends. Possible values are <code>release</code> or <code>cancel</code> with the default being <code>release</code>. <code>release</code> will end the subscription schedule and keep the underlying subscription running.<code>cancel</code> will end the subscription schedule and cancel the underlying subscription.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property \SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject[] $phases Configuration for the subscription schedule's phases.
 * @property null|\SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject $prebilling Time period and invoice for a Subscription billed in advance.
 * @property null|int $released_at Time at which the subscription schedule was released. Measured in seconds since the Unix epoch.
 * @property null|string $released_subscription ID of the subscription once managed by the subscription schedule (if it is released).
 * @property string $status The present status of the subscription schedule. Possible values are <code>not_started</code>, <code>active</code>, <code>completed</code>, <code>released</code>, and <code>canceled</code>. You can read more about the different states in our <a href="https://stripe.com/docs/billing/subscriptions/subscription-schedules">behavior guide</a>.
 * @property null|string|\SimpleSecureWP\SimpleSecureStripe\Stripe\Subscription $subscription ID of the subscription managed by the subscription schedule.
 * @property null|string|\SimpleSecureWP\SimpleSecureStripe\Stripe\TestHelpers\TestClock $test_clock ID of the test clock this subscription schedule belongs to.
 *
 * @license MIT
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class SubscriptionSchedule extends ApiResource
{
    const OBJECT_NAME = 'subscription_schedule';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    const BILLING_BEHAVIOR_PRORATE_ON_NEXT_PHASE = 'prorate_on_next_phase';
    const BILLING_BEHAVIOR_PRORATE_UP_FRONT = 'prorate_up_front';

    const END_BEHAVIOR_CANCEL = 'cancel';
    const END_BEHAVIOR_NONE = 'none';
    const END_BEHAVIOR_RELEASE = 'release';
    const END_BEHAVIOR_RENEW = 'renew';

    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_RELEASED = 'released';

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\SubscriptionSchedule the amended subscription schedule
     */
    public function amend($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/amend';
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
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\SubscriptionSchedule the canceled subscription schedule
     */
    public function cancel($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/cancel';
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
     * @return \SimpleSecureWP\SimpleSecureStripe\Stripe\SubscriptionSchedule the released subscription schedule
     */
    public function release($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/release';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}