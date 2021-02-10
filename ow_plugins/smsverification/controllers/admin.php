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

/**
 * smsverification admin controller
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.smsverification.controllers
 * @since 1.7.6
 */
class SMSVERIFICATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $language = OW::getLanguage();

        $form = new SMSVERIFICATION_CLASS_SettingsForm();
        $this->addForm($form);
        
        $form->getElement('testAccountSID')->setValue(OW::getConfig()->getValue('smsverification', 'testAccountSID'));
        $form->getElement('testAuthToken')->setValue(OW::getConfig()->getValue('smsverification', 'testAuthToken'));
        $form->getElement('accountSID')->setValue(OW::getConfig()->getValue('smsverification', 'accountSID'));
        $form->getElement('authToken')->setValue(OW::getConfig()->getValue('smsverification', 'authToken'));        
        $form->getElement('sandboxMode')->setValue(OW::getConfig()->getValue('smsverification', 'sandboxMode'));
        $form->getElement('twilioTelNumber')->setValue(OW::getConfig()->getValue('smsverification', 'twilioTelNumber'));
        $form->getElement('emailVerif')->setValue(OW::getConfig()->getValue('base', 'confirm_email'));
        $form->getElement('mandatory_sms_verification')->setValue(OW::getConfig()->getValue('smsverification', 'mandatorySmsVerification'));
        

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();

            OW::getConfig()->saveConfig ('smsverification', 'testAccountSID', $values['testAccountSID']);
            OW::getConfig()->saveConfig ('smsverification', 'testAuthToken', $values['testAuthToken']);
            OW::getConfig()->saveConfig ('smsverification', 'accountSID', $values['accountSID']);
            OW::getConfig()->saveConfig ('smsverification', 'authToken', $values['authToken']);
            OW::getConfig()->saveConfig ('smsverification', 'sandboxMode', $values['sandboxMode']);
            OW::getConfig()->saveConfig ('smsverification', 'twilioTelNumber', $values['twilioTelNumber']);
            OW::getConfig()->saveConfig('base', 'confirm_email', $values['emailVerif']);
            OW::getConfig()->saveConfig('smsverification', 'mandatorySmsVerification', $values['mandatory_sms_verification']);

            OW::getFeedback()->info($language->text('smsverification', 'settings_updated'));
            $this->redirect();
        }
        
        $this->setPageHeading(OW::getLanguage()->text('smsverification', 'config_page_heading'));
        $this->assign('mandatory_description', $language->text('smsverification', 'mandatory_description'));
       
    }
    
}



