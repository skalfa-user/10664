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
 * CCBill order pages controller
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.billing_ccbill_flex.controllers
 * @since 1.8.6
 */


class BILLINGCCBILLFLEX_CTRL_Order extends OW_ActionController
{
    /**
     * @var BOL_BillingService
     */
    protected $billingService;

    /**
     * @var BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter
     */
    protected $ccbillFlexAdapter;

    /**
     * @var string
     */
    protected $pluginKey;

    public function __construct()
    {
        parent::__construct();

        $this->billingService = BOL_BillingService::getInstance();
        $this->ccbillFlexAdapter = new BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter();
        $this->pluginKey = BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter::GATEWAY_KEY;
    }

    public function form()
    {
        $sale = $this->billingService->getSessionSale();
        $formDigest = null; // An MD5 Hex Digest based on the some values. The set of values depends on single or subscription payment;
        $activeCurrencyCode = $this->ccbillFlexAdapter->getActiveCurrencyCode();
        $redirectUrl = null;

        $fieldsParams = array(
            'pluginKey' => $sale->pluginKey,
            'entityKey' => $sale->entityKey
        );
        
        $fieds = $this->ccbillFlexAdapter->getFields($fieldsParams);

        if ( $sale->recurring ) { // compose redirect url for subscription payments
            $numRebills = 99; //An integer representing the total times the subscription will rebill. Passing a value of 99 will cause the subscription to rebill indefinitely.
            $formDigest = $this->ccbillFlexAdapter->generateRecurringTransactionDigest(
                sprintf("%01.2f", $sale->totalAmount),
                $sale->period,
                sprintf("%01.2f", $sale->totalAmount),
                $sale->period,
                $numRebills,
                $this->ccbillFlexAdapter->getActiveCurrencyCode(),
                $fieds['salt']
            );

            $redirectUrl = $this->ccbillFlexAdapter->composePaymentRedirectUrl(
                $fieds['flexFormId'],
                $fieds['clientSubacc'],
                sprintf("%01.2f", $sale->totalAmount),
                $sale->period,
                $activeCurrencyCode,
                $formDigest,
                $sale->hash,
                true,
                sprintf("%01.2f", $sale->totalAmount),
                $sale->period,
                $numRebills
            );

        }
        else { // compose redirect url for single payments
            $formDigest = $this->ccbillFlexAdapter->generateSingleTransactionDigest(
                sprintf("%01.2f", $sale->totalAmount),
                $sale->period,
                $this->ccbillFlexAdapter->getActiveCurrencyCode(),
                $fieds['salt']
            );

            $redirectUrl = $this->ccbillFlexAdapter->composePaymentRedirectUrl(
                $fieds['flexFormId'],
                $fieds['clientSubacc'],
                sprintf("%01.2f", $sale->totalAmount),
                $sale->period,
                $activeCurrencyCode,
                $formDigest,
                $sale->hash
            );
        }

        $this->redirect($redirectUrl);

    }
}