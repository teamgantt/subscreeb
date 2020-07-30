<?php

namespace TeamGantt\Subscreeb\Tests;

use Dotenv\Dotenv;
use TeamGantt\Subscreeb\Exceptions\SubscriptionNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Braintree\Configuration;
use TeamGantt\Subscreeb\Models\SubscriptionStatus;
use TeamGantt\Subscreeb\Subscriptions\SubscriptionRequestMapper;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 4));
$dotenv->load();

describe('BraintreeSubscriptionGateway', function () {

    beforeAll(function () {
        $this->config = new Configuration(
            $_ENV['BRAINTREE_ENVIRONMENT'],
            $_ENV['BRAINTREE_MERCHANT_ID'],
            $_ENV['BRAINTREE_PUBLIC_KEY'],
            $_ENV['BRAINTREE_PRIVATE_KEY']
        );

        $this->gateway = new BraintreeSubscriptionGateway($this->config);

        $this->faker = \Faker\Factory::create();
    });

    context('canceling a subscription', function () {

        beforeAll(function () {
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
            $deletedSubscription = $this->gateway->cancel($subscriptionId);

            expect($deletedSubscription->getStatus())->toBe(SubscriptionStatus::CANCELED);
            expect($deletedSubscription->getCustomer()->getId())->toBe($this->customer->id);
        });

        it('should throw an exception if the subscription isn not found', function() {
            $sut = function () {
                $this->gateway->cancel('1234abcd');
            };

            expect($sut)->toThrow(new SubscriptionNotFoundException('Subscription 1234abcd not found'));
        });
    });
});

