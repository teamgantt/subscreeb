<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Adapters;

use Braintree\AddOn as BraintreeAddOn;
use Braintree\Discount as BraintreeDiscount;
use Braintree\Subscription;
use Carbon\Carbon;
use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

class BraintreeSubscriptionAdapter implements SubscriptionInterface
{
    /**
     * @var Subscription
     */
    protected Subscription $subscription;

    /**
     * @var Customer
     */
    protected Customer $customer;

    /**
     * @var Plan
     */
    protected Plan $plan;

    /**
     * @var Payment
     */
    protected Payment $payment;

    /**
     * BraintreeSubscriptionAdapter constructor.
     * @param Subscription $subscription
     * @param Customer $customer
     */
    public function __construct(Subscription $subscription, Customer $customer)
    {
        $this->subscription = $subscription;
        $this->customer = $customer;

        $this->payment = new Payment('', $subscription->paymentMethodToken);

        $this->plan = new Plan($subscription->planId, Carbon::instance($this->subscription->firstBillingDate)->toDateString());
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return $this->subscription->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @return Plan
     */
    public function getPlan(): Plan
    {
        return $this->plan;
    }

    /**
     * {@inheritDoc}
     */
    public function getAddOns(): array
    {
        return array_map(function (BraintreeAddOn $addOnItem) {
            return new AddOn($addOnItem->id, $addOnItem->quantity ?? 0);
        }, $this->subscription->addOns);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscounts(): array
    {
        return array_map(function (BraintreeDiscount $discountItem) {
            return new Discount($discountItem->id, (float) $discountItem->amount, (int) $discountItem->numberOfBillingCycles);
        }, $this->subscription->discounts);
    }
}
