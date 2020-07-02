<?php

namespace TeamGantt\Subscreeb\Models\Discount;

class DiscountCollection
{
    /**
     * @var array|Discount[]
     */
    protected array $discounts;

    public function __construct(Discount ...$discounts)
    {
        $this->discounts = $discounts;
    }

    /**
     * @return array|Discount[]
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    /**
     * @param Discount $discount
     */
    public function addDiscount(Discount $discount): void
    {
        $this->discounts[] = $discount;
    }
}
