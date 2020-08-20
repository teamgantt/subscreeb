<?php

namespace TeamGantt\Subscreeb\Models;

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
     * @param string $status
     */
    public function __construct(string $id, Customer $customer, Payment $payment, Plan $plan, array $addOns, array $discounts, string $status = '')
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->payment = $payment;
        $this->plan = $plan;
        $this->addOns = $addOns;
        $this->discounts = $discounts;
        $this->status = $status;
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
}
