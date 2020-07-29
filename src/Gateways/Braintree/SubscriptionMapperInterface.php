<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Subscription as BraintreeSubscription;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Subscription;

interface SubscriptionMapperInterface
{
    /**
     * Creates a Subscription domain model from a Braintree Subscription result
     *
     * @param BraintreeSubscription $subscription
     * @param Customer $customer
     * @return Subscription
     */
    public function fromBraintreeSubscription(BraintreeSubscription $subscription, Customer $customer): Subscription;

    /**
     * Creates a Braintree request from a Subscription domain model
     *
     * @param Subscription $subscription
     * @return array
     */
    public function toBraintreeRequest(Subscription $subscription): array;
}
