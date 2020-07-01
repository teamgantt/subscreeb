<?php

namespace TeamGantt\Subscreeb\Gateways\Configuration;

class BraintreeConfiguration implements BraintreeConfigurationInterface
{
    /**
     * @var string
     */
    protected string $environment;

    /**
     * @var string
     */
    protected string $merchantId;

    /**
     * @var string
     */
    protected string $publicKey;

    /**
     * @var string
     */
    protected string $privateKey;

    public function __construct(string $environment, string $merchantId, string $publicKey, string $privateKey)
    {
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }
}
