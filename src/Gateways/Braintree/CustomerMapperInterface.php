<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Subscription as BraintreeSubscription;
use TeamGantt\Subscreeb\Models\Customer;

interface CustomerMapperInterface
{
    /**
     * Creates a Customer domain model from a Braintree Subscription result
     *
     * @param BraintreeSubscription $subscription
     * @return Customer
     */
    public function fromBraintree(BraintreeSubscription $subscription): Customer;
}
