<?php

namespace TeamGantt\Subscreeb\Models\GatewayCustomer;

class GatewayCustomer
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $paymentToken;

    /**
     * GatewayCustomer constructor.
     * @param string $id
     * @param string $paymentToken
     */
    public function __construct(string $id, string $paymentToken = "")
    {
        $this->id = $id;
        $this->paymentToken = $paymentToken;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPaymentToken(): string
    {
        return $this->paymentToken;
    }
}
