<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway;

use Braintree\CustomerGateway;
use Braintree\PaymentMethodGateway;
use Braintree\PlanGateway;
use Braintree\SubscriptionGateway;

interface BraintreeGatewayInterface
{
    /**
     * @return SubscriptionGateway
     */
    public function subscription(): SubscriptionGateway;

    /**
     * @return CustomerGateway
     */
    public function customer(): CustomerGateway;

    /**
     * @return PaymentMethodGateway
     */
    public function paymentMethod(): PaymentMethodGateway;

    /**
     * @return PlanGateway
     */
    public function plan(): PlanGateway;
}
