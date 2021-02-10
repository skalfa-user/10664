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
class SKMOBILEAPP_CLASS_EventHandler extends SKMOBILEAPP_CLASS_AbstractEventHandler
{
    use OW_Singleton;

    /**
     * Init
     */
    public function init()
    {
        parent::genericInit();

        $eventManager = OW::getEventManager();
        $requestHandler = OW::getRequestHandler();

	    $requestHandler->addCatchAllRequestsExclude('base.splash_screen', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.password_protected', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.members_only', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.maintenance_mode', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.email_verify', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.suspended_user', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.wait_for_approval', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.complete_profile', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.complete_profile.account_type', 'SKMOBILEAPP_CTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('lpage.main', 'SKMOBILEAPP_CTRL_Api', 'index');

        $eventManager->bind('class.get_instance.LPAGE_CTRL_Base', array($this, 'onGettingLpageCtrlBaseInstance'));
        $eventManager->bind('class.get_instance.SKADATE_CMP_MobileExperience', array($this, 'onGettingSkadateCmpMobileExperienceInstance'));
        $eventManager->bind('admin.add_admin_notification', array($this, 'onAddingAdminNotifications'));
        $eventManager->bind('usercredits.get_product_id', array($this, 'usercreditsGetProductId'), 100000);
        $eventManager->bind('membership.get_product_id', array($this, 'membershipGetProductId'), 100000);
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'beforePluginUninstall'));
    }

    /**
     * Before plugin uninstall
     */
    public function beforePluginUninstall( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] == 'skmobileapp' ) 
        {
            OW::getFeedback()->warning(OW::getLanguage()->text('skmobileapp', 'plugin_delete_warning'));

            throw new RedirectException(OW::getRouter()->urlForRoute('admin_plugins_installed'));
        }
    }

    /**
     * User credits get product id
     *
     * @param OW_Event $e
     * @return void
     */
    public function usercreditsGetProductId( OW_Event $e )
    {
        $params = $e->getParams();

        $productId = mb_strtolower(USERCREDITS_CLASS_UserCreditsPackProductAdapter::PRODUCT_KEY . '_' . $params['id']);

        $e->setData($productId);
    }

    /**
     * Membership get product id
     *
     * @param OW_Event $e
     * @return void
     */
    public function membershipGetProductId( OW_Event $e )
    {
        $params = $e->getParams();

        $productId = mb_strtolower(MEMBERSHIP_CLASS_MembershipPlanProductAdapter::PRODUCT_KEY . '_' . $params['id']);

        $e->setData($productId);
    }

    /**
     * On adding admin notifications
     *
     * @param ADMIN_CLASS_NotificationCollector $e
     * @return void
     */
    public function onAddingAdminNotifications( ADMIN_CLASS_NotificationCollector $e )
    {
        list($isApplicationReady, $configurationError) = SKMOBILEAPP_BOL_Service::getInstance()->isApplicationReadyForUsage();

        if ( !$isApplicationReady )
        {
            $e->add($configurationError, ADMIN_CLASS_NotificationCollector::NOTIFICATION_WARNING);
        }
    }

    /**
     * On getting LPAGE_CTRL_Base instance
     *
     * @param OW_Event $event
     * @return void
     */
    public function onGettingLpageCtrlBaseInstance( OW_Event $event )
    {
        $event->setData( new SKMOBILEAPP_CTRL_LandingPage() );
    }

    /**
     * On getting SKADATE_CMP_MobileExperience instance
     *
     * @param OW_Event $event
     * @return void
     */
    public function onGettingSkadateCmpMobileExperienceInstance( OW_Event $event )
    {
        $event->setData( new SKMOBILEAPP_CMP_MobileExperience( $event->getParams()['arguments'][0] ) );
    }
}
