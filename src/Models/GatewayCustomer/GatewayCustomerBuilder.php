<?php

namespace TeamGantt\Subscreeb\Models\GatewayCustomer;

use TeamGantt\Subscreeb\Models\Builders\BaseModelBuilder;

class GatewayCustomerBuilder extends BaseModelBuilder implements GatewayCustomerBuilderInterface
{
    /**
     * @var string
     */
    protected string $id = '';

    /**
     * @var string
     */
    protected string $paymentToken = '';

    /**
     * {@inheritDoc}
     */
    public function build(): GatewayCustomer
    {
        $gatewayCustomer = new GatewayCustomer($this->id, $this->paymentToken);

        $this->reset();

        return $gatewayCustomer;
    }

    /**
     * {@inheritDoc}
     */
    public function withId(string $id): GatewayCustomerBuilderInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withPaymentToken(string $token): GatewayCustomerBuilderInterface
    {
        $this->paymentToken = $token;

        return $this;
    }
}
