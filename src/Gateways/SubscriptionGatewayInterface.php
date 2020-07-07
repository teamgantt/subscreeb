<?php

namespace TeamGantt\Subscreeb\Gateways;

use TeamGantt\Subscreeb\Models\AddOn\AddOnCollection;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount\DiscountCollection;
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
     * @param AddOnCollection $addOns
     * @param DiscountCollection $discounts
     *
     * @return SubscriptionInterface
     */
    public function create(Customer $customer, Payment $payment, Plan $plan, AddOnCollection $addOns, DiscountCollection $discounts): SubscriptionInterface;
}
