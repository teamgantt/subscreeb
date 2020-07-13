<?php

namespace TeamGantt\Subscreeb\Models\Subscription;

use TeamGantt\Subscreeb\Models\Customer;

class Subscription implements SubscriptionInterface
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var Customer
     */
    protected Customer $customer;

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
     * @param Customer $customer
     * @param string $startDate
     * @param array $addOns
     * @param array $discounts
     */
    public function __construct(string $id, Customer $customer, string $startDate, array $addOns, array $discounts)
    {
        $this->id = $id;
        $this->customer = $customer;
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
    public function getCustomer(): Customer
    {
        return $this->customer;
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
