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
class SMSVERIFICATION_CLASS_InputNumberForm extends Form
{
     /**
     *
     * @var SMSVERIFICATION_BOL_Service 
     */
    private $service;
    
    public function __construct()
    {
        parent::__construct('inputNumber-form');
        $this->service = SMSVERIFICATION_BOL_Service::getInstance();
        $lang = OW::getLanguage();

        $telNumber = new TextField('telNumber');
        $telNumber->setRequired();
        $this->addElement($telNumber);        
        
        $hidden = new HiddenField('countryName');
        $hidden->setRequired();
        $this->addElement($hidden);

        // submit
        $submit = new Submit('send_btn');
        $submit->setValue($lang->text('smsverification', 'btn_send'));
        $this->addElement($submit);
        
    }
    
    public function sendMessage()
    {
        require_once OW::getPluginManager()->getPlugin("smsverification")->getRootDir() . "classes" . DS . "twilio-php" . DS . "Services" . DS . "Twilio.php";
        $lang = OW::getLanguage();
        $telNumber = $_POST['telNumber'];
        $countryCode = $_POST['country_code'];
        $userCountry = $_POST['countryName'];
        $fullNumber = '+'.$countryCode.$telNumber;
        $userCode = strtolower(UTIL_String::getRandomString(7, 3));
        
        $sid = SMSVERIFICATION_CLASS_TwilioAdapter::getAccountSID();        
        $token = SMSVERIFICATION_CLASS_TwilioAdapter::getAuthToken();
        $twilioTelNumber = SMSVERIFICATION_CLASS_TwilioAdapter::getTwilioTelNumber();
        $userId = OW::getUser()->getId();
       
        $client = new Services_Twilio($sid, $token);
        $result = $client->account->messages->sendMessage( $twilioTelNumber, $fullNumber, $lang->text('smsverification', 'enter_code_message', array( 'userCode' => $userCode )));
        $this->service->setUserData($userId, $fullNumber, $userCode, $countryCode, $userCountry);
            
    }   
    
}

