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

class SKMOBILEAPP_BOL_MailboxService extends SKMOBILEAPP_BOL_Service
{
    use OW_Singleton;

    /**
     * Max last message length
     */
    const MAX_LAST_MESSAGE_LENGTH = 50;

    /**
     * Newest messages time (sec)
     */
    const NEWEST_MESSAGES_TIME = 60;

    /**
     * Get history messages
     *
     * @param integer $userId
     * @param integer $conversationId
     * @param integer $beforeMessageId
     * @param integer $limit
     * @return array
     */
    public function getHistoryMessages($userId, $conversationId, $beforeMessageId, $limit)
    {
        $mailboxService = MAILBOX_BOL_ConversationService::getInstance();
        $conversation = $mailboxService->getConversation($conversationId);
        $messages = [];

        if ( $conversation->initiatorId == $userId || $conversation->interlocutorId == $userId )
        {
            $mailboxMessageDao = MAILBOX_BOL_MessageDao::getInstance();
            $deletedTimestamp = $mailboxService->getConversationDeletedTimestamp($conversationId);
            $dtoList = $mailboxMessageDao->findHistory($conversationId, $beforeMessageId, $limit, $deletedTimestamp);

            // process messages
            foreach( $dtoList as $message )
            {
                $message = $this->getMessageData($userId, $conversationId, $message);

                // mark message as read
                if (!$message['isRecipientRead'] && !$message['isAuthor']) {
                    $mailboxService->markMessageRead($message['id']);
                    $message['isRecipientRead'] = true;
                    $message['updateStamp'] = time();
                }

                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Get latest messages
     *
     * @param integer $userId
     * @param integer $afterMessageId
     * @return array
     */
    public function getLatestMessages($userId, $afterMessageId = 0)
    {
        $messageDao = MAILBOX_BOL_MessageDao::getInstance();
        $conversationDao = MAILBOX_BOL_ConversationDao::getInstance();
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $sql = "
            SELECT
                `a`.*
            FROM
                `{$messageDao->getTableName()}` AS `a`
            INNER JOIN
                `{$conversationDao->getTableName()}` AS `b`
            ON
                a.`conversationId` = b.`id`
                    AND
                a.`timeStamp` > IF(b.`initiatorId` = :userId, b.`initiatorDeletedTimestamp`, b.`interlocutorDeletedTimestamp`)
            WHERE
                (
                    a.`senderId` = :userId
                        OR
                    a.`recipientId` = :userId
                )
                    AND
                (
                    a.`timeStamp` >= :timeStamp
                        OR
                    a.`updateStamp` >= :timeStamp
                )
                    AND
                a.`id` > :afterMessageId
            ORDER BY
              a.`id`
        ";

        $messages = OW::getDbo()->queryForList($sql, [
            'userId' => $userId,
            'afterMessageId' => $afterMessageId,
            'timeStamp' => time() - self::NEWEST_MESSAGES_TIME
        ]);

        $processedMessages = [];

        // process messages
        if ( $messages )
        {
            foreach ( $messages as $message ) 
            {
                $processedMessages[] = $this->getMessageData($userId,
                        $message['conversationId'], $conversationService->getMessage($message['id']));
            }
        }

        return $processedMessages;
    }

    /**
     * Get messages
     *
     * @param integer $userId
     * @param integer $conversationId
     * @param integer $limit
     * @return array
     */
    public function getMessages($userId, $conversationId, $limit)
    {
        $mailboxService = MAILBOX_BOL_ConversationService::getInstance();
        $conversation = $mailboxService->getConversation($conversationId);
        $messages = [];

        if ($conversation->initiatorId == $userId || $conversation->interlocutorId == $userId)
        {
            $mailboxMessageDao = MAILBOX_BOL_MessageDao::getInstance();
            $deletedTimestamp = $mailboxService->getConversationDeletedTimestamp($conversationId);
            $dtoList = $mailboxMessageDao->findListByConversationId($conversationId, $limit, $deletedTimestamp);

            // process messages
            foreach ( $dtoList as $message )
            {
                $messages[] = $this->getMessageData($userId, $conversationId, $message);
            }
        }

        return $messages;
    }

    /**
     * Get conversations
     *
     * @param integer $userId
     * @param integer $limit
     * @param integer $conversationId
     * @return array
     */
    public function getConversations($userId, $limit)
    {
        $mailboxService = MAILBOX_BOL_ConversationService::getInstance();
        $mailboxConversationDao = MAILBOX_BOL_ConversationDao::getInstance();

        $eventParams = $mailboxService->
            getQueryFilter(MAILBOX_BOL_ConversationService::EVENT_ON_BEFORE_GET_CONVERSATION_LIST_BY_USER_ID);

        // get only chats conversations
        $conversations = [];
        $conversationItemList = $mailboxConversationDao->
            findConversationItemListByUserId($userId, ['chat'], 0, $limit, null, $eventParams);

        $idList = [];

        // process conversations
        foreach( $conversationItemList as $conversation )
        {
            $isConversationRead = false;
            $isConversationReadByOpponent = false;
            $opponentId = 0;

            // try to define if conversation is read or not
            switch ( $userId )
            {
                case $conversation['initiatorId']:
                    $opponentId = $conversation['interlocutorId'];

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                    {
                        $isConversationRead = true;
                    }

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                    {
                        $isConversationReadByOpponent = true;
                    }

                    break;

                case $conversation['interlocutorId']:
                    $opponentId = $conversation['initiatorId'];

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                    {
                        $isConversationRead = true;
                    }

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                    {
                        $isConversationReadByOpponent = true;
                    }

                    break;
            }

            $conversations[$opponentId] = [
                'id' => $userId . '_' . $opponentId,
                'isNew' => !$isConversationRead,
                'isReply' => $userId != $conversation['initiatorMessageRecipientId'],
                'isOpponentRead' => $isConversationReadByOpponent,
                'lastMessageTimestamp' => (int) $conversation['initiatorMessageTimestamp'],
                'previewText' => $this->getConversationPreviewText($userId, $conversation),
                'avatar' => null,
                'user' => [
                    'id' => (int) $opponentId,
                    'userName' => null,
                    'isBlocked' => false
                ]
            ];

            $idList[] = $opponentId;
        }

        // find blocked users
        if ( $idList )
        {
            $blockedUsers = BOL_UserService::getInstance()->findBlockedListByUserIdList($userId, $idList);

            foreach ( $blockedUsers as $userId => $blockedStatus ) 
            {
                $conversations[$userId]['user']['isBlocked'] = $blockedStatus;
            }
        }

        // load avatars
        $avatarList = BOL_AvatarService::getInstance()->findByUserIdList($idList);

        foreach( $avatarList as $avatar ) 
        {
            $conversations[$avatar->userId]['avatar'] = $this->getAvatarData($avatar, false);
        }

        // load user names
        $userNames = BOL_UserService::getInstance()->getUserNamesForList($idList);

        foreach( $userNames as $userId => $userName ) 
        {
            $conversations[$userId]['user']['userName'] = $userName;
        }

        // load display names
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($idList);

        foreach( $displayNames as $userId => $displayName ) 
        {
            if ( $displayName ) 
            {
                $conversations[$userId]['user']['userName'] = $displayName;
            }
        }

        // convert to an array from the dictionary
        $data = [];
        foreach( $conversations as $conversation )
        {
            $data[] = $conversation;
        }

        return $data;
    }

    /**
     * Create photo message
     *
     * @param integer $userId
     * @param integer $opponentId
     * @param array $file
     * @param string $tempId
     * @throws Exception
     * @return array
     */
    public function createPhotoMessage($userId, $opponentId, $file, $tempId = null)
    {
        $language = OW::getLanguage();
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        // get a conversation id between users
        $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentId);
        $conversation = !$conversationId
            ? $conversationService->createChatConversation($userId, $opponentId)
            : $conversationService->getConversation($conversationId);

        $actionName = $this->checkIsMessagePostAllowed($conversation, $userId, $opponentId);
        $attachmentService = BOL_AttachmentService::getInstance();

        // create a message
        $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
        $validFileExtensions = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);
        $uid = UTIL_HtmlTag::generateAutoId('mailbox_conversation_' . $conversationId . '_' . $opponentId);

        $attachmentService->processUploadedFile('mailbox', $file, $uid, $validFileExtensions, $maxUploadSize);
        $files = $attachmentService->getFilesByBundleName('mailbox', $uid);

        if ( !empty($files) )
        {
            $text = $language->text('mailbox', 'attachment');

            $event = new OW_Event('mailbox.before_send_message', [
                'senderId' => $userId,
                'recipientId' => $opponentId,
                'conversationId' => $conversation->getId(),
                'message' => $text,
                'attachments' => $files
            ], ['result' => true, 'error' => '', 'message' => $text]);

            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            if ( !$data['result'] )
            {
                return $data;
            }

            $message = $conversationService->createMessage($conversation, $userId, $text, false, $tempId);
            $conversationService->addMessageAttachments($message->id, $files);

            BOL_AuthorizationService::getInstance()->trackActionForUser($userId, 'mailbox', $actionName);

            $event = new OW_Event('mailbox.after_send_message', [
                'senderId' => $userId,
                'recipientId' => $opponentId,
                'conversationId' => $conversation->getId(),
                'message' => $text
            ], ['result' => true, 'error' => '', 'message' => $text]);

            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            if ( !$data['result'] )
            {
                return $data;
            }

            return $this->getMessageData($userId, $conversation->getId(), $message);
        }
    }

    /**
     * Create message
     *
     * @param integer $userId
     * @param integer $opponentId
     * @param string $text
     * @param string $tempId
     * @throws Exception
     * @return array
     */
    public function createMessage($userId, $opponentId, $text, $tempId = null)
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $language = OW::getLanguage();

        if ( !trim(strip_tags($text)) )
        {
            throw new Exception($language->text('mailbox', 'chat_message_empty'));
        }

        if ( mb_strlen($text) > MAILBOX_BOL_AjaxService::MAX_MESSAGE_TEXT_LENGTH )
        {
            throw new Exception($language->text('mailbox', 'message_too_long_error', [
                'maxLength' => MAILBOX_BOL_AjaxService::MAX_MESSAGE_TEXT_LENGTH
            ]));
        }

        // get a conversation id between users
        $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentId);
        $conversation = !$conversationId
            ? $conversationService->createChatConversation($userId, $opponentId)
            : $conversationService->getConversation($conversationId);

