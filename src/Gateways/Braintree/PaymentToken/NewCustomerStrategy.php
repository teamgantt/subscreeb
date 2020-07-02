<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\PaymentToken;

use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomer;
use TeamGantt\Subscreeb\Models\Payment;

class NewCustomerStrategy extends BaseStrategy
{
    /**
     * Get a payment token for a new customer
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return PaymentToken
     */
    public function getPaymentToken(Customer $customer, Payment $payment): PaymentToken
    {
        $gatewayCustomer = $this->createGatewayCustomer($customer, $payment);
        $paymentToken = $gatewayCustomer->getPaymentToken();

        return new PaymentToken($paymentToken, $gatewayCustomer);
    }

    /**
     * Create a new Braintree customer
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return GatewayCustomer
     */
    protected function createGatewayCustomer(Customer $customer, Payment $payment): GatewayCustomer
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

        return new GatewayCustomer($customerId, $paymentToken);
    }
}
