<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Gateway as Braintree;
use Carbon\Carbon;
use DateTime;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Gateways\Braintree\Customer\CustomerStrategy;
use TeamGantt\Subscreeb\Gateways\SubscriptionGatewayInterface;
use TeamGantt\Subscreeb\Models\AddOn;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription;

class BraintreeSubscriptionGateway implements SubscriptionGatewayInterface
{
    /**
     * @var Braintree
     */
    protected Braintree $gateway;

    /**
     * @var CustomerStrategy
     */
    protected CustomerStrategy $customerStrategy;

    /**
     * @var SubscriptionMapper
     */
    protected SubscriptionMapper $subscriptionMapper;

    /**
     * BraintreeSubscriptionGateway constructor.
     * @param ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->gateway = new Braintree([
            'environment' => $config->getEnvironment(),
            'merchantId' => $config->getMerchantId(),
            'publicKey' => $config->getPublicKey(),
            'privateKey' => $config->getPrivateKey()
        ]);

        $this->customerStrategy = new CustomerStrategy($this->gateway);
        $this->subscriptionMapper = new SubscriptionMapper();
    }

    /**
     * {@inheritDoc}
     *
     * @throws CreateSubscriptionException
     */
    public function create(Subscription $subscription): Subscription
    {
        $customer = $this->customerStrategy->savePaymentToken($subscription->getCustomer(), $subscription->getPayment());

        return $this->createSubscription($customer, $subscription->getPlan(), $subscription->getAddOns(), $subscription->getDiscounts());
    }

    public function getAddOns(array $addOns): array
    {
        return array_map(function (AddOn $addOn) {
            return [
                'inheritedFromId' => $addOn->getId(),
                'quantity' => $addOn->getQuantity()
            ];
        }, $addOns);
    }

    protected function getDiscounts(array $discounts): array
    {
        return array_map(function (Discount $discount) {
            return [
                'inheritedFromId' => $discount->getId(),
                'amount' => $discount->getAmount(),
                'numberOfBillingCycles' => $discount->getBillingCycles()
            ];
        }, $discounts);
    }

    protected function getStartDate(Plan $plan): DateTime
    {
        return $plan->getStartDate()
            ? new Carbon($plan->getStartDate())
            : new Carbon();
    }

    protected function createSubscription(Customer $customer, Plan $plan, array $addOns, array $discounts): Subscription
    {
        $planId = $plan->getId();

        $result = $this->gateway
            ->subscription()
            ->create([
                'paymentMethodToken' => $customer->getPaymentToken(),
                'planId' => $planId,
                'firstBillingDate' => $this->getStartDate($plan),
                'addOns' => [
                    'add' => $this->getAddOns($addOns)
                ],
                'discounts' => [
                    'add' => $this->getDiscounts($discounts)
                ]
            ]);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        return $this->subscriptionMapper->map($result->subscription, $customer);
    }
}
