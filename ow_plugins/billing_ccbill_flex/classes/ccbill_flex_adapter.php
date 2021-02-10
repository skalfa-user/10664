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
 * CCBill billing gateway adapter class.
 *
 * @author Sergey Pryadkin <GIperProger@gmail.com>
 * @package ow.ow_plugins.billing_ccbill_flex.classes
 * @since 1.8.6
 */

class BILLINGCCBILLFLEX_CLASS_CcbillFlexAdapter implements OW_BillingAdapter
{
    const GATEWAY_KEY = 'billingccbillflex';

    const FLEX_FORM_BASE_URL_LIVE = 'https://api.ccbill.com/wap-frontflex/flexforms/';

    const FLEX_FORM_BASE_URL_SANDBOX = 'https://sandbox-api.ccbill.com/wap-frontflex/flexforms/';

    /**
     * @var BOL_BillingService
     */
    private $billingService;
    /**
     * @var array // CCBILL has their own currency codes
     */
    private static $currencies = array(
        'USD' => 840, 'EUR' => 978, 'AUD' => 036, 'CAD' => 124, 'GBP' => 826, 'JPY' => 392
    );

    public function __construct()
    {
        $this->billingService = BOL_BillingService::getInstance();
    }

    public function prepareSale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    public function verifySale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    /**
     * @param null $params
     * @return array
     */
    public function getFields($params = null )
    {
        // call event to get subaccount config
        $event = new OW_Event('billingccbill.get-subaccount-config', array('pluginKey' => $params['pluginKey'], 'entityKey' => $params['entityKey']));
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        $subaccount = !empty($data) ? $data : $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'clientSubacc');

