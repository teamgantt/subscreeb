<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway\Instrumented;

use Braintree\Gateway;
use Braintree\CustomerGateway as BaseGateway;
use Psr\Log\LoggerInterface;

class CustomerGateway extends BaseGateway
{
     use LogsMethods;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CustomerGateway constructor
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

    public function fetch($query, $ids)
    {
        return $this->instrumented(__FUNCTION__, $query, $ids);
    }

    public function create($attribs = [])
    {
        return $this->instrumented(__FUNCTION__, $attribs);
    }

    public function createNoValidate($attribs = [])
    {
        return $this->instrumented(__FUNCTION__, $attribs);
    }

    public function find($id, $associationFilterId = null)
    {
        return $this->instrumented(__FUNCTION__, $id, $associationFilterId);
    }

    public function credit($customerId, $transactionAttribs)
    {
        return $this->instrumented(__FUNCTION__, $customerId, $transactionAttribs);
    }

    public function creditNoValidate($customerId, $transactionAttribs)
    {
        return $this->instrumented(__FUNCTION__, $customerId, $transactionAttribs);
    }

    public function delete($customerId)
    {
        return $this->instrumented(__FUNCTION__, $customerId);
    }

    public function sale($customerId, $transactionAttribs)
    {
        return $this->instrumented(__FUNCTION__, $customerId, $transactionAttribs);
    }

    public function saleNoValidate($customerId, $transactionAttribs)
    {
        return $this->instrumented(__FUNCTION__, $customerId, $transactionAttribs);
    }

    public function search($query)
    {
        return $this->instrumented(__FUNCTION__, $query);
    }

    public function update($customerId, $attributes)
    {
        return $this->instrumented(__FUNCTION__, $customerId, $attributes);
    }

    public function updateNoValidate($customerId, $attributes)
    {
        return $this->instrumented(__FUNCTION__, $customerId, $attributes);
    }
}
