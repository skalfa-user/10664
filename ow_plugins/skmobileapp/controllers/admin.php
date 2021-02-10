<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class SKMOBILEAPP_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * Init 
     */
    public function init()
    {
        parent::init();

        $handler = OW::getRequestHandler()->getHandlerAttributes();
        $menus = array();

        $ads = new BASE_MenuItem();
        $ads->setLabel(OW::getLanguage()->text('skmobileapp', 'menu_ads_label'));
        $ads->setUrl(OW::getRouter()->urlForRoute('skmobileapp_admin_ads'));
        $ads->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'ads');
        $ads->setKey('ads');
        $ads->setIconClass('ow_ic_app');
        $ads->setOrder(0);
        $menus[] = $ads;

        $push = new BASE_MenuItem();
        $push->setLabel(OW::getLanguage()->text('skmobileapp', 'menu_push_label'));
        $push->setUrl(OW::getRouter()->urlForRoute('skmobileapp_admin_push'));
        $push->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'push');
        $push->setKey('push');
        $push->setIconClass('ow_ic_chat');
        $push->setOrder(1);
        $menus[] = $push;

        $inapps = new BASE_MenuItem();
        $inapps->setLabel(OW::getLanguage()->text('skmobileapp', 'menu_inapps_label'));
        $inapps->setUrl(OW::getRouter()->urlForRoute('skmobileapp_admin_inapps'));
        $inapps->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'inapps');
        $inapps->setKey('inapps');
        $inapps->setIconClass('ow_ic_cart');
        $inapps->setOrder(2);
        $menus[] = $inapps;

        $settings = new BASE_MenuItem();
        $settings->setLabel(OW::getLanguage()->text('skmobileapp', 'menu_settings_label'));
        $settings->setUrl(OW::getRouter()->urlForRoute('skmobileapp_admin_settings'));
        $settings->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'settings');
        $settings->setKey('settings');
        $settings->setIconClass('ow_ic_script');
        $settings->setOrder(3);
        $menus[] = $settings;

        $this->addComponent('menu', new BASE_CMP_ContentMenu($menus));
    }

    /**
     * General settings
     */
    public function settings()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading(OW::getLanguage()->text('skmobileapp', 'admin_settings'));
        }

        $form = new Form('skmobileapp_settings');

        $iosAppUrl = new TextField('ios_app_url');
        $iosAppUrl->setValue(OW::getConfig()->getValue('skmobileapp', 'ios_app_url'));
        $iosAppUrl->setLabel(OW::getLanguage()->text('skmobileapp', 'ios_app_url_label'));
        $iosAppUrl->setDescription(OW::getLanguage()->text('skmobileapp', 'default_app_url_desc'));
        $form->addElement($iosAppUrl);

        $androidAppUrl = new TextField('android_app_url');
        $androidAppUrl->setValue(OW::getConfig()->getValue('skmobileapp', 'android_app_url'));
        $androidAppUrl->setLabel(OW::getLanguage()->text('skmobileapp', 'android_app_url_label'));
        $androidAppUrl->setDescription(OW::getLanguage()->text('skmobileapp', 'default_app_url_desc'));
        $form->addElement($androidAppUrl);

        $searchMode = new RadioField('search_mode');
        $searchMode->setLabel(OW::getLanguage()->text('skmobileapp', 'search_mode_label'));
        $searchMode->setOptions(array(
            'both' => OW::getLanguage()->text('skmobileapp', 'search_mode_both'),
            'tinder' => OW::getLanguage()->text('skmobileapp', 'search_mode_tinder'),
            'browse' => OW::getLanguage()->text('skmobileapp', 'search_mode_browse')
        ));
        $searchMode->setValue(OW::getConfig()->getValue('skmobileapp', 'search_mode'));
        $form->addElement($searchMode);

        $googleMapApiKey = new TextField('google_map_api_key');
        $googleMapApiKey->setValue(OW::getConfig()->getValue('skmobileapp', 'google_map_api_key'));
        $googleMapApiKey->setLabel(OW::getLanguage()->text('skmobileapp', 'google_map_api_key_label'));
        $googleMapApiKey->setDescription(OW::getLanguage()->text('skmobileapp', 'google_map_api_key_desc'));
        $googleMapApiKey->setRequired(true);
        $form->addElement($googleMapApiKey);

        $submit = new Submit('settings_submit');
        $submit->setValue(OW::getLanguage()->text('skmobileapp', 'settings_submit'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            OW::getConfig()->saveConfig('skmobileapp', 'ios_app_url', $form->getElement('ios_app_url')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'android_app_url', $form->getElement('android_app_url')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'search_mode', $form->getElement('search_mode')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'google_map_api_key', $form->getElement('google_map_api_key')->getValue());

            OW::getFeedback()->info(OW::getLanguage()->text('skmobileapp', 'settings_saved'));

            $this->redirect();
        }
    }

    /**
     * Ads settings
     */
    public function ads( array $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading(OW::getLanguage()->text('skmobileapp', 'admin_settings'));
        }

        $form = new Form('skmobileapp_ads');

        $key = new TextField('ads_key');
        $key->setRequired();
        $key->setValue(OW::getConfig()->getValue('skmobileapp', 'ads_api_key'));
        $key->setLabel(OW::getLanguage()->text('skmobileapp', 'ads_label'));
        $key->setDescription(OW::getLanguage()->text('skmobileapp', 'ads_desc'));

        $form->addElement($key);

        $enabled = new CheckboxField('ads_enabled');
        $enabled->setValue(OW::getConfig()->getValue('skmobileapp', 'ads_enabled'));
        $enabled->setLabel(OW::getLanguage()->text('skmobileapp', 'ads_enabled_label'));

        $form->addElement($enabled);

        $submit = new Submit('ads_submit');
        $submit->setValue(OW::getLanguage()->text('skmobileapp', 'ads_submit'));

        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            OW::getConfig()->saveConfig('skmobileapp', 'ads_api_key', $form->getElement('ads_key')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'ads_enabled', $form->getElement('ads_enabled')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('skmobileapp', 'settings_saved'));

            $this->redirect();
        }

        $this->addForm($form);
    }

    /**
     * Inaps settings
     */
    public function inapps( array $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading(OW::getLanguage()->text('skmobileapp', 'admin_settings'));
        }

        $clientEmail = OW::getConfig()->getValue('skmobileapp', 'inapps_apm_android_client_email');
        $privateKey = OW::getConfig()->getValue('skmobileapp', 'inapps_apm_android_private_key');

        if ( !empty($privateKey) && mb_strlen($privateKey) > 100 )
        {
            $privateKey = mb_substr($privateKey, 0, 100);
        }

        $this->assign('androidClientEmail', $clientEmail);
        $this->assign('androidPrivateKey', $privateKey);

        $form = new Form('skmobileapp_inapps');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $inappsEnabled = new CheckboxField('inapps_enable');
        $inappsEnabled->setValue(OW::getConfig()->getValue('skmobileapp', 'inapps_enable'));
        $inappsEnabled->setLabel(OW::getLanguage()->text('skmobileapp', 'inapps_enable'));

        $form->addElement($inappsEnabled);

        $showMembershipActions = new Selectbox('inapps_show_membership_actions');
        $showMembershipActions->setRequired();
        $showMembershipActions->setLabel(OW::getLanguage()->text('skmobileapp', 'inapps_show_membership_actions'));
        $showMembershipActions->setDescription(OW::getLanguage()->text('skmobileapp', 'inapps_show_membership_actions_desc'));
        $showMembershipActions->setValue(OW::getConfig()->getValue('skmobileapp', 'inapps_show_membership_actions'));

        $showMembershipActions->setOptions([
            SKMOBILEAPP_BOL_PaymentsService::APP_ONLY_MEMBERSHIP_ACTIONS => OW::getLanguage()->text('skmobileapp', 'inapps_app_only_membership_actions'),
            SKMOBILEAPP_BOL_PaymentsService::ALL_MEMBERSHIP_ACTIONS => OW::getLanguage()->text('skmobileapp', 'inapps_all_membership_actions'),
        ]);

        $form->addElement($showMembershipActions);

        $key = new TextField('inapps_apm_key');
        $key->setValue(OW::getConfig()->getValue('skmobileapp', 'inapps_apm_key'));
        $key->setLabel(OW::getLanguage()->text('skmobileapp', 'inapps_apm_key_label'));
        $key->setDescription(OW::getLanguage()->text('skmobileapp', 'inapps_apm_key_desc'));

        $form->addElement($key);

        $promoPackageName = new TextField('inapps_apm_package_name');
        $promoPackageName->setLabel(OW::getLanguage()->text('skmobileapp', 'inapps_apm_package_name_label'));
        $promoPackageName->setDescription(OW::getLanguage()->text('skmobileapp', 'inapps_apm_package_name_desc'));
        $promoPackageName->setValue(OW::getConfig()->getValue('skmobileapp', 'inapps_apm_package_name'));
        $form->addElement($promoPackageName);

        $androidAccountKey = new FileField('inapps_apm_android_account_key');
        $androidAccountKey->addValidator(
            new SKMOBILEAPP_CLASS_AndroidAccountKeyValidator('inapps_apm_android_account_key', '#android_private_key')
        );
        $androidAccountKey->setLabel(OW::getLanguage()->text('skmobileapp', 'inapps_apm_android_account_key_label'));
        $androidAccountKey->setDescription(OW::getLanguage()->text('skmobileapp', 'inapps_apm_android_account_key_desc'));
        $androidAccountKey->setValue(OW::getConfig()->getValue('skmobileapp', 'inapps_apm_android_private_key'));
        $form->addElement($androidAccountKey);

        $secret = new TextField('inapps_itunes_shared_secret');
        $secret->setValue(OW::getConfig()->getValue('skmobileapp', 'inapps_itunes_shared_secret'));
        $secret->setLabel(OW::getLanguage()->text('skmobileapp', 'inapps_itunes_shared_secret_label'));
        $secret->setDescription(OW::getLanguage()->text('skmobileapp', 'inapps_itunes_shared_secret_desc'));
        $form->addElement($secret);

        $enabled = new CheckboxField('inapps_ios_test_mode');
        $enabled->setValue(OW::getConfig()->getValue('skmobileapp', 'inapps_ios_test_mode'));
        $enabled->setLabel(OW::getLanguage()->text('skmobileapp', 'inapps_ios_test_mode_label'));

        $form->addElement($enabled);

        $submit = new Submit('inapps_submit');
        $submit->setValue(OW::getLanguage()->text('skmobileapp', 'inapps_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid(array_merge($_POST, $_FILES)) )
        {
            OW::getConfig()->saveConfig('skmobileapp', 'inapps_show_membership_actions', $form->getElement('inapps_show_membership_actions')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'inapps_apm_key', $form->getElement('inapps_apm_key')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'inapps_itunes_shared_secret', $form->getElement('inapps_itunes_shared_secret')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'inapps_ios_test_mode', $form->getElement('inapps_ios_test_mode')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'inapps_enable', $form->getElement('inapps_enable')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'inapps_apm_package_name', $form->getElement('inapps_apm_package_name')->getValue());

            if ( !empty($_FILES['inapps_apm_android_account_key']['tmp_name']) )
            {
                $content = file_get_contents($_FILES['inapps_apm_android_account_key']['tmp_name']);

                $list = json_decode($content, true);

                if ( !empty($list['private_key']) || !empty($list['client_email']) )
                {
                    OW::getConfig()->saveConfig('skmobileapp', 'inapps_apm_android_client_email',$list['client_email']);
                    OW::getConfig()->saveConfig('skmobileapp', 'inapps_apm_android_private_key',  $list['private_key']);
                }
            }

            OW::getFeedback()->info(OW::getLanguage()->text('skmobileapp', 'settings_saved'));

            $this->redirect();
        }

        $this->addForm($form);
    }

    /**
     * Push settings
     */
    public function push( array $params )
    {

        $form = new Form('skmobileapp_push');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $enabled = new CheckboxField('pn_enabled');
        $enabled->setValue(OW::getConfig()->getValue('skmobileapp', 'pn_enabled'));
        $enabled->setLabel(OW::getLanguage()->text('skmobileapp', 'pn_enabled_label'));

        $form->addElement($enabled);

        $vapidKey = new TextField('pn_vapid_key');
        $vapidKey->setValue(OW::getConfig()->getValue('skmobileapp', 'pn_vapid_key'));
        $vapidKey->setLabel(OW::getLanguage()->text('skmobileapp', 'pn_vapid_key_label'));
        $vapidKey->setDescription(OW::getLanguage()->text('skmobileapp', 'pn_vapid_key_desc'));

        $form->addElement($vapidKey);

        $senderID = new TextField('pn_sender_id');
        $senderID->setValue(OW::getConfig()->getValue('skmobileapp', 'pn_sender_id'));
        $senderID->setLabel(OW::getLanguage()->text('skmobileapp', 'pn_sender_id_label'));
        $senderID->setDescription(OW::getLanguage()->text('skmobileapp', 'pn_sender_id_desc'));
 
        $form->addElement($senderID);

        $serverKey = new TextField('pn_server_key');
        $serverKey->setValue(OW::getConfig()->getValue('skmobileapp', 'pn_server_key'));
        $serverKey->setLabel(OW::getLanguage()->text('skmobileapp', 'pn_server_key_label'));
        $serverKey->setDescription(OW::getLanguage()->text('skmobileapp', 'pn_server_key_desc'));

        $form->addElement($serverKey);

        $field = new RadioField('pn_apns_mode');
        $field->setLabel(OW::getLanguage()->text('skmobileapp', 'pn_apns_mode_label'));

        $field->setOptions(array(
            'test' => OW::getLanguage()->text('skmobileapp', 'pn_apns_mode_test'),
            'live' => OW::getLanguage()->text('skmobileapp', 'pn_apns_mode_live')
        ));
        $field->setValue(OW::getConfig()->getValue('skmobileapp', 'pn_apns_mode') ? OW::getConfig()->getValue('skmobileapp', 'pn_apns_mode') : 'test');

        $form->addElement($field);

        $apnsCert = new FileField('pn_apns_cert');
        $apnsCert->addValidator(new SKMOBILEAPP_CLASS_AppnsCertificateValidator);
        $apnsCert->setLabel(OW::getLanguage()->text('skmobileapp', 'pn_apns_cert_label'));
        $apnsCert->setDescription(OW::getLanguage()->text('skmobileapp', 'pn_apns_cert_desc'));

        $form->addElement($apnsCert);

        $passPhrase = new TextField('pn_apns_pass_phrase');
        $passPhrase->setValue(OW::getConfig()->getValue('skmobileapp', 'pn_apns_pass_phrase'));
        $passPhrase->setLabel(OW::getLanguage()->text('skmobileapp', 'pn_apns_pass_phrase_label'));
        $passPhrase->setDescription(OW::getLanguage()->text('skmobileapp', 'pn_apns_pass_phrase_desc'));

        $form->addElement($passPhrase);

        $submit = new Submit('push');
        $submit->setValue(OW::getLanguage()->text('skmobileapp', 'pn_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid(array_merge($_POST, $_FILES)) )
        {
            $oldValue = OW::getConfig()->getValue('skmobileapp', 'pn_apns_mode');
            $newValue = $form->getElement('pn_apns_mode')->getValue();

            if ($oldValue != $newValue)
            {
                SKMOBILEAPP_BOL_DeviceService::getInstance()->removeIOSDevices();
            }

            OW::getConfig()->saveConfig('skmobileapp', 'pn_vapid_key', $form->getElement('pn_vapid_key')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'pn_sender_id', $form->getElement('pn_sender_id')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'pn_server_key', $form->getElement('pn_server_key')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'pn_enabled', $form->getElement('pn_enabled')->getValue());

            OW::getConfig()->saveConfig('skmobileapp', 'pn_apns_pass_phrase', $form->getElement('pn_apns_pass_phrase')->getValue());
            OW::getConfig()->saveConfig('skmobileapp', 'pn_apns_mode', $form->getElement('pn_apns_mode')->getValue());

            // upload the certificate file
            if ( !empty($_FILES[$apnsCert->getName()]['tmp_name']) )
            {
                move_uploaded_file(
                    $_FILES[$apnsCert->getName()]['tmp_name'],
                    SKMOBILEAPP_BOL_Service::getInstance()->getApnsCertificateFilePath()
                );
            }

            OW::getFeedback()->info(OW::getLanguage()->text('skmobileapp', 'settings_saved'));

            $this->redirect();

        }

        $this->addForm($form);
    }
}
