<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Customer;

use TeamGantt\Subscreeb\Gateways\Braintree\Gateway\BraintreeGatewayInterface;

abstract class BaseStrategy implements StrategyInterface
{
    /**
     * @var BraintreeGatewayInterface
     */
    protected BraintreeGatewayInterface $gateway;

    /**
     * BaseStrategy constructor
     *
     * @param BraintreeGatewayInterface $gateway
     */
    public function __construct(BraintreeGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }
}
