<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getEnvironment(): string;

    /**
     * @return string
     */
    public function getMerchantId(): string;

    /**
     * @return string
     */
    public function getPublicKey(): string;

    /**
     * @return string
     */
    public function getPrivateKey(): string;
}
