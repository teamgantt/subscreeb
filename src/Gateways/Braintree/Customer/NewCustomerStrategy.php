<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Customer;

use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;

class NewCustomerStrategy extends BaseStrategy
{
    /**
     * Get a payment token for a new customer
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return Customer
     * @throws CreateCustomerException
     */
    public function savePaymentToken(Customer $customer, Payment $payment): Customer
    {
        return $this->saveCustomer($customer, $payment);
    }

    /**
     * Create a new Braintree customer
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return Customer
     * @throws CreateCustomerException
     */
    protected function saveCustomer(Customer $customer, Payment $payment): Customer
    {
        $result = $this->gateway
            ->customer()
            ->create([
                'firstName' => $customer->getFirstName(),
                'lastName' => $customer->getLastName(),
                'email' => $customer->getEmailAddress(),
                'paymentMethodNonce' => $payment->getNonce()
            ]);

        if (!$result->success) {
            throw new CreateCustomerException($result->message); // @phpstan-ignore-line
        }

        $customerId = $result->customer->id; // @phpstan-ignore-line
        $paymentToken = $result->customer->paymentMethods[0]->token; // @phpstan-ignore-line
        $payment->setToken($paymentToken);

        $customer = new Customer($customerId, $customer->getFirstName(), $customer->getLastName(), $customer->getEmailAddress());
        return $customer->setPayment($payment);
    }
}
