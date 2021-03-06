<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Exception\NotFound;
use Braintree\Gateway as Braintree;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
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
     * @var SubscriptionMapperInterface
     */
    protected SubscriptionMapperInterface $subscriptionMapper;

    /**
     * @var UpdatedSubscriptionBuilder
     */
    protected UpdatedSubscriptionBuilder $updatedSubscriptionBuilder;

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
        $this->updatedSubscriptionBuilder = new UpdatedSubscriptionBuilder($this->gateway);
        $this->subscriptionMapper = new SubscriptionMapper($this->gateway);
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

        return $this->subscriptionMapper->fromBraintreeSubscription($result->subscription);
    }

    /**
     * @inheritDoc
     */
    public function getByCustomer(string $customerId): array
    {
        $customer = null;

        try {
            $customer = $this->gateway->customer()->find($customerId);
        } catch (NotFound $e) {
            throw new CustomerNotFoundException("Customer with id {$customerId} does not exist");
        }

        $subscriptions = [];
        // @phpstan-ignore-next-line
        foreach ($customer->paymentMethods as $paymentMethod) {
            // @phpstan-ignore-next-line
            $subscriptions = array_merge($subscriptions, $paymentMethod->subscriptions);
        }

        return array_map(function ($subscription) {
            return $this->subscriptionMapper->fromBraintreeSubscription($subscription);
        }, $subscriptions);
    }

    /**
     * @inheritDoc
     */
    public function update(Subscription $subscriptionUpdates): Subscription
    {
        $subscriptionUpdates = $this->updatedSubscriptionBuilder
            ->setSubscription($subscriptionUpdates)
            ->hydratePlan($subscriptionUpdates->getPlan()->getId())
            ->setPriceOverride($subscriptionUpdates->getPrice())
            ->getSubscription();

        $existingSubscription = $this->getSubscription($subscriptionUpdates->getId());
        $hasPlanChanged = !$existingSubscription->getPlan()->equals($subscriptionUpdates->getPlan());

        return $this->updateSubscription($subscriptionUpdates, $hasPlanChanged);
    }

    protected function createSubscription(Subscription $subscription): Subscription
    {
        $request = $this->subscriptionMapper->toBraintreeCreateRequest($subscription);

        $result = $this->gateway
            ->subscription()
            ->create($request);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        return $this->subscriptionMapper->fromBraintreeSubscription($result->subscription);
    }

    protected function getSubscription(string $subscriptionId): Subscription
    {
        $subscription = null;

        try {
            $subscription = $this->gateway->subscription()->find($subscriptionId);
        } catch (NotFound $e) {
            throw new SubscriptionNotFoundException("Subscription {$subscriptionId} not found");
        }

        return $this->subscriptionMapper->fromBraintreeSubscription($subscription);
    }

    protected function updateSubscription(Subscription $subscriptionUpdates, bool $hasPlanChanged): Subscription
    {
        $request = $this->subscriptionMapper->toBraintreeUpdateRequest($subscriptionUpdates, $hasPlanChanged);

        $result = $this->gateway
            ->subscription()
            ->update($subscriptionUpdates->getId(), $request);

        if (!$result->success) {
            throw new UpdateSubscriptionException($result->message);
        }

        return $this->subscriptionMapper->fromBraintreeSubscription($result->subscription);
    }
}
