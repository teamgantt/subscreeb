<?php

namespace TeamGantt\Subscreeb\Subscriptions;

use Gateway;
use TeamGantt\Subscreeb\Gateways\SubscriptionGatewayInterface;
use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

class SubscriptionManager
{
    /**
     * @var SubscriptionGatewayInterface
     */
    protected SubscriptionGatewayInterface $gateway;

    /**
     * SubscriptionManager constructor.
     * @param SubscriptionGatewayInterface $gateway
     */
    public function __construct(SubscriptionGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param array $data
     * @return SubscriptionInterface
     */
    public function create(array $data): SubscriptionInterface
    {
        // $subscriptionBuilder = new Subscription($data);
        // $builder->withCustomer();

        // $builder->customerFromKey('customer');
        // Subscription $subscription = $builder->build($data);

        $customer = new Customer(
            $data['customer']['id'] ?? '',
            $data['customer']['firstName'] ?? '',
            $data['customer']['lastName'] ?? '',
            $data['customer']['emailAddress'] ?? ''
        );

        $payment = new Payment($data['payment']['nonce']);
        $plan = new Plan(
            $data['plan']['id'],
            $data['plan']['startDate'] ?? ''
        );

        $addOns = [];
        foreach (($data['addOns'] ?? []) as $addOnItem) {
            $addOns[] = new AddOn($addOnItem['id'], $addOnItem['quantity']);
        }

        $discounts = [];
        foreach (($data['discounts'] ?? []) as $discountItem) {
            $discounts[] = new Discount($discountItem['id'], $discountItem['amount'], $discountItem['billingCycles']);
        }

        return $this->gateway->create($customer, $payment, $plan, $addOns, $discounts);
    }
}
