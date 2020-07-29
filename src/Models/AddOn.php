<?php

namespace TeamGantt\Subscreeb\Models;

class AddOn
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var int
     */
    protected int $quantity;

    /**
     * AddOn constructor.
     * @param string $id
     * @param int $quantity
     */
    public function __construct(string $id, int $quantity)
    {
        $this->id = $id;
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
