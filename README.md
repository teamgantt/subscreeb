# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teamgantt/subscreeb.svg?style=flat-square)](https://packagist.org/packages/teamgantt/subscreeb)
[![Build Status](https://img.shields.io/travis/teamgantt/subscreeb/master.svg?style=flat-square)](https://travis-ci.org/teamgantt/subscreeb)
[![Quality Score](https://img.shields.io/scrutinizer/g/teamgantt/subscreeb.svg?style=flat-square)](https://scrutinizer-ci.com/g/teamgantt/subscreeb)
[![Total Downloads](https://img.shields.io/packagist/dt/teamgantt/subscreeb.svg?style=flat-square)](https://packagist.org/packages/teamgantt/subscreeb)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require teamgantt/subscreeb
```

## Usage

``` php
// Example create data
$data = [
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
        'id' => '401m',
        'startDate' => '2020-12-01',
    ],
    'addOns' => [
        'id' => 'addOn-id',
        'quantity' => 5,
        'price' => 10.00
    ],
    'discount' => [
        'id' => 'discount-id',
        'price' => 100.00
    ]

];
```

### Testing

``` bash
composer test
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
