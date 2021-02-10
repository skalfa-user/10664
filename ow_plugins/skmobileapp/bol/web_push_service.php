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

class SKMOBILEAPP_BOL_WebPushService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Add a new message
     *
     * @param integer $userId
     * @param integer $deviceId
     * @param string $title
     * @param string $message
     * @param integer $expirationTime
     * @param array $payload
     * @return void
     */
    public function addNewMessage($userId, $deviceId, $title, $message, $expirationTime, $payload = [])
    {
        $pushNotice = new SKMOBILEAPP_BOL_WebPush();
        $pushNotice->userId = $userId;
        $pushNotice->deviceId = $deviceId;
        $pushNotice->title = $title;
        $pushNotice->message = $message;
        $pushNotice->expirationTime = $expirationTime;
        $pushNotice->pushParams = $payload
            ? json_encode($payload)
            : null;

        SKMOBILEAPP_BOL_WebPushDao::getInstance()->save($pushNotice);
    }

    /**
     * Find first message
     * 
     * @param integer $userId
     * @param integer $deviceId
     * @return SKMOBILEAPP_BOL_WebPush
     */
    public function findFirstMessage($userId, $deviceId)
    {
        return SKMOBILEAPP_BOL_WebPushDao::getInstance()->findFirstMessage($userId, $deviceId);
    }

    /**
     * Clean expired messages
     */
    public function cleanExpiredMessages()
    {
        SKMOBILEAPP_BOL_WebPushDao::getInstance()->cleanExpiredMessages();
    }

    /**
     * Delete message
     * 
     * @param integer $id
     * @return void
     */
    public function deleteMessage($id)
    {
        SKMOBILEAPP_BOL_WebPushDao::getInstance()->deleteById($id);
    }
}
