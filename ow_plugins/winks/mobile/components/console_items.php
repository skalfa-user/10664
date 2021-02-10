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
 * Console friends section items component
 *
 * @author Kairat Bakytov <kainisoft@gmail.com>
 * @package ow.ow_plugins.winks.mobile.components
 * @since 1.7.6
 */
class WINKS_MCMP_ConsoleItems extends OW_MobileComponent
{
    private $service;

    public function __construct( $limit )
    {
        parent::__construct();

        $this->service = WINKS_BOL_Service::getInstance();

        $language = OW::getLanguage();
        $avatarService = BOL_AvatarService::getInstance();

        $userId = OW::getUser()->getId();
        $activeModes = json_decode(OW::getConfig()->getValue('mailbox', 'active_modes'));

        $winks = $this->service->findWinkList($userId, 0, $limit, $activeModes);
        $mode = is_array($activeModes) ? (in_array('chat', $activeModes)) ? 'chat' : 'mail' : 'chat';

        $viewedIds = $items = array();

        foreach ( $winks as $wink )
        {
            $item = array();

            $avatar = $avatarService->getDataForUserAvatars(array($wink->getUserId()), true, true, true, false);
            $avatar = $avatar[$wink->getUserId()];
            $item['avatar'] = $avatar;
            $item['viewed'] = $wink->getViewed();

            if ( $wink->getUserId() == $userId )
            {
                if ( $wink->getStatus() == WINKS_BOL_WinksDao::STATUS_ACCEPT && $wink->getWinkback() )
                {
                    $userService = BOL_UserService::getInstance();

                    $item['string'] = $language->text('winks', 'console_wink_accept_item', array(
                        'userUrl' => $userService->getUserUrl($wink->getPartnerId()),
                        'displayName' => $userService->getDisplayName($wink->getPartnerId())
                    ));

                    if ( $mode == 'mail' || $wink->messageType == 'mail' )
                    {
                        $item['url'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array(
                            'convId' => $wink->getConversationId()
                        ));
                    }
                    else
                    {
                        $item['url'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array(
                            'userId' => $wink->getPartnerId()
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
                $item['string'] = $language->text('winks', 'console_wink_wait_item', array(
                    'userUrl' => $avatar['url'],
                    'displayName' => $avatar['title']
                ));

                if ( $mode == 'mail' || $wink->messageType == 'mail' )
                {
                    $item['url'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array(
                        'convId' => $wink->getConversationId()
                    ));
                }
                else
                {
                    $item['url'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array(
                        'userId' => $wink->getUserId()
                    ));
                }
            }
            else
            {
                $item['string'] = OW::getLanguage()->text('winks', 'console_wink_wait_item', array(
                    'userUrl'=> $avatar['url'],
                    'displayName'=>$avatar['title']
                ));

                $item['toolbar'] = array(
                    'accept' => array(
                        'label' => $language->text('winks', 'accept_request'),
                        'onclick' => 'Winks.accept(' . $wink->getUserId() . ',' . $wink->getPartnerId() . ');',
                        'userId' => $wink->getUserId()
                    ),
                    'ignore' => array(
                        'label' => $language->text('winks', 'ignore_request'),
                        'onclick' => 'Winks.ignore(' . $wink->getUserId() . ',' . $wink->getPartnerId() . ');',
                        'userId' => $wink->getUserId()
                    )
                );
            }

            $items[] = $item;
            $viewedIds[] = $wink->getId();
        }

        $this->assign('items', $items);
        $this->service->markViewedByIds($viewedIds);
    }
}
