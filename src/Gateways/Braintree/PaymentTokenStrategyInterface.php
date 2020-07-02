<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Gateway;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;

interface PaymentTokenStrategyInterface
{
    public function getPaymentToken(Customer $customer, Payment $payment): PaymentToken;

    public function setGateway(Gateway $gateway);
}
