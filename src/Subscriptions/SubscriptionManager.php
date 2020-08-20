<?php

namespace TeamGantt\Subscreeb\Subscriptions;

use TeamGantt\Subscreeb\Gateways\SubscriptionGatewayInterface;
use TeamGantt\Subscreeb\Models\Subscription;

class SubscriptionManager
{
    /**
     * @var SubscriptionGatewayInterface
     */
    protected SubscriptionGatewayInterface $gateway;

    /**
     * @var SubscriptionRequestMapper
     */
    protected SubscriptionRequestMapper $requestMapper;

    /**
     * SubscriptionManager constructor.
     * @param SubscriptionGatewayInterface $gateway
     */
    public function __construct(SubscriptionGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
        $this->requestMapper = new SubscriptionRequestMapper();
    }

    /**
     * @param array $request
     * @return Subscription
     */
    public function create(array $request): Subscription
    {
        $subscription = $this->requestMapper->map($request);

        return $this->gateway->create($subscription);
    }

    /**
     * @param string $subscriptionId
     * @return Subscription
     */
    public function cancel(string $subscriptionId): Subscription
    {
        return $this->gateway->cancel($subscriptionId);
    }

    /**
     * @param string $customerId
     * @return array<Subscription>
     */
    public function getByCustomer(string $customerId): array
    {
        return $this->gateway->getByCustomer($customerId);
    }

    /**
     * @param array $request
     * @return Subscription
     */
    public function update(array $request): Subscription
    {
        $subscription = $this->requestMapper->map($request);

        return $this->gateway->update($subscription);
    }
}
