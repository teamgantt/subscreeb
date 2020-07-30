<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Exception\NotFound;
use Braintree\Gateway as Braintree;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Exceptions\SubscriptionNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\Customer\CustomerStrategy;
use TeamGantt\Subscreeb\Gateways\SubscriptionGatewayInterface;
use TeamGantt\Subscreeb\Models\Subscription;

class BraintreeSubscriptionGateway implements SubscriptionGatewayInterface
{
    /**
     * @var Braintree
     */
    protected Braintree $gateway;

    /**
     * @var CustomerStrategy
     */
    protected CustomerStrategy $customerStrategy;

    /**
     * @var SubscriptionMapper
     */
    protected SubscriptionMapperInterface $subscriptionMapper;

    /**
     * @var CustomerMapper
     */
    protected CustomerMapper $customerMapper;

    /**
     * BraintreeSubscriptionGateway constructor.
     * @param ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->gateway = new Braintree([
            'environment' => $config->getEnvironment(),
            'merchantId' => $config->getMerchantId(),
            'publicKey' => $config->getPublicKey(),
            'privateKey' => $config->getPrivateKey()
        ]);

        $this->customerStrategy = new CustomerStrategy($this->gateway);
        $this->subscriptionMapper = new SubscriptionMapper();
        $this->customerMapper = new CustomerMapper();
    }

    /**
     * {@inheritDoc}
     *
     * @throws CreateSubscriptionException
     */
    public function create(Subscription $subscription): Subscription
    {
        $customer = $this->customerStrategy->savePaymentToken($subscription->getCustomer(), $subscription->getPayment());

        $subscription->setCustomer($customer);

        return $this->createSubscription($subscription);
    }

    /**
     * @inheritDoc
     *
     * @throws SubscriptionNotFoundException
     */
    public function cancel(string $subscriptionId): Subscription
    {
        try {
            $result = $this->gateway
                ->subscription()
                ->cancel($subscriptionId);
        } catch (NotFound $e) {
            throw new SubscriptionNotFoundException("Subscription {$subscriptionId} not found");
        }

        $customer = $this->customerMapper->fromBraintree($result->subscription);

        return $this->subscriptionMapper
            ->fromBraintreeSubscription($result->subscription, $customer);
    }

    protected function createSubscription(Subscription $subscription): Subscription
    {
        $request = $this->subscriptionMapper->toBraintreeRequest($subscription);

        $result = $this->gateway
            ->subscription()
            ->create($request);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        return $this->subscriptionMapper
            ->fromBraintreeSubscription($result->subscription, $subscription->getCustomer());
    }
}
