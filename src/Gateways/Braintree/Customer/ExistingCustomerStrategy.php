<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Customer;

use Braintree\Exception\NotFound;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;

class ExistingCustomerStrategy extends BaseStrategy
{
    /**
     * Get a payment token for an existing customer
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return Customer
     * @throws CreatePaymentMethodException
     * @throws CustomerNotFoundException
     */
    public function savePaymentToken(Customer $customer, Payment $payment): Customer
    {
        $this->verifyCustomer($customer);
        $payment = $this->createPaymentMethod($customer, $payment);

        return $customer->setPayment($payment);
    }

    /**
     * @param Customer $customer
     * @return void
     * @throws CustomerNotFoundException
     */
    protected function verifyCustomer(Customer $customer): void
    {
        try {
            $this->gateway
                ->customer()
                ->find($customer->getId());
        } catch (NotFound $e) {
            throw new CustomerNotFoundException("Customer with id {$customer->getId()} does not exist");
        }
    }

    /**
     * @param Customer $customer
     * @param Payment $payment
     * @return Payment
     * @throws CreatePaymentMethodException
     */
    protected function createPaymentMethod(Customer $customer, Payment $payment): Payment
    {
        $result = $this->gateway
            ->paymentMethod()
            ->create([
                'customerId' => $customer->getId(),
                'paymentMethodNonce' => $payment->getNonce(),
                'options' => [
                    'makeDefault' => true
                ]
            ]);

        if (!$result->success) {
            throw new CreatePaymentMethodException($result->message);
        }

        return $payment->setToken($result->paymentMethod->token);
    }
}
