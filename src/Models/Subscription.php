<?php

namespace TeamGantt\Subscreeb\Models;

use TeamGantt\Subscreeb\Exceptions\NegativePriceException;

class Subscription
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
     * @var array
     */
    protected array $addOns;

    /**
     * @var array
     */
    protected array $discounts;

    /**
     * @var Payment
     */
    protected Payment $payment;

    /**
     * @var Plan
     */
    protected Plan $plan;

    /**
     * @var float|null
     */
    protected ?float $price;

    /**
     * @var string
     */
    protected string $startDate;

    /**
     * @var string
     */
    protected string $status;

    /**
     * Subscription constructor.
     * @param string $id
     * @param Customer $customer
     * @param Payment $payment
     * @param Plan $plan
     * @param array $addOns
     * @param array $discounts
     * @param float|null $price
     * @param string $startDate
     * @param string $status
     * @throws NegativePriceException
     */
    public function __construct(string $id, Customer $customer, Payment $payment, Plan $plan, array $addOns, array $discounts, ?float $price, string $startDate, string $status = '')
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->payment = $payment;
        $this->plan = $plan;
        $this->addOns = $addOns;
        $this->discounts = $discounts;
        $this->price = $price;
        $this->startDate = $startDate;
        $this->status = $status;

        if ($price < 0) {
            throw new NegativePriceException('Price cannot be a negative value');
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return Payment
     */
    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @return Plan
     */
    public function getPlan(): Plan
    {
        return $this->plan;
    }

    /**
     * @return array
     */
    public function getAddOns(): array
    {
        return $this->addOns;
    }

    /**
     * @return array
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @param Plan $plan
     */
    public function setPlan(Plan $plan): void
    {
        $this->plan = $plan;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}
