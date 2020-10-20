<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway;

use Braintree\CustomerGateway;
use Braintree\PaymentMethodGateway;
use Braintree\PlanGateway;
use Braintree\SubscriptionGateway;
use Psr\Log\LoggerInterface;
use TeamGantt\Subscreeb\Gateways\Braintree\ConfigurationInterface;
use TeamGantt\Subscreeb\Gateways\Braintree\Gateway\Instrumented;

class InstrumentedGateway extends DefaultGateway
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * InstrumentedGateway constructor
     * 
     * @param ConfigurationInterface $config 
     * @param LoggerInterface $logger 
     * @return void 
     */
    public function __construct(ConfigurationInterface $config, LoggerInterface $logger)
    {
        parent::__construct($config);
        $this->logger = $logger;
    }

    /**
     * @return SubscriptionGateway 
     */
    public function subscription(): SubscriptionGateway
    {
        return new Instrumented\SubscriptionGateway($this->logger, $this->getUnderlyingGateway());
    }

    /**
     * @return CustomerGateway 
     */
    public function customer(): CustomerGateway
    {
        return new Instrumented\CustomerGateway($this->logger, $this->getUnderlyingGateway());
    }

    /**
     * @return PaymentMethodGateway 
     */
    public function paymentMethod(): PaymentMethodGateway
    {
        return new Instrumented\PaymentMethodGateway($this->logger, $this->getUnderlyingGateway());
    }

    /**
     * @return PlanGateway 
     */
    public function plan(): PlanGateway
    {
        return new Instrumented\PlanGateway($this->logger, $this->getUnderlyingGateway());
    }
}
