<?php

namespace TeamGantt\Subscreeb\Gateways;

use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

interface SubscriptionGatewayInterface
{
    /**
     * Creates a new subscription.
     *
     * @param Customer $customer
     * @param Payment $payment
     * @param Plan $plan
     * @param array<AddOn> $addOns
     * @param array<Discount> $discounts
     *
     * @return SubscriptionInterface
     */
    public function create(Customer $customer, Payment $payment, Plan $plan, array $addOns, array $discounts): SubscriptionInterface;
}
