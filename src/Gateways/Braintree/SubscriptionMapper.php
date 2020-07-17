<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\AddOn as BraintreeAddOn;
use Braintree\Discount as BraintreeDiscount;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use DateTime;
use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

class SubscriptionMapper implements SubscriptionMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public function fromBraintreeSubscription(BraintreeSubscription $subscription, Customer $customer): Subscription
    {
        $subscriptionId = $subscription->id;

        $payment = new Payment('', $subscription->paymentMethodToken);
        $plan = new Plan($subscription->planId, Carbon::instance($subscription->firstBillingDate)->toDateString());

        $addOns = $this->fromBraintreeAddOns($subscription);
        $discounts = $this->fromBraintreeDiscounts($subscription);

        return new Subscription($subscriptionId, $customer, $payment, $plan, $addOns, $discounts);
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function toBraintreeRequest(Subscription $subscription): array
    {
        $customer = $subscription->getCustomer();
        $plan = $subscription->getPlan();
        $addOns = $subscription->getAddOns();
        $discounts = $subscription->getDiscounts();

        return [
            'paymentMethodToken' => $customer->getPaymentToken(),
            'planId' => $plan->getId(),
            'firstBillingDate' => $this->toBraintreeStartDate($plan),
            'addOns' => [
                'add' => $this->toBraintreeAddOns($addOns)
            ],
            'discounts' => [
                'add' => $this->toBraintreeDiscounts($discounts)
            ]
        ];
    }

    /**
     * @param BraintreeSubscription $subscription
     * @return array<AddOn>
     */
    protected function fromBraintreeAddOns(BraintreeSubscription $subscription): array
    {
        return array_map(function (BraintreeAddOn $addOnItem) {
            return new AddOn($addOnItem->id, $addOnItem->quantity ?? 0);
        }, $subscription->addOns);
    }

    /**
     * @param BraintreeSubscription $subscription
     * @return array<Discount>
     */
    protected function fromBraintreeDiscounts(BraintreeSubscription $subscription): array
    {
        return array_map(function (BraintreeDiscount $discountItem) {
            return new Discount($discountItem->id, (float) $discountItem->amount, (int) $discountItem->numberOfBillingCycles);
        }, $subscription->discounts);
    }

    /**
     * @param array<AddOn> $addOns
     * @return array
     */
    public function toBraintreeAddOns(array $addOns): array
    {
        return array_map(function (AddOn $addOn) {
            return [
                'inheritedFromId' => $addOn->getId(),
                'quantity' => $addOn->getQuantity()
            ];
        }, $addOns);
    }

    /**
     * @param array<Discount> $discounts
     * @return array
     */
    protected function toBraintreeDiscounts(array $discounts): array
    {
        return array_map(function (Discount $discount) {
            return [
                'inheritedFromId' => $discount->getId(),
                'amount' => $discount->getAmount(),
                'numberOfBillingCycles' => $discount->getBillingCycles()
            ];
        }, $discounts);
    }

    /**
     * @param Plan $plan
     * @return DateTime
     * @throws \Exception
     */
    protected function toBraintreeStartDate(Plan $plan): DateTime
    {
        return $plan->getStartDate()
            ? new Carbon($plan->getStartDate(), 'utc')
            : new Carbon('now', 'utc');
    }
}
