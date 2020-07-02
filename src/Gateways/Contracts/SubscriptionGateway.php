<?php

namespace TeamGantt\Subscreeb\Gateways\Contracts;

use TeamGantt\Subscreeb\Models\AddOn\AddOnCollection;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

interface SubscriptionGateway
{
    /**
     * Creates a new subscription.
     *
     * @param Customer $customer
     * @param Payment $payment
     * @param Plan $plan
     * @param AddOnCollection $addOns
     *
     * @return SubscriptionInterface
     */
    public function create(Customer $customer, Payment $payment, Plan $plan, AddOnCollection $addOns): SubscriptionInterface;
}
