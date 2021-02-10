<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

final class BILLINGSTRIPE_BOL_Service
{
    /**
     * @var BILLINGSTRIPE_BOL_CustomerDao
     */
    private $customerDao;
    /**
     * @var BILLINGSTRIPE_BOL_PaymentDao
     */
    private $paymentDao;
    /**
     * @var BILLINGSTRIPE_BOL_SubscriptionDao
     */
    private $subscriptionDao;

    /**
     * @var BOL_BillingService
     */
    protected $billingService;

    /**
     * @var OW_Log
     */
    protected $logger;

    /**
     * @var OW_Language
     */
    protected $lang;

    /**
     * Class instance
     *
     * @var BILLINGSTRIPE_BOL_Service
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->customerDao = BILLINGSTRIPE_BOL_CustomerDao::getInstance();
        $this->paymentDao = BILLINGSTRIPE_BOL_PaymentDao::getInstance();
        $this->subscriptionDao = BILLINGSTRIPE_BOL_SubscriptionDao::getInstance();
        $this->billingService = BOL_BillingService::getInstance();
        $this->lang = OW::getLanguage();
        $this->logger = OW::getLogger('billing_stripe_sca');

        // init Stripe library
        \Stripe\Stripe::setApiKey(BILLINGSTRIPE_CLASS_StripeAdapter::getSecretKey());
    }

    /**
     * Returns class instance
     *
     * @return BILLINGSTRIPE_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Unset process stripe session
     */
    public function unsetProcessSession()
    {
        Ow::getSession()->delete('stripe_redirect');
        Ow::getSession()->delete('stripe_redirect_message');
        Ow::getSession()->delete('stripe_customer_id');
        Ow::getSession()->delete('stripe_payment_id');
    }

    /**
     * @param $userId
     * @return BILLINGSTRIPE_BOL_Customer
     */
    public function findCustomerByUserId( $userId )
    {
        if ( !$userId )
        {
            return null;
        }

        return $this->customerDao->findByUserId($userId);
    }

    /**
     * @param $id
     * @return BILLINGSTRIPE_BOL_Customer
     */
    public function findCustomerByStripeId( $id )
    {
        if ( !mb_strlen($id) )
        {
            return null;
        }

        return $this->customerDao->findByStripeId($id);
    }

    /**
     * @param $id
     * @return BILLINGSTRIPE_BOL_Subscription
     */
    public function findSubscriptionByStripeId( $id )
    {
        if ( !mb_strlen($id) )
        {
            return null;
        }

        return $this->subscriptionDao->findByStripeId($id);
    }

    /**
     * @param BILLINGSTRIPE_BOL_Customer $dbcustomer
     * @param Stripe\Customer $customer
     * @param $userId
     * @return BILLINGSTRIPE_BOL_Customer
     */
    public function storeCustomer( $customer, $userId, BILLINGSTRIPE_BOL_Customer $dbcustomer = null )
    {
        if ( !$dbcustomer )
        {
            $dbcustomer = new BILLINGSTRIPE_BOL_Customer();
            $dbcustomer->userId = $userId;
        }

        $dbcustomer->stripeCustomerId = $customer->id;
        $dbcustomer->defaultCard = $customer->default_card;
        $dbcustomer->createStamp = $customer->created;
        $dbcustomer->cards = $customer->cards;
        $dbcustomer->subscriptions = $customer->subscriptions;
        $dbcustomer->currency = $customer->currency;

        $this->customerDao->save($dbcustomer);

        return $dbcustomer;

    }

    /**
     * Store payment to db
     *
     * @param $userId
     * @param $payment
     * @param $sale
     */
    public function storePaymentToDb( $userId, $payment, $sale )
    {
        $dbPayment = new BILLINGSTRIPE_BOL_Payment();
        $dbPayment->userId = $userId;
        $dbPayment->stripePaymentId = $payment->id;
        $dbPayment->stripeCustomerId = $payment->customer;
        $dbPayment->createStamp = $payment->created;
        $dbPayment->amount = $payment->amount;
        $dbPayment->currency = $payment->currency;
        $dbPayment->card = $payment->payment_method;
        $dbPayment->saleId = $sale->id;

        $this->paymentDao->save($dbPayment);
    }

