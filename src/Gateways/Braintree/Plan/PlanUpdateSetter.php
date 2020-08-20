<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Plan;

use Braintree\Gateway;
use TeamGantt\Subscreeb\Exceptions\PlanNotFoundException;
use TeamGantt\Subscreeb\Models\Plan;

class PlanUpdateSetter implements PlanUpdateSetterInterface
{
    /**
     * @var Gateway
     */
    protected Gateway $gateway;

    /**
     * BasePlanUpdater constructor
     *
     * @param Gateway $gateway
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @inheritDoc
     *
     * Fully hydrates a plan when an Id is set
     * Overrides plan price when originally set
     */
    public function set(Plan $plan): Plan
    {
        $planId = $plan->getId();
        $priceOverride = $plan->getPrice();

        $updatedPlan = clone $plan;

        if (!empty($planId)) {
            $updatedPlan = $this->getPlan($planId);
        }

        if (!empty($priceOverride)) {
            $updatedPlan->setPrice($priceOverride);
        }

        return $updatedPlan;
    }

    protected function getPlan(string $planId): Plan
    {
        $plans = $this->gateway->plan()->all();

        foreach ($plans as $plan) {
            if ($plan->id === $planId) {
                // TODO: Start date should not be on the plan but the subscription
                return new Plan($plan->id, '', $plan->price);
            }
        }

        throw new PlanNotFoundException("Plan {$planId} not found");
    }
}
