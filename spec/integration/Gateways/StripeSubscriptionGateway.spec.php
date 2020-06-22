<?php


namespace TeamGantt\Subscreeb\Tests;

use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\StripeSubscriptionGateway;

describe('StripeSubscriptionGateway', function () {

    $this->gateway = null;

    beforeAll(function () {
        $this->gateway = new StripeSubscriptionGateway($_ENV['STRIPE_API_KEY']);
    });

    context('when customer is new', function () {
        it('should create a new Braintree customer when ', function () {
            $this->gateway->create('', 'tyson@teamgantt.com', 'pm_card_visa');
        });
    });

    context('when existing customer cannot be found', function () {
        it('should throw a not found exception', function () {
            $sut = function () {
                $this->gateway->create('a-customer-that-doesnt-exist', 'fake@fake.com', 'pm_card_visa');
            };

            expect($sut)->toThrow(new CustomerNotFoundException('No such customer: a-customer-that-doesnt-exist'));
        });
    });
});