    /**
     * @param $userId
     * @param $subscription
     * @param $sale
     */
    public function storeSubscription( $userId, $subscription, $sale )
    {
        $dbSubscription = new BILLINGSTRIPE_BOL_Subscription();
        $dbSubscription->userId = $userId;
        $dbSubscription->saleId = $sale->id;
        $dbSubscription->stripeSubscriptionId = $subscription->id;
        $dbSubscription->stripeCustomerId = $subscription->customer;
        $dbSubscription->startStamp = $subscription->start_date;
        $dbSubscription->currentStartStamp = $subscription->current_period_start;
        $dbSubscription->currentEndStamp = $subscription->current_period_end;
        $dbSubscription->plan = $subscription->plan;
        $dbSubscription->stripeInitialInvoiceId = $subscription->latest_invoice;

        $this->subscriptionDao->save($dbSubscription);
    }

    /**
     * @param $subscription
     * @param $sale
     */
    public function updateSubscription( $subscription, $sale )
    {
        $dbSubscription = new BILLINGSTRIPE_BOL_Subscription();
        $dbSubscription->id = $subscription->id;
        $dbSubscription->userId = $subscription->userId;
        $dbSubscription->saleId = $sale->id;
        $dbSubscription->stripeSubscriptionId = $subscription->stripeSubscriptionId;
        $dbSubscription->stripeCustomerId = $subscription->stripeCustomerId;
        $dbSubscription->startStamp = $subscription->startStamp;
        $dbSubscription->currentStartStamp = $subscription->currentStartStamp;
        $dbSubscription->currentEndStamp = $subscription->currentEndStamp;
        $dbSubscription->plan = $subscription->plan;

        $dbSubscription->stripeInitialInvoiceId = $subscription->stripeInitialInvoiceId;

        $this->subscriptionDao->save($dbSubscription);
    }


    public function createToken( $cardDetails, $apiKey )
    {
        $expDate =  explode('-', $cardDetails['expiration_date']);

        Stripe::setApiKey($apiKey);

        $tokenData = [
            'card' => [
                'name' => $cardDetails['card_name'],
                'number' => $cardDetails['card_number'],
                'cvc' => $cardDetails['cvc'],
                'exp_month' => $expDate[1],
                'exp_year' => $expDate[0],
            ]
        ];

        $token = Stripe_Token::create($tokenData);

        if( empty($token) )
        {
            $this->logger->addEntry('token_was_not_created', 'create_token_operation_service');
        }

        return $token->id;
    }

