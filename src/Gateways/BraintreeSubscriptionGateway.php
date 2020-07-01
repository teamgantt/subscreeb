<?php

namespace TeamGantt\Subscreeb\Gateways;

use Braintree\Exception\NotFound;
use Braintree\Gateway as Braintree;
use Carbon\Carbon;
use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\Configuration\BraintreeConfigurationInterface;
use TeamGantt\Subscreeb\Gateways\Contracts\SubscriptionGateway;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomerBuilderInterface;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

class BraintreeSubscriptionGateway implements SubscriptionGateway
{
    /**
     * @var Braintree
     */
    protected Braintree $gateway;

    /**
     * @var GatewayCustomerBuilderInterface
     */
    protected GatewayCustomerBuilderInterface $gatewayCustomerBuilder;

    /**
     * BraintreeSubscriptionGateway constructor.
     * @param BraintreeConfigurationInterface $config
     * @param GatewayCustomerBuilderInterface $gatewayCustomerBuilder
     */
    public function __construct(BraintreeConfigurationInterface $config, GatewayCustomerBuilderInterface $gatewayCustomerBuilder)
    {
        $this->gateway = new Braintree([
            'environment' => $config->getEnvironment(),
            'merchantId' => $config->getMerchantId(),
            'publicKey' => $config->getPublicKey(),
            'privateKey' => $config->getPrivateKey()
        ]);

        $this->gatewayCustomerBuilder = $gatewayCustomerBuilder;
    }

    public function create(Customer $customer, Payment $payment, Plan $plan): Subscription
    {
        $customerId = $customer->getId();
        $paymentToken = null;

        if (!$customerId) {
            $gatewayCustomer = $this->createGatewayCustomer($customer, $payment);
            $paymentToken = $gatewayCustomer->getPaymentToken();
        } else {
            $gatewayCustomer = $this->findGatewayCustomer($customer);
            $paymentToken = $this->createPaymentMethod($gatewayCustomer, $payment);
        }

        return $this->createSubscription($gatewayCustomer, $plan, $paymentToken);
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

        return $this->gatewayCustomerBuilder
            ->withId($customerId)
            ->withPaymentToken($paymentToken)
            ->build();
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

    protected function createSubscription(GatewayCustomer $gatewayCustomer, Plan $plan, string $paymentToken): Subscription
    {
        $planId = $plan->getId();
        $startDate = $plan->getStartDate()
            ? new Carbon($plan->getStartDate())
            : new Carbon();

        $result = $this->gateway
            ->subscription()
            ->create([
                'paymentMethodToken' => $paymentToken,
                'planId' => $planId,
                'firstBillingDate' => $startDate
            ]);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        $subscription = $result->subscription;
        $subscriptionId = $subscription->id;
        $subscriptionStartDate = new Carbon($subscription->firstBillingDate);

        return new Subscription($subscriptionId, $gatewayCustomer->getId(), $subscriptionStartDate->toDateString());
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

        return $this->gatewayCustomerBuilder
            ->withId($customerId)
            ->build();
    }
}
