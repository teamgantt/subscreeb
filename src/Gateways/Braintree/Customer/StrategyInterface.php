<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Customer;

use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;

interface StrategyInterface
{
    /**
     * @param Customer $customer
     * @param Payment $payment
     * @return Customer
     */
    public function savePaymentToken(Customer $customer, Payment $payment): Customer;
}
