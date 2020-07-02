<?php

namespace TeamGantt\Subscreeb\Gateways;

use Braintree\Exception\NotFound;
use Braintree\Gateway as Braintree;
use Carbon\Carbon;
use TeamGantt\Subscreeb\Exceptions\CreateCustomerException;
use TeamGantt\Subscreeb\Exceptions\CreatePaymentMethodException;
use TeamGantt\Subscreeb\Exceptions\CreateSubscriptionException;
use TeamGantt\Subscreeb\Exceptions\CustomerNotFoundException;
use TeamGantt\Subscreeb\Gateways\Braintree\ConfigurationInterface;
use TeamGantt\Subscreeb\Gateways\Braintree\PaymentTokenStrategyInterface;
use TeamGantt\Subscreeb\Gateways\Contracts\SubscriptionGateway;
use TeamGantt\Subscreeb\Models\Adapters\BraintreeSubscriptionAdapter;
use TeamGantt\Subscreeb\Models\AddOn\AddOn;
use TeamGantt\Subscreeb\Models\AddOn\AddOnCollection;
use TeamGantt\Subscreeb\Models\Customer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomer;
use TeamGantt\Subscreeb\Models\GatewayCustomer\GatewayCustomerBuilderInterface;
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
     * @var PaymentTokenStrategyInterface
     */
    protected PaymentTokenStrategyInterface $paymentTokens;

    /**
     * BraintreeSubscriptionGateway constructor.
     * @param ConfigurationInterface $config
     * @param PaymentTokenStrategyInterface $paymentTokens
     */
    public function __construct(ConfigurationInterface $config, PaymentTokenStrategyInterface $paymentTokens)
    {
        $this->gateway = new Braintree([
            'environment' => $config->getEnvironment(),
            'merchantId' => $config->getMerchantId(),
            'publicKey' => $config->getPublicKey(),
            'privateKey' => $config->getPrivateKey()
        ]);

        $this->paymentTokens = $paymentTokens;
        $this->paymentTokens->setGateway($this->gateway);
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
        $paymentToken = $this->paymentTokens->getPaymentToken($customer, $payment);
        $token = $paymentToken->getToken();
        $gatewayCustomer = $paymentToken->getCustomer();
        return $this->createSubscription($gatewayCustomer, $plan, $addOns, $token);
    }

    protected function createSubscription(GatewayCustomer $gatewayCustomer, Plan $plan, AddOnCollection $addOns, string $paymentToken): SubscriptionInterface
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
                'paymentMethodToken' => $paymentToken,
                'planId' => $planId,
                'firstBillingDate' => $startDate,
                'addOns' => [
                    'update' => $addOnItems
                ]
            ]);

        if (!$result->success) {
            throw new CreateSubscriptionException($result->message);
        }

        return new BraintreeSubscriptionAdapter($result->subscription, $gatewayCustomer);
    }

    
}
