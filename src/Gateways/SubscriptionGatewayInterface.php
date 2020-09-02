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

    /**
     * Get subscriptions for a given customer id.
     *
     * @param string $customerId
     * @return array<Subscription>
     */
    public function getByCustomer(string $customerId): array;

    /**
     * Updates an existing subscription
     *
     * @param Subscription $subscriptionUpdates A subscription model consisting of updates to send to the Gateway
     * @return Subscription
     */
    public function update(Subscription $subscriptionUpdates): Subscription;
}
