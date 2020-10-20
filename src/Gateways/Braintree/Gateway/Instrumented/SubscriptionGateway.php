<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway\Instrumented;

use Braintree\Gateway;
use Braintree\SubscriptionGateway as BaseGateway;
use Psr\Log\LoggerInterface;

class SubscriptionGateway extends BaseGateway
{
    use LogsMethods;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SubscriptionGateway constructor
     * 
     * @param LoggerInterface $logger 
     * @return void 
     */
    public function __construct(LoggerInterface $logger, Gateway $gateway)
    {
        parent::__construct($gateway);
        $this->logger = $logger;
    }


    public function create($attributes)
    {
        return $this->instrumented(__FUNCTION__, $attributes);
    }

    public function find($id)
    {
        return $this->instrumented(__FUNCTION__, $id);
    }

    public function search($query)
    {
        return $this->instrumented(__FUNCTION__, $query);
    }

    public function fetch($query, $ids)
    {
        return $this->instrumented(__FUNCTION__, $query, $ids);
    }

    public function update($subscriptionId, $attributes)
    {
        return $this->instrumented(__FUNCTION__, $subscriptionId, $attributes);
    }

    public function retryCharge($subscriptionId, $amount = null, $submitForSettlement = false)
    {
        return $this->instrumented(__FUNCTION__, $subscriptionId, $amount, $submitForSettlement);
    }

    public function cancel($subscriptionId)
    {
        return $this->instrumented(__FUNCTION__, $subscriptionId);
    }
}
