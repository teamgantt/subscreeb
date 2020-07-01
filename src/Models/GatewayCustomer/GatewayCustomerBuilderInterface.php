<?php

namespace TeamGantt\Subscreeb\Models\GatewayCustomer;

use TeamGantt\Subscreeb\Models\Builders\ModelBuilderInterface;

interface GatewayCustomerBuilderInterface extends ModelBuilderInterface
{
    /**
     * Construct and return a new GetwayCustomer.
     *
     * @return GatewayCustomer
     */
    public function build(): GatewayCustomer;

    /**
     * Specify the id of the customer.
     *
     * @param string $id
     * @return GatewayCustomerBuilderInterface
     */
    public function withId(string $id): GatewayCustomerBuilderInterface;

    /**
     * Specify the payment token associated with the customer.
     *
     * @param string $token
     * @return GatewayCustomerBuilderInterface
     */
    public function withPaymentToken(string $token): GatewayCustomerBuilderInterface;
}
