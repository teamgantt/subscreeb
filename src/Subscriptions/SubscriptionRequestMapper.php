<?php

namespace TeamGantt\Subscreeb\Subscriptions;

use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\Subscription;

class SubscriptionRequestMapper
{
    /**
     * @param array $request
     * @return Subscription
     */
    public function map(array $request): Subscription
    {
        $customer = $this->mapCustomer($request['customer']);
        $payment = $this->mapPayment($request['payment']);
        $plan = $this->mapPlan($request['plan']);
        $addOns = $this->mapAddons($request['addOns'] ?? []);
        $discounts = $this->mapDiscounts($request['discounts'] ?? []);

        return new Subscription('', $customer, $payment, $plan, $addOns, $discounts);
    }

    /**
     * @param array $attributes
     * @return Customer
     */
    protected function mapCustomer(array $attributes): Customer
    {
        return new Customer(
            $attributes['id'] ?? '',
            $attributes['firstName'] ?? '',
            $attributes['lastName'] ?? '',
            $attributes['emailAddress'] ?? ''
        );
    }

    /**
     * @param array $attributes
     * @return Payment
     */
    protected function mapPayment(array $attributes): Payment
    {
        return new Payment($attributes['nonce']);
    }

    /**
     * @param array $attributes
     * @return Plan
     */
    protected function mapPlan(array $attributes): Plan
    {
        return new Plan(
            $attributes['id'],
            $attributes['startDate'] ?? ''
        );
    }

    /**
     * @param array $attributes
     * @return array<AddOn>
     */
    protected function mapAddons(array $attributes = []): array
    {
        $addOns = [];
        foreach ($attributes as $addOnItem) {
            $addOns[] = new AddOn(
                $addOnItem['id'],
                $addOnItem['quantity']
            );
        }

        return $addOns;
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function mapDiscounts(array $attributes = []): array
    {
        $discounts = [];
        foreach ($attributes as $discountItem) {
            $discounts[] = new Discount(
                $discountItem['id'],
                $discountItem['amount'],
                $discountItem['billingCycles']
            );
        }

        return $discounts;
    }
}
