<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Subscription as BraintreeSubscription;
use TeamGantt\Subscreeb\Models\Subscription;

interface SubscriptionMapperInterface
{
    /**
     * Creates a Subscription domain model from a Braintree Subscription result
     *
     * @param BraintreeSubscription $subscription
     * @return Subscription
     */
    public function fromBraintreeSubscription(BraintreeSubscription $subscription): Subscription;

    /**
     * Creates a Braintree subscription create request
     *
     * @param Subscription $subscription
     * @return array
     */
    public function toBraintreeCreateRequest(Subscription $subscription): array;

    /**
     * Creates a Braintree subscription update request
     *
     * @param Subscription $subscription
     * @param bool $hasPlanChanged
     * @return array
     */
    public function toBraintreeUpdateRequest(Subscription $subscription, bool $hasPlanChanged): array;
}
