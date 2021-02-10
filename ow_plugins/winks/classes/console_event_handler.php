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
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.winks.classes
 * @since 1.0
 */
class WINKS_CLASS_ConsoleEventHandler
{
    CONST CONSOLE_ITEM_KEY = 'wink_requests';

    private static $classInstance;

    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $service;

    private function __construct()
    {
        $this->service = WINKS_BOL_Service::getInstance();
    }

    public function init()
    {
        $eventManager = OW::getEventManager();

        $eventManager->bind('console.collect_items', array($this, 'collectItems'));
        $eventManager->bind('console.ping', array($this, 'ping'));
        $eventManager->bind('console.load_list', array($this, 'loadList'));
    }

    public function collectItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $item = new WINKS_CMP_ConsoleWinkRequests();
            $userId = OW::getUser()->getId();
            $activeModes = $this->service->getActiveModes();

            if ( $this->service->countWinksForUser($userId, array(WINKS_BOL_WinksDao::STATUS_ACCEPT, WINKS_BOL_WinksDao::STATUS_WAIT), null, $activeModes) === 0 && $this->service->countWinkBackedByUserId($userId, $activeModes) === 0 )
            {
                $item->setIsHidden(true);
            }

            $event->addItem($item, 5);
        }
    }

    public function ping( BASE_CLASS_ConsoleDataEvent $event )
    {
        $userId = OW::getUser()->getId();
        $activeModes = $this->service->getActiveModes();

        $event->setItemData(self::CONSOLE_ITEM_KEY, array('counter' => array(
            'all' => $this->service->countWinksForUser($userId, array(WINKS_BOL_WinksDao::STATUS_WAIT), null, $activeModes),
            'new' => $this->service->countWinksForUser($userId, array(WINKS_BOL_WinksDao::STATUS_WAIT), 0, $activeModes)
        )));
    }

    public function loadList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $userId = OW::getUser()->getId();

        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;
        }

        $activeModes = json_decode(OW::getConfig()->getValue('mailbox', 'active_modes'));

        $winks = $this->service->findWinkList($userId, $params['offset'], 10, $activeModes);

        $viewedIds = array();
        $language = OW::getLanguage();

        $language->addKeyForJs('winks', 'msg_accept_request');
        $language->addKeyForJs('winks', 'msg_ignore_request');
        $mode = is_array($activeModes) ? (in_array('chat', $activeModes)) ? 'chat' : 'mail' : 'chat';

        foreach ( $winks as $wink )
        {
            $viewedIds[] = $wink->getId();

            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($wink->getUserId()), true, true, true, false);

            $item = new BASE_CMP_ConsoleListIpcItem();
            $item->setAvatar($avatar[$wink->getUserId()]);
            $item->setKey('wink-item-' . $wink->getId());

            $userUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>BOL_UserService::getInstance()->getUserName($wink->getUserId())));
            $displayName = BOL_UserService::getInstance()->getDisplayName($wink->getUserId());
            //OW::getRouter()->addRoute(new OW_Route('mailbox_conversation', 'messages/mail/:convId', 'MAILBOX_CTRL_Messages', 'index'));
            if ( $wink->getUserId() == $userId )
            {
                if ( $wink->getStatus() == WINKS_BOL_WinksDao::STATUS_ACCEPT && $wink->getWinkback() )
                {
                    $string = $language->text('winks', 'console_wink_accept_item', array(
                        'userUrl' => OW::getRouter()->urlForRoute('base_user_profile', array('username'=>BOL_UserService::getInstance()->getUserName($wink->getPartnerId()))),
                        'displayName' => BOL_UserService::getInstance()->getDisplayName($wink->getPartnerId()))
                    );

                  if ( $mode == 'mail' || $wink->messageType == 'mail' )
                  {
                        $item->setToolbar(array(
                            array(
                                'label' => $language->text('winks', 'send_message'),
                                'url' => OW::getRouter()->urlForRoute('mailbox_conversation', array('convId'=>$wink->getConversationId()))
                            )
                        ));
                  }
                  else
                  {
                      $item->setToolbar(array(
                            array(
                                'label' => $language->text('winks', 'send_message'),
                                'onclick' => 'OW.trigger(\'mailbox.open_dialog\',{convId:' . $wink->getConversationId() . ',opponentId:' . $wink->getPartnerId() . ',mode:\'' . $mode . '\'});return false;'
                            )
                        ));
                  }
                }
                else
                {
                    continue;
                }
            }
            elseif ( $wink->getStatus() == WINKS_BOL_WinksDao::STATUS_ACCEPT )
            {
                $string = $language->text('winks', 'console_wink_wait_item', array('userUrl' => $userUrl, 'displayName' => $displayName));

                if ( $mode == 'mail' || $wink->messageType == 'mail' )
                {
                    $item->setToolbar(array(
                        array(
                            'label' => $language->text('winks', 'send_message'),
                            'url' => OW::getRouter()->urlForRoute('mailbox_conversation', array('convId'=>$wink->getConversationId()))
                        )
                    ));
                }
                else
                {
                    $item->setToolbar(array(
                        array(
                            'label' => $language->text('winks', 'send_message'),
                            'onclick' => 'OW.trigger(\'mailbox.open_dialog\',{convId:' . $wink->getConversationId() . ',opponentId:' . $wink->getUserId() . ',mode:\'' . $mode . '\'});return false;'
                        )
                    ));
                }
            }
            else
            {
                $string = OW::getLanguage()->text('winks', 'console_wink_wait_item', array('userUrl'=> $userUrl, 'displayName'=>$displayName));
                $item->setToolbar(array(
                    array(
                        'label' => $language->text('winks', 'accept_request'),
                        'onclick' => 'Winks.accept(\'' . $item->getKey() . '\',' . $wink->getUserId() . ',' . $wink->getPartnerId() . ');'
                    ),
                    array(
                        'label' => $language->text('winks', 'ignore_request'),
                        'onclick' => 'Winks.ignore(\'' . $item->getKey() . '\',' . $wink->getUserId() . ',' . $wink->getPartnerId() . ');'
                    ),
                    array(
                        'label' => $language->text('winks', 'send_message'),
                        'class' => 'ow_hidden',
                        'id' => 'send-message-' . $item->getKey()
                    )
                ));
            }

            $item->setContent($string);

            if ( $wink->getViewed() === 0 )
            {
                $item->addClass('ow_console_new_message');
            }

            $event->addItem($item->render());
        }

        $this->service->markViewedByIds($viewedIds);
    }
}
