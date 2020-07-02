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
use TeamGantt\Subscreeb\Models\Adapters\BraintreeSubscriptionAdapter;
use TeamGantt\Subscreeb\Models\AddOn\AddOn;
use TeamGantt\Subscreeb\Models\AddOn\AddOnCollection;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomerBuilderInterface;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

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


    /**
     * {@inheritDoc}
     *
     * @throws CreateCustomerException
     * @throws CreatePaymentMethodException
     * @throws CreateSubscriptionException
     * @throws CustomerNotFoundException
     */
    public function create(Customer $customer, Payment $payment, Plan $plan, AddOnCollection $addOns): SubscriptionInterface
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

        return $this->createSubscription($gatewayCustomer, $plan, $addOns, $paymentToken);
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

    protected function createSubscription(GatewayCustomer $gatewayCustomer, Plan $plan, AddOnCollection $addOns, string $paymentToken): SubscriptionInterface
    {
        $planId = $plan->getId();
        $startDate = $plan->getStartDate()
            ? new Carbon($plan->getStartDate())
            : new Carbon();

        $addOnItems = array_map(function (AddOn $addOn) {
            return  [
               'existingId' => $addOn->getId(),
               'quantity' => $addOn->getQuantity()
            ];
        }, $addOns->getAddons());

        $result = $this->gateway
            ->subscription()
            ->create([
                'paymentMethodToken' => $paymentToken,
                'planId' => $planId,
                'firstBillingDate' => $startDate,
                'addOns' => [
                    'update' => $addOnItems
                ]
            ]);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        return new BraintreeSubscriptionAdapter($result->subscription, $gatewayCustomer);
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
