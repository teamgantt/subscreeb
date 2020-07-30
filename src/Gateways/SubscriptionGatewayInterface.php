<?php

namespace TeamGantt\Subscreeb\Gateways;

use TeamGantt\Subscreeb\Models\Subscription;

interface SubscriptionGatewayInterface
{
    /**
     * Creates a new subscription.
     *
     * @param Subscription $subscription
     * @return Subscription
     */
    public function create(Subscription $subscription): Subscription;

    /**
     * Cancels an existing subscription.
     *
     * @param string $subscriptionId
     * @return Subscription
     */
    public function cancel(string $subscriptionId): Subscription;
}
