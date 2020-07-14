<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Customer;

use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;

class CustomerStrategy extends BaseStrategy
{
    /**
     * {@inheritDoc}
     */
    public function savePaymentToken(Customer $customer, Payment $payment): Customer
    {
        return $this->getStrategy($customer)->savePaymentToken($customer, $payment);
    }

    /**
     * @param Customer $customer
     * @return StrategyInterface
     */
    protected function getStrategy(Customer $customer): StrategyInterface
    {
        if ($customer->isNew()) {
            return new NewCustomerStrategy($this->gateway);
        }

        return new ExistingCustomerStrategy($this->gateway);
    }

}
