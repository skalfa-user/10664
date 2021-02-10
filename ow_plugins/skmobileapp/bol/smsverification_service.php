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

class SKMOBILEAPP_BOL_SmsverificationService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    const PLUGIN_KEY = 'smsverification';

    const TYPE_PHONE_NUMBER_NOT_VERIFIED = 'phoneNumberNotVerified';
    const TYPE_PHONE_CODE_NOT_VERIFIED = 'phoneCodeNotVerified';
    const TYPE_PHONE_NUMBER_VERIFIED = 'phoneNumberVerified';

    public function isSmsNotVerified( $userId )
    {
        $service =  SMSVERIFICATION_BOL_Service::getInstance();

        $userId = intval($userId);
        $mandatorySmsVerification = OW::getConfig()->getValue(self::PLUGIN_KEY, 'mandatorySmsVerification');
        $getVerified = $service->getVerifyed($userId);

        if ( $getVerified === 0 ) {
            return [
                true,
                self::TYPE_PHONE_NUMBER_NOT_VERIFIED
            ];
        }

        if( $getVerified === 1) {
            return [
                true,
                self::TYPE_PHONE_CODE_NOT_VERIFIED
            ];
        }

        if( $getVerified === -1 && $mandatorySmsVerification == true ) {
            $registeredUser = new SMSVERIFICATION_BOL_User();
            $registeredUser->userId = $userId;
            $registeredUser->isVeryfied = 0;
            $service->saveRegisteredUser($registeredUser);

            return [
                true,
                self::TYPE_PHONE_NUMBER_NOT_VERIFIED
            ];
        }

        return [
            false,
            self::TYPE_PHONE_NUMBER_VERIFIED
        ];
    }

    public function getUserPhone( $userId )
    {
        $service =  SMSVERIFICATION_BOL_Service::getInstance();

        $userId = intval($userId);
        $userDataInfo = $service->getUserDataByUserId($userId);

        $userData = [];

        if ( !empty($userDataInfo[0]) )
        {
            $userData = $userDataInfo[0];

            $userData->number = ( strpos($userData->number, $userData->countryCode) !== false ) ?
                substr($userData->number, strpos($userData->number, $userData->countryCode) + strlen($userData->countryCode)) : $userData->number;
        }

        return $userData;
    }

    public function sendSms( $userId, $countryCode, $phoneNumber )
    {
        $countryCode = intval($countryCode);
        $service = SMSVERIFICATION_BOL_Service::getInstance();

        require_once OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getRootDir() . "classes" . DS . "twilio-php" . DS . "Services" . DS . "Twilio.php";

        $twillioTelNumber = SMSVERIFICATION_CLASS_TwilioAdapter::getTwilioTelNumber();
        $sid = SMSVERIFICATION_CLASS_TwilioAdapter::getAccountSID();
        $token = SMSVERIFICATION_CLASS_TwilioAdapter::getAuthToken();

        $clearPhoneCode = str_replace(['(', ')'], '',$countryCode);
        $fullNumber = '+' . $clearPhoneCode .$phoneNumber;
        $userCode = strtolower(UTIL_String::getRandomString(7, 3));

        $client = new Services_Twilio($sid, $token);

        try {

            $message =  OW::getLanguage()->text(self::PLUGIN_KEY, 'enter_code_message', ['userCode' => $userCode]);
            $client->account->messages->sendMessage( $twillioTelNumber, $fullNumber, $message);

            $service->setUserData($userId, $fullNumber, $userCode, $countryCode, null);

            return [
                'success' => true,
                'message' => null
            ];

        } catch ( Exception $e ) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

        }
    }

    public function getUserDataByUserId( $userId )
    {
        $service = SMSVERIFICATION_BOL_Service::getInstance();

        $userDataInfo = $service->getUserDataByUserId($userId);

        $userData = [];

        if ( !empty($userDataInfo[0]) )
        {
            $userData = $userDataInfo[0];

            $userData->number = $this->getClinePhoneNumber($userData->number, $userData->countryCode);
        }

        return $userData;
    }

    public function getClinePhoneNumber( $number, $countryCode )
    {
        if ( strpos($number, $countryCode) !== false )
        {
            return substr($number, strpos($number, $countryCode) + strlen($countryCode));
        }

        return $number;
    }
}