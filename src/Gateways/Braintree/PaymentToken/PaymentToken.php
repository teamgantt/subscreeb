<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\PaymentToken;

use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomer;

class PaymentToken
{
    /**
     * @var string
     */
    protected string $token;

    /**
     * @var GatewayCustomer
     */
    protected GatewayCustomer $customer;

    /**
     * PaymentToken constructor
     *
     * @param string $token
     * @param GatewayCustomer $customer
     */
    public function __construct(string $token, GatewayCustomer $customer)
    {
        $this->token = $token;
        $this->customer = $customer;
    }

    /**
     * Get the value of customer
     */
    public function getCustomer(): GatewayCustomer
    {
        return $this->customer;
    }

    /**
     * Get the string value of the token
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
