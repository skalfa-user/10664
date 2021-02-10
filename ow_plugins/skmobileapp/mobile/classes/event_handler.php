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
class SKMOBILEAPP_MCLASS_EventHandler extends SKMOBILEAPP_CLASS_AbstractEventHandler
{
    use OW_Singleton;

    /**
     * Init
     */
    public function init()
    {
        parent::genericInit();

        $requestHandler = OW::getRequestHandler();

	    $requestHandler->addCatchAllRequestsExclude('base.splash_screen', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.password_protected', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.members_only', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.maintenance_mode', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.email_verify', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.suspended_user', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.wait_for_approval', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.complete_profile', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('base.complete_profile.account_type', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('lpage.main', 'SKMOBILEAPP_MCTRL_Api', 'index');
        $requestHandler->addCatchAllRequestsExclude('skmobile.pwa', 'SKMOBILEAPP_MCTRL_Api', 'index');

        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'onAfterRoute'));
    }

    /**
     * On after route
     */
    public function onAfterRoute($event) 
    {
        try {
            if ( SKMOBILEAPP_BOL_Service::REDIRECT_TO_FIREBIRD )
            {
                $route = OW::getRouter()->route();

                // Skip api requests
                if ( $route && $route['controller'] === 'SKMOBILEAPP_MCTRL_Api' )
                {
                    return;
                }

                // redirect to the PWA version (only if user on the root page)
                if ( $route && $route['controller'] === 'BASE_MCTRL_WidgetPanel' && $route['action'] == 'index' )
                {
                    UTIL_Url::redirect(SKMOBILEAPP_BOL_Service::getInstance()->getPwaUrl());

                    return;
                }

                // redirect all other requests to the desktop version
                if ( SKMOBILEAPP_BOL_Service::REDIRECT_LINKS_TO_DESKTOP ) 
                {
                    $requestUri = OW::getRequest()->getRequestUri();
                    OW::getApplication()->redirect($requestUri, OW::CONTEXT_DESKTOP);
                }
            }
        }
        catch(Exception $e) {
            // redirect all exception's requests to the desktop version
            if ( SKMOBILEAPP_BOL_Service::REDIRECT_LINKS_TO_DESKTOP )
            {
                $requestUri = OW::getRequest()->getRequestUri();
                OW::getApplication()->redirect($requestUri, OW::CONTEXT_DESKTOP);
            }
        }
    }
}