        return [
            'salt' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'dynamicPricingSalt'),
            'flexFormId' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY,'flexFormId'),
            'clientAccnum' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'clientAccnum'),
            'clientSubacc' => $subaccount, //we use two different subaccounts for user credits and membership plugins
        ];
    }

    public function getOrderFormUrl()
    {
        return OW::getRouter()->urlForRoute(self::GATEWAY_KEY.'_order_form');
    }

    public function getLogoUrl()
    {
        $plugin = OW::getPluginManager()->getPlugin(self::GATEWAY_KEY);

        return $plugin->getStaticUrl() . 'img/ccbill_logo.png';
    }

    public static function getCurrencies()
    {
        $result = array();

        foreach ( self::$currencies as $cur => $code )
        {
            $result[$cur] = $cur;
        }

        return $result;
    }

    /**
     * Returns active currency corresponding code
     * 
     * @return int
     */
    public function getActiveCurrencyCode()
    {
        $currency = $this->billingService->getActiveCurrency();

        if ( mb_strlen($currency) && key_exists($currency, self::$currencies) )
        {
            return self::$currencies[$currency];
        }

        return null;
    }

    public function getAdditionalSubaccounts()
    {
        // collect additional subaccount configs
        $event = new BASE_CLASS_EventCollector('billingccbill.collect-subaccount-fields', array());
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( !is_array($data) )
        {
            return null;
        }

        $billingService = BOL_BillingService::getInstance();
        $subaccounts = array();
        foreach ( $data as $sub )
        {
            if ( isset($sub['key']) && isset($sub['label']) )
            {
                $subaccounts[$sub['key']] = array(
                    'label' => $sub['label'],
                    'value' => $billingService->getGatewayConfigValue(self::GATEWAY_KEY, $sub['key'])
                );
            }
        }

        return $subaccounts;
    }

    /**
     * Generates digest key for single transaction
     *
     * @param float $formPrice
     * @param int $formPeriod
     * @param int $currencyCode
     * @param string $salt CCBill uses your salt value to verify the hash and can be obtained in one of two ways:
                    Contact merchant support and receive the salt value, OR
                    Create your own salt value (up to 24 alphanumeric characters) and provide it to Merchant Support.
     * @return string
     */
    public function generateSingleTransactionDigest( $formPrice, $formPeriod, $currencyCode, $salt )
    {
        return md5($formPrice . $formPeriod . $currencyCode . $salt);
    }

    /**
     * Generates digest key for recurring transaction
     *
     * @param float $formPrice
     * @param int $formPeriod
     * @param float $formRecurringPrice
     * @param int $formRecurringPeriod
     * @param int $formRebills
     * @param int $currencyCode
     * @param string $salt  CCBill uses your salt value to verify the hash and can be obtained in one of two ways:
                            Contact merchant support and receive the salt value, OR
                            Create your own salt value (up to 24 alphanumeric characters) and provide it to Merchant Support.
     * @return string
     */
    public function generateRecurringTransactionDigest( $formPrice, $formPeriod, $formRecurringPrice, $formRecurringPeriod, $formRebills, $currencyCode, $salt )
    {
        return md5($formPrice . $formPeriod . $formRecurringPrice . $formRecurringPeriod . $formRebills . $currencyCode . $salt);
    }

    /**
     * @param $flexFormId
     * @param $cliendSubacc
     * @param $initialPrice
     * @param $initialPeriod
     * @param $activeCurrecyCode
     * @param $formDigest
     * @param $saleHash
     * @param bool $isRecurring //set true if you want to create recurring payment
     * @param null $recurringPrice //pass this parameter if you want to create recurring payment
     * @param null $recurringPeriod //pass this parameter if you want to create recurring payment
     * @param null $numRebills //pass this parameter if you want to create recurring payment
     * @return string
     */
    public function composePaymentRedirectUrl(
        $flexFormId,
        $cliendSubacc,
        $initialPrice,
        $initialPeriod,
        $activeCurrecyCode,
        $formDigest,
        $saleHash,
        $isRecurring = false,
        $recurringPrice = null,
        $recurringPeriod = null,
        $numRebills = null

        ){

        $sandboxMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY,'sandbox');

        $redirectUrl = $sandboxMode ? self::FLEX_FORM_BASE_URL_SANDBOX : self::FLEX_FORM_BASE_URL_LIVE;

        $redirectUrl .=
            $flexFormId . '?' .
            'clientSubacc='.$cliendSubacc .
            '&initialPrice=' . $initialPrice .
            '&initialPeriod=' . $initialPeriod .
            '&currencyCode=' . $activeCurrecyCode .
            '&custom=' . $saleHash .
            '&formDigest=' . $formDigest ;

        if( $isRecurring && ( !$recurringPrice || !$recurringPeriod || !$numRebills )){

            $errorMessageLang = OW::getUser()->isAdmin() ? 'some_recurring_parameters_missed' :'billing_order_canceled';
            OW::getFeedback()->error(OW::getLanguage()->text(self::GATEWAY_KEY, $errorMessageLang));

            $subscribeRoute = OW::getPluginManager()->isPluginActive('membership') ?
                'membership_subscribe' :
                'usercredits.buy_credits';

            OW::getApplication()->redirect(OW::getRouter()->urlForRoute($subscribeRoute));
        }
        else if($isRecurring){
            $redirectUrl .=
            '&recurringPrice=' . $recurringPrice .
            '&recurringPeriod=' . $recurringPeriod.
            '&numRebills=' . $numRebills;
        }
        
        return $redirectUrl;

    }

    /**
     * Checks if transaction was approved
     *
     * @param $clientAccnum
     * @param $clientSubacc
     * @param $transId
     * @param $digest
     * @param $sale BOL_BillingSale
     * @return boolean
     */
    public function transactionApproved( $clientAccnum, $clientSubacc, $transId, $digest, $sale )
    {
        if ( $clientAccnum != $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'clientAccnum') )
        {
            return false;
        }

        return true;

        /*
                $salt = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'dynamicPricingSalt');

                $calcDigest = md5($transId . 1 . $salt);


                return strcmp($calcDigest, $digest) == 0;
        */

    }

}