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
abstract class SKMOBILEAPP_CLASS_AbstractEventHandler
{
    /**
     * Generic init
     */
    public function genericInit()
    {
        $eventManager = OW::getEventManager();

        $eventManager->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        $eventManager->bind('mailbox.send_message', array($this, 'afterMailboxMessageSent'));
        $eventManager->bind(OW_EventManager::ON_APPLICATION_INIT, array($this, 'checkApiUrl'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnRegister'));
        $eventManager->bind('membership.expire_user_membership_list', array($this, 'onExpireUserMembershipList'), 0);
        $eventManager->bind('membership.expire_user_membership', array($this, 'onExpireUserMembership'), 0);
        $eventManager->bind('membership.deliver_sale_notification', array($this, 'onDeliverSaleNotification'), 0);
    }

    /**
     * On notify actions
     */
    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'skmobileapp',
            'action' => 'skmobileapp-new_match_message',
            'sectionIcon' => 'ow_ic_mail',
            'sectionLabel' => OW::getLanguage()->text('skmobileapp', 'skmobileapp_email_notifications_section_label'),
            'description' => OW::getLanguage()->text('skmobileapp', 'skmobileapp_email_notifications_new_match_message'),
            'selected' => true
        ));
    }

    /**
     * On user un register
     *
     * @param OW_Event $e
     */
    public function onUserUnRegister( OW_Event $e )
    {
        $params = $e->getParams();

        SKMOBILEAPP_BOL_Service::getInstance()->deleteUserData($params['userId']);
    }

    /**
     * After mailbox message sent
     *
     * @param OW_Event $event
     * @return void
     */
    public function afterMailboxMessageSent( OW_Event $event )
    {
        $params = $event->getParams();
        $message = $event->getData();

        if ( !empty($params['isSystem']) )
        {
            return;
        }

        $userId = $params['recipientId'];
        $senderId = $params['senderId'];
        $conversationId = $params['conversationId'];

        $senderName = BOL_UserService::getInstance()->getDisplayName($senderId);
        $text = strip_tags($params['message']);
        $dataText = json_decode($params['message'], true);

        $isPushAllowed = (bool) BOL_PreferenceService::
                getInstance()->getPreferenceValue('skmobileapp_new_messages_push', $userId);

        if ( !is_array($dataText) && $isPushAllowed )
        {
            $processedMessage = SKMOBILEAPP_BOL_MailboxService::
                    getInstance()->getMessageData($userId, $conversationId, $message);

            // recipient cannot read the message
            if (!$processedMessage['isAuthorized']) {
                $text = trim(strip_tags(OW::getLanguage()->text('skmobileapp', 'conversation_new_message')));
            }

            $pushMessage = new SKMOBILEAPP_BOL_PushMessage;
            $pushMessage->setMessageType('message')
                ->setMessageParams([
                    'conversationId' => (int) $conversationId,
                    'senderId' => (int) $senderId
                ]);

            $pushMessage->sendNotification($userId, 'pn_new_message_title', 'pn_new_message', [
                'username' => $senderName,
                'message' => $text
            ]);
        }
    }

    /**
     * Check api url
     *
     * @return void
     */
    public function checkApiUrl()
    {
        try
        {
            $apiRoute = OW::getRouter()->getRoute('skmobileapp.api');

            if ( stristr($_SERVER['REQUEST_URI'], $apiRoute->getRoutePath()) )
            {
                OW::getRouter()->setUri($apiRoute->getRoutePath()); // redirect all actions to the index action
            }
        }
        catch(Exception $e)
        {}
    }


    public function onExpireUserMembershipList( OW_Event $event )
    {
        $data = $event->getData();

        $inappsService = SKMOBILEAPP_BOL_PaymentsService::getInstance();

        /* @var SKMOBILEAPP_BOL_PaymentsService $inappsService */

        if ( !empty($data) )
        {
            $memberships = array();

            foreach ( $data as $membershipUser )
            {
                /* @var MEMBERSHIP_BOL_MembershipUser $membershipUser */

                if ( $membershipUser->recurring )
                {
                    $info = $inappsService->findInappByMembershipId($membershipUser->getId());

                    if ( !empty($info) )
                    {
                        /* @var SKMOBILEAPP_BOL_InappsPurchase $info */

                        $plan = $inappsService->getMembershipPlanBySaleId($info->saleId);

                        if ( !empty($plan) )
                        {
                            // extend membership
                            $inappsService->extendMembershipUser($info->saleId, $membershipUser->id);

                            // save to temporary table
                            $expirationPurchase = $inappsService->findExpirationPurchase( $membershipUser->userId, $membershipUser->id );

                            if ( empty($expirationPurchase) )
                            {
                                $expirationPurchase = new SKMOBILEAPP_BOL_ExpirationPurchase();
                            }

                            $expirationPurchase->membershipId = $membershipUser->getId();
                            $expirationPurchase->typeId = $membershipUser->typeId;
                            $expirationPurchase->userId = $membershipUser->userId;
                            $expirationPurchase->expirationTime = time(); // default time + 7200
                            $expirationPurchase->counter = 0;
                            $inappsService->updateExpirationPurchase($expirationPurchase);

                            continue;
                        }
                        else
                        {
                            $inappsService->deleteInappsPurchaseByObject($info);
                        }
                    }
                }

                $memberships[] = $membershipUser;
            }

            $event->setData($memberships);
        }
    }

    public function onExpireUserMembership( OW_Event $event )
    {
        // remove expiration purchase and in apps membership

        $params = $event->getParams();

        if ( isset($params['id']) && isset($params['userId']) )
        {
            $inappsService = SKMOBILEAPP_BOL_PaymentsService::getInstance();

            $inappsService->deleteInappsPurchase($params['id']);
            $inappsService->deleteExpirationPurchase($params['id'], $params['userId']);
        }
    }

    public function onDeliverSaleNotification( OW_Event $event )
    {
        $params = $event->getParams();

        if ( isset($params['sale']) && !empty($params['sale']) )
        {
            $inappsService = SKMOBILEAPP_BOL_PaymentsService::getInstance();

            $sale = $params['sale'];
            $membership = $params['membership'];

            if ( $membership->recurring )
            {
                $extraData = json_decode($sale->extraData, true);

                if ( isset($extraData['platform']) &&
                    in_array(strtolower($extraData['platform']), [SKMOBILEAPP_BOL_PaymentsService::PLATFORM_IOS, SKMOBILEAPP_BOL_PaymentsService::PLATFORM_ANDROID]) )
                {
                    $platform = $extraData['platform'];

                    $inappsPurchase = new SKMOBILEAPP_BOL_InappsPurchase();
                    $inappsPurchase->saleId = $sale->getId();
                    $inappsPurchase->membershipId = $membership->getId();
                    $inappsPurchase->platform = $platform;

                    $inappsService->updateInappsPurchase($inappsPurchase);
                }
            }
        }
    }
}
