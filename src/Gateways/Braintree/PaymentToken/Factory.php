<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\PaymentToken;

use Braintree\Gateway;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;

final class Factory
{
    /**
     * @var Gateway
     */
    private Gateway $gateway;

    /**
     * Factory constructor
     *
     * @param Gateway $gateway
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }
    
    /**
     * Make a payment token for the given customer and payment
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return PaymentToken
     */
    public function make(Customer $customer, Payment $payment): PaymentToken
    {
        $strategy = $this->getStrategy($customer);
        return $strategy->getPaymentToken($customer, $payment);
    }

    /**
     * @param Customer $customer
     * @return StrategyInterface
     */
    private function getStrategy(Customer $customer): StrategyInterface
    {
        if ($customer->isNew()) {
            return new NewCustomerStrategy($this->gateway);
        }

        return new ExistingCustomerStrategy($this->gateway);
    }
}
