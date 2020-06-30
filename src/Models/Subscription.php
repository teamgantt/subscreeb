<?php

namespace TeamGantt\Subscreeb\Models;

class Subscription
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var string
     */
    private string $gatewayCustomerId;

    /**
     * Subscription constructor.
     * @param string $id
     * @param string $gatewayCustomerId
     */
    public function __construct(string $id, string $gatewayCustomerId)
    {
        $this->id = $id;
        $this->gatewayCustomerId = $gatewayCustomerId;
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
    public function getGatewayCustomerId(): string
    {
        return $this->gatewayCustomerId;
    }
}
