<?php

namespace TeamGantt\Subscreeb\Models;

class Subscription
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $customerId;

    /**
     * @var string  Example: 2020-01-01
     */
    protected string $startDate;

    /**
     * Subscription constructor.
     * @param string $id
     * @param string $customerId
     * @param string $startDate
     */
    public function __construct(string $id, string $customerId, string $startDate)
    {
        $this->id = $id;
        $this->customerId = $customerId;
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
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }
}
