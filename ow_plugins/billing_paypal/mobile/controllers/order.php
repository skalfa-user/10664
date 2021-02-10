<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * PayPal order pages controller
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.billing_paypal.mobile.controllers
 * @since 1.0
 */
class BILLINGPAYPAL_MCTRL_Order extends OW_MobileActionController
{
    use BILLINGPAYPAL_CTRL_IpnMethods;

    public function form()
    {
        $billingService = BOL_BillingService::getInstance();
        $adapter = new BILLINGPAYPAL_CLASS_PaypalAdapter();
        $lang = OW::getLanguage();

        $sale = $billingService->getSessionSale();

        if ( !$sale )
        {
            $url = $billingService->getSessionBackUrl();
            if ( $url != null )
            {
                OW::getFeedback()->warning($lang->text('base', 'billing_order_canceled'));
                $billingService->unsetSessionBackUrl();
                $this->redirect($url);
            }
            else 
            {
                $this->redirect($billingService->getOrderFailedPageUrl());
            }
        }

        $formId = uniqid('order_form-');
        $this->assign('formId', $formId);

        $js = '$("#' . $formId . '").submit()';
        OW::getDocument()->addOnloadScript($js);

        $fields = $adapter->getFields(null, true);

        $partnerCode = $adapter->getPartnerCode();
        $this->assign('fields', $fields);
        $this->assign('partnerCode', $partnerCode);

        if ( $billingService->prepareSale($adapter, $sale) )
        {
            $sale->totalAmount = floatval($sale->totalAmount);
            $this->assign('sale', $sale);
            $this->assign('monthPeriod', intval($sale->period / 30));

            $billingService->unsetSessionSale();
        }
        else
        {
            $productAdapter = $billingService->getProductAdapter($sale->entityKey);

            if ( $productAdapter )
            {
                $productUrl = $productAdapter->getProductOrderUrl();
            }
            
            OW::getFeedback()->warning($lang->text('base', 'billing_order_init_failed'));
            $url = isset($productUrl) ? $productUrl : $billingService->getOrderFailedPageUrl();
            
            $this->redirect($url);
        }
    }

    public function notify()
    {
        $logger = OW::getLogger('billingpaypal');
        $logger->addEntry(print_r($_POST, true), 'ipn.data-array');
        $logger->writeLog();

        if ( empty($_POST['custom']) )
        {
            exit;
        }

        $hash = trim($_POST['custom']);

        $amount = !empty($_POST['mc_gross']) ? $_POST['mc_gross'] : $_POST['payment_gross'];
        $transactionId = trim($_POST['txn_id']);
        $status = mb_strtoupper(trim($_POST['payment_status']));
        $currency = trim($_POST['mc_currency']);
        $transactionType = trim($_POST['txn_type']);
        $business = isset($_REQUEST['business']) ? trim($_REQUEST['business']) : trim($_REQUEST['receiver_email']);

        $billingService = BOL_BillingService::getInstance();
        $adapter = new BILLINGPAYPAL_CLASS_PaypalAdapter();

        if ( $adapter->isVerified($_POST) )
        {
            $sale = $billingService->getSaleByHash($hash);

            if ( !$sale || !strlen($transactionId) )
            {
                exit;
            }

            if ( $amount != $sale->totalAmount )
            {
                $logger->addEntry("Wrong amount: " . $amount , 'notify.amount-mismatch');
                $logger->writeLog();
                exit;
            }

            if ( $billingService->getGatewayConfigValue(BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY, 'business') != $business )
            {
                $logger->addEntry("Wrong PayPal account: " . $business , 'notify.account-mismatch');
                $logger->writeLog();
                exit;
            }

            if ( $status == 'COMPLETED' )
            {
                switch ( $transactionType )
                {
                    case 'web_accept':
                    case 'subscr_payment':
                        if ( !$billingService->saleDelivered($transactionId, $sale->gatewayId) )
                        {
                            $sale->transactionUid = $transactionId;

                            if ( $billingService->verifySale($adapter, $sale) )
                            {
                                $sale = $billingService->getSaleById($sale->id);

                                $productAdapter = $billingService->getProductAdapter($sale->entityKey);

                                if ( $productAdapter )
                                {
                                    $billingService->deliverSale($productAdapter, $sale);
                                }
                            }
                        }
                        break;

                    case 'recurring_payment':
                        $rebillTransId = $_REQUEST['recurring_payment_id'];

                        $gateway = $billingService->findGatewayByKey(BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY);

                        if ( $billingService->saleDelivered($rebillTransId, $gateway->id) )
                        {
                            exit;
                        }

                        $rebillSaleId = $billingService->registerRebillSale($adapter, $sale, $rebillTransId);

                        if ( $rebillSaleId )
                        {
                            $rebillSale = $billingService->getSaleById($rebillSaleId);

                            $productAdapter = $billingService->getProductAdapter($rebillSale->entityKey);
                            if ( $productAdapter )
                            {
                                $billingService->deliverSale($productAdapter, $rebillSale);
                            }
                        }
                        break;
                }
            }
        }
        else
        {
            exit;
        }
    }

    public function completed()
    {

        $hash = !empty($_REQUEST['cm']) ? $_REQUEST['cm'] : $_REQUEST['custom'];

        $this->redirect(BOL_BillingService::getInstance()->getOrderCompletedPageUrl($hash, true));
    }

    public function canceled()
    {
        $this->redirect(BOL_BillingService::getInstance()->getOrderCancelledPageUrl(null, true));
    }
}