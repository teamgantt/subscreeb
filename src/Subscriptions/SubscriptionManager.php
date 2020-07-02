<?php

namespace TeamGantt\Subscreeb\Subscriptions;

use Gateway;
use TeamGantt\Subscreeb\Gateways\Contracts\SubscriptionGateway;
use TeamGantt\Subscreeb\Models\AddOn\AddOn;
use TeamGantt\Subscreeb\Models\AddOn\AddOnCollection;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

class SubscriptionManager
{
    /**
     * @var SubscriptionGateway
     */
    protected SubscriptionGateway $gateway;

    /**
     * SubscriptionManager constructor.
     * @param SubscriptionGateway $gateway
     */
    public function __construct(SubscriptionGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param array $data
     * @return SubscriptionInterface
     */
    public function create(array $data): SubscriptionInterface
    {
        // Validate structure of $data
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

        $addOns = new AddOnCollection();
        $addOnItems = $data['addOns'] ?? [];
        foreach ($addOnItems as $addOnItem) {
            $addOn = new AddOn($addOnItem['id'], $addOnItem['quantity']);
            $addOns->addAddon($addOn);
        }

        return $this->gateway->create($customer, $payment, $plan, $addOns);
    }
}
