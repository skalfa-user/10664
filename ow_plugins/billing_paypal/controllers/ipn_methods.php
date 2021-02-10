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

trait BILLINGPAYPAL_CTRL_IpnMethods
{
    public function notify()
    {
        $logger = OW::getLogger('billingpaypal');
        $logger->addEntry(print_r($_POST, true), 'ipn.data-array');
        $logger->writeLog();

        if ( empty($_POST['custom']) )
        {
            $logger->addEntry('Empty post[custom]', 'ipn.data-array');
            $logger->writeLog();
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

            if ( !$sale )
            {
                $logger->addEntry('Empty sale object', 'ipn.data-array');
                $logger->writeLog();
                exit;
            }

            if ( !strlen($transactionId) )
            {
                $logger->addEntry('Empty transaction ID', 'ipn.data-array');
                $logger->writeLog();
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

            if ( $status == 'COMPLETED' ||  $status == 'PENDING' )
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
                                else
                                {
                                    $logger->addEntry('Empty product adapter object', 'ipn.data-array');
                                    $logger->writeLog();
                                }
                            }
                            else
                            {
                                $logger->addEntry('Unverified sale', 'ipn.data-array');
                                $logger->writeLog();
                            }
                        }
                        else
                        {
                            $logger->addEntry('Sale not delivered', 'ipn.data-array');
                            $logger->writeLog();
                        }
                        break;

                    case 'recurring_payment':
                        $rebillTransId = $_REQUEST['recurring_payment_id'];

                        $gateway = $billingService->findGatewayByKey(BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY);

                        if ( $billingService->saleDelivered($rebillTransId, $gateway->id) )
                        {
                            $logger->addEntry('Sale already delivered(rec)', 'ipn.data-array');
                            $logger->writeLog();
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
                            else
                            {
                                $logger->addEntry('Empty product adapter(rec)', 'ipn.data-array');
                                $logger->writeLog();
                            }
                        }
                        else
                        {
                            $logger->addEntry('Empty rebill saleId(rec)', 'ipn.data-array');
                            $logger->writeLog();
                        }
                        break;
                }
            }
            else
            {
                $logger->addEntry('Uncompleted sale status ', 'ipn.data-array');
                $logger->writeLog();
            }
        }
        else
        {
            $logger->addEntry('Unverified POST', 'ipn.data-array');
            $logger->writeLog();
            exit;
        }

        exit;
    }

    public function completed()
    {
        $hash = !empty($_REQUEST['cm']) ? $_REQUEST['cm'] : $_REQUEST['custom'];
        $this->redirect(BOL_BillingService::getInstance()->getOrderCompletedPageUrl($hash));
    }

    public function canceled()
    {
        $this->redirect(BOL_BillingService::getInstance()->getOrderCancelledPageUrl());
    }
}






