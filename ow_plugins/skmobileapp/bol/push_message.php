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

// firebase
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Notification;

// apns
use Apple\ApnPush\Certificate\Certificate;
use Apple\ApnPush\Notification as ApnsNotification;
use Apple\ApnPush\Notification\Connection as NotificationConnection;
use Apple\ApnPush\Notification\Message as ApnsMessage;

use GuzzleHttp\Client as GuzzleClient;

class SKMOBILEAPP_BOL_PushMessage
{
    /**
     * Message params
     * 
     * @var array
     */
    protected $messageParams = [];

    /**
     * Language prefix
     * 
     * @var string
     */
    protected $languagePrefix = 'skmobileapp';

    /**
     * Message type
     * 
     * @var string
     */
    protected $messageType;

    /**
     * Sound name
     * 
     * @var string
     */
    protected $soundName; // e.g match.wav

    /**
     * Life time in hours
     * 
     * @var integer
     */
    protected $lifeTimeInHours = 1;

    /**
     * Set message params
     * 
     * @return SKMOBILEAPP_BOL_PushMessage
     */
    public function setMessageParams($params)
    {
        $this->messageParams = $params;

        return $this;
    }

    /**
     * Set language prefix
     * 
     * @return SKMOBILEAPP_BOL_PushMessage
     */
    public function setLanguagePrefix($languagePrefix)
    {
        $this->languagePrefix = $languagePrefix;

        return $this;
    }

    /**
     * Set message type
     * 
     * @return SKMOBILEAPP_BOL_PushMessage
     */
    public function setMessageType($type)
    {
        $this->messageType = $type;

        return $this;
    }

    /**
     * Set sound name
     * 
     * @return SKMOBILEAPP_BOL_PushMessage
     */
    public function setSoundName($soundName)
    {
        $this->soundName = $soundName;

        return $this;
    }

    /**
     * Set life time in hours
     * 
     * @return SKMOBILEAPP_BOL_PushMessage
     */
    public function setLifeTimeInHours($lifeTime)
    {
        $this->lifeTimeInHours = $lifeTime;

        return $this;
    }

    /**
     * Send notification
     * 
     * @param integer $recipientId
     * @param string $titleLangKey
     * @param string $messageLangKey
     * @param array $langVars
     * @return void
     */
    public function sendNotification($recipientId, $titleLangKey, $messageLangKey, $langVars = []) 
    {
        // push notifications are disabled
        if ( !OW::getConfig()->getValue('skmobileapp', 'pn_enabled') )
        {
            return;
        }

        // get all registered recipient's devices
        $devices = SKMOBILEAPP_BOL_DeviceService::getInstance()->findByUserId($recipientId);

        if ( empty($devices) )
        {
            return;
        }

        $languageService = BOL_LanguageService::getInstance();

        // send notification to all registered devices
        foreach ( $devices as $device )
        {
            $messageLanguage = $languageService->findByTag($device->language);

            if ( !$messageLanguage )
            {
                $messageLanguage = $languageService->getCurrent();
            }

            $languageService->setCurrentLanguage($messageLanguage);

            // translate the message
            $title = $languageService->
                    getText($messageLanguage->getId(), $this->languagePrefix, $titleLangKey, $langVars);

            $message = $languageService->
                    getText($messageLanguage->getId(), $this->languagePrefix, $messageLangKey, $langVars);

            // common payload
            $payload = [
                'title' => $title,
                'body' => $message,
                'uuid' => uniqid(),
                'type' => $this->messageType
            ] + $this->messageParams;

            // send message
            switch( $device->platform ) 
            {
                case SKMOBILEAPP_BOL_DeviceDao::PLATFORM_ANDROID :
                case SKMOBILEAPP_BOL_DeviceDao::PLATFORM_BROWSER :
                    // save the payload for the browser platform
                    if ( $device->platform == SKMOBILEAPP_BOL_DeviceDao::PLATFORM_BROWSER ) 
                    {
                        $expirationTime = time() + $this->lifeTimeInHours * 3600;
                        $webPushPayload = $payload;

                        if ( $this->soundName ) 
                        {
                            $webPushPayload = array_merge($webPushPayload, [
                                'sound' => $this->soundName
                            ]);
                        }

                        SKMOBILEAPP_BOL_WebPushService::getInstance()->
                            addNewMessage($recipientId, $device->id, $title, $message, $expirationTime, $webPushPayload);
                    }

                    $this->sendToFirebase($device->token, $title, $message, $payload);

                    break;

                case SKMOBILEAPP_BOL_DeviceDao::PLATFORM_IOS :
                    $this->sendToApns($device->token, $message, $payload);

                    break;

                default :
            }
        }
    }

    /**
     * Send to apns
     * 
     * @param string $token
     * @param string $notificationMessage 
     * @param array $payload
     * @return void
     */
    protected function sendToApns($token, $notificationMessage, $payload)
    {
        // check the certification file existing
        if ( !file_exists(SKMOBILEAPP_BOL_Service::getInstance()->getApnsCertificateFilePath()) )
        {
            return;
        }

        $message = new ApnsMessage($token, $notificationMessage);
        $message->setCustomData($payload);
        $message->setExpires(new DateTime('+' . $this->lifeTimeInHours . ' hours', new DateTimeZone('UTC')));

        if ($this->soundName) 
        {
            $message->setSound($this->soundName);
        }

        $certificate = new Certificate(
            SKMOBILEAPP_BOL_Service::getInstance()->getApnsCertificateFilePath(),
            OW::getConfig()->getValue('skmobileapp', 'pn_apns_pass_phrase')
        );

        $isSandbox = OW::getConfig()->getValue('skmobileapp', 'pn_apns_mode') == 'test';

        $connection = new NotificationConnection($certificate, $isSandbox);
        $notificationService = new ApnsNotification($connection);

        $notificationService->send($message);
    }

    /**
     * Send to firebase
     * 
     * @param string $token
     * @param string $notificationTitle
     * @param string $notificationMessage 
     * @param array $payload
     * @return void
     */
    protected function sendToFirebase($token, $notificationTitle, $notificationMessage, $payload)
    {
        $serverKey = OW::getConfig()->getValue('skmobileapp', 'pn_server_key');

        if ( empty($serverKey) )
        {
            return;
        }

        $client = new Client();

        $client->setApiKey($serverKey);
        $client->injectGuzzleHttpClient(new GuzzleClient());

        // create a ne message
        $message = new Message();

        $message->setPriority('high');
        $message->addRecipient(new Device($token));
        $message->setTimeToLive($this->lifeTimeInHours * 3600);

        $payload = array_merge($payload, [
            'android_channel_id' => 'default'
        ]);

        if ( $this->soundName ) 
        {
            $fileName = pathinfo($this->soundName, PATHINFO_FILENAME);

            $payload = array_merge($payload, [
                'android_channel_id' => $fileName, // channels (Android O and above)
                'sound' => $fileName // we should provide only file name for the firebase
            ]);
        }

        $message->setData($payload);

        $client->send($message);
    }
}
