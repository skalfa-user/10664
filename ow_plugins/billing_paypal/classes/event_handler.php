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
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow_plugins.billing_paypal.classes
 * @since 1.6.0
 */
class BILLINGPAYPAL_CLASS_EventHandler
{
    /**
     * @var BILLINGPAYPAL_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return BILLINGPAYPAL_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function addAdminNotification( BASE_CLASS_EventCollector $coll )
    {
        $billingService = BOL_BillingService::getInstance();

        if ( !mb_strlen($billingService->getGatewayConfigValue(BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY, 'business')) )
        {
            $coll->add(
                OW::getLanguage()->text(
                    'billingpaypal',
                    'plugin_configuration_notice',
                    array('url' => OW::getRouter()->urlForRoute('billing_paypal_admin'))
                )
            );
        }
    }

    public function addAccessException( BASE_CLASS_EventCollector $e )
    {
        $e->add(array('controller' => 'BILLINGPAYPAL_CTRL_Order', 'action' => 'notify'));
    }

    public function init()
    {
        $em = OW::getEventManager();

        $em->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $em->bind('base.members_only_exceptions', array($this, 'addAccessException'));
        $em->bind('base.password_protected_exceptions', array($this, 'addAccessException'));
        $em->bind('base.splash_screen_exceptions', array($this, 'addAccessException'));
    }
}