<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\AddOn as BraintreeAddOn;
use Braintree\Discount as BraintreeDiscount;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

class SubscriptionMapper
{
    /**
     * @param BraintreeSubscription $subscription
     * @param Customer $customer
     * @return Subscription
     */
    public function map(BraintreeSubscription $subscription, Customer $customer): Subscription
    {
        $subscriptionId = $subscription->id;

        $payment = new Payment('', $subscription->paymentMethodToken);
        $plan = new Plan($subscription->planId, Carbon::instance($subscription->firstBillingDate)->toDateString());

        $addOns = $this->getAddOns($subscription);
        $discounts = $this->getDiscounts($subscription);

        return new Subscription($subscriptionId, $customer, $payment, $plan, $addOns, $discounts);
    }

    /**
     * @param BraintreeSubscription $subscription
     * @return array<AddOn>
     */
    protected function getAddOns(BraintreeSubscription $subscription): array
    {
        return array_map(function (BraintreeAddOn $addOnItem) {
            return new AddOn($addOnItem->id, $addOnItem->quantity ?? 0);
        }, $subscription->addOns);
    }

    /**
     * @param BraintreeSubscription $subscription
     * @return array<Discount>
     */
    protected function getDiscounts(BraintreeSubscription $subscription): array
    {
        return array_map(function (BraintreeDiscount $discountItem) {
            return new Discount($discountItem->id, (float) $discountItem->amount, (int) $discountItem->numberOfBillingCycles);
        }, $subscription->discounts);
    }
}