        // check extra permissions
        $actionName = $this->checkIsMessagePostAllowed($conversation, $userId, $opponentId);

        // create message
        $text = UTIL_HtmlTag::stripTags(UTIL_HtmlTag::stripJs($text));
        $text = nl2br($text);

        $event = new OW_Event('mailbox.before_send_message', [
            'senderId' => $userId,
            'recipientId' => $opponentId,
            'conversationId' => $conversation->getId(),
            'message' => $text
        ], ['result' => true, 'error' => '', 'message' => $text]);

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( !$data['result'] )
        {
            return $data;
        }

        $text = $data['message'];
        $message = $conversationService->createMessage($conversation, $userId, $text, false, $tempId);

        BOL_AuthorizationService::getInstance()->trackActionForUser($userId, 'mailbox', $actionName);

        $event = new OW_Event('mailbox.after_send_message', [
            'senderId' => $userId,
            'recipientId' => $opponentId,
            'conversationId' => $conversation->getId(),
            'message' => $text
        ], ['result' => true, 'error' => '', 'message' => $text]);

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( !$data['result'] )
        {
            return $data;
        }

        return $this->getMessageData($userId, $conversation->getId(), $message);
    }

    /**
     * Get message data
     *
     * @param integer $userId
     * @param integer $conversationId
     * @param MAILBOX_BOL_Message $message
     * @return array
     */
    public function getMessageData($userId, $conversationId, MAILBOX_BOL_Message $message)
    {
        $mailboxService = MAILBOX_BOL_ConversationService::getInstance();
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'read_chat_message', [
            'userId' => $userId
        ]);

        $text = '';
        $readMessageAuthorized  = true;
        $messageAttachments = [];
        $messageWasAuthorized =  $message->senderId == $userId ? true : $message->wasAuthorized;

        // check permission for viewing chat messages
        if ( (int) $message->senderId != $userId && !$message->wasAuthorized )
        {
            switch($status['status'])
            {
                case BOL_AuthorizationService::STATUS_AVAILABLE :
                    if ($status['authorizedBy'] == SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY)
                    {
                        $action = USERCREDITS_BOL_CreditsService::getInstance()->findAction('mailbox', 'read_chat_message');
                        $actionPrice = USERCREDITS_BOL_CreditsService::getInstance()->findActionPriceForUser($action->id, $userId);

                        // action is available for free
                        if ($actionPrice->amount >= 0 || $actionPrice->disabled)
                        {
                            $readMessageAuthorized = true;
                            $mailboxService->markMessageAuthorizedToRead($message->id);
                            $messageWasAuthorized = true;

                            //  increase credits
                            if ($actionPrice->amount > 0 && !$actionPrice->disabled)
                            {
                                BOL_AuthorizationService::getInstance()->
                                    trackActionForUser($userId, 'mailbox', 'read_chat_message', ['checkInterval' => false]);
                            }

                            continue;
                        }

                        $readMessageAuthorized = false;

                        continue;
                    }

                    $readMessageAuthorized = true;
                    $mailboxService->markMessageAuthorizedToRead($message->id);
                    $messageWasAuthorized = true;
                    break;

                case BOL_AuthorizationService::STATUS_PROMOTED:
                default :
                    $readMessageAuthorized = false;
            }
        }

        if ( $readMessageAuthorized )
        {
            $text = $message->text;

            // get attachments
            $attachments = MAILBOX_BOL_AttachmentDao::getInstance()->findAttachmentsByMessageId($message->id);

            if ( !empty($attachments) )
            {
                foreach( $attachments as $attachment )
                {
                    $ext = UTIL_File::getExtension($attachment->fileName);
                    $attachmentPath = $mailboxService->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                    $attItem = [];
                    $attItem['downloadUrl'] = OW::getStorage()->getFileUrl($attachmentPath);
                    $attItem['fileName'] = $attachment->fileName;
                    $attItem['fileSize'] = $attachment->fileSize;
                    $attItem['type'] = $mailboxService->getAttachmentType($attachment);

                    $messageAttachments[] = $attItem;
                }
            }
        }

        return [ 
            'id' => (int) $message->id,
            'text' => trim(strip_tags($text)),
            'tempId' => $message->tempId,
            'isSystem' => (bool) $message->isSystem,
            'date' => date('Y-m-d', (int)$message->timeStamp),
            'dateLabel' => UTIL_DateTime::formatDate( (int) $message->timeStamp, true),
            'time' => date('h:iA', (int) $message->timeStamp),
            'isAuthor' => $message->senderId == $userId,
            'attachments' => $messageAttachments,
            'isAuthorized' => (bool) $messageWasAuthorized,
            'isRecipientRead' => (bool) $message->recipientRead,
            'timeStamp' => (int) $message->timeStamp,
            'updateStamp' => (int) $message->updateStamp,
            'opponentId' => $message->senderId !== $userId ? $message->senderId : $userId,
            'conversation' => [
                'id' => $userId . '_' . ($message->senderId == $userId ? $message->recipientId : $message->senderId)
            ]
        ];
    }

    /**
     * Is read chat message allowed after tracking
     * 
     * @param integer $userId
     * @return array
     */
    public function isReadChatMessageAllowedAfterTracking($userId, $isActionAllowed) 
    {
        $isCreditsActive = OW::getPluginManager()->isPluginActive(SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY);

        $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'read_chat_message', [
            'userId' => $userId
        ]);

        switch($status['status'])
        {
            case BOL_AuthorizationService::STATUS_AVAILABLE :
                if ($status['authorizedBy'] == self::USER_CREDITS_PLUGIN_KEY && $isCreditsActive)
                {
                    $action = USERCREDITS_BOL_CreditsService::getInstance()->findAction('mailbox', 'read_chat_message');
                    $actionPrice = USERCREDITS_BOL_CreditsService::getInstance()->findActionPriceForUser($action->id, $userId);

                    if ($actionPrice->amount < 0 && !$actionPrice->disabled)
                    {
                        return [
                            true,
                            false
                        ];
                    }

                    return [
                        false,
                        $isActionAllowed
                    ];
                }

            default :
        }

        return [
            false,
            $isActionAllowed
        ];
    }

    /**
     * Get conversation preview text
     *
     * @param integer $userId
     * @param array $conversation
     * @return string
     */
    protected function getConversationPreviewText($userId, array $conversation)
    {
        if ( $conversation['initiatorMessageSenderId'] != $userId && !$conversation['initiatorMessageWasAuthorized'] ) 
        {
            // check extra permission settings
            $isReadMessageAllowed = $this->isPermissionAllowed($userId, 'mailbox', 'read_chat_message');
            list($isActionAllowedAfterTracking, $isActionAllowed) = 
                    $this->isReadChatMessageAllowedAfterTracking($userId, $isReadMessageAllowed);

            if ( !$isActionAllowed || $isActionAllowedAfterTracking ) 
            {
                return trim(strip_tags(OW::getLanguage()->text('skmobileapp', 'conversation_new_message')));
            }
        }

        // check if message is system
        if ( $conversation['initiatorMessageIsSystem'] )
        {
            $eventParams = json_decode($conversation['initiatorText'], true);
            $eventParams['params']['messageId'] = (int)$conversation['initiatorLastMessageId'];
            $eventParams['params']['getPreview'] = true;

            $event = new OW_Event($eventParams['entityType'] . '.' . $eventParams['eventName'], $eventParams['params']);
            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            if ( !empty($data) )
            {
                return trim(strip_tags($data));
            }

            return trim(strip_tags(OW::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', [
                'entityType' => $eventParams['entityType']
            ])));
        }

        $message = mb_strlen($conversation['initiatorText']) > self::MAX_LAST_MESSAGE_LENGTH
            ? mb_substr($conversation['initiatorText'], 0,  self::MAX_LAST_MESSAGE_LENGTH) . '...'
            : $conversation['initiatorText'];

        $event = new OW_Event('mailbox.message_render', array(
            'conversationId' => $conversation['id'],
            'messageId' => $conversation['initiatorLastMessageId'],
            'senderId' => $conversation['initiatorMessageSenderId'],
            'recipientId' => $conversation['initiatorMessageRecipientId'],
        ), [ 'short' => $message, 'full' => $conversation['initiatorText'] ]);

        OW::getEventManager()->trigger($event);

        $eventData = $event->getData();

        return trim(strip_tags($eventData['short']));
    }

    /**
     * Check is message post allowed
     *
     * @param MAILBOX_BOL_Conversation $conversation
     * @param integer $userId
     * @param integer $opponentId
     * @throws Exception
     * @return string
     */
    protected function checkIsMessagePostAllowed(MAILBOX_BOL_Conversation $conversation, $userId, $opponentId)
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $checkResult = $conversationService->checkUser($userId, $opponentId);

        MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($opponentId);

        if ( $checkResult['isSuspended'] )
        {
            throw new Exception($checkResult['suspendReasonMessage']);
        }

        $firstMessage = $conversationService->getFirstMessage($conversation->getId());
        $actionName = empty($firstMessage) ? 'send_chat_message' : 'reply_to_chat_message';

        $isAuthorized = $this->isPermissionAllowed($userId, 'mailbox', $actionName);

        if ( !$isAuthorized )
        {
            throw new Exception(OW::getLanguage()->text('mailbox', $actionName . '_permission_denied'));
        }

        if ($conversation->initiatorId != $userId && $conversation->interlocutorId != $userId) {
            throw new Exception(OW::getLanguage()->text('mailbox', $actionName . '_permission_denied'));
        }

        // check privacy
        if (!$firstMessage)
        {
            $canInvite = $conversationService->getInviteToChatPrivacySettings($userId, $opponentId);

            if (!$canInvite)
            {
                throw new Exception(OW::getLanguage()->text('mailbox', 'warning_user_privacy_friends_only', [
                    'displayname' => BOL_UserService::getInstance()->getDisplayName($opponentId)
                ]));
            }
        }

        return $actionName;
    }
}
