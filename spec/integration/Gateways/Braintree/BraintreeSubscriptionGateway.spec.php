<?php

namespace TeamGantt\Subscreeb\Tests;

use Dotenv\Dotenv;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Exceptions\SubscriptionNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Braintree\Configuration;
use TeamGantt\Subscreeb\Models\Subscription;
use TeamGantt\Subscreeb\Models\SubscriptionStatus;
use TeamGantt\Subscreeb\Subscriptions\SubscriptionRequestMapper;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 4));
$dotenv->load();

describe('BraintreeSubscriptionGateway', function () {

    beforeAll(function () {
        $this->faker = \Faker\Factory::create();

        $this->config = new Configuration(
            $_ENV['BRAINTREE_ENVIRONMENT'],
            $_ENV['BRAINTREE_MERCHANT_ID'],
            $_ENV['BRAINTREE_PUBLIC_KEY'],
            $_ENV['BRAINTREE_PRIVATE_KEY']
        );

        $this->gateway = new BraintreeSubscriptionGateway($this->config);

        $this->braintree = new \Braintree\Gateway([
            'environment' => $this->config->getEnvironment(),
            'merchantId' => $this->config->getMerchantId(),
            'publicKey' => $this->config->getPublicKey(),
            'privateKey' => $this->config->getPrivateKey()
        ]);

        $result = $this->braintree->customer()->create([
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email
        ]);

        $this->customer = $result->customer;
    });

    context('getting subscriptions by customer', function () {
        it('should get multiple subscriptions', function() {
            $mapper = new SubscriptionRequestMapper();

            $subscription1 = $mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-mastercard-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-b-monthly',
                ]
            ]);
            $this->gateway->create($subscription1);

            $subscription2 = $mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-mastercard-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-b-yearly',
                ]
            ]);
            $this->gateway->create($subscription2);

            $subscriptions = $this->gateway->getByCustomer($this->customer->id);

            expect($subscriptions[0])->toBeAnInstanceOf(Subscription::class);
            expect($subscriptions)->toHaveLength(2);
        });

        it('should throw an exeption when customer not found', function() {
            $sut = function () {
                $this->gateway->getByCustomer('fake-customer-id');
            };

            expect($sut)->toThrow(new CustomerNotFoundException('Customer with id fake-customer-id does not exist'));
        });
    });

    context('canceling a subscription', function () {
        it('should cancel a subscription', function () {
            $mapper = new SubscriptionRequestMapper();
            $subscription = $mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-mastercard-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-a-monthly',
                ]
            ]);

            $subscription = $this->gateway->create($subscription);
            $subscriptionId = $subscription->getId();
            $canceledSubscription = $this->gateway->cancel($subscriptionId);

            expect($canceledSubscription->getStatus())->toBe(SubscriptionStatus::CANCELED);
            expect($canceledSubscription->getCustomer()->getId())->toBe($this->customer->id);
        });

        it('should throw an exception if the subscription is not found', function() {
            $sut = function () {
                $this->gateway->cancel('1234abcd');
            };

            expect($sut)->toThrow(new SubscriptionNotFoundException('Subscription 1234abcd not found'));
        });
    });
});
