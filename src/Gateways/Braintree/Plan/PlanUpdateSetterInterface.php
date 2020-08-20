<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Plan;

use TeamGantt\Subscreeb\Models\Plan;

interface PlanUpdateSetterInterface
{
    /**
     * Sets updated plan attributes based on the incoming request data
     * @param Plan $plan
     * @return Plan
     */
    public function set(Plan $plan): Plan;
}
