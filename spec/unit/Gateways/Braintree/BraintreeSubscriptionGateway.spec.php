<?php

use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\CustomerGateway;
use Braintree\Exception\NotFound;
use Braintree\PaymentMethodGateway;
use Braintree\PaymentMethodParser;
use Kahlan\Plugin\Double;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Braintree\Gateway\BraintreeGatewayInterface;

describe('BraintreeSubscriptionGateway', function () {
    context('getting subscriptions by customer', function () {
        beforeEach(function () {
            $this->gateway = Double::instance(['implements' => BraintreeGatewayInterface::class]);

            $this->customerGateway = Double::instance(['extends' => CustomerGateway::class, 'methods' => ['__construct']]);
            $this->paymentMethodGateway = Double::instance(['extends' => PaymentMethodGateway::class, 'methods' => ['__construct']]);

            allow($this->gateway)->toReceive('customer')->andReturn($this->customerGateway);
            allow($this->gateway)->toReceive('paymentMethod')->andReturn($this->paymentMethodGateway);

            $this->subscriptionGateway = new BraintreeSubscriptionGateway($this->gateway);
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
            $attributes = $this->loadFixture('unit/fixtures/customer.json');
            $customer = Customer::factory($attributes);
            allow($this->customerGateway)->toReceive('find')->with('114657386')->andReturn($customer);

            $attributes = $this->loadFixture('unit/fixtures/payment-method.json');
            $paymentMethod = CreditCard::factory($attributes);
            allow($this->paymentMethodGateway)->toReceive('find')->andReturn($paymentMethod);

            $subscriptions = $this->subscriptionGateway->getByCustomer('114657386');

            expect($subscriptions)->toHaveLength(1);
            $subscription = $subscriptions[0];

            expect($subscription->getId())->toEqual('63m426');
            expect($subscription->getPrice())->toEqual(10);
            expect($subscription->getStartDate())->toEqual('2020-10-19');
            expect($subscription->getStatus())->toEqual('active');
            
            $customer = $subscription->getCustomer();
            expect($customer->getId())->toEqual('114657386');
            expect($customer->getFirstName())->toEqual('Jeremie');
            expect($customer->getLastName())->toEqual("O'Conner");
            expect($customer->getEmailAddress())->toEqual('lcasper@hotmail.com');

            expect($subscription->getAddOns())->toHaveLength(0);
            expect($subscription->getDiscounts())->toHaveLength(0);

            $payment = $subscription->getPayment();
            expect($payment->getToken())->toEqual('hrvpfdb');

            $plan = $subscription->getPlan();
            expect($plan->getId())->toEqual('test-plan-b-monthly');
            expect($plan->getPrice())->toEqual(10);
        });
    });
});
