<?php

namespace TeamGantt\Subscreeb\Gateways;

use Braintree\Gateway as Braintree;
use Carbon\Carbon;
use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\ConfigurationInterface;
use TeamGantt\Subscreeb\Gateways\Braintree\PaymentToken\{PaymentToken, Factory as PaymentTokenFactory};
use TeamGantt\Subscreeb\Gateways\Contracts\SubscriptionGateway;
use TeamGantt\Subscreeb\Models\Adapters\BraintreeSubscriptionAdapter;
use TeamGantt\Subscreeb\Models\AddOn\AddOn;
use TeamGantt\Subscreeb\Models\AddOn\AddOnCollection;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\Payment;
use TeamGantt\Subscreeb\Models\Plan;
use TeamGantt\Subscreeb\Models\Subscription\SubscriptionInterface;

class BraintreeSubscriptionGateway implements SubscriptionGateway
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
     * @throws CreateCustomerException
     * @throws CreatePaymentMethodException
     * @throws CreateSubscriptionException
     * @throws CustomerNotFoundException
     */
    public function create(Customer $customer, Payment $payment, Plan $plan, AddOnCollection $addOns): SubscriptionInterface
    {
        $paymentToken = $this->paymentTokens->make($customer, $payment);
        return $this->createSubscription($paymentToken, $plan, $addOns);
    }

    protected function createSubscription(PaymentToken $token, Plan $plan, AddOnCollection $addOns): SubscriptionInterface
    {
        $planId = $plan->getId();
        $startDate = $plan->getStartDate()
            ? new Carbon($plan->getStartDate())
            : new Carbon();

        $addOnItems = array_map(function (AddOn $addOn) {
            return  [
               'existingId' => $addOn->getId(),
               'quantity' => $addOn->getQuantity()
            ];
        }, $addOns->getAddons());

        $result = $this->gateway
            ->subscription()
            ->create([
                'paymentMethodToken' => $token->getToken(),
                'planId' => $planId,
                'firstBillingDate' => $startDate,
                'addOns' => [
                    'update' => $addOnItems
                ]
            ]);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        return new BraintreeSubscriptionAdapter($result->subscription, $token->getCustomer());
    }
}
