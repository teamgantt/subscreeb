<?php

use Braintree\Customer;
use Braintree\CustomerGateway;
use Braintree\Exception\NotFound;
use Kahlan\Plugin\Double;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Braintree\Gateway\BraintreeGatewayInterface;

describe('BraintreeSubscriptionGateway', function () {
    context('getting subscriptions by customer', function () {
        beforeEach(function () {
            $this->customerGateway = Double::instance(['extends' => CustomerGateway::class, 'methods' => ['__construct']]);
            $this->gateway = Double::instance(['implements' => BraintreeGatewayInterface::class]);
            $this->subscriptionGateway = new BraintreeSubscriptionGateway($this->gateway);
            allow($this->gateway)->toReceive('customer')->andReturn($this->customerGateway);
        });

        it('should throw an exception if a NotFound exception is encountered', function () {
            allow($this->customerGateway)->toReceive('find')->with('123')->andRun(function () {
                throw new NotFound("UH OH");
            });

            $sut = function () {
                $this->subscriptionGateway->getByCustomer('123');
            };

            expect($sut)->toThrow(new CustomerNotFoundException());
        });

        it('should merge all payment methods into a collection of customer subscriptions', function () {
            $fixture = file_get_contents(__DIR__ . '/fixtures/customer.json');
            $attributes = json_decode($fixture, true);
            $customer = Customer::factory($attributes);

            allow($this->customerGateway)->toReceive('find')->with('629290106')->andReturn($customer);

            $subscriptions = $this->subscriptionGateway->getByCustomer('629290106');

            print_r($subscriptions);
        });
    });
});
