<?php

namespace TeamGantt\Subscreeb\Gateways;

use TeamGantt\Subscreeb\Models\Subscription\Subscription;

interface SubscriptionGatewayInterface
{
    /**
     * Creates a new subscription.
     *
     * @param Subscription $subscription
     * @return Subscription
     */
    public function create(Subscription $subscription): Subscription;
}
