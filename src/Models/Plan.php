<?php

namespace TeamGantt\Subscreeb\Models;

class Plan
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var float
     */
    protected float $price;

    /**
     * Plan constructor.
     * @param string $id
     * @param float $price
     */
    public function __construct(string $id, float $price = 0.00)
    {
        $this->id = $id;
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
}
