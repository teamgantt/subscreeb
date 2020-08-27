<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Gateway;
use TeamGantt\Subscreeb\Exceptions\PlanNotFoundException;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

class UpdatedSubscriptionBuilder implements UpdatedSubscriptionBuilderInterface
{
    /**
     * @var Gateway
     */
    protected Gateway $gateway;

    /**
     * @var Subscription
     */
    protected Subscription $subscription;

    /**
     * @inheritDoc
     */
    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    /**
     * UpdatedSubscriptionBuilder constructor
     *
     * @param Gateway $gateway
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @inheritDoc
     */
    public function hydratePlan(string $planId): UpdatedSubscriptionBuilderInterface
    {
        if (!empty($planId)) {
            $hydratedPlan = $this->getPlan($planId);
            $this->subscription->setPlan($hydratedPlan);
            $this->subscription->setPrice($hydratedPlan->getPrice());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPriceOverride(float $priceOverride): UpdatedSubscriptionBuilderInterface
    {
        if (!empty($priceOverride)) {
            $this->subscription->setPrice($priceOverride);
        }

        return $this;
    }

    protected function getPlan(string $planId): Plan
    {
        $plans = $this->gateway->plan()->all();

        foreach ($plans as $plan) {
            if ($plan->id === $planId) {
                return new Plan($plan->id, $plan->price);
            }
        }

        throw new PlanNotFoundException("Plan {$planId} not found");
    }

    /**
     * @inheritDoc
     */
    public function setSubscription(Subscription $subscription): UpdatedSubscriptionBuilderInterface
    {
        $this->subscription = clone $subscription;

        return $this;
    }
}
