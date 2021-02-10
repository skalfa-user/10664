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
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow_plugins.smsverification.classes
 * @since 1.7.6
 */
class SMSVERIFICATION_CLASS_SettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');

        $lang = OW::getLanguage();

        $sandboxMode = new CheckboxField('sandboxMode');
        $sandboxMode->setLabel($lang->text('smsverification', 'sandbox_mode'));
        $this->addElement($sandboxMode);

        $testAccountSID = new TextField('testAccountSID ');
        $testAccountSID->setLabel($lang->text('smsverification', 'test_accounts_id'));
        $this->addElement($testAccountSID);

        $testAuthToken = new TextField('testAuthToken');
        $testAuthToken->setLabel($lang->text('smsverification', 'test_auth_token'));
        $this->addElement($testAuthToken);
        
        
        $accountSID = new TextField('accountSID');
        $accountSID->setLabel($lang->text('smsverification', 'accounts_id'));
        $this->addElement($accountSID);

        $authToken = new TextField('authToken');
        $authToken->setLabel($lang->text('smsverification', 'auth_token'));
        $this->addElement($authToken);  
        
        $twilioNumber = new TextField('twilioTelNumber');
        $twilioNumber->setLabel($lang->text('smsverification', 'twilio_tel_number'));
        $this->addElement($twilioNumber);
        
        $enableEmailVerif = new CheckboxField('emailVerif');
        $enableEmailVerif->setLabel($lang->text('smsverification', 'enable_email'));
        $this->addElement($enableEmailVerif);
        
        $element = new CheckboxField('mandatory_sms_verification');
        $element->setLabel($lang->text('smsverification', 'mandatory_sms_verification'));
        $this->addElement($element);

        // submit
        $submit = new Submit('save');
        $submit->setValue($lang->text('smsverification', 'btn_save'));
        $this->addElement($submit);
        
    }
    
}