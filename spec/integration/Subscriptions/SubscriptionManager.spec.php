<?php

namespace TeamGantt\Subscreeb\Tests;

use Carbon\Carbon;
use Dotenv\Dotenv;
use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Configuration\BraintreeConfiguration;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomerBuilder;
use TeamGantt\Subscreeb\Subscriptions\SubscriptionManager;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

describe('SubscriptionManager', function () {

    beforeAll(function () {
        $this->config = new BraintreeConfiguration(
            $_ENV['BRAINTREE_ENVIRONMENT'],
            $_ENV['BRAINTREE_MERCHANT_ID'],
            $_ENV['BRAINTREE_PUBLIC_KEY'],
            $_ENV['BRAINTREE_PRIVATE_KEY']
        );

        $this->gateway = new BraintreeSubscriptionGateway($this->config, new GatewayCustomerBuilder());

        $this->faker = \Faker\Factory::create();
    });

    context('creating a new subscription', function () {

        context('with an existing user', function () {

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

            fit('should create a subscription', function () {
                $manager = new SubscriptionManager($this->gateway);

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => '401m',
                    ]
                ];

                $subscription = $manager->create($data);

                expect($subscription->getId())->not->toBeFalsy();
            });

            fit('should create a subscription with a start date', function () {
                $manager = new SubscriptionManager($this->gateway);
                $startDate = Carbon::tomorrow()->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => '400y',
                        'startDate' => $startDate
                    ]
                ];

                $subscription = $manager->create($data);

                expect($subscription->getStartDate())->toBe($startDate);
            });

            fit('should throw an exception when start date is invalid', function () {
                $manager = new SubscriptionManager($this->gateway);
                $startDate = Carbon::yesterday()->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => '401y',
                        'startDate' => $startDate
                    ]
                ];

                $sut = function () use ($manager, $data) {
                    $manager->create($data);
                };

                expect($sut)->toThrow(new CreateSubscriptionException('First Billing Date cannot be in the past.'));
            });

            fit('should throw an exception when user not found', function () {
                $manager = new SubscriptionManager($this->gateway);

                $data = [
                    'customer' => [
                        'id' => 'fakecustomer123',
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => '401m',
                    ]
                ];

                $sut = function () use ($manager, $data) {
                    $manager->create($data);
                };

                expect($sut)->toThrow(new CustomerNotFoundException('Customer with id fakecustomer123 does not exist'));
            });

            fit('should throw an exception when create payment method fails', function () {
                $manager = new SubscriptionManager($this->gateway);

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'bad-nonce'
                    ],
                    'plan' => [
                        'id' => '401m',
                    ]
                ];

                $sut = function () use ($manager, $data) {
                    $manager->create($data);
                };

                expect($sut)->toThrow(new CreatePaymentMethodException('Unknown or expired payment_method_nonce.'));
            });

            afterAll(function () {
               $this->braintree->customer()->delete($this->customer->id);
            });
        });

        context('with a new user', function () {

            fit('should create a subscription', function () {
                $manager = new SubscriptionManager($this->gateway);

                $data = [
                    'customer' => [
                        'firstName' => $this->faker->firstName,
                        'lastName' => $this->faker->lastName,
                        'emailAddress' => $this->faker->email
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => '401y',
                    ]
                ];

                $subscription = $manager->create($data);

                expect($subscription->getId())->not->toBeFalsy();
                expect($subscription->getGatewayCustomerId())->not->toBeFalsy();
            });

            fit('should throw an exception if customer creation fails', function () {
                $manager = new SubscriptionManager($this->gateway);

                $data = [
                    'customer' => [
                        'firstName' => 'Timmy',
                        'lastName' => 'Little',
                        'emailAddress' => 'littletimmy@whatever.com'
                    ],
                    'payment' => [
                        'nonce' => 'bad-nonce'
                    ],
                    'plan' => [
                        'id' => '401y',
                    ]
                ];

                $sut = function () use ($manager, $data) {
                    $manager->create($data);
                };

                expect($sut)->toThrow(new CreateCustomerException('Unknown or expired payment_method_nonce.'));
            });
        });
    });
});

