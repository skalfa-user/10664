<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class FIREBASEAUTH_CLASS_SettingsForm extends Form
{
    /**
     * Class constructor
     * 
     * @param array $params
     */
    public function __construct( array $params ) 
    {
        // process params
        $formName = !empty($params['name']) ? $params['name'] : 'settings-form';
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        parent::__construct($formName);

        // api key
        $apiKey = new TextField('api_key');
        $apiKey->setRequired(true);
        $apiKey->setValue(FIREBASEAUTH_BOL_Service::getInstance()->getApiKey());
        $apiKey->setLabel(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'form_api_title_label'));

        $this->addElement($apiKey);

        // auth  domain
        $authDomain = new TextField('auth_domain');
        $authDomain->setRequired(true);
        $authDomain->setValue(FIREBASEAUTH_BOL_Service::getInstance()->getAuthDomain());
        $authDomain->setLabel(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'form_auth_domain_title_label'));

        $this->addElement($authDomain);

        // auth  domain
        $emailExample = new TextField('email_example');
        $emailExample->setRequired(true);
        $emailExample->setValue(FIREBASEAUTH_BOL_Service::getInstance()->getEmailExample());
        $emailExample->setLabel(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'form_email_example_title_label'));
        $emailExample->setDescription(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'form_email_example_description'));
        $emailExample->addValidator(new EmailValidator());

        $this->addElement($emailExample);

        // process providers
        $processedProviders = [];
        $allProviders = FIREBASEAUTH_BOL_Service::getInstance()->getAllRegisteredProviders();

        foreach ( $allProviders as $providerKey => $providerLabel )
        {
            $processedProviders[$providerKey] = 
                    '<span class="firebase-auth-provider" data-provider="' . $providerKey . '"></span>' . $providerLabel;
        }

        // providers
        $providers = new CheckboxGroup('enabled_providers');
        $providers->setValue(FIREBASEAUTH_BOL_Service::getInstance()->getEnabledProviders());
        $providers->setOptions($processedProviders);
        $providers->setRequired();
        $providers->setColumnCount(3);

        $this->addElement($providers);

        // firebase admin private key file
        $keyFile = new FileField('firebase_admin_key');
        $keyFile->setLabel(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'firebase_admin_key_file_label'));

        // set path to firebase admin key
        $keyPath = OW::getPluginManager()->getPlugin("firebaseauth")->getPluginFilesDir()
            . FIREBASEAUTH_BOL_Service::ADMIN_KEY_FILE_NAME;

        // checking file existence
        if (!file_exists($keyPath))
        {
            $keyFile->setRequired();
        }

        $this->addElement($keyFile);

        // submit
        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'form_submit_label'));
        $this->addElement($submit);
    }

    /**
     * Save settings
     * 
     * @return void
     */
    public function saveSettings()
    {
        FIREBASEAUTH_BOL_Service::getInstance()->setApiKey($this->getElement('api_key')->getValue());
        FIREBASEAUTH_BOL_Service::getInstance()->setAuthDomain($this->getElement('auth_domain')->getValue());
        FIREBASEAUTH_BOL_Service::getInstance()->setEnabledProviders($this->getElement('enabled_providers')->getValue());
        FIREBASEAUTH_BOL_Service::getInstance()->setEmailExample($this->getElement('email_example')->getValue());
    }

    /**
     * Save admin key
     *
     * @return boolean
     */
    public function saveAdminKey()
    {
        $keyFilePath = OW::getPluginManager()->getPlugin("firebaseauth")->getPluginFilesDir()
            . FIREBASEAUTH_BOL_Service::ADMIN_KEY_FILE_NAME;

        if ( !empty($_FILES['firebase_admin_key']['tmp_name']) )
        {
            if ( (int) $_FILES['firebase_admin_key']['error'] === 0
                    && is_uploaded_file($_FILES['firebase_admin_key']['tmp_name'])
                    && UTIL_File::getExtension($_FILES['firebase_admin_key']['name']) === 'json' )
            {


                if ( file_exists($keyFilePath) )
                {
                    @unlink($keyFilePath);
                }

                @move_uploaded_file($_FILES['firebase_admin_key']['tmp_name'], $keyFilePath);

                if ( file_exists($_FILES['firebase_admin_key']['tmp_name']) )
                {
                    @unlink($_FILES['firebase_admin_key']['tmp_name']);
                }

                return true;
            }
        }

        if (file_exists($keyFilePath))
        {
            return true;
        }

        return false;
    }
}
