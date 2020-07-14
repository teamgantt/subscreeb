<?php

namespace TeamGantt\Subscreeb\Subscriptions;

use TeamGantt\Subscreeb\Gateways\SubscriptionGatewayInterface;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

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
     * @return SubscriptionInterface
     */
    public function create(array $request): SubscriptionInterface
    {
        $subscription = $this->requestMapper->map($request);

        return $this->gateway->create($subscription);
    }
}
