# A PHP subscription platform abstraction

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teamgantt/subscreeb.svg?style=flat-square)](https://packagist.org/packages/teamgantt/subscreeb)
[![Build Status](https://img.shields.io/travis/teamgantt/subscreeb/master.svg?style=flat-square)](https://travis-ci.org/teamgantt/subscreeb)
[![Quality Score](https://img.shields.io/scrutinizer/g/teamgantt/subscreeb.svg?style=flat-square)](https://scrutinizer-ci.com/g/teamgantt/subscreeb)
[![Total Downloads](https://img.shields.io/packagist/dt/teamgantt/subscreeb.svg?style=flat-square)](https://packagist.org/packages/teamgantt/subscreeb)

## Getting Started

### Installation

You can install the package via composer:

```bash
composer require teamgantt/subscreeb
```

## Usage

### Subscription Attributes

Attribute | Description | Example
--------- | ----------- | -------
id | A unique value that identifies a specific subscription |
startDate | The day the subscription will start billing in UTC time  | '2020-12-01' 
price | The price for the subscription. This will override the plan's default price | 20.00
customer | The customer's details related to the subscription | See Customer Attributes
payment | The payment details associated with the subscription | See Payment Attributes
plan | The plan details associated with the subscription | See Plan Attributes
addOns | An array of addOns that should be associated with the subscription | See AddOn Attributes
discounts | An array of discount to associated with the subscription | See Discount Attributes

#### Example Response

``` php
[
    'id' => 'new-rad-subscription',
    'startDate' => '2020-12-01',
    'price' => 10.00,
    'customer' => [
        'id' => '267360606',
        'firstName' => 'Tyson',
        'lastName' => 'Nero',
        'emailAddress' => 'yes@what.com'
    ],
    'payment' => [
        'nonce' => 'fake-valid-visa-nonce'
    ],
    'plan' => [
        'id' => '401m'
        'price' => 10.00
    ],
    'addOns' => [
        'id' => 'addOn-id',
        'quantity' => 5,
        'price' => 10.00
    ],
    'discounts' => [
        'id' => 'discount-id',
        'amount' => 100.00,
        'billingCycles' => 1
    ],

];
```

### Customer Attributes
Attribute | Description | Example
--------- | ----------- | -------
id | A unique value that identifies the customer |
firstName | The first name of the customer | 'Frank'
lastName | The last name of the customer | 'Dux'
emailAddress | the email address of the customer |

### Payment Attributes
Attribute | Description | Example
--------- | ----------- | -------
nonce | A single use reference to the payment method provided by the customer | 

### Plan Attributes
Attribute | Description | Example
--------- | ----------- | -------
id | A unique value that identifies the plan |
price | The amount that will override the plan's default prices | 25.00

### AddOn Attributes
Attribute | Description | Example
--------- | ----------- | -------
id | A unique value that identifies the addOn |
quantity | The number of times the addOn should be applied to the subscription | 5
price | The amount that will override the addOn's default price | 5.00

### Discount Attributes
Attribute | Description | Example
--------- | ----------- | -------
id | A unique value that identifies the discount |
amount | The discount amount the will be applied to subscription | 5.75
billingCycles | The number of billing cycles in which the discount should be applied | 5

### Initialize SubscriptionManager with Braintree

```php
// Create Configuration object
$config = new Configuration(
    $_ENV['BRAINTREE_ENVIRONMENT'],
    $_ENV['BRAINTREE_MERCHANT_ID'],
    $_ENV['BRAINTREE_PUBLIC_KEY'],
    $_ENV['BRAINTREE_PRIVATE_KEY']
);

// Instantiate BraintreeSubscriptionGateway
$gateway = new BraintreeSubscriptionGateway($config);

// Instantiate SubscriptionManager
$manager = new SubscriptionManager($gateway);
```

### Create a subscription

Create a new subscription. If an existing customer is not passed, a new one can be created.

#### Supported Parameters

- startDate - if not set, the subscription will bill immediately
- customer
  - id - required when using an existing customer
  - firstName - optional when creating a new customer
  - lastName - optional when creating a new customer
  - emailAddress - optional when creating a new customer
- payment
  - nonce - required
- plan
  - id - required
- addOns (array)
  - id - required
  - quantity - required
  - price
- discounts (array)
  - id - required
  - amount - required
  - billingCycles - required
  
#### Returns

A Subscription model
  
#### With an existing user

```php
$data = [
    'customer' => [
        'id' => 'existing-customer-id',
    ],
    'payment' => [
        'nonce' => 'fake-valid-visa-nonce'
    ],
    'plan' => [
        'id' => 'existing-plan-id',
    ]
];

$subscription = $manager->create($data);
```

#### With a new user

```php
$data = [
    'customer' => [
        'firstName' => 'Kirk',
        'lastName' => 'Franklin',
        'emailAddress' => 'kirkfranklin@mailinator.com'
    ],
    'payment' => [
        'nonce' => 'fake-valid-visa-nonce'
    ],
    'plan' => [
        'id' => 'existing-plan-id'
    ]
];

$subscription = $manager->create($data);
```

#### With a future billing date

```php
$data = [
    'startDate' => '2050-01-01',
    'customer' => [
        'id' => 'existing-customer-id',
    ],
    'payment' => [
        'nonce' => 'fake-valid-visa-nonce'
    ],
    'plan' => [
        'id' => 'existing-plan-id'
    ]
];

$subscription = $manager->create($data);
```

#### With addOns
```php
$data = [
    'customer' => [
        'id' => 'existing-customer-id',
    ],
    'payment' => [
        'nonce' => 'fake-valid-visa-nonce'
    ],
    'plan' => [
        'id' => 'existing-plan-id',
    ],
    'addOns' => [
        [
            'id' => 'addOn-id',
            'quantity' => '5'
        ]
    ]
];

$subscription = $manager->create($data);
```

#### With discounts

```php
$data = [
    'customer' => [
        'id' => 'existing-customer-id',
    ],
    'payment' => [
        'nonce' => 'fake-valid-visa-nonce'
    ],
    'plan' => [
        'id' => 'existing-plan-id',
    ],
    'discounts' => [
        [
            'id' => 'discount-id',
            'amount' => '15.50',
            'billingCycles' => 1
        ]
    ]
];

$subscription = $manager->create($data);
```
  
## Development

### Requirements
- Docker
- direnv https://direnv.net/

### Setup

1. Run `$ direnv allow .` to allow loading of `.envrc`
1. Run `$ composer install`

### Testing

1. Copy .env.example to .env and set secrets where necessary
1. Run `$ composer test`

### Coding Standards

``` bash
$ composer phpcs
```

### Static Analysis

``` bash
$ composer phpstan
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email tyson@teamgantt.com instead of using the issue tracker.

## Credits

- [Tyson Nero](https://github.com/teamgantt)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## PHP Package Boilerplate

This package was generated using the [PHP Package Boilerplate](https://laravelpackageboilerplate.com).
