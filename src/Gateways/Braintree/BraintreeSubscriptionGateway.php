<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree;

use Braintree\Gateway as Braintree;
use Carbon\Carbon;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Gateways\Braintree\Adapters\BraintreeSubscriptionAdapter;
use TeamGantt\Subscreeb\Gateways\Braintree\PaymentToken\{Factory as PaymentTokenFactory, PaymentToken};
use TeamGantt\Subscreeb\Gateways\SubscriptionGatewayInterface;
use TeamGantt\Subscreeb\Models\AddOn\AddOn;
use TeamGantt\Subscreeb\Models\AddOn\AddOnCollection;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Discount\Discount;
use TeamGantt\Subscreeb\Models\Discount\DiscountCollection;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

class BraintreeSubscriptionGateway implements SubscriptionGatewayInterface
{
    /**
     * @var Braintree
     */
    protected Braintree $gateway;

    /**
     * @var PaymentTokenFactory
     */
    protected PaymentTokenFactory $paymentTokens;

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

        $this->paymentTokens = new PaymentTokenFactory($this->gateway);
    }

    /**
     * {@inheritDoc}
     *
     * @throws CreateSubscriptionException
     */
    public function create(Customer $customer, Payment $payment, Plan $plan, AddOnCollection $addOns, DiscountCollection $discounts): SubscriptionInterface
    {
        $paymentToken = $this->paymentTokens->make($customer, $payment);

        return $this->createSubscription($paymentToken, $plan, $addOns, $discounts);
    }

    protected function createSubscription(PaymentToken $token, Plan $plan, AddOnCollection $addOns, DiscountCollection $discounts): SubscriptionInterface
    {
        $planId = $plan->getId();
        $startDate = $plan->getStartDate()
            ? new Carbon($plan->getStartDate())
            : new Carbon();

        $addOnItems = array_map(function (AddOn $addOn) {
            return [
                'inheritedFromId' => $addOn->getId(),
                'quantity' => $addOn->getQuantity()
            ];
        }, $addOns->getAddons());

        $discountItems = array_map(function (Discount $discount) {
            return [
                'inheritedFromId' => $discount->getId(),
                'amount' => $discount->getAmount(),
                'numberOfBillingCycles' => $discount->getBillingCycles()
            ];
        }, $discounts->getDiscounts());

        $result = $this->gateway
            ->subscription()
            ->create([
                'paymentMethodToken' => $token->getToken(),
                'planId' => $planId,
                'firstBillingDate' => $startDate,
                'addOns' => [
                    'add' => $addOnItems
                ],
                'discounts' => [
                    'add' => $discountItems
                ]
            ]);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        return new BraintreeSubscriptionAdapter($result->subscription, $token->getCustomer());
    }
}
