<?php

namespace TeamGantt\Subscreeb\Tests;

use Dotenv\Dotenv;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Exceptions\NegativePriceException;
use TeamGantt\Subscreeb\Exceptions\PlanNotFoundException;
use TeamGantt\Subscreeb\Exceptions\SubscriptionNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Braintree\Configuration;
use TeamGantt\Subscreeb\Gateways\Braintree\UpdateSubscriptionException;
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
        it('should get multiple subscriptions', function () {
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

        it('should throw an exception when customer not found', function () {
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

        it('should throw an exception if the subscription is not found', function () {
            $sut = function () {
                $this->gateway->cancel('1234abcd');
            };

            expect($sut)->toThrow(new SubscriptionNotFoundException('Subscription 1234abcd not found'));
        });
    });

    context('updating subscription', function () {

        beforeAll(function () {
            $this->mapper = new SubscriptionRequestMapper();

            $subscriptionRequest = $this->mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-discover-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-a-monthly',
                ]
            ]);

            $this->subscription = $this->gateway->create($subscriptionRequest);
        });

        it('should update to a plan with the same billing cycle', function () {
            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $this->subscription->getId(),
                'plan' => [
                    'id' => 'test-plan-b-monthly',
                ]
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);

            expect($updatedSubscription->getPlan()->getId())->toBe('test-plan-b-monthly');
            expect($updatedSubscription->getPrice())->toBe(10.00);
        });

        it('should update the base price when set', function () {
            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $this->subscription->getId(),
                'price' => 20.00,
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);

            expect($updatedSubscription->getPrice())->toBe(20.00);
        });

        it('should override price when plan id set', function() {
            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $this->subscription->getId(),
                'price' => 50.00,
                'plan' => [
                    'id' => 'test-plan-b-monthly',
                ]
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);

            expect($updatedSubscription->getPrice())->toBe(50.00);
        });

        it('should support 0 based price overrides', function () {
            $subscription = $this->mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-amex-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-c-monthly',
                ],
            ]);

            $subscription = $this->gateway->create($subscription);

            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $subscription->getId(),
                'price' => 0,
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);

            expect($updatedSubscription->getPrice())->toBe(0.00);
        });

        it('should not override price when not set', function () {
            $subscription = $this->mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-dinersclub-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-a-monthly',
                ],
            ]);

            $subscription = $this->gateway->create($subscription);

            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $subscription->getId(),
                'addOns' => [
                    [
                        'id' => 'test-plan-a-monthly-user',
                        'quantity' => 5
                    ]
                ],
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);

            expect($updatedSubscription->getPrice())->toBe(5.00);
        });

        it('should update addons quantity when the plan is the same', function () {
            $subscription = $this->mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-discover-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-b-monthly',
                ],
                'addOns' => [
                    [
                        'id' => 'test-plan-b-monthly-user',
                        'quantity' => 5
                    ]
                ]
            ]);

            $subscription = $this->gateway->create($subscription);

            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $subscription->getId(),
                'addOns' => [
                    [
                        'id' => 'test-plan-b-monthly-user',
                        'quantity' => 10
                    ]
                ]
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);
            $addOns = $updatedSubscription->getAddons();

            expect($addOns[0]->getId())->toBe('test-plan-b-monthly-user');
            expect($addOns[0]->getQuantity())->toBe(10);
        });

        it('should update addons when changing the plan', function () {
            $mapper = new SubscriptionRequestMapper();
            $subscription = $mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-discover-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-c-monthly',
                ],
                'addOns' => [
                    [
                        'id' => 'test-plan-c-monthly-user',
                        'quantity' => 5
                    ]
                ]
            ]);

            $subscription = $this->gateway->create($subscription);

            $updatedSubscription = $mapper->map([
                'subscriptionId' => $subscription->getId(),
                'plan' => [
                    'id' => 'test-plan-a-monthly',
                ],
                'addOns' => [
                    [
                        'id' => 'test-plan-a-monthly-user',
                        'quantity' => 10
                    ]
                ]
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);
            $addOns = $updatedSubscription->getAddons();

            expect($addOns[0]->getId())->toBe('test-plan-a-monthly-user');
            expect($addOns[0]->getQuantity())->toBe(10);
        });

        it('should not update the plan when plan hasn\'t changed', function () {
            $subscription = $this->mapper->map([
                'customer' => [
                    'id' => $this->customer->id,
                ],
                'payment' => [
                    'nonce' => 'fake-valid-amex-nonce'
                ],
                'plan' => [
                    'id' => 'test-plan-a-monthly',
                ]
            ]);

            $this->gateway->create($subscription);

            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $this->subscription->getId(),
                'plan' => [
                    'id' => 'test-plan-a-monthly',
                ]
            ]);

            $updatedSubscription = $this->gateway->update($updatedSubscription);

            expect($updatedSubscription->getPlan()->getId())->toBe('test-plan-a-monthly');
        });

        it('should throw an exception when price is negative', function () {
            $sut = function () {
                $this->mapper->map([
                    'subscriptionId' => $this->subscription->getId(),
                    'price' => -20.00,
                ]);
            };

            expect($sut)->toThrow(new NegativePriceException('Price cannot be a negative value'));
        });

        it('should throw an exception when changing a plan with a different billing cycle', function () {
            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $this->subscription->getId(),
                'plan' => [
                    'id' => 'test-plan-a-yearly',
                ]
            ]);

            $sut = function () use ($updatedSubscription) {
                $this->gateway->update($updatedSubscription);
            };

            expect($sut)->toThrow(new UpdateSubscriptionException('Cannot update subscription to a plan with a different billing frequency.'));
        });

        it('should throw an exception when plan doesn\'t exist', function() {
            $updatedSubscription = $this->mapper->map([
                'subscriptionId' => $this->subscription->getId(),
                'plan' => [
                    'id' => 'fake-plan-id',
                ]
            ]);

            $sut = function () use ($updatedSubscription) {
                $this->gateway->update($updatedSubscription);
            };

            expect($sut)->toThrow(new PlanNotFoundException('Plan fake-plan-id not found'));
        });
    });
});
