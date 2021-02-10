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

class FIREBASEAUTH_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * Settings
     */
    public function index()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading(OW::getLanguage()->text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'admin_header'));
        }

        // init css
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->
            getPlugin(FIREBASEAUTH_BOL_Service::PLUGIN_KEY)->getStaticCssUrl() . 'connect_buttons.css');

        // create a form
        $form = OW::getClassInstance('FIREBASEAUTH_CLASS_SettingsForm', [
            'name' => 'settings_form'
        ]);

        // register the form
        $this->addForm($form);

        // validate and save data
        if ( OW::getRequest()->isPost() && $form->isValid(array_merge($_POST, $_FILES)) )
        {
            $form->saveSettings();

            if (!$form->saveAdminKey())
            {
                OW::getFeedback()->error(OW::getLanguage()->
                text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'admin_key_file_save_error'));

                $this->redirect();
            }


            OW::getFeedback()->info(OW::getLanguage()->
                    text(FIREBASEAUTH_BOL_Service::PLUGIN_KEY, 'settings_successfully_saved'));

            $this->redirect();
        }
    }
}
