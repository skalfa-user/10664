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

trait BILLINGSTRIPE_CLASS_ActionMethods
{
    /**
     * Incomplete subscription status
     *
     * @var BILLINGSTRIPE_BOL_Service
     */
    public $stripeService;

    /**
     * @var BOL_BillingService
     */
    public $billingService;

    /**
     * @var OW_Log
     */
    public $logger;

    /**
     * @var OW_Log
     */
    public $stripe;

    /**
     * @var OW_Language
     */
    public $lang;

    public function __construct()
    {
        $this->lang = OW::getLanguage();
        $this->billingService = BOL_BillingService::getInstance();
        $this->stripeService = BILLINGSTRIPE_BOL_Service::getInstance();
        $this->logger = OW::getLogger('billingstripe_sca.order');

        // init Stripe library
        \Stripe\Stripe::setApiKey(BILLINGSTRIPE_CLASS_StripeAdapter::getSecretKey());
    }

    /**
     * Order form action
     *
     * @throws AuthenticateException
     */
    public function orderForm()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        // get sale session
        $sale = $this->billingService->getSessionSale();

        if ( !$sale )
        {
            $url = $this->billingService->getSessionBackUrl();
            if ( $url != null )
            {
                OW::getFeedback()->warning($this->lang->text('base', 'billing_order_canceled'));
                $this->billingService->unsetSessionBackUrl();
                OW::getApplication()->redirect($url);
            }

            OW::getApplication()->redirect($this->billingService->getOrderFailedPageUrl());
        }

        OW::getFeedback()->warning($this->lang->text('billingstripe', 'warning_process_complete_info'));

        $gwKey = BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY;

        // product to string
        $productString = $this->lang->text(
            'billingstripe',
            'product_string',
            array(
                'product' => strip_tags($sale->entityDescription)
            )
        );

        $this->assign('productString', $productString);

        // set require data
        $requireData = $this->billingService->getGatewayConfigValue($gwKey, 'requireData');
        $this->assign('requireData', $requireData);

        // set uniq form id
        $formId = uniqid("stripe-form-");

        // assign params
        $this->assign('formId', $formId);
        $this->assign('formAction',OW::getRouter()->urlForRoute('billingstripe.after_sale'));
        $this->assign('countries', BILLINGSTRIPE_CLASS_StripeAdapter::getCountryList());

        // add JS languages
        $this->lang->addKeyForJs('billingstripe', 'card_number_invalid');
        $this->lang->addKeyForJs('billingstripe', 'exp_date_invalid');
        $this->lang->addKeyForJs('billingstripe', 'cvc_invalid');
        $this->lang->addKeyForJs('billingstripe', 'name_on_card_required');
        $this->lang->addKeyForJs('billingstripe', 'notification_after_actions');

        if ($requireData) {
            $this->lang->addKeyForJs('billingstripe', 'country_required');
            $this->lang->addKeyForJs('billingstripe', 'address_required');
            $this->lang->addKeyForJs('billingstripe', 'state_required');
            $this->lang->addKeyForJs('billingstripe', 'zip_code_required');
        }

