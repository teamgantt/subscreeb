<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway;

use Braintree\CustomerGateway;
use Braintree\Gateway;
use Braintree\PaymentMethodGateway;
use Braintree\PlanGateway;
use Braintree\SubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Braintree\ConfigurationInterface;

class DefaultGateway implements BraintreeGatewayInterface
{
    /**
     * @var Gateway
     */
    protected Gateway $gateway;

    /**
     * DefaultGateway constructor
     *
     * @param ConfigurationInterface $config
     * @return void
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->gateway = new Gateway([
            'environment' => $config->getEnvironment(),
            'merchantId' => $config->getMerchantId(),
            'publicKey' => $config->getPublicKey(),
            'privateKey' => $config->getPrivateKey()
        ]);
    }

    /**
     * @return SubscriptionGateway
     */
    public function subscription(): SubscriptionGateway
    {
        return $this->gateway->subscription();
    }

    /**
     * @return CustomerGateway
     */
    public function customer(): CustomerGateway
    {
        return $this->gateway->customer();
    }

    /**
     * @return PaymentMethodGateway
     */
    public function paymentMethod(): PaymentMethodGateway
    {
        return $this->gateway->paymentMethod();
    }

    /**
     * @return PlanGateway
     */
    public function plan(): PlanGateway
    {
        return $this->gateway->plan();
    }
}
