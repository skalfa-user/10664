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
 * CCBillFlex order pages controller
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.billing_ccbill_flex.controllers
 * @since 1.8.6
 */
class BILLINGCCBILLFLEX_CTRL_PostBackHandler extends OW_ActionController
{
    /**
     * @var string
     */
    protected $pluginKey;

    public function __construct()
    {
        parent::__construct();

        $this->pluginKey = BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter::GATEWAY_KEY;
    }

    public function postback()
    {
        $logger = OW::getLogger($this->pluginKey);
        $logger->addEntry(print_r($_POST, true), 'postback.data-array');
        $logger->writeLog();

        $clientAccnum = $_POST['clientAccnum'];
        $clientSubacc = $_POST['clientSubacc'];
        $amount = $_POST['initialPrice'] ? $_POST['initialPrice'] : $_POST['recurringPrice'];
        $saleHash = $_POST['custom'];
        $transId = $_POST['subscription_id'];
        $digest = $_POST['responseDigest'];

        if ( !mb_strlen($saleHash) )
        {
            $logger->addEntry('Missed sale hash', 'postback.data-array');
            $logger->writeLog();
            exit;
        }

        if ( !mb_strlen($transId) )
        {
            $logger->addEntry('Missed transaction id', 'postback.data-array');
            $logger->writeLog();
            exit;
        }

        $billingService = BOL_BillingService::getInstance();
        $adapter = new BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter();

        $sale = $billingService->getSaleByHash($saleHash);

        if ( !$sale )
        {
            $logger->addEntry('Empty sale object', 'postback.data-array');
            $logger->writeLog();
            exit;
        }

        if ( $amount != $sale->totalAmount )
        {
            $logger->addEntry("Wrong amount: " . $amount , 'postback.amount-mismatch');
            $logger->writeLog();
            exit;
        }

        if ( $billingService->getGatewayConfigValue($this->pluginKey, 'clientAccnum') != $clientAccnum )
        {
            $logger->addEntry("Wrong CCBill account: " . $clientAccnum , 'postback.account-mismatch');
            $logger->writeLog();
            exit;
        }

        if ( $adapter->transactionApproved($clientAccnum, $clientSubacc, $transId, $digest, $sale) )
        {
            if ( $sale->status != BOL_BillingSaleDao::STATUS_DELIVERED )
            {
                $sale->transactionUid = $transId;

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
                        $logger->addEntry('Empty product adapter object', 'postback.data-array');
                        $logger->writeLog();
                    }
                }
                else
                {
                    $logger->addEntry('Verify sale problem', 'postback.data-array');
                    $logger->writeLog();
                }
            }
            else
            {
                $logger->addEntry('Not delivered status', 'postback.data-array');
                $logger->writeLog();
            }
        }
        else
        {
            $logger->addEntry('Transaction not approved', 'postback.data-array');
            $logger->writeLog();
        }

        exit;
    }
}