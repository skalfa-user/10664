<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

abstract class GDPR_CLASS_BaseEventHandler
{
    /**
     * Genaric init
     */
    public function genericInit()
    {
        $em = OW::getEventManager();

        $em->bind(OW_EventManager::ON_FINALIZE, [$this, 'onIncludeStaticFiles']);
        $em->bind('skmobileapp.get_application_config', [$this, 'onSkmobileappGetConfig']);
        $em->bind('skmobileapp.get_translations', [$this, 'onGetTranslations']);
    }

    /**
     * Include statis files
     */
    public function onIncludeStaticFiles()
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('gdpr')->getStaticCssUrl() . 'style.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('gdpr')->getStaticJsUrl() . 'main.js');
        OW::getLanguage()->addKeyForJs('gdpr', 'gdpr_send_message_label');
    }

    /**
     * Add config
     */
    public function onSkmobileappGetConfig( OW_Event $event )
    {
        $data = $event->getData();
        $data['gdprThirdPartyServices'] = (int) OW::getConfig()->getValue('gdpr', 'gdpr_third_party_services');

        $event->setData($data);
    }

    /**
     * Add languages
     */
    public function onGetTranslations( OW_Event $event )
    {
        $langService = BOL_LanguageService::getInstance();
        $translations = [];
        $language = OW::getLanguage();

        $langs = $langService->findAllPrefixKeys($langService->findPrefixId('gdpr'));

        if ( !empty($langs) )
        {
            foreach ( $langs as $item )
            {
                $translations[$item->key] = $language->text('gdpr', $item->key);
            }

            $event->add('gdpr', $translations);
        }
    }
}
