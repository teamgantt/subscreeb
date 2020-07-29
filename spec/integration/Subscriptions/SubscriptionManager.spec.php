<?php

namespace TeamGantt\Subscreeb\Tests;

use Carbon\Carbon;
use Dotenv\Dotenv;
use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\BraintreeSubscriptionGateway;
use TeamGantt\Subscreeb\Gateways\Braintree\Configuration;
use TeamGantt\Subscreeb\Models\SubscriptionStatus;
use TeamGantt\Subscreeb\Subscriptions\SubscriptionManager;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

describe('SubscriptionManager', function () {

    beforeAll(function () {
        $this->config = new Configuration(
            $_ENV['BRAINTREE_ENVIRONMENT'],
            $_ENV['BRAINTREE_MERCHANT_ID'],
            $_ENV['BRAINTREE_PUBLIC_KEY'],
            $_ENV['BRAINTREE_PRIVATE_KEY']
        );

        $this->gateway = new BraintreeSubscriptionGateway($this->config);
        $this->manager = new SubscriptionManager($this->gateway);

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

            it('should create a subscription', function () {
                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-a-monthly',
                    ]
                ];

                $subscription = $this->manager->create($data);

                expect($subscription->getId())->not->toBeFalsy();
            });

            it('should create a subscription with a future start date', function () {
                $startDate = Carbon::today('utc')->addWeek()->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-a-yearly',
                        'startDate' => $startDate
                    ]
                ];

                $subscription = $this->manager->create($data);

                expect($subscription->getPlan()->getStartDate())->toBe($startDate);
                expect($subscription->getStatus())->toBe(SubscriptionStatus::PENDING);
            });

            it('should bill immediately when start date is set to today', function () {
                $startDate = Carbon::today('utc')->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-b-yearly',
                        'startDate' => $startDate
                    ]
                ];

                $subscription = $this->manager->create($data);

                expect($subscription->getStatus())->toBe(SubscriptionStatus::ACTIVE);
            });

            it('should bill immediately when start date is not set', function () {
                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-c-monthly',
                    ]
                ];

                $subscription = $this->manager->create($data);

                expect($subscription->getStatus())->toBe(SubscriptionStatus::ACTIVE);
            });

            it('should create a subscription with an addOn', function () {
                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-b-monthly',
                    ],
                    'addOns' => [
                        [
                            'id' => 'test-plan-b-monthly-user',
                            'quantity' => '5'
                        ]
                    ]
                ];

                $subscription = $this->manager->create($data);
                $addOns = $subscription->getAddons();

                expect($addOns[0]->getId())->toBe('test-plan-b-monthly-user');
                expect($addOns)->toHaveLength(1);
            });

            it('should create a subscription with multiple addOns', function () {
                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-b-monthly',
                    ],
                    'addOns' => [
                        [
                            'id' => 'test-plan-a-monthly-user',
                            'quantity' => '5'
                        ],
                        [
                            'id' => 'test-plan-b-monthly-user',
                            'quantity' => '5'
                        ]
                    ]
                ];

                $subscription = $this->manager->create($data);
                $addOns = $subscription->getAddons();

                expect($addOns[0]->getId())->toBe('test-plan-a-monthly-user');
                expect($addOns[1]->getId())->toBe('test-plan-b-monthly-user');
                expect($addOns)->toHaveLength(2);
            });

            it('should create a subscription with a discount', function () {
                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-b-yearly',
                    ],
                    'discounts' => [
                        [
                            'id' => 'test-discount-a',
                            'amount' => '15.50',
                            'billingCycles' => 1
                        ]
                    ]
                ];

                $subscription = $this->manager->create($data);
                $discounts = $subscription->getDiscounts();

                expect($discounts[0]->getId())->toBe('test-discount-a');
                expect($discounts[0]->getAmount())->toBe(15.50);
                expect($discounts[0]->getBillingCycles())->toBe(1);
                expect($discounts)->toHaveLength(1);
            });

            it('should create a subscription with multiple discounts', function () {
                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-b-yearly',
                    ],
                    'discounts' => [
                        [
                            'id' => 'test-discount-a',
                            'amount' => '15.50',
                            'billingCycles' => 1
                        ],
                        [
                            'id' => 'test-discount-b',
                            'amount' => '4.50',
                            'billingCycles' => 1
                        ]
                    ]
                ];

                $subscription = $this->manager->create($data);
                $discounts = $subscription->getDiscounts();

                expect($discounts[0]->getId())->toBe('test-discount-a');
                expect($discounts[0]->getAmount())->toBe(15.50);
                expect($discounts[0]->getBillingCycles())->toBe(1);

                expect($discounts[1]->getId())->toBe('test-discount-b');
                expect($discounts[1]->getAmount())->toBe(4.50);
                expect($discounts[1]->getBillingCycles())->toBe(1);

                expect($discounts)->toHaveLength(2);
            });

            it('should throw an exception when plan id is invalid', function () {
                $startDate = Carbon::today()->subWeek()->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'plan-id-that-doesn-not-exist'
                    ],
                ];

                $sut = function () use ($data) {
                    $this->manager->create($data);
                };

                expect($sut)->toThrow(new CreateSubscriptionException('Plan ID is invalid.'));
            });

            it('should throw an exception when addOn is invalid', function () {
                $startDate = Carbon::today()->subWeek()->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-a-monthly'
                    ],
                    'addOns' => [
                        [
                            'id' => 'this-addon-does-not-exist',
                            'quantity' => '5'
                        ]
                    ]
                ];

                $sut = function () use ($data) {
                    $this->manager->create($data);
                };

                expect($sut)->toThrow(new CreateSubscriptionException('Inherited From ID is invalid.'));
            });

            it('should throw an exception when discount is invalid', function () {
                $startDate = Carbon::today()->subWeek()->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-a-monthly'
                    ],
                    'discounts' => [
                    [
                        'id' => 'very-bad-promo-code-that-does-not-exist',
                        'amount' => '15.50',
                        'billingCycles' => 1
                    ]
                ]
                ];

                $sut = function () use ($data) {
                    $this->manager->create($data);
                };

                expect($sut)->toThrow(new CreateSubscriptionException('Inherited From ID is invalid.'));
            });

            it('should throw an exception when start date is invalid', function () {
                $startDate = Carbon::today()->subWeek()->toDateString();

                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-a-monthly',
                        'startDate' => $startDate
                    ]
                ];

                $sut = function () use ($data) {
                    $this->manager->create($data);
                };

                expect($sut)->toThrow(new CreateSubscriptionException('First Billing Date cannot be in the past.'));
            });

            it('should throw an exception when user not found', function () {
                $data = [
                    'customer' => [
                        'id' => 'fakecustomer123',
                    ],
                    'payment' => [
                        'nonce' => 'fake-valid-visa-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-a-monthly',
                    ]
                ];

                $sut = function () use ($data) {
                    $this->manager->create($data);
                };

                expect($sut)->toThrow(new CustomerNotFoundException('Customer with id fakecustomer123 does not exist'));
            });

            it('should throw an exception when create payment method fails', function () {
                $data = [
                    'customer' => [
                        'id' => $this->customer->id,
                    ],
                    'payment' => [
                        'nonce' => 'bad-nonce'
                    ],
                    'plan' => [
                        'id' => 'test-plan-a-monthly',
                    ]
                ];

                $sut = function () use ($data) {
                    $this->manager->create($data);
                };

                expect($sut)->toThrow(new CreatePaymentMethodException('Unknown or expired payment_method_nonce.'));
            });
        });

        context('with a new user', function () {

            it('should create a subscription', function () {
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
                        'id' => 'test-plan-a-yearly',
                    ]
                ];

                $subscription = $this->manager->create($data);

                expect($subscription->getId())->not->toBeFalsy();
                expect($subscription->getCustomer()->getId())->not->toBeFalsy();
            });

            it('should throw an exception if customer creation fails', function () {
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
                        'id' => 'test-plan-a-yearly',
                    ]
                ];

                $sut = function () use ($data) {
                    $this->manager->create($data);
                };

                expect($sut)->toThrow(new CreateCustomerException('Unknown or expired payment_method_nonce.'));
            });
        });
    });
});

