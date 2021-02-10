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
 * Paypal billing gateway adapter class.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.billing_paypal.classes
 * @since 1.0
 */
class BILLINGPAYPAL_CLASS_PaypalAdapter implements OW_BillingAdapter
{
    const GATEWAY_KEY = 'billingpaypal';
    const PARTNER_CODE = 'Skalfa_SP';

    /**
     * @var BOL_BillingService
     */
    private $billingService;

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
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getFields($params)
     */
    public function getFields( $params = null, $mobile = false )
    {
        $router = OW::getRouter();

            return array(
                'return' => $router->urlForRoute('billing_paypal_completed'),
                'cancel_return' => $router->urlForRoute('billing_paypal_canceled'),
                'notify_url' => OW::getRouter()->urlForRoute('billing_paypal_notify'),
                'business' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'business'),
                'form_action_url' => $this->getOrderFormActionUrl(),
                'bn' => self::PARTNER_CODE,
                'no_shipping' => (int)!$this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'shippingAddress'),
            );
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getOrderFormUrl()
     */
    public function getOrderFormUrl()
    {
        return OW::getRouter()->urlForRoute('billing_paypal_order_form');
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getLogoUrl()
     */
    public function getLogoUrl( $mobile = false )
    {
        $plugin = OW::getPluginManager()->getPlugin('billingpaypal');

        if($mobile)
        {
            return $plugin->getStaticUrl() . 'img/paypal_logo_mobile.png';
        }
        else
        {
            return $plugin->getStaticUrl() . 'img/paypal_logo.png';

        }

    }

    /**
     * Returns Paypal gateway script url (sandbox or live)
     * 
     * @return string
     */
    private function getOrderFormActionUrl()
    {
        $sandboxMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');

        return $sandboxMode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
    }

    /**
     * Posts data back to PayPal for order verification
     *  
     * @param array $post
     * @return boolean
     */
    public function isVerified( $post )
    {
        $sandboxMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');

        if( $sandboxMode )
        {
            return true;
        }

        $sandboxMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');
        $hostname = $sandboxMode ? 'www.sandbox.paypal.com' : 'www.paypal.com';

        $nvpStr = '';
        foreach ( $post as $key => $value )
        {
            $value = urlencode(stripslashes($value));
            $nvpStr .= "$key=$value&";
        }
        $nvpStr .= 'cmd=_notify-validate';

        $str = file_get_contents('https://' . $hostname . '/cgi-bin/webscr?' . $nvpStr);

        return mb_strstr($str, 'VERIFIED') !== false;
    }

    public function getExtraFields( BOL_BillingSale $sale )
    {

        $fields = [
            ['key' => 'rm', 'value' => 2],
            ['key' => 'item_name', 'value' => $sale->entityDescription],
            ['key' => 'currency_code', 'value' => $sale->currency],
            ['key' => 'custom', 'value' => $sale->hash],
            ['key' => 'charset', 'value' => 'utf-8'],
            ['key' => 'no_note', 'value' => '1'],
        ];

        if( $sale->recurring )
        {
            $fields[] = ['key' => 'cmd', 'value' => '_xclick-subscriptions'];
            $fields[] = ['key' => 'a3', 'value' => floatval($sale->totalAmount)];
            $fields[] = ['key' => 't3', 'value' => strtoupper($sale->periodUnits[0])];
            $fields[] = ['key' => 'src', 'value' => 1];
            $fields[] = ['key' => 'p3', 'value' => $sale->period];
        }
        else
        {
            $fields[] = ['key' => 'cmd', 'value' => '_xclick'];
            $fields[] = ['key' => 'amount', 'value' => $sale->totalAmount];

        }

        return $fields;


    }
}