<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Customer;

use Braintree\Gateway;

abstract class BaseStrategy implements StrategyInterface
{
    /**
     * @var Gateway
     */
    protected Gateway $gateway;

    /**
     * BaseStrategy constructor
     *
     * @param Gateway $gateway
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }
}
