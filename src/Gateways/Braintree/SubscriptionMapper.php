<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\AddOn as BraintreeAddOn;
use Braintree\Discount as BraintreeDiscount;
use Braintree\Exception\NotFound;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use DateTime;
use TeamGantt\Subscreeb\Gateways\Braintree\Gateway\BraintreeGatewayInterface;
use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\NullCustomer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

class SubscriptionMapper implements SubscriptionMapperInterface
{
    /**
     * @var BraintreeGatewayInterface
     */
    protected BraintreeGatewayInterface $gateway;

    public function __construct(BraintreeGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritDoc}
     */
    public function fromBraintreeSubscription(BraintreeSubscription $subscription): Subscription
    {
        $subscriptionId = $subscription->id;
        $price = (float)$subscription->price;
        $startDate = Carbon::instance($subscription->firstBillingDate)->toDateString();

        $customer = $this->fromBraintreeCustomer($subscription);

        $payment = new Payment('', $subscription->paymentMethodToken);
        $plan = new Plan($subscription->planId, $price);

        $addOns = $this->fromBraintreeAddOns($subscription);
        $discounts = $this->fromBraintreeDiscounts($subscription);

        $status = strtolower($subscription->status);

        return new Subscription($subscriptionId, $customer, $payment, $plan, $addOns, $discounts, $price, $startDate, $status);
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function toBraintreeCreateRequest(Subscription $subscription): array
    {
        $customer = $subscription->getCustomer();
        $plan = $subscription->getPlan();
        $addOns = $subscription->getAddOns();
        $discounts = $subscription->getDiscounts();

        return [
            'paymentMethodToken' => $customer->getPaymentToken(),
            'planId' => $plan->getId(),
            'firstBillingDate' => $this->toBraintreeStartDate($subscription->getStartDate()),
            'addOns' => $this->toBraintreeNewAddOns($addOns),
            'discounts' => [
                'add' => $this->toBraintreeDiscounts($discounts)
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function toBraintreeUpdateRequest(Subscription $subscription, bool $hasPlanChanged): array
    {
        $plan = $subscription->getPlan();
        $planId = $plan->getId();
        $price = $subscription->getPrice();
        $addOns = $subscription->getAddOns();

        $request = [
            'options' => [
                'prorateCharges' => true
            ]
        ];

        if (!empty($planId)) {
            $request['planId'] = $planId;
        }

        if (!is_null($price)) {
            $request['price'] = $price;
        }

        if (!empty($addOns)) {
            $request['addOns'] = $hasPlanChanged
                ? $this->toBraintreeNewAddOns($addOns)
                : $this->toBraintreeUpdatedAddOns($addOns);
        }

        if (!empty($addOns) && $hasPlanChanged) {
            $request['options']['replaceAllAddOnsAndDiscounts'] = true;
        }

        return $request;
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
     * @return Customer
     */
    protected function fromBraintreeCustomer(BraintreeSubscription $subscription): Customer
    {
        $paymentMethodToken = $subscription->paymentMethodToken;
        try {
            $paymentMethod = $this->gateway->paymentMethod()->find($paymentMethodToken);
        } catch (NotFound $e) {
            return new NullCustomer();
        }

        $customerId = $paymentMethod->customerId;
        $customer = null;
        try {
            $customer = $this->gateway->customer()->find($customerId);
        } catch (NotFound $e) {
            return new NullCustomer();
        }

        // @phpstan-ignore-next-line
        return new Customer($customer->id, $customer->firstName, $customer->lastName, $customer->email);
    }

    /**
     * @param BraintreeSubscription $subscription
     * @return array<Discount>
     */
    protected function fromBraintreeDiscounts(BraintreeSubscription $subscription): array
    {
        return array_map(function (BraintreeDiscount $discountItem) {
            return new Discount($discountItem->id, (float)$discountItem->amount, (int)$discountItem->numberOfBillingCycles);
        }, $subscription->discounts);
    }

    /**
     * Returns a Braintree addons array for adding new addons
     * @param array<AddOn> $addOns
     * @return array
     */
    protected function toBraintreeNewAddOns(array $addOns): array
    {
        $addAddons = array_map(function (AddOn $addOn) {
            return [
                'inheritedFromId' => $addOn->getId(),
                'quantity' => $addOn->getQuantity()
            ];
        }, $addOns);

        return [
            'add' => $addAddons
        ];
    }

    /**
     * Returns a Braintree addons array for updating existing addons
     * @param array $addOns
     * @return array
     */
    protected function toBraintreeUpdatedAddOns(array $addOns): array
    {
        $updateAddons = array_map(function (AddOn $addOn) {
            return [
                'existingId' => $addOn->getId(),
                'quantity' => $addOn->getQuantity()
            ];
        }, $addOns);

        return [
            'update' => $updateAddons
        ];
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
     * @param string $startDate
     * @return DateTime
     * @throws \Exception
     */
    protected function toBraintreeStartDate(string $startDate): ?DateTime
    {
        $isToday = $startDate === Carbon::today('utc')->toDateString();

        return (!$startDate || $isToday)
            ? null
            : new Carbon($startDate, 'utc');
    }
}
