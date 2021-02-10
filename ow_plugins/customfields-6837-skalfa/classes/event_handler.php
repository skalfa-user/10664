<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class CUSTOMFIELDS_CLASS_EventHandler
{
    use OW_Singleton;

    public function init()
    {
        $eventManager = OW::getEventManager();

        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        // redefine controllers
        $eventManager->bind('class.get_instance.SKADATE_CTRL_Join', array($this, 'onGetJoinControllerInstance'));
        $eventManager->bind('class.get_instance.BASE_CTRL_CompleteProfile', array($this, 'onGetCompleteProfileControllerInstance'));
        $eventManager->bind('class.get_instance.USEARCH_CTRL_Search', array($this, 'onGetSearchControllerInstance'));
        $eventManager->bind('class.get_instance.BASE_CTRL_ComponentPanel', array($this, 'onGetComponentPanelControllerInstance'));
        $eventManager->bind('class.get_instance.MEMBERSHIP_CTRL_Subscribe', array($this, 'onGetSubscribeControllerInstance'), 1000000);
        $eventManager->bind('class.get_instance.USERCREDITS_CTRL_BuyCredits', array($this, 'onGetBuyCreditsControllerInstance'), 1000000);
        $eventManager->bind('class.get_instance.BASE_CTRL_Preference', array($this, 'onGetPreferenceControllerInstance'));
        $eventManager->bind('class.get_instance.NOTIFICATIONS_CTRL_Notifications', array($this, 'onGetNotificationsControllerInstance'));
        $eventManager->bind('class.get_instance.BASE_CTRL_Edit', array($this, 'onGetEditControllerInstance'), 1000000);
        $eventManager->bind('class.get_instance.PRIVACY_CTRL_Privacy', array($this, 'onGetPrivacyControllerInstance'));
    }

    public function onBeforeDocumentRender()
    {
        $doc = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin(CUSTOMFIELDS_BOL_Service::PLUGIN_KEY);
        // add JCF scripts
        $doc->addScript($plugin->getStaticJsUrl() . 'jcf/jcf.js');
        $doc->addScript($plugin->getStaticJsUrl() . 'jcf/jcf.checkbox.js');
        $doc->addScript($plugin->getStaticJsUrl() . 'jcf/jcf.radio.js');
        $doc->addScript($plugin->getStaticJsUrl() . 'jcf/jcf.select.js');
        // add JCF styles
        $doc->addStyleSheet($plugin->getStaticCssUrl() . 'jcf/theme-minimal/jcf.css');
    }

    /**
     * @param OW_Event $event
     */
    public function onGetJoinControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_Join());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetCompleteProfileControllerInstance($event)
    {
        $event->setdata(new CUSTOMFIELDS_CTRL_CompleteProfile());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetSearchControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_Search());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetComponentPanelControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_ComponentPanel());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetSubscribeControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_Subscribe());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetBuyCreditsControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_BuyCredits());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetPreferenceControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_Preference());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetNotificationsControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_Notifications());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetEditControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_Edit());
    }

    /**
     * @param OW_Event $event
     */
    public function onGetPrivacyControllerInstance($event)
    {
        $event->setData(new CUSTOMFIELDS_CTRL_Privacy());
    }
}