        $this->setPageHeading($this->lang->text('billingstripe', 'pay_via_credit_card'));

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('billingstripe')->getStaticCssUrl().'card_form.css');

        OW::getDocument()->addScript("https://js.stripe.com/v3/");

        // add script params
        $js = UTIL_JsGenerator::newInstance();
        OW::getDocument()->addScriptDeclarationBeforeIncludes($js);

        $configKey = 'billingstripe';
        $js->addScript('var stripeParams = {
                recurring: {$recurring},
                publicKey:  {$publicKey}, 
                requireData: {$requireData},
                processSale: {$processSale},
                formId: {$formId},
                fontColor: {$fontColor},
                iconColor: {$iconColor},
                fontFamily: {$fontFamily},
                fontSize: {$fontSize},
                placeholderColor: {$placeholderColor},
                errorFontColor: {$errorFontColor},
                errorIconColor: {$errorIconColor},
            }',
            array(
                'publicKey' => BILLINGSTRIPE_CLASS_StripeAdapter::getPublicKey(),
                'requireData' => $requireData,
                'processSale' =>  OW::getRouter()->urlForRoute('billingstripe.process_sale'),
                'formId' => $formId,
                'recurring' => $sale->recurring,
                'fontColor' => OW::getConfig()->getValue($configKey, 'card_detail_font_color'),
                'iconColor' => OW::getConfig()->getValue($configKey, 'card_detail_icon_color'),
                'fontFamily' => OW::getConfig()->getValue($configKey, 'card_detail_font_family'),
                'fontSize' => OW::getConfig()->getValue($configKey, 'card_detail_font_size') . 'px',
                'placeholderColor' => OW::getConfig()->getValue($configKey, 'card_detail_placeholder_color'),
                'errorFontColor' => OW::getConfig()->getValue($configKey, 'card_detail_error_font_color'),
                'errorIconColor' => OW::getConfig()->getValue($configKey, 'card_detail_error_icon_color')
            )
        );

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('billingstripe')->getStaticJsUrl() . 'stripe_billing.js');
    }

    /**
     * Redirect after success sale
     */
    public function afterSale()
    {
        // unset session
        $this->billingService->unsetSessionSale();
        $this->billingService->unsetSessionBackUrl();

        $backUrl = !empty($_POST['redirect']) ? $_POST['redirect'] : OW::getRouter()->getBaseUrl();

        // check success
        if (!empty($_POST['status']) && $_POST['status'] === 'success')
        {
            OW::getFeedback()->info($this->lang->text('base', 'billing_order_completed_successfully'));
            $this->redirect($backUrl);
        }

        // check error
        if (!empty($_POST['status']) && $_POST['status'] === 'error')
        {
            // get error message
            $errorMessage = !empty($_POST['message'])
                ? $_POST['message']
                : $this->lang->text('base', 'billing_order_canceled');

            OW::getFeedback()->error($errorMessage);
            $this->redirect($this->billingService->getOrderFailedPageUrl());
        }

        $this->redirect($backUrl);
    }

    /**
     * Process sale
     *
     * @throws AuthenticateException
     * @throws Redirect403Exception
     */
    public function processSale()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        // get sale session
        $sale = $this->billingService->getSessionSale();

        if ( !$sale )
        {
            exit(json_encode(['status' => 'error', 'message' => $this->lang->text('base', 'billing_order_canceled')]));
        }

        $userId = OW::getUser()->getId();

        // process first step subscription
        if ($sale->recurring && !empty($_POST['token']))
        {
            // get customer
            $customer = $this->stripeService->getCustomer($userId, trim($_POST['token']));

            // check customer
            if (!empty($customer['status']) && $customer['status'] === 'error')
            {
                exit(json_encode($customer));
            }

            // get product adapter
            $productAdapter = $this->billingService->getProductAdapter($sale->entityKey);

            // set product id (plan)
            $productId = strtoupper($productAdapter->getProductKey() . '_' . $sale->entityId);

            // process subscription
            try
            {
                $plan = \Stripe\Plan::retrieve($productId);

                if ($plan)
                {
                    $data = array('plan' => $productId, 'metadata' => array('hash' => $sale->hash));

                    // create subscription
                    try
                    {
                        $subscription = $customer->subscriptions->create($data);

                        // check additional actions (SCA)
                        if ($subscription->status === 'incomplete')
                        {
                            // get latest invoice
                            $lastInvoice = \Stripe\Invoice::retrieve($subscription->latest_invoice);

                            // get payment by invoice
                            $paymentIntentRequiredAction = \Stripe\PaymentIntent::retrieve($lastInvoice->payment_intent);

                            $this->logger->addEntry(print_r($paymentIntentRequiredAction, true), 'billingstripe.subscription-payment');


                            $this->logger->addEntry(print_r($subscription, true), 'billingstripe.subscription-incomplete');
                            $this->logger->writeLog();

                            // pass client_secret for required action (SCA) to client side
                            exit(json_encode([
                                'status' => 'subscription_requires_action',
                                'payment_intent_client_secret' => $paymentIntentRequiredAction->client_secret,
                                'subscriptionId' => $subscription->id
                            ]));
                        }

                        // the subscription didn't need any additional actions and completed
                        // store subscription to db
                        $this->stripeService->storeSubscription($userId, $subscription, $sale);

                        // get back url
                        $backUrl = $this->billingService->getSessionBackUrl();

                        // deliver sale
                        $this->stripeService->deliverSale($subscription->id, $sale);

                        $this->logger->addEntry(print_r($subscription, true), 'billingstripe.subscription-create');
                        $this->logger->writeLog();

                        // pass success status to client side
                        exit(json_encode(['status' => 'success', 'redirect' => $backUrl]));
                    }
                    catch ( Exception $e )
                    {
                        $this->logger->addEntry("Data: " . print_r($data, true) . "\n" .
                            $e->getMessage(), 'billingstripe.subscription-create-error');

                        $this->logger->writeLog();

                        exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
                    }
                }
            }
            catch ( Exception $e )
            {
                $this->logger->addEntry("Product ID: " . $productId . "\n" . $e->getMessage(), 'billingstripe.plan-retrieve-error');
                $this->logger->writeLog();

                exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            }
        }

        // process subscription after required action (SCA)
        if (!empty($_POST['status']) && $_POST['status'] === 'subscription_payment_success')
        {
            try
            {
                // retrieve payment
                $payment = \Stripe\PaymentIntent::retrieve($_POST['paymentId']);

                // retrieve subscription
                $subscription = \Stripe\Subscription::retrieve($_POST['subscriptionId']);

                // check statuses
                if ($payment->status === 'succeeded' && $subscription->metadata['hash'] === $sale->hash) {
                    // store subscription to db
                    $this->stripeService->storeSubscription($userId, $subscription, $sale);

                    // get back url
                    $backUrl = $this->billingService->getSessionBackUrl();

                    // deliver sale
                    $this->stripeService->deliverSale($subscription->id, $sale);

                    // pass success status to client side
                    exit(json_encode(['status' => 'success', 'redirect' => $backUrl]));
                }
            }
            catch (Exception $e)
            {
                $this->logger->addEntry( $e->getMessage(), 'billingstripe.paymentIntent_subscription.retrieve-error');
                $this->logger->writeLog();

                exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            }
        }

        // process first step payment
        if (!$sale->recurring && !empty($_POST['token']) && !empty($_POST['paymentMethodId']))
        {
            // get customer
            $customer = $this->stripeService->getCustomer($userId, trim($_POST['token']));

            // check customer
            if (!empty($customer['status']) && $customer['status'] === 'error')
            {
                exit(json_encode($customer));
            }

            try
            {
                // create the PaymentIntent
                $intent = \Stripe\PaymentIntent::create([
                    'payment_method' => $_POST['paymentMethodId'],
                    'amount' => $sale->totalAmount * 100,
                    'currency' => strtolower($this->billingService->getActiveCurrency()),
                    'confirmation_method' => 'manual',
                    'confirm' => true,
                    'metadata' => ['hash' => $sale->hash],
                    'customer' => $customer->id,
                ]);

                // check error
                if (!empty($intent->last_payment_error))
                {
                    $this->logger->addEntry( print_r($intent, true), 'billingstripe.paymentIntent.create-error');
                    $this->logger->writeLog();

                    exit(json_encode(['status' => 'error', 'message' => $intent->last_payment_error]));
                }

                // check additional actions (SCA)
                if ($intent->status == 'requires_action' && $intent->next_action->type === 'use_stripe_sdk') {
                    // pass client_secret for required action (SCA) to client side
                    exit(json_encode([
                        'status' => 'payment_requires_action', 'payment_intent_client_secret' => $intent->client_secret
                    ]));
                }

                // The payment didn't need any additional actions and completed
                if ($intent->status == 'succeeded') {
                    // store payment to db
                    $this->stripeService->storePaymentToDb($userId, $intent, $sale);

                    // get back url
                    $backUrl = $this->billingService->getSessionBackUrl();

                    // deliver sale
                    $this->stripeService->deliverSale($intent->id, $sale);

                    // confirm intent

                    // pass success status to client side
                    exit(json_encode(['status' => 'success', 'redirect' => $backUrl]));
                }
            }
            catch (Exception $e)
            {
                $this->logger->addEntry( $e->getMessage(), 'billingstripe.paymentIntent.create-error');
                $this->logger->writeLog();

                exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            }
        }

        // process payment after required action (SCA)
        if (!empty($_POST['status']) && $_POST['status'] === 'payment_success' && !empty($_POST['paymentId']))
        {
            try {
                $intent = \Stripe\PaymentIntent::retrieve($_POST['paymentId']);

                // confirm intent
                $intent->confirm();

                if ($intent->status == 'succeeded') {
                    // store payment to db
                    $this->stripeService->storePaymentToDb($userId, $intent, $sale);

                    // get back url
                    $backUrl = $this->billingService->getSessionBackUrl();

                    // deliver sale
                    $this->stripeService->deliverSale($intent->id, $sale);

                    // pass success status to client side
                    exit(json_encode(['status' => 'success', 'redirect' => $backUrl]));
                }
            }
            catch (Exception $e)
            {
                $this->logger->addEntry( $e->getMessage(), 'billingstripe.paymentIntent_retrieve_confirm-error');
                $this->logger->writeLog();

                exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            }
        }
    }

    public function webhook()
    {
        $adapter = new BILLINGSTRIPE_CLASS_StripeAdapter();

        $logger = OW::getLogger('billingstripe_sca.webhook');

        $input = @file_get_contents("php://input");

        $eventJson = json_decode($input);

        $logger->addEntry(print_r($eventJson, true), 'billingstripe.webhook-json');

        $eventId = $eventJson->id;

        try
        {
            $event = \Stripe\Event::retrieve($eventId);

            $logger->addEntry(print_r($event, true), 'billingstripe.event-retrieve');
        }
        catch ( Exception $e )
        {
            $logger->addEntry("Event ID: " . $eventId . "\n" . $e->getMessage(), 'billingstripe.event-retrieve-error');
            $logger->writeLog();

            $this->send200Status();
        }

        if ( !empty($event) && $event->type == 'invoice.payment_succeeded' )
        {
            $invoiceId = $event->data->object->id;

            try
            {
                $invoice = \Stripe\Invoice::retrieve($invoiceId);

                $logger->addEntry(print_r($invoice, true), 'billingstripe.invoice-retrieve');
            }
            catch ( Exception $e )
            {
                $logger->addEntry("Invoice ID: " . $invoiceId . "\n" . $e->getMessage(), 'billingstripe.invoice-retrieve-error');
                $logger->writeLog();

                $this->send200Status();
            }

            // check first invoice
            if (!empty($invoice) && $invoice->billing_reason === 'subscription_create')
            {
                $logger->addEntry("Initial invoice, ID: " . $invoiceId, 'billingstripe.subscription-initial-invoice');
                $logger->writeLog();

                $this->send200Status();
            }

            $customerId = $event->data->object->customer;

            $customer = $this->stripeService->findCustomerByStripeId($customerId);

            if ( !$customer )
            {
                $logger->addEntry("Customer not found, ID: " . $customerId, 'billingstripe.customer-not-found');
                $logger->writeLog();

                $this->send200Status();
            }

            if ( !empty($invoice) )
            {
                foreach ( $invoice->lines->data as $line )
                {
                    if ( $line->type != 'subscription')
                    {
                        $logger->addEntry($line->type, 'line type');
                        continue;
                    }

                    $subscriptionId = $line->subscription;
                    $subscription = $this->stripeService->findSubscriptionByStripeId($subscriptionId);

                    if ( !$subscription )
                    {
                        $logger->addEntry("Subscription not found, ID: " . $subscriptionId, 'billingstripe.subscription-not-found');
                        $logger->writeLog();

                        $this->send200Status();
                    }

                    $sale = $this->billingService->getSaleById($subscription->saleId);

                    // register rebill
                    $rebillTransId = $invoice->id;

                    $gateway = $this->billingService->findGatewayByKey(BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY);

                    if ( $this->billingService->saleDelivered($rebillTransId, $gateway->id) )
                    {
                        $logger->addEntry("Rebill already delivered, transaction ID: " . $rebillTransId, 'billingstripe.subscription-rebill-delivered');
                        $logger->writeLog();

                        $this->send200Status();
                    }

                    $rebillSaleId = $this->billingService->registerRebillSale($adapter, $sale, $rebillTransId);

                    $logger->addEntry("Rebill sale ID: " . (int) $rebillSaleId, 'billingstripe.subscription-rebill-sale-id');

                    if ( $rebillSaleId )
                    {
                        $rebillSale = $this->billingService->getSaleById($rebillSaleId);

                        $productAdapter = $this->billingService->getProductAdapter($rebillSale->entityKey);
                        if ( $productAdapter )
                        {
                            $this->billingService->deliverSale($productAdapter, $rebillSale);

                            $logger->addEntry("Rebill registered, ID: " . $rebillSaleId, 'billingstripe.subscription-rebill');
                        }
                    }
                }
            }
        }

        $logger->writeLog();

        $this->send200Status();
    }

    private function send200Status()
    {
        header("HTTP/1.1 200 OK");

        exit;
    }
}
