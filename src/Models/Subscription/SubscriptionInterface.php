<?php

namespace TeamGantt\Subscreeb\Models\Subscription;

use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;

interface SubscriptionInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return Customer
     */
    public function getCustomer(): Customer;

    /**
     * @return Payment
     */
    public function getPayment(): Payment;

    /**
     * @return Plan
     */
    public function getPlan(): Plan;

    /**
     * @return array<AddOn>
     */
    public function getAddOns(): array;

    /**
     * @return array<Addon>
     */
    public function getDiscounts(): array;
}
