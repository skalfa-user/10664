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
 * Video IM controller
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_plugin.videoim.controllers
 * @since 1.8.1
 */
class VIDEOIM_CTRL_VideoIm extends OW_ActionController
{
    /**
     * Service
     *
     * @var VIDEOIM_BOL_VideoImService
     */
    protected $service;

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        $this->service = VIDEOIM_BOL_VideoImService::getInstance();
    }

    /**
     * Get chat link
     */
    public function ajaxGetChatLink()
    {
        $recipientId = !empty($_GET['recipientId']) ? (int) $_GET['recipientId'] : -1;

        echo json_encode(array(
            'content' => OW::getClassInstance('VIDEOIM_CMP_ChatLink', $recipientId)->render()
        ));

        exit;
    }

    /**
     * Ajax mark notifications as accepted
     */
    public function ajaxNotificationsMarkAccepted()
    {
        if (OW::getRequest()->isPost() && OW::getUser()->getId())
        {
            $userId = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : -1;
            $stringId = !empty($_POST['session_id']) ? (string) $_POST['session_id'] : null;

            $this->service->markAcceptedNotifications($userId, OW::getUser()->getId(), $stringId);
        }

        exit;
    }

    /**
     * Ajax track credits timing call
     */
    public function ajaxTrackCreditsTimingCall()
    {
        $allowed = false;

        if (OW::getRequest()->isPost() && OW::getUser()->getId() && isset($_POST['is_initiator']))
        {
            $userId = OW::getUser()->getId();
            $isInitiator = (bool) $_POST['is_initiator'];

            $allowed = $this->service->isCreditsTimingCallEnough($userId, $isInitiator);

            if ($allowed)
            {
                $this->service->trackTimedAction();
            }
        }

        echo json_encode(array(
            'allowed' => $allowed
        ));

        exit;
    }

    /**
     * Ajax decline request
     */
    public function ajaxDeclineRequest()
    {
        if (OW::getRequest()->isPost() && OW::getUser()->getId())
        {
            $userId = OW::getUser()->getId();
            $recipientId = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : -1;
            $sessionId = !empty($_POST['session_id']) ? (string) $_POST['session_id'] : null;

            VIDEOIM_CLASS_NotificationHandler::getInstance()
                ->sendDeclineRequestNotification($userId, $recipientId, $sessionId);
        }

        exit;
    }

    /**
     * Ajax block user
     */
    public function ajaxBlockUser()
    {
        if (OW::getRequest()->isPost() && OW::getUser()->getId())
        {
            $userId = OW::getUser()->getId();
            $recipientId = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : -1;
            $sessionId = !empty($_POST['session_id']) ? (string) $_POST['session_id'] : null;

            VIDEOIM_CLASS_NotificationHandler::getInstance()
                ->sendBlockUserNotification($userId, $recipientId, $sessionId);
        }

        exit;
    }

    /**
     * Ajax send notification
     */
    public function ajaxSendNotification()
    {
        if (OW::getRequest()->isPost() && OW::getUser()->getId())
        {
            $userId = OW::getUser()->getId();
            $recipientId = !empty($_POST['recipient_id']) ? (int) $_POST['recipient_id'] : -1;
            $sessionId = !empty($_POST['session_id']) ? (string) $_POST['session_id'] : null;
            $notification = !empty($_POST['notification']) ? json_decode($_POST['notification'], true) : null;
            $result = VIDEOIM_CLASS_NotificationHandler::getInstance()
                ->sendNotification($userId, $recipientId, $sessionId, $notification);

            echo json_encode($result);
        }

        exit;
    }

    /**
     * Chat window
     */
    public function chatWindow()
    {
        // get vars
        $recipientId = !empty($_GET['recipientId']) ? (int) $_GET['recipientId'] : -1;
        $sessionId = !empty($_GET['sessionId']) ? (string) $_GET['sessionId'] :null;
        $isInitiator = !empty($_GET['initiator']) ? 1 : 0;

        // check user existing
        if (null === BOL_UserService::getInstance()->findUserById($recipientId))
        {
            throw new Redirect404Exception();
        }

        // clear all old notifications
        if ($isInitiator)
        {
            $this->service->deleteUserNotifications(OW::getUser()->getId(), $recipientId);
        }

        // change the master page
        OW::getDocument()->getMasterPage()->
        setTemplate(OW::getPluginManager()->getPlugin('videoim')->getRootDir() . 'views/master_pages/blank.html');

        // include necessary js and css files
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->
            getPlugin('videoim')->getStaticCssUrl() . 'videoim.css?build=' . $this->service->getPluginBuild());

        $this->includeBaseResources($recipientId, $isInitiator, $sessionId);
    }

    /**
     * Include base resources
     *
     * @param integer $recipientId
     * @param integer $isInitiator
     * @param string $sessionId
     * @apram integer $avatarSize
     * @return void
     */
    protected function includeBaseResources($recipientId, $isInitiator, $sessionId, $avatarSize = 2)
    {
        $currentPluginBuild = $this->service->getPluginBuild();

        // include necessary js and css files
        OW::getDocument()->addScript(OW::getPluginManager()->
            getPlugin('videoim')->getStaticJsUrl() . 'adapter.js?build=' . $currentPluginBuild);

        OW::getDocument()->addScript(OW::getPluginManager()->
            getPlugin('videoim')->getStaticJsUrl() . 'videoim.js?build=' . $currentPluginBuild);

        OW::getDocument()->addScript(OW::getPluginManager()->
            getPlugin('videoim')->getStaticJsUrl() . 'videoim_controls.js?build=' . $currentPluginBuild);

        OW::getDocument()->addScript(OW::getPluginManager()->
            getPlugin('videoim')->getStaticJsUrl() . 'jquery.fullscreen.js?build=' . $currentPluginBuild);

        OW::getDocument()->addScript(OW::getPluginManager()->
            getPlugin('videoim')->getStaticJsUrl() . 'timer.jquery.js?build=' . $currentPluginBuild);

        $loggedUserId = OW::getUser()->getId();
        $configs = OW::getConfig()->getValues('videoim');

        $timedCallPrice = 0;
        $currentTrackCreditsType = $configs['track_credits_type'];

        if ( !OW::getUser()->isAdmin() )
        {
            if ( $currentTrackCreditsType === VIDEOIM_BOL_VideoImService::TRACK_CREDITS_BOTH ||
                ($currentTrackCreditsType === VIDEOIM_BOL_VideoImService::TRACK_CREDITS_INITIATOR && $isInitiator) ||
                ($currentTrackCreditsType === VIDEOIM_BOL_VideoImService::TRACK_CREDITS_INTERLOCUTOR && !$isInitiator) )
            {
                $timedCallPrice = $this->service->getTimedCallPrice();
            }
        }

        // init view variables
        $this->assign('serverList', $configs['server_list']);
        $this->assign('loggedUserId', $loggedUserId);
        $this->assign('recipientId', $recipientId);
        $this->assign('sessionId', $sessionId);
        $this->assign('recipientUrl', BOL_UserService::getInstance()->getUserUrl($recipientId));
        $this->assign('recipientAvatar', $this->service->getUserAvatar($recipientId, $avatarSize));
        $this->assign('recipientName', BOL_UserService::getInstance()->getDisplayName($recipientId));
        $this->assign('isInitiator', $isInitiator);
        $this->assign('notificationsLifetime', VIDEOIM_BOL_NotificationDao::NOTIFICATIONS_LIFETIME);
        $this->assign('timedCallPrice', $timedCallPrice);
    }
}