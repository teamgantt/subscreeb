<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway\Instrumented;

use Braintree\Gateway;
use Braintree\PaymentMethodGateway as BaseGateway;
use Psr\Log\LoggerInterface;

class PaymentMethodGateway extends BaseGateway
{
    use LogsMethods;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * PaymentMethodGateway constructor
     * 
     * @param LoggerInterface $logger 
     * @return void 
     */
    public function __construct(LoggerInterface $logger, Gateway $gateway)
    {
        parent::__construct($gateway);
        $this->logger = $logger;
    }

    public function create($attribs)
    {
        return $this->instrumented(__FUNCTION__, $attribs);
    }

    public function find($token)
    {
        return $this->instrumented(__FUNCTION__, $token);
    }

    public function update($token, $attribs)
    {
        return $this->instrumented(__FUNCTION__, $token, $attribs);
    }

    public function delete($token, $options = [])
    {
        return $this->instrumented(__FUNCTION__, $token, $options);
    }

    public function grant($sharedPaymentMethodToken, $attribs = [])
    {
        return $this->instrumented(__FUNCTION__, $sharedPaymentMethodToken, $attribs);
    }

    public function revoke($sharedPaymentMethodToken)
    {
        return $this->instrumented(__FUNCTION__, $sharedPaymentMethodToken);
    }
}