    /**
     * Get customer
     *
     * @param $userId
     * @param $token
     * @return mixed
     */
    public function getCustomer($userId, $token)
    {
        $userEmail = BOL_UserService::getInstance()->findUserById($userId)->getEmail();
        $customerParams = array('email' => $userEmail, 'source' => $token);

        // get existing customer
        $dbCustomer = $this->findCustomerByUserId($userId);

        $customer = null;

        // update existing customer
        if ($dbCustomer)
        {
            try
            {
                $customer = \Stripe\Customer::retrieve($dbCustomer->stripeCustomerId);
                if ($customer)
                {
                    // this is an old method, we use it because of our payment flow
                    $customer->card = $token;
                    $customer->save();
                }
            }
            catch ( Exception $e )
            {
                $this->logger->addEntry("Data: " . print_r($customerParams, true) . "\n" . $e->getMessage(),
                    'billingstripe.customer-update-error');

                $this->logger->writeLog();

                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
        else // create new customer with card token
        {
            try
            {
                $customer = \Stripe\Customer::create($customerParams);

                $this->logger->addEntry(print_r($customer, true), 'billingstripe.customer-create');
            }
            catch ( Exception $e )
            {
                $this->logger->addEntry("Data: " . print_r($customerParams, true) . "\n" . $e->getMessage(),
                    'billingstripe.customer-create-error');

                $this->logger->writeLog();

                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        if(!$customer->default_card)
        {
            $customer->default_card = $customer->sources->data[0]->id;
        }

        //  check customer's card
        if (empty($customer->sources->data))
        {
            return ['status' => 'error', 'message' => $this->lang->text('billingstripe', 'card_not_valid')];
        }

        $arrayCustomer = $customer->jsonSerialize();
        $customer->cards = json_encode($arrayCustomer['sources']['data']);

        $this->logger->writeLog();

        $this->storeCustomer($customer, $userId, $dbCustomer);

        return $customer;
    }

    /**
     * Fet client_secret from Stripe.Payment
     *
     * @param $sale
     * @param $customerId
     * @return array|string
     */
    public function getPaymentClientSecret($sale, $customerId)
    {
        // init PaymentIntent
        $data = ['amount' => $sale->totalAmount * 100,
            'currency' => strtolower($this->billingService->getActiveCurrency()),
            'metadata' => ['hash' => $sale->hash],
            'customer' => $customerId
        ];

        try {

            $paymentIntent = \Stripe\PaymentIntent::create($data);

            $this->logger->addEntry(print_r($paymentIntent, true), 'billingstripe.paymentIntent.create');

            if ($paymentIntent->last_payment_error) {
                $this->logger->addEntry("Data: " . print_r($paymentIntent->last_payment_error, true), 'billingstripe.paymentIntent.create-error');
                $this->logger->writeLog();

                return ['status' => 'error', 'message' => $paymentIntent->last_payment_error];
            }
        } catch (Exception $e) {
            $this->logger->addEntry("Data: " . print_r($data, true) . "\n" . $e->getMessage(), 'billingstripe.paymentIntent.create-error');
            $this->logger->writeLog();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        $this->logger->writeLog();

        return $paymentIntent->client_secret;
    }

    /**
     * Deliver sale
     *
     * @param $transactionId
     * @param $sale
     */
    public function deliverSale($transactionId, $sale)
    {
        $adapter = new BILLINGSTRIPE_CLASS_StripeAdapter();
        $productAdapter = $this->billingService->getProductAdapter($sale->entityKey);

        if ( !$this->billingService->saleDelivered($transactionId, $sale->gatewayId) )
        {
            $sale->transactionUid = $transactionId;

            if ( $this->billingService->verifySale($adapter, $sale) )
            {
                $sale = $this->billingService->getSaleById($sale->id);

                if ( $productAdapter )
                {
                    $this->billingService->deliverSale($productAdapter, $sale);

                    $this->billingService->unsetSessionSale();
                    $this->billingService->unsetSessionBackUrl();
                }
            }
        }
    }

    /**
     * Process mobile application payment
     *
     * @param BOL_BillingSale $sale
     * @param array $purchaseOptions
     * @return array
     */
    public function processApplicationPayment($sale, $purchaseOptions = [])
    {
        $productAdapter = $this->billingService->getProductAdapter($sale->entityKey);

        $userId = $sale->userId;

        // process init sale status
        if ($purchaseOptions['status'] === 'init')
        {
            $customer = $this->getCustomer($userId, $purchaseOptions['data']);

            if (!empty($customer['status']) && $customer['status'] === 'error')
            {
                return $customer;
            }

            if (!$sale->recurring) // init paymentIntent
            {
                return [
                    'status' => 'init_payment',
                    'data' => $this->getPaymentClientSecret($sale, $customer->id)
                ];
            }
            else // init subscription
            {
                // set product id (plan)
                $productId = strtoupper($productAdapter->getProductKey() . '_' . $sale->entityId);

                // check plan exists
                try
                {
                    // get plan
                    $plan = \Stripe\Plan::retrieve($productId);

                    if ($plan)
                    {
                        $data = array('plan' => $productId, 'metadata' => array('hash' => $sale->hash));

                        // add subscription
                        try
                        {
                            $subscription = $customer->subscriptions->create($data);

                            // check required action (ScA)
                            if ($subscription->status === 'incomplete')
                            {
                                // get last invoice
                                $lastInvoice = \Stripe\Invoice::retrieve($subscription->latest_invoice);

                                // get payment by invoice
                                $paymentIntentRequiredAction = \Stripe\PaymentIntent::retrieve($lastInvoice->payment_intent);
                                $paymentIntentRequiredAction->metadata = ['subscriptionId' => $subscription->id];
                                $paymentIntentRequiredAction->save();

                                $this->logger->addEntry(print_r($subscription, true), 'billingstripe.subscription-incomplete');
                                $this->logger->writeLog();

                                // pass client_secret for required action (ScA) on client side
                                return [
                                    'status' => 'incomplete_subscription',
                                    'data' => $paymentIntentRequiredAction->client_secret
                                ];
                            }

                            // the subscription didn't need any additional actions and completed

                            $this->logger->addEntry(print_r($subscription, true),
                                    'billingstripe.subscription_completed_successfully');

                            $this->logger->writeLog();

                            // store subscription to db
                            $this->storeSubscription($userId, $subscription, $sale);

                            // delivered
                            $this->deliverSale($subscription->id, $sale);

                            return ['status' => 'success'];
                        }
                        catch ( Exception $e )
                        {
                            $this->logger->addEntry("Data: " . print_r($data, true) . "\n" .
                                $e->getMessage(), 'billingstripe.subscription-create-error');

                            $this->logger->writeLog();

                            return ['status' => 'error', 'message' => $e->getMessage()];
                        }
                    }
                }
                catch ( Exception $e )
                {
                    $this->logger->addEntry("Product ID: " . $productId . "\n" . $e->getMessage(), 'billingstripe.plan-retrieve-error');
                    $this->logger->writeLog();

                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
            }
        }

        // process success payment status
        if ($purchaseOptions['status'] === 'success_payment')
        {
            try {
                // retrieve payment
                $payment = \Stripe\PaymentIntent::retrieve($purchaseOptions['data']);


                // check status
                if ($payment->status === 'succeeded' && $payment->metadata['hash'] === $sale->hash) {
                    // store Payment to db
                    $this->storePaymentToDb($userId, $payment, $sale);

                    // delivered
                    $this->deliverSale($payment->id, $sale);

                    $this->logger->addEntry(print_r($payment, true), 'billingstripe.order_completed_successfully');
                    $this->logger->writeLog();

                    return ['status' => 'success'];
                }
            }
            catch (Exception $e)
            {
                $this->logger->addEntry( $e->getMessage(), 'billingstripe.paymentIntent.retrieve-error');
                $this->logger->writeLog();

                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        // process success payment of subscription status (after additional actions (SCA))
        if ($purchaseOptions['status'] === 'success_payment_subscription')
        {
            try
            {
                // retrieve payment
                $payment = \Stripe\PaymentIntent::retrieve($purchaseOptions['data']);

                // retrieve subscription
                $subscription = \Stripe\Subscription::retrieve($payment->metadata['subscriptionId']);

                // check statuses
                if ($payment->status === 'succeeded' && $subscription->metadata['hash'] === $sale->hash) {
                    // store subscription to db
                    $this->storeSubscription($userId, $subscription, $sale);

                    // delivered
                    $this->deliverSale($subscription->id, $sale);

                    $this->logger->addEntry(print_r($subscription, true), 'billingstripe.order_completed_successfully');
                    $this->logger->writeLog();

                    return ['status' => 'success'];
                }
            }
            catch (Exception $e)
            {
                $this->logger->addEntry( $e->getMessage(), 'billingstripe.paymentIntent_and_subscription.retrieve-error');
                $this->logger->writeLog();

                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return ['status' => 'error', 'message' => ''];
    }
}
