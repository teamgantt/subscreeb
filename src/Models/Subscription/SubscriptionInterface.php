<?php

namespace TeamGantt\Subscreeb\Models\Subscription;

use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;

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
     * @return string
     */
    public function getStartDate(): string;

    /**
     * @return array<AddOn>
     */
    public function getAddOns(): array;

    /**
     * @return array<Addon>
     */
    public function getDiscounts(): array;
}
