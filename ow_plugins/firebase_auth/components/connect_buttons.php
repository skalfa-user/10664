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

class FIREBASEAUTH_CMP_ConnectButtons extends OW_Component
{
    /**
     * Invite code
     */
    protected $inviteCode = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->inviteCode = !empty($_GET['code']) 
            ? $_GET['code'] 
            : '';

        $this->setVisible(FIREBASEAUTH_BOL_Service::getInstance()->isRegisterAllowed($this->inviteCode));
    }

    /**
     * On before render
     */
    public function onBeforeRender()
    {
        $enabledProviders = FIREBASEAUTH_BOL_Service::getInstance()->getEnabledProviders();

        $isAuthLongProviderList = count($enabledProviders) > 
                FIREBASEAUTH_BOL_Service::getInstance()->getMaxDisplayedProviders();

        // init css
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->
                getPlugin(FIREBASEAUTH_BOL_Service::PLUGIN_KEY)->getStaticCssUrl() . 'connect_buttons.css');

        // init js
        OW::getDocument()->addScript('https://www.gstatic.com/firebasejs/5.9.3/firebase-app.js');
        OW::getDocument()->addScript('https://www.gstatic.com/firebasejs/5.9.3/firebase-auth.js');
        OW::getDocument()->addOnloadScript('
            firebase.initializeApp({
                apiKey: "' . FIREBASEAUTH_BOL_Service::getInstance()->getApiKey() . '",
                authDomain: "' . FIREBASEAUTH_BOL_Service::getInstance()->getAuthDomain() . '"
            });
        ');

        OW::getDocument()->addScript(OW::getPluginManager()->
                getPlugin(FIREBASEAUTH_BOL_Service::PLUGIN_KEY)->getStaticJsUrl() . 'auth.js?build=' . FIREBASEAUTH_BOL_Service::getInstance()->getPluginBuild());

        OW::getDocument()->addScriptDeclaration(UTIL_JsGenerator::composeJsString('
            var firebaseAuth = new FireBaseAuth({
                "authWrapperSelector": {$authWrapperSelector},
                "basicButtonWrapSelector": {$basicButtonWrapSelector},
                "connectButtonSelector": {$connectButtonSelector},
                "isLongProviderList" : {$isLongProviderList},
                "authenticateUrl": {$authenticateUrl},
                "backUri": {$backUri},
                "inviteCode": {$inviteCode},
                "twitterProvider": {$twitterProvider},
                "facebookProvider": {$facebookProvider},
                "googleProvider": {$googleProvider},
                "linkedInProvider": {$linkedInProvider},
                "instagramProvider": {$instagramProvider},
                "authDomain": {$authDomain},
                "displayCustomTokenUrl": {$displayCustomTokenUrl},
                "messages": {
                    "errorOccurred": {$errorOccurred},
                    "errorAccountExists": {$errorAccountExists}
                }
            });
        ', [
            'authWrapperSelector' => '.firebaseauth-auth-wrapper',
            'basicButtonWrapSelector' => '.firebaseauth-button-wrap',
            'connectButtonSelector' => '.firebaseauth-connect-button',
            'isLongProviderList' => $isAuthLongProviderList,
            'authenticateUrl' => OW::getRouter()->urlForRoute('firebaseauth_authenticate'),
            'backUri' => OW::getRequest()->getRequestUri(),
            'inviteCode' => $this->inviteCode,
            'twitterProvider' => FIREBASEAUTH_BOL_Service::TWITTER_PROVIDER,
            'facebookProvider' => FIREBASEAUTH_BOL_Service::FACEBOOK_PROVIDER,
            'googleProvider' => FIREBASEAUTH_BOL_Service::GOOGLE_PROVIDER,
            'linkedInProvider' => FIREBASEAUTH_BOL_Service::LINKEDIN_PROVIDER,
            'instagramProvider' => FIREBASEAUTH_BOL_Service::INSTAGRAM_PROVIDER,
            'authDomain' => FIREBASEAUTH_BOL_Service::getInstance()->getAuthDomain(),
            'displayCustomTokenUrl' => OW::getRouter()->urlForRoute('firebaseauth_display_token'),
            'errorOccurred' => OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'authenticate_error'),
            'errorAccountExists' => OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'authenticate_error_account_exists')
        ]));

        // init view variables
        $this->assign('enabledProviders', $enabledProviders);
        $this->assign('isLongProviderList', $isAuthLongProviderList);
    }
}
