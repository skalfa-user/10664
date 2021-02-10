<?php

use Kreait\Firebase\Factory;

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

abstract class FIREBASEAUTH_CLASS_AbstractEventHandler
{
    /**
     * Generic init
     *
     * @return void
     */
    public function genericInit()
    {
        OW::getEventManager()->bind('skmobileapp.get_translations', [$this, 'onGetAppTranslations']);
        OW::getEventManager()->bind('skmobileapp.get_application_config', [$this, 'onGetAppConfigs']);

        OW::getEventManager()->bind('base.members_only_exceptions', array($this, 'addGoogleException'));
        OW::getEventManager()->bind('base.splash_screen_exceptions', array($this, 'addGoogleException'));
        OW::getEventManager()->bind('base.password_protected_exceptions', array($this, 'addGoogleException'));

        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onDeleteUserContent'), 1);
    }

    /**
     * Process user unregister event
     *
     * @param OW_Event $event
     * @throws \Kreait\Firebase\Exception\AuthException
     * @throws \Kreait\Firebase\Exception\FirebaseException
     */
    public function onDeleteUserContent( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int)$params['userId'];

        // get user remote auth
        $uid = BOL_RemoteAuthService::getInstance()->findByUserId($userId);

        if (!empty($uid) && !empty($uid->remoteId))
        {
            try
            {
                $keyPath = OW::getPluginManager()->getPlugin("firebaseauth")->getPluginFilesDir()
                        . FIREBASEAUTH_BOL_Service::ADMIN_KEY_FILE_NAME;

                // init firebase SDK
                $auth = (new Factory)
                    ->withServiceAccount($keyPath)
                    ->createAuth();

                // delete user
                $auth->deleteUser($uid->remoteId);
            }
            catch (Exception $e)
            {
                // skip delete remote auth
            }
        }
    }

    /**
     * Get translations for the skmobileapp
     *
     * @return void
     */
    public function onGetAppTranslations( OW_Event $event )
    {
        $langService = BOL_LanguageService::getInstance();
        $translations = [];
        $language = OW::getLanguage();

        $langs = $langService->findAllPrefixKeys($langService->findPrefixId(FIREBASEAUTH_BOL_Service::PLUGIN_KEY));

        if ( !empty($langs) )
        {
            foreach ( $langs as $item )
            {
                $translations[$item->key] = $language->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, $item->key);
            }

            $event->add(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, $translations);
        }
    }

    /**
     * Get configs for the skmobileapp
     *
     * @return void
     */
    public function onGetAppConfigs( OW_Event $event )
    {
        if ( FIREBASEAUTH_BOL_Service::getInstance()->isFirebaseAuthEnabled() )
        {
            $event->setData(array_merge(
                $event->getData(), [
                    'maxDisplayedAuthProviders' => FIREBASEAUTH_BOL_Service::getInstance()->getMaxDisplayedProviders(),
                    'authProviders' => FIREBASEAUTH_BOL_Service::getInstance()->getEnabledProviders()
                ]
            ));
        }
    }

    public function addGoogleException( BASE_CLASS_EventCollector $e )
    {
        $e->add(array('controller' => 'FIREBASEAUTH_CTRL_Connect', 'action' => 'authenticate'));
    }
}
