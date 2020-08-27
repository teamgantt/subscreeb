<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use TeamGantt\Subscreeb\Models\Subscription;

interface UpdatedSubscriptionBuilderInterface
{
    /**
     * Return the subscription instance
     *
     * @return Subscription
     */
    public function getSubscription(): Subscription;

    /**
     * Sets a fully hydrated plan on the subscription
     *
     * @param string $planId
     * @return UpdatedSubscriptionBuilderInterface
     */
    public function hydratePlan(string $planId): UpdatedSubscriptionBuilderInterface;


    /**
     * Sets subscription price override
     *
     * @param float $priceOverride
     * @return UpdatedSubscriptionBuilderInterface
     */
    public function setPriceOverride(float $priceOverride): UpdatedSubscriptionBuilderInterface;

    /**
     * Sets a subscription instance
     *
     * @param Subscription $subscription
     * @return UpdatedSubscriptionBuilderInterface
     */
    public function setSubscription(Subscription $subscription): UpdatedSubscriptionBuilderInterface;
}
