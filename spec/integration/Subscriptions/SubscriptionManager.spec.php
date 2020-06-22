<?php

namespace TeamGantt\Subscreeb\Tests;

use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Subscriptions\SubscriptionManager;

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

describe('SubscriptionManager', function () {

    beforeAll(function () {
        $this->gateway = new BraintreeSubscriptionGateway(
            $_ENV['BRAINTREE_ENVIRONMENT'],
            $_ENV['BRAINTREE_MERCHANT_ID'],
            $_ENV['BRAINTREE_PUBLIC_KEY'],
            $_ENV['BRAINTREE_PRIVATE_KEY']
        );

        $this->faker = \Faker\Factory::create();
    });

    context('creating a new subscription', function () {

        context('with an existing user', function () {

            fit('should create a subscription', function () {
                $manager = new SubscriptionManager($this->gateway);

                $data = [
                    'customer' => [
                        'id' => '504412174',
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
                        'id' => '504412174',
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

