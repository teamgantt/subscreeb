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
     * @var string  Example: 2020-01-01
     */
    protected string $startDate;

    /**
     * Subscription constructor.
     * @param string $id
     * @param string $gatewayCustomerId
     * @param string $startDate
     */
    public function __construct(string $id, string $gatewayCustomerId, string $startDate)
    {
        $this->id = $id;
        $this->gatewayCustomerId = $gatewayCustomerId;
        $this->startDate = $startDate;
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

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }
}
