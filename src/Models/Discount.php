<?php

namespace TeamGantt\Subscreeb\Models;

class Discount
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var float
     */
    protected float $amount;

    /**
     * @var int
     */
    protected int $billingCycles;

    public function __construct(string $id, float $amount, int $billingCycles)
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->billingCycles = $billingCycles;
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
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getBillingCycles(): int
    {
        return $this->billingCycles;
    }
}
