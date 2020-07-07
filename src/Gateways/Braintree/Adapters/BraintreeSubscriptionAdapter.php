<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Adapters;

use Braintree\AddOn as BraintreeAddOn;
use Braintree\Discount as BraintreeDiscount;
use Braintree\Subscription;
use Carbon\Carbon;
use TeamGantt\Subscreeb\Gateways\Braintree\GatewayCustomer;
use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

class BraintreeSubscriptionAdapter implements SubscriptionInterface
{
    /**
     * @var Subscription
     */
    protected Subscription $subscription;

    /**
     * @var GatewayCustomer
     */
    protected GatewayCustomer $customer;

    /**
     * BraintreeSubscriptionAdapter constructor.
     * @param Subscription $subscription
     * @param GatewayCustomer $customer
     */
    public function __construct(Subscription $subscription, GatewayCustomer $customer)
    {
        $this->subscription = $subscription;
        $this->customer = $customer;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->subscription->id;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): string
    {
        return $this->customer->getId();
    }

    /**
     * @inheritDoc
     */
    public function getStartDate(): string
    {
        return Carbon::instance($this->subscription->firstBillingDate)->toDateString();
    }

    /**
     * @inheritDoc
     */
    public function getAddOns(): array
    {
        return array_map(function (BraintreeAddOn $addOnItem) {
            return new AddOn($addOnItem->id, $addOnItem->quantity ?? 0);
        }, $this->subscription->addOns);
    }

    /**
     * @inheritDoc
     */
    public function getDiscounts(): array
    {
        return array_map(function (BraintreeDiscount $discountItem) {
            return new Discount($discountItem->id, (float) $discountItem->amount, (int) $discountItem->numberOfBillingCycles);
        }, $this->subscription->discounts);
    }
}