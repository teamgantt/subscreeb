<?php


namespace TeamGantt\Subscreeb\Tests;

use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Messages\CreateSubscriptionRequest;

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

describe('BraintreeSubscriptionGateway', function () {

    $this->gateway = null;

    beforeAll(function () {
        $this->gateway = new BraintreeSubscriptionGateway(
            $_ENV['BRAINTREE_ENVIRONMENT'],
            $_ENV['BRAINTREE_MERCHANT_ID'],
            $_ENV['BRAINTREE_PUBLIC_KEY'],
            $_ENV['BRAINTREE_PRIVATE_KEY']
        );
    });

    context('when customer is new', function () {
        it('should create a new customer when ', function () {
            $customer = new Customer('', 'tyson@teamgantt.com', 'Tony', 'Clifton');
            $request = new CreateSubscriptionRequest($customer, '401m', 'fake-valid-visa-nonce');
            $this->gateway->create($request);
        });
    });

    context('when existing customer cannot be found', function () {
        it('should throw a not found exception', function () {
            $customer = new Customer('a-customer-that-doesnt-exist', 'fake@fake.com', '', '');
            $request = new CreateSubscriptionRequest($customer, '401m', 'fake-valid-visa-nonce');

            $sut = function () use ($request) {
                $this->gateway->create($request);
            };

            expect($sut)->toThrow(new CustomerNotFoundException('customer with id a-customer-that-doesnt-exist not found'));
        });
    });
});

