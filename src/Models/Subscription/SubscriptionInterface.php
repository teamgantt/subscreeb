<?php

namespace TeamGantt\Subscreeb\Models\Subscription;

use TeamGantt\Subscreeb\Models\AddOn;

interface SubscriptionInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getCustomerId(): string;

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
