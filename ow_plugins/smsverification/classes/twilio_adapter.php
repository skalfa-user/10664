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
 * Paymentwall billing gateway adapter class.
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.smsverification.classes
 * @since 1.7.6
 */
class SMSVERIFICATION_CLASS_TwilioAdapter
{

    public function __construct()
    {
       
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getLogoUrl()
     */
    public function getLogoUrl()
    {
        $plugin = OW::getPluginManager()->getPlugin('billingpwall');

        return $plugin->getStaticUrl() . 'img/pwall_logo.png';
    }
    
    /**
     * @return string
     */
    public static function getAccountSID()
    {
        $sandboxMode = OW::getConfig()->getValue('smsverification', 'sandboxMode');

        if ( $sandboxMode )
        {
            return OW::getConfig()->getValue('smsverification', 'testAccountSID');
        }
        else
        {
            return OW::getConfig()->getValue('smsverification', 'accountSID');
        }
    }

    /**
     * @return string
     */
    public static function getAuthToken()
    {
        $sandboxMode = OW::getConfig()->getValue('smsverification', 'sandboxMode');

        if ( $sandboxMode )
        {
            return OW::getConfig()->getValue('smsverification', 'testAuthToken');
        }
        else
        {
            return OW::getConfig()->getValue('smsverification', 'authToken');
        }
    }
    
    /**
     * @return string
     */
    public static function getTwilioTelNumber()
    {
        return OW::getConfig()->getValue('smsverification', 'twilioTelNumber');
    }
    
   
}