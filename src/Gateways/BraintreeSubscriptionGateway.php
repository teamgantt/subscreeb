<?php

namespace TeamGantt\Subscreeb\Gateways;

use Braintree\Exception\NotFound;
use Braintree\Gateway as Braintree;
use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\Contracts\SubscriptionGateway;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\GatewayCustomer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

class BraintreeSubscriptionGateway implements SubscriptionGateway
{
    protected Braintree $gateway;

    public function __construct(string $environment, string $merchantId, string $publicKey, string $privateKey)
    {
        $this->gateway = new Braintree([
            'environment' => $environment,
            'merchantId' => $merchantId,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey
        ]);
    }

    public function create(Customer $customer, Payment $payment, Plan $plan): Subscription
    {
        $customerId = $customer->getId();
        $planId = $plan->getId();
        $paymentToken = null;

        if (!$customerId) {
            $gatewayCustomer = $this->createGatewayCustomer($customer, $payment);
            $paymentToken = $gatewayCustomer->getPaymentToken();
        } else {
            $gatewayCustomer = $this->findGatewayCustomer($customer);
            $paymentToken = $this->createPaymentMethod($gatewayCustomer, $payment);
        }

        $subscriptionResult = $this->gateway
            ->subscription()
            ->create([
                'paymentMethodToken' => $paymentToken,
                'planId' => $planId
            ]);

        $subscriptionId = $subscriptionResult->subscription->id;

        return new Subscription($subscriptionId);
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
            throw new CreateCustomerException($result->message); /** @phpstan-ignore-line */
        }

        $customerId = $result->customer->id; /** @phpstan-ignore-line */
        $paymentToken = $result->customer->paymentMethods[0]->token; /** @phpstan-ignore-line */

        return new GatewayCustomer($customerId, $paymentToken);
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

        $customerId = $gatewayCustomer->id; /** @phpstan-ignore-line */

        return new GatewayCustomer($customerId, '');
    }
}
