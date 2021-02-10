<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Kairat Bakytov <kainisoft@gmail.com>
 * @package ow_plugins.winks.mobile.controllers
 * @since 1.7.6
 */
class WINKS_MCTRL_Action extends OW_MobileActionController
{
    private $service;

    public function __construct()
    {
        $this->service = WINKS_BOL_Service::getInstance();
    }

    public function accept()
    {
        $partnerId = OW::getUser()->getId();
        $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : null;

        if ( ($wink = $this->service->findWinkByUserIdAndPartnerId($userId, $partnerId)) === null )
        {
            exit(json_encode(array(
                'result' => false,
                'msg' => OW::getLanguage()->text('winks', 'wink_sent_error')
            )));
        }

        $wink->setStatus(WINKS_BOL_WinksDao::STATUS_ACCEPT);
        WINKS_BOL_WinksDao::getInstance()->save($wink);

        if ( ($winkPartner = $this->service->findWinkByUserIdAndPartnerId($partnerId, $userId)) !== null )
        {
            $winkPartner->setStatus(WINKS_BOL_WinksDao::STATUS_IGNORE);
            WINKS_BOL_WinksDao::getInstance()->save($winkPartner);
        }

        $params = array(
            'userId' => $userId,
            'partnerId' => $partnerId,
            'content' => array(
                'entityType' => 'wink',
                'eventName' => 'renderWink',
                'params' => array(
                    'winkId' => $wink->id,
                    'winkBackEnabled' => 1
                )
            )
        );

        $event = OW::getEventManager()->trigger(new OW_Event('winks.onAcceptWink', $params));
        $data = $event->getData();

        if ( !empty($data['conversationId']) )
        {
            $wink->setConversationId($data['conversationId']);
            WINKS_BOL_WinksDao::getInstance()->save($wink);

            $activeModes = OW::getEventManager()->call('mailbox.get_active_mode_list');

            if ( is_array($activeModes) )
            {
                $mode = in_array('chat', $activeModes) ? 'chat' : 'mail';
            }
            else
            {
                $mode = $activeModes;
            }

            OW::getFeedback()->info(OW::getLanguage()->text('winks', 'msg_accept_request'));

            if ( $mode == 'mail' || $wink->messageType == 'mail' )
            {
                exit(json_encode(array(
                    'result' => true,
                    'url' => OW::getRouter()->urlForRoute('mailbox_mail_conversation', array(
                        'convId' => $wink->getConversationId()
                    ))
                )));
            }

            exit(json_encode(array(
                'result' => true,
                'url' => OW::getRouter()->urlForRoute('mailbox_chat_conversation', array(
                    'userId' => $wink->getUserId()
                ))
            )));
        }

        exit(json_encode(array(
            'result' => false,
            'msg' => OW::getLanguage()->text('winks', 'wink_sent_error')
        )));
    }

    public function ignore()
    {
        $partnerId = OW::getUser()->getId();
        $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : null;

        if ( ($wink = $this->service->findWinkByUserIdAndPartnerId($userId, $partnerId)) === null )
        {
            exit(json_encode(array(
                'result' => false,
                'msg' => OW::getLanguage()->text('winks', 'wink_sent_error')
            )));
        }

        $wink->setStatus(WINKS_BOL_WinksDao::STATUS_IGNORE);
        WINKS_BOL_WinksDao::getInstance()->save($wink);

        OW::getEventManager()->trigger(
            new OW_Event('winks.onIgnoreWink', array('userId' => $userId, 'partnerId' => $partnerId))
        );

        exit(json_encode(array('result' => true)));
    }

    public function winkBack( $params )
    {
        if ( empty($_POST['userId']) || empty($_POST['partnerId']) || empty($_POST['messageId']) || ($wink = $this->service->findWinkByUserIdAndPartnerId($_POST['userId'], $_POST['partnerId'])) === NULL )
        {
            return array('result' => false, 'msg' => OW::getLanguage()->text('winks', 'wink_back_error'));
        }

        if ( $this->service->setWinkback($wink->getId(), true) )
        {
            if ( OW::getEventManager()->call('notifications.is_permited', array('userId' => $wink->userId, 'action' => 'wink_email_notification')) )
            {
                $this->service->sendWinkEmailNotification($wink->partnerId, $wink->userId, WINKS_BOL_Service::EMAIL_BACK);
            }
        }

        OW::getEventManager()->trigger(new OW_Event('winks.onWinkBack', array(
            'userId' => $wink->getUserId(),
            'partnerId' => $wink->getPartnerId(),
            'conversationId' => $wink->getConversationId(),
            'content' => array(
                'entityType' => 'wink',
                'eventName' => 'renderWinkBack',
                'params' => array(
                    'winkId' => $wink->id,
                    'messageId' => $_POST['messageId']
                )
            )
        )));

	exit(json_encode(array('result' => true)));
    }

    public function sendWink( array $params )
    {
        $userService = BOL_UserService::getInstance();

        if ( !isset($_POST['userId'], $_POST['partnerId']) )
        {
            exit(json_encode(array('result' => false, 'msg' => OW::getLanguage()->text('winks', 'wink_sent_error'))));
        }

        $userId = (int)$_POST['userId'];
        $partnerId = (int)$_POST['partnerId'];

        if ( $userService->findUserById($userId) === null || $userService->findUserById($partnerId) === null ||
            $userService->isBlocked($partnerId, $userId) ||
            $this->service->isLimited($userId, $partnerId) )
        {
            exit(json_encode(array('result' => false, 'msg' => OW::getLanguage()->text('winks', 'wink_sent_error'))));
        }

        if ( $this->service->sendWink($userId, $partnerId) )
        {
            OW::getEventManager()->trigger(new OW_Event('winks.send_wink', array(
                'userId' => $userId,
                'partnerId' => $partnerId
            )));

            if ( OW::getPluginManager()->isPluginActive('notifications') )
            {
                $rule = NOTIFICATIONS_BOL_Service::getInstance()->findRuleList($partnerId, array('wink_email_notification'));

                if ( !isset($rule['wink_email_notification']) || (int)$rule['wink_email_notification']->checked )
                {
                    $this->service->sendWinkEmailNotification($userId, $partnerId, WINKS_BOL_Service::EMAIL_SEND);
                }
            }

            exit(json_encode(array('result' => true)));
        }

        exit(json_encode(array('result' => false)));
    }
}
