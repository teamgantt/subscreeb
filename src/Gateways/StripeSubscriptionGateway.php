<?php

namespace TeamGantt\Subscreeb\Gateways;

use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Models\Messages\CreateCustomerResponse;

class StripeSubscriptionGateway
{
    protected $gateway;

    public function __construct(string $apiKey)
    {
        $this->gateway = new StripeClient($apiKey);
    }

    public function create(string $customerId, string $email, string $paymentId)
    {
        if (!$customerId) {
            $customer = $this->gateway->customers->create([
                'email' => $email
            ]);

            $customerId = $customer->id;
        }

        try {
            $this->gateway->customers->retrieve($customerId);
        } catch (InvalidRequestException $e) {
            throw new CustomerNotFoundException($e->getMessage());
        }

        try {
            $paymentMethod = $this->gateway->paymentMethods->retrieve($paymentId);
            $paymentMethod->attach(['customer' => $customerId]);
            $this->gateway->customers->update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod->id
                ]
            ]);
        } catch (Exception $e) {
            throw new CreatePaymentMethodException($e->getMessage());
        }

        $subscription = $this->gateway->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => 'price_1GuPcJIkgriGW4cNN8oJ4dQO']],
        ]);
    }

    public function createCustomer(string $email, string $firstName, string $lastName): CreateCustomerResponse
    {
        $customer = $this->gateway->customers->create([
            'name' => "$firstName $lastName",
            'email' => $email
        ]);

        return new CreateCustomerResponse($customer->id);
    }
}