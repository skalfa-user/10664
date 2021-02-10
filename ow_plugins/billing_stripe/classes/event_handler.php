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
 * @package ow_plugins.billing_stripe.classes
 * @since 1.6.0
 */
class BILLINGSTRIPE_CLASS_EventHandler
{
    /**
     * @var BILLINGSTRIPE_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return BILLINGSTRIPE_CLASS_EventHandler
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

        $sandboxMode = $billingService->getGatewayConfigValue(BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY, 'sandboxMode');

        if ( $sandboxMode )
        {
            $pKey = $billingService->getGatewayConfigValue(BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY, 'testPK');
            $sKey = $billingService->getGatewayConfigValue(BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY, 'testSK');
        }
        else
        {
            $pKey = $billingService->getGatewayConfigValue(BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY, 'livePK');
            $sKey = $billingService->getGatewayConfigValue(BILLINGSTRIPE_CLASS_StripeAdapter::GATEWAY_KEY, 'liveSK');
        }

        if ( !mb_strlen($pKey) || !mb_strlen($sKey) )
        {
            $coll->add(
                OW::getLanguage()->text(
                    'billingstripe',
                    'plugin_configuration_notice',
                    array('url' => OW::getRouter()->urlForRoute('billingstripe.admin'))
                )
            );
        }
    }

    public function addAccessException( BASE_CLASS_EventCollector $e )
    {
        $e->add(array('controller' => 'BILLINGSTRIPE_CTRL_Action', 'action' => 'webhook'));
    }

    public function genericInit()
    {
        $em = OW::getEventManager();
        $em->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $em->bind('base.members_only_exceptions', array($this, 'addAccessException'));
        $em->bind('base.password_protected_exceptions', array($this, 'addAccessException'));
        $em->bind('base.splash_screen_exceptions', array($this, 'addAccessException'));
    }

    public function init()
    {
        $this->genericInit();
    }
}