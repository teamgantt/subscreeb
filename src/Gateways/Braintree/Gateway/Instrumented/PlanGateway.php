<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway\Instrumented;

use Braintree\Gateway;
use Braintree\PlanGateway as BaseGateway;
use Psr\Log\LoggerInterface;

class PlanGateway extends BaseGateway
{
    use LogsMethods;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * PlanGateway constructor
     * 
     * @param LoggerInterface $logger 
     * @return void 
     */
    public function __construct(LoggerInterface $logger, Gateway $gateway)
    {
        parent::__construct($gateway);
        $this->logger = $logger;
    }

    public function all()
    {
        return $this->instrumented(__FUNCTION__);
    }
}
