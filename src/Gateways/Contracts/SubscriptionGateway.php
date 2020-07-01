<?php

namespace TeamGantt\Subscreeb\Gateways\Contracts;

use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

interface SubscriptionGateway
{
    public function create(Customer $customer, Payment $payment, Plan $plan): Subscription;
}
