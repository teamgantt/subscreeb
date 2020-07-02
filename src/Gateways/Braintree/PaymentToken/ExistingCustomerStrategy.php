<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\PaymentToken;

use Braintree\Exception\NotFound;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomer;
use TeamGantt\Subscreeb\Models\Payment;

class ExistingCustomerStrategy extends BaseStrategy
{
    /**
     * Get a payment token for an existing customer
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return PaymentToken
     */
    public function getPaymentToken(Customer $customer, Payment $payment): PaymentToken
    {
        $gatewayCustomer = $this->findGatewayCustomer($customer);
        $paymentToken = $this->createPaymentMethod($gatewayCustomer, $payment);

        return new PaymentToken($paymentToken, $gatewayCustomer);
    }

    /**
     * @param Customer $customer
     * @return GatewayCustomer
     */
    protected function findGatewayCustomer(Customer $customer): GatewayCustomer
    {
        $gatewayCustomer = null;

        try {
            $gatewayCustomer = $this->gateway
                ->customer()
                ->find($customer->getId());
        } catch (NotFound $e) {
            throw new CustomerNotFoundException("Customer with id {$customer->getId()} does not exist");
        }

        $customerId = $gatewayCustomer->id; // @phpstan-ignore-line

        return new GatewayCustomer($customerId);
    }

    /**
     * @param GatewayCustomer $customer
     * @param Payment $payment
     * @return string
     */
    protected function createPaymentMethod(GatewayCustomer $customer, Payment $payment): string
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

        return $result->paymentMethod->token;
    }
}
