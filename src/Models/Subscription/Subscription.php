<?php

namespace TeamGantt\Subscreeb\Models\Subscription;

use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;

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
     * Subscription constructor.
     * @param string $id
     * @param Customer $customer
     * @param Payment $payment
     * @param Plan $plan
     * @param array $addOns
     * @param array $discounts
     */
    public function __construct(string $id, Customer $customer, Payment $payment, Plan $plan, array $addOns, array $discounts)
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->payment = $payment;
        $this->plan = $plan;
        $this->addOns = $addOns;
        $this->discounts = $discounts;
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayment(): Payment
    {
        return $this->getPayment();
    }

    /**
     * {@inheritDoc}
     */
    public function getPlan(): Plan
    {
        return $this->plan;
    }

    /**
     * {@inheritDoc}
     */
    public function getAddOns(): array
    {
        return $this->addOns;
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }
}
