<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CUSTOMINDEX_CLASS_EventHandler extends CUSTOMINDEX_CLASS_BaseEventHandler
{
    /**
     * Init
     */
    public function init()
    {
        parent::genericInit();

        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, [$this, 'onGettingMasterPage']);
        OW::getEventManager()->bind('base.ping', array($this, 'onGettingPing'));
        OW::getEventManager()->bind('admin.add_admin_notification', array($this, 'onAddingAdminNotifications'));
        OW::getEventManager()->bind('class.get_instance.BASE_CMP_UserList', array($this, 'onGettingUserListComponentInstance'));
    }

    /**
     * On getting user list component instance
     */
    public function onGettingUserListComponentInstance( OW_Event $event )
    {
        $data = OW::getClassInstance('CUSTOMINDEX_CMP_UserList');

        $event->setData($data);
    }

    /**
     * On adding admin notifications
     *
     * @param ADMIN_CLASS_NotificationCollector $e
     * @return void
     */
    public function onAddingAdminNotifications( ADMIN_CLASS_NotificationCollector $e )
    {
        list($isPluginReady, $configurationError) = CUSTOMINDEX_BOL_Service::getInstance()->isPluginReadyForUsage();

        if ( !$isPluginReady )
        {
            $e->add($configurationError, ADMIN_CLASS_NotificationCollector::NOTIFICATION_WARNING);
        }
    }

    /**
     * On getting ping
     *
     * @param OW_Event $event
     * @return void
     */
    public function onGettingPing( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['command'] != CUSTOMINDEX_BOL_Service::PLUGIN_KEY . '_users_count' )
        {
            return;
        }

       $event->setData([
           'count' => CUSTOMINDEX_BOL_Service::getInstance()->getUsersCount()
       ]);
    }

    /**
     * On getting master page
     *
     * @return void
     */
    public function onGettingMasterPage()
    {
        list($isPluginReady) = CUSTOMINDEX_BOL_Service::getInstance()->isPluginReadyForUsage();

        if ( $isPluginReady )
        {
            $request = OW::getRequestHandler()->getHandlerAttributes();

            if ( isset($request['controller'], $request['action']) &&
                $request['controller'] == 'BASE_CTRL_ComponentPanel' && $request['action'] == 'index' )
            {
                // change the master page
                $template = OW::getPluginManager()->
                        getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY)->getRootDir() . 'views/master_pages/index.html';


                // init view variables
                OW_ViewRenderer::getInstance()->assignVar('isAuthenticated', OW::getUser()->isAuthenticated());
                OW_ViewRenderer::getInstance()->assignVar('isUserSearchInstalled', OW::getPluginManager()->isPluginActive('usearch'));
                OW_ViewRenderer::getInstance()->assignVar('isPhotoInstalled', OW::getPluginManager()->isPluginActive('photo'));

                if ( !OW::getUser()->getId( )) 
                {
                    OW::getDocument()->addBodyClass('user_not_logged');
                }

                OW::getDocument()->getMasterPage()->setTemplate($template);
            }
        }
    }
}
