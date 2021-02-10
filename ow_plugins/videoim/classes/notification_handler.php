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
 * Video IM notification handler
 *
 * @author Sergey Filipovich <psiloscop@gmail.com>
 * @package ow_plugins.videoim.classes
 * @since 1.8.4
 */
class VIDEOIM_CLASS_NotificationHandler
{
    /**
     * Service
     *
     * @var VIDEOIM_BOL_VideoImService
     */
    protected $service;
    
    use OW_Singleton;
    
    public function __construct()
    {
        $this->service = VIDEOIM_BOL_VideoImService::getInstance();
    }

    /**
     * Process notification
     *
     * @param integer $userId
     * @param integer $recipientId
     * @param string $sessionId
     * @param array $notification
     * @return array
     */
    public function sendNotification( $userId, $recipientId, $sessionId, $notification )
    {
        $result = false;
        $errorMessage = OW::getLanguage()->text('videoim', 'unsupported_notification');

        if ( $notification )
        {
            $clearOldNotifications = false;

            if ( !empty($notification['type']) )
            {
                switch( $notification['type'] )
                {
                    case VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_OFFER :
                        list($result, $errorMessage) = $this->service->isAllowedSendVideoImRequest($recipientId);
                        $this->service->saveAuthActionsGroupKey();
//                        $clearOldNotifications = true;
                        break;

                    case VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_ANSWER :
                        list($result, $errorMessage) = $this->service->isAllowedReceiveVideoImRequest();

                        if (!$result)
                        {
                            // answer is not permitted
                            $notificationBol = new VIDEOIM_BOL_Notification;
                            $notificationBol->userId = $userId;
                            $notificationBol->recipientId = $recipientId;
                            $notificationBol->notification = json_encode(array(
                                'type' => VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_NOT_PERMITTED,

                            ));

                            $notificationBol->createStamp = time();
                            $notificationBol->sessionId = $sessionId;
                            $notificationBol->accepted = VIDEOIM_BOL_NotificationDao::NOTIFICATION_NOT_ACCEPTED;
                            $this->service->addNotification($notificationBol);
                        }
                        else
                        {
                            // increase the actions track
                            $this->service->saveAuthActionsGroupKey();
                            $this->service->trackBasicActions($recipientId);
                        }
                        break;

                    case VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_CANDIDATE :
                    case VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_DECLINED :
                        $result = true;
                        $errorMessage = null;
                        break;

                    case VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_NOT_SUPPORTED :
                    case VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_BYE :
                    case VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_CREDITS_OUT :
                        $result = true;
                        $errorMessage = null;
                        $clearOldNotifications = true;
                        break;

                    default :
                }
            }

            if ( $clearOldNotifications )
            {
                $this->service->deleteUserNotifications($userId, $recipientId);
            }

            if ( $result )
            {
                // add a new notification
                $notificationBol = new VIDEOIM_BOL_Notification;
                $notificationBol->userId = $userId;
                $notificationBol->recipientId = $recipientId;
                $notificationBol->notification = json_encode($notification);
                $notificationBol->createStamp = time();
                $notificationBol->sessionId = $sessionId;
                $notificationBol->accepted = VIDEOIM_BOL_NotificationDao::NOTIFICATION_NOT_ACCEPTED;

                $this->service->addNotification($notificationBol);
            }
        }

        return array(
            'result'  => $result,
            'message' => $errorMessage
        );
    }

    /**
     * Sends decline-request notification
     * 
     * @param integer $userId
     * @param integer $recipientId
     * @param string $sessionId
     * @return void
     */
    public function sendDeclineRequestNotification( $userId, $recipientId, $sessionId )
    {
        // clear all old notifications
        $this->service->deleteUserNotifications($recipientId, $userId);

        // add a new notification
        $notificationBol = new VIDEOIM_BOL_Notification;
        $notificationBol->userId = $userId;
        $notificationBol->createStamp = time();
        $notificationBol->recipientId = $recipientId;
        $notificationBol->sessionId = $sessionId;
        $notificationBol->accepted = VIDEOIM_BOL_NotificationDao::NOTIFICATION_NOT_ACCEPTED;
        $notificationBol->notification = json_encode(array(
            'type' => VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_DECLINED
        ));

        $this->service->addNotification($notificationBol);
    }

    /**
     * Sends block-user notification
     *
     * @param integer $userId
     * @param integer $recipientId
     * @param string $sessionId
     * @return void
     */
    public function sendBlockUserNotification( $userId, $recipientId, $sessionId )
    {
        if ( OW::getRequest()->isPost() && $userId )
        {
            if ( !$this->service->isSuperModerator($recipientId) )
            {
                // clear all old notifications
                $this->service->deleteUserNotifications($recipientId, $userId);

                // add a new notification
                $notificationBol = new VIDEOIM_BOL_Notification;
                $notificationBol->userId = $userId;
                $notificationBol->createStamp = time();
                $notificationBol->recipientId = $recipientId;
                $notificationBol->sessionId = $sessionId;
                $notificationBol->accepted = VIDEOIM_BOL_NotificationDao::NOTIFICATION_NOT_ACCEPTED;
                $notificationBol->notification = json_encode(array(
                    'type' => VIDEOIM_BOL_NotificationDao::NOTIFICATION_TYPE_BLOCKED
                ));

                $this->service->addNotification($notificationBol);

                // block
                BOL_UserService::getInstance()->block($userId);
            }
        }

        exit;
    }
}