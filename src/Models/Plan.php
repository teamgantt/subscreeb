<?php

namespace TeamGantt\Subscreeb\Models;

class Plan
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $startDate;

    /**
     * @var float
     */
    protected float $price;

    /**
     * Plan constructor.
     * @param string $id
     * @param string $startDate
     * @param float $price
     */
    public function __construct(string $id, string $startDate, float $price)
    {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->price = $price;
    }

    public function equals(Plan $plan): bool
    {
        return $this->id === $plan->getId();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}
