<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Subscription as BraintreeSubscription;
use TeamGantt\Subscreeb\Models\Customer;

class CustomerMapper implements CustomerMapperInterface
{
    /**
     * @inheritDoc
     */
    public function fromBraintree(BraintreeSubscription $subscription): Customer
    {
        $customer = $subscription->transactions[0]->customerDetails;

        return new Customer($customer->id, $customer->firstName, $customer->lastName, $customer->email);
    }
}
