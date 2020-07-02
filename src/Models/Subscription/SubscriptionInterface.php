<?php

namespace TeamGantt\Subscreeb\Models\Subscription;

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
     * @return array
     */
    public function getAddOns(): array;

    /**
     * @return array
     */
    public function getDiscounts(): array;
}
