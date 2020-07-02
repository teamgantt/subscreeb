<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomer;
use TeamGantt\Subscreeb\Models\Payment;

class PaymentTokenStrategy implements PaymentTokenStrategyInterface
{
    /**
     * @var Gateway
     */
    protected $gateway;

    /**
     * Undocumented function
     *
     * @param Customer $customer
     * @param Payment $payment
     * @return PaymentToken
     */
    public function getPaymentToken(Customer $customer, Payment $payment): PaymentToken
    {
        $customerId = $customer->getId();
        $paymentToken = null;
        $gatewayCustomer = null;

        if (!$customerId) {
            $gatewayCustomer = $this->createGatewayCustomer($customer, $payment);
            $paymentToken = $gatewayCustomer->getPaymentToken();
        } else {
            $gatewayCustomer = $this->findGatewayCustomer($customer);
            $paymentToken = $this->createPaymentMethod($gatewayCustomer, $payment);
        }

        return new PaymentToken($paymentToken, $gatewayCustomer);
    }

    public function setGateway(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

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
}
