<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class FIREBASEAUTH_CLASS_EventHandler extends FIREBASEAUTH_CLASS_AbstractEventHandler
{
    use OW_Singleton;

    /**
     * Init
     */
    public function init()
    {
        parent::genericInit();

        // init admin notification
        OW::getEventManager()->bind('admin.add_admin_notification', [$this, 'onAddingAdminNotifications']);

        // init auth buttons
        OW::getEventManager()->bind(BASE_CMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST, [$this, 'onCollectButtonList']);
    }

    /**
     * On adding admin notifications
     *
     * @param ADMIN_CLASS_NotificationCollector $e
     * @return void
     */
    public function onAddingAdminNotifications( ADMIN_CLASS_NotificationCollector $e )
    {
        if ( !FIREBASEAUTH_BOL_Service::getInstance()->isFirebaseAuthEnabled() )
        {
            $errorMessage = OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'missing_settings_error', [
                'url' => OW::getRouter()->urlForRoute('firebaseauth_admin_index')
            ]);

            $e->add($errorMessage, ADMIN_CLASS_NotificationCollector::NOTIFICATION_WARNING);
        }
    }

    /**
     * On collect buttons list
     * 
     * @return void
     */
    public function onCollectButtonList( BASE_CLASS_EventCollector $event )
    {
        if ( FIREBASEAUTH_BOL_Service::getInstance()->isFirebaseAuthEnabled() )
        {
            $buttonList = new FIREBASEAUTH_CMP_ConnectButtons();

            $event->add([
                'iconClass' => FIREBASEAUTH_BOL_Service::PLUGIN_KEY,
                'markup' => $buttonList->render()
            ]);
        }
    }
}
