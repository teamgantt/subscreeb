<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\PaymentToken;

use Braintree\Gateway;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;

interface StrategyInterface
{
    /**
     * @param Customer $customer
     * @param Payment $payment
     * @return PaymentToken
     */
    public function getPaymentToken(Customer $customer, Payment $payment): PaymentToken;
}
