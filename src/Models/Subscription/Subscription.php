<?php

namespace TeamGantt\Subscreeb\Models\Subscription;

class Subscription implements SubscriptionInterface
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
     * @var array
     */
    protected array $addOns;

    /**
     * @var array
     */
    protected array $discounts;

    /**
     * Subscription constructor.
     * @param string $id
     * @param string $customerId
     * @param string $startDate
     * @param array $addOns
     * @param array $discounts
     */
    public function __construct(string $id, string $customerId, string $startDate, array $addOns, array $discounts)
    {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->startDate = $startDate;
        $this->addOns = $addOns;
        $this->discounts = $discounts;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @inheritDoc
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @inheritDoc
     */
    public function getAddOns(): array
    {
        return $this->addOns;
    }

    /**
     * @inheritDoc
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }
}
