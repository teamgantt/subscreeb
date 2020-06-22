<?php

namespace TeamGantt\Subscreeb\Subscriptions;

use Gateway;
use TeamGantt\Subscreeb\Gateways\Contracts\SubscriptionGateway;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

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
     * @return Subscription
     */
    public function create(array $data): Subscription
    {
        // Validate structure of $data
        $customer = new Customer(
            $data['customer']['id'] ?? '',
            $data['customer']['firstName'] ?? '',
            $data['customer']['lastName'] ?? '',
            $data['customer']['emailAddress'] ?? ''
        );
        $payment = new Payment($data['payment']['nonce']);
        $plan = new Plan($data['plan']['id']);

        return $this->gateway->create($customer, $payment, $plan);
    }
}
