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
namespace Skadate\Mobile\Controller;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use OW;
use MAILBOX_BOL_ConversationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use SKMOBILEAPP_BOL_MailboxService;
use BOL_AuthorizationService;

class Mailbox extends Base
{
    /**
     * Max messages count
     */
    const MAX_MESSAGES_COUNT = 20;

    /**
     * Is plugin active
     *
     * @var bool
     */
    protected $isPluginActive = false;

    /**
     * Mailbox service
     *
     * @var SKMOBILEAPP_BOL_MailboxService
     */
    protected $mailboxService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('mailbox');
        $this->mailboxService = SKMOBILEAPP_BOL_MailboxService::getInstance();
    }

    /**
     * Connect methods
     *
     * @param SilexApplication $app
     * @return mixed
     */
    public function connect(SilexApplication $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // update messages
        $controllers->put('/messages/', function (Request $request, SilexApplication $app) {
            if ($this->isPluginActive) {
                $messageIdsList = json_decode($request->getContent(), true);
                $loggedUserId = $app['users']->getLoggedUserId();
                $service = MAILBOX_BOL_ConversationService::getInstance();

                if (empty($messageIdsList['ids'])) {
                    throw new BadRequestHttpException('Message ids list is empty');
                }

                // mark messages as read
                foreach($messageIdsList['ids'] as $messageId) {
                    $messageDto = $service->getMessage($messageId);

                    if ($messageDto && $messageDto->recipientId == $loggedUserId) {
                        $service->markMessageRead($messageId);
                    }
                }

                return $app->json([], 204); // ok
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        // photo messages
        $controllers->post('/photo-messages/', function (Request $request, SilexApplication $app) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $opponentId = $request->get('opponentId', -1);
                $tempId = $request->get('id', null);

                // check the uploaded file
                if (empty($_FILES['file']['tmp_name'])) {
                    throw new BadRequestHttpException('File was not uploaded');
                }

                if ($opponentId) {
                    try {
                        $result = $this->mailboxService->createPhotoMessage($loggedUserId, $opponentId, $_FILES['file'], $tempId);

                        // generate error
                        if (isset($result['result'], $result['error']) && !$result['result']) {
                            throw new BadRequestHttpException($result['error']);
                        }

                        return $app->json($result);
                    }
                    catch(Exception $e) {
                        return new Response(json_encode(['messagesError' => $e->getMessage()]), 404, [
                            'Content-Type' => 'application/json'
                        ]);
                    }
                }

                throw new BadRequestHttpException('opponentId param is missing');
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        // create message
        $controllers->post('/messages/', function (Request $request, SilexApplication $app) {
            if ($this->isPluginActive) {
                $vars = json_decode($request->getContent(), true);

                $loggedUserId = $app['users']->getLoggedUserId();
                $tempId = !empty($vars['id']) ? $vars['id'] : null;
                $text = !empty($vars['text']) ? $vars['text'] : '';
                $opponentId = !empty($vars['opponentId']) ? $vars['opponentId'] : -1;

                if ($opponentId && $text) {
                    try {
                        $result = $this->mailboxService->createMessage($loggedUserId, $opponentId, $text, $tempId);

                        // generate error
                        if (isset($result['result'], $result['error']) && !$result['result']) {
                            throw new BadRequestHttpException($result['error']);
                        }

                        return $app->json($result);
                    }
                    catch(Exception $e) {
                        return new Response(json_encode(['messagesError' => $e->getMessage()]), 404, [
                            'Content-Type' => 'application/json'
                        ]);
                    }
                }

                throw new BadRequestHttpException('opponentId or text param is missing');
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        // get history messages
        $controllers->get('/messages/history/user/{id}/', function (Request $request, SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $service = MAILBOX_BOL_ConversationService::getInstance();
                $loggedUserId = $app['users']->getLoggedUserId();
                $beforeMessageId = (int) $request->query->get('beforeMessageId', -1);
                $limit = (int) $request->query->get('limit', self::MAX_MESSAGES_COUNT);

                // get a conversation id
                $conversationId = $service->getChatConversationIdWithUserById($loggedUserId, $id);

                if ($conversationId) {
                    $maxMessages = $limit <= 0 || $limit > self::MAX_MESSAGES_COUNT ? self::MAX_MESSAGES_COUNT : $limit;

                    return $app->json($this->mailboxService->
                            getHistoryMessages($loggedUserId, $conversationId, $beforeMessageId, $maxMessages));
                }

                throw new BadRequestHttpException('There is no conversation between users');
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        // get message
        $controllers->get('/messages/{id}/', function (SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $service = MAILBOX_BOL_ConversationService::getInstance();
                $message = $service->getMessage($id);
                $loggedUserId = $app['users']->getLoggedUserId();

                if (empty($message) || ($message->senderId != $loggedUserId && $message->recipientId != $loggedUserId)) {
                    throw new BadRequestHttpException('Message not found');
                }

                // track action
                if (!$message->wasAuthorized) {
                    $trackResult = BOL_AuthorizationService::getInstance()->
                            trackActionForUser($loggedUserId, 'mailbox', 'read_chat_message', ['checkInterval' => false]);


                    if (!$trackResult['status']) {
                        throw new BadRequestHttpException('Message cannot be tracked');
                    }
                }

                $message = $service->markMessageAuthorizedToRead($id);

                return $app->json($this->mailboxService->
                        getMessageData($loggedUserId, $message->conversationId, $message));
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        // get messages
        $controllers->get('/messages/user/{id}/', function (Request $request, SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $service = MAILBOX_BOL_ConversationService::getInstance();
                $limit = (int) $request->query->get('limit', self::MAX_MESSAGES_COUNT);

                // get a conversation id
                $conversationId = $service->getChatConversationIdWithUserById($loggedUserId, $id);

                if ($conversationId) {
                    $maxMessages = $limit <= 0 || $limit > self::MAX_MESSAGES_COUNT ? self::MAX_MESSAGES_COUNT : $limit;

                    return $app->json($this->
                            mailboxService->getMessages($loggedUserId, $conversationId, $maxMessages));
                }

                throw new BadRequestHttpException('There is no conversation between users');
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        // update conversation
        $controllers->put('/conversations/{id}/', function (Request $request, SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $vars = json_decode($request->getContent(), true);

                // extract the opponent id from conversation id
                list(, $opponentId) = explode('_', $id);

                if (isset($vars['isRead']) && $opponentId) {
                    $service = MAILBOX_BOL_ConversationService::getInstance();
                    $loggedUserId = $app['users']->getLoggedUserId();

                    // get a real conversation id
                    $conversationId = $service->getChatConversationIdWithUserById($loggedUserId, $opponentId);

                    if ($conversationId) {
                        $vars['isRead'] === true 
                            ? $service->markRead([$conversationId], $loggedUserId) 
                            : $service->markUnread([$conversationId], $loggedUserId);
                    }

                    return $app->json([], 204); // ok
                }

                throw new BadRequestHttpException('Param isRead is missing or opponent id is undefined');
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        // delete conversation
        $controllers->delete('/conversations/{id}/', function (SilexApplication $app, $id) {
            if ($this->isPluginActive) {
                $loggedUserId = $app['users']->getLoggedUserId();

                // extract the opponent id from conversation id
                list(, $opponentId) = explode('_', $id);               

                if ($opponentId) {
                    $service = MAILBOX_BOL_ConversationService::getInstance();

                    // get a real conversation id
                    $conversationId = $service->getChatConversationIdWithUserById($loggedUserId, $opponentId);

                    if ($conversationId) {
                        MAILBOX_BOL_ConversationService::getInstance()->deleteConversation([$conversationId], $loggedUserId);
                    }

                    return $app->json([], 204); // ok
                }

                throw new BadRequestHttpException('Opponent id is undefined');
            }

            throw new BadRequestHttpException('Mailbox plugin is not activated');
        });

        return $controllers;
    }
}

