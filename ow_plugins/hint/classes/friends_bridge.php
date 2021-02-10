<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.classes
 */
class HINT_CLASS_FriendsBridge
{
    const PRIVACY_ACTION = "friends_view";
    
    /**
     * Class instance
     *
     * @var HINT_CLASS_FriendsBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_FriendsBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {

    }

    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive('friends');
    }

    // Next Methods were copied from FRIENDS_CTRL_Action and modified

    private function request( $userId )
    {
        $requesterId = OW::getUser()->getId();

        if ( BOL_UserService::getInstance()->isBlocked($requesterId, $userId) )
        {
            return false;
        }

        if( FRIENDS_BOL_Service::getInstance()->findFriendship($requesterId, $userId) === null )
        {
            FRIENDS_BOL_Service::getInstance()->request($requesterId, $userId);
            $this->onRequest($userId);
        }

        return true;
    }

    private function onRequest( $userId )
    {
        $requesterId = OW::getUser()->getId();

        $event = new OW_Event('friends.request-sent', array(
                'senderId' => $requesterId,
                'recipientId' => $userId,
                'time' => time()
        ));

        OW::getEventManager()->trigger($event);
    }

    private function onAccept( $userId, $requesterId, FRIENDS_BOL_Friendship $frendshipDto )
    {
        $se = BOL_UserService::getInstance();

        $names = $se->getDisplayNamesForList(array($requesterId, $userId));
        //$unames = $se->getUserNamesForList(array($requesterId, $userId));
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList(array($requesterId, $userId));
        $uUrls = $se->getUserUrlsForList(array($requesterId, $userId));

        //Add Newsfeed activity action
        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'friends',
            'entityType' => 'friend_add',
            'entityId' => $frendshipDto->id,
            'userId' => array($userId, $requesterId),
            'feedType' => 'user',
            'feedId' => $requesterId
        ), array(
            'line' => OW::getLanguage()->text('friends', 'activity_title', array(
                'user_url' => $uUrls[$userId],
                'name' => $names[$userId],
                'requester_url' => $uUrls[$requesterId],
                'requester_name' => $names[$requesterId]
            )),
            'content' => '<a href="' . $uUrls[$userId] . '"><img title="' . $names[$userId] . '" src="' . $avatars[$userId] . '" /></a>&nbsp
                <a href="' . $uUrls[$requesterId] . '"><img title="' . $names[$requesterId] . '" src="' . $avatars[$requesterId] . '" /></a>'
        ));
        OW::getEventManager()->trigger($event);


        //Send notification about accept of friendship request
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];

        $notificationParams = array(
            'pluginKey' => 'friends',
            'action' => 'friends-accept',
            'entityType' => 'friends-accept',
            'entityId' => $frendshipDto->id,
            'userId' => $requesterId,
            'time' => time()
        );

        $receiver = '<a href="'.$uUrls[$userId].'" target="_blank" >'.$names[$userId].'</a>';

        $notificationData = array(
            'string' => array(
                'key' => 'friends+notify_accept',
                'vars' => array(
                    'receiver' => $receiver
                )
            ),
            'avatar' => $avatar,
            'url' => $uUrls[$userId]
        );

        $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
        OW::getEventManager()->trigger($event);

        $event = new OW_Event('friends.request-accepted', array(
            'senderId' => $requesterId,
            'recipientId' => OW::getUser()->getId(),
            'time' => time()
        ));

        OW::getEventManager()->trigger($event);
    }


    public function accept( $requesterId )
    {
        $userId = OW::getUser()->getId();
        $frendshipDto = FRIENDS_BOL_Service::getInstance()->accept($userId, $requesterId);

        if (!empty($frendshipDto))
        {
            $this->onAccept($userId, $requesterId, $frendshipDto);
        }

        return true;
    }

    public function ignore( $userId )
    {
        $requesterId = OW::getUser()->getId();
        FRIENDS_BOL_Service::getInstance()->ignore($userId, $requesterId);
    }


    public function cancel( $requesterId )
    {
        $userId = OW::getUser()->getId();

        $event = new OW_Event('friends.cancelled', array(
            'senderId' => $requesterId,
            'recipientId' => $userId
        ));

        OW::getEventManager()->trigger($event);
    }

    public function activate( $requesterId )
    {
        $userId = OW::getUser()->getId();

        FRIENDS_BOL_Service::getInstance()->activate($userId, $requesterId);
    }


    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $userId = $params["entityId"];

        $uniqId = uniqid("hint-af-");

        if ( !OW::getUser()->isAuthenticated() || OW::getUser()->getId() == $userId || !OW::getUser()->isAuthorized('friends', 'add_friend') )
        {
            return;
        }

        $service = FRIENDS_BOL_Service::getInstance();

        $language = OW::getLanguage();

        $dto = $service->findFriendship($userId, OW::getUser()->getId());
        $js = UTIL_JsGenerator::newInstance();

        $blocked = BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId);

        $label = null;
        $command = "friends.add";

        if ( $dto === null )
        {
            if ( $blocked )
            {
                $js->jQueryEvent('#' . $uniqId, 'click', 'OW.error(e.data.msg); return false;', array('e'), array(
                    "msg" => $language->text('base', 'user_block_message')
                ));
            }

            $label = $language->text('hint', 'button_friends_add_label');
        }
        else
        {
            switch ( $dto->getStatus() )
            {
                case FRIENDS_BOL_Service::STATUS_ACTIVE:
                    $label = $language->text('hint', 'button_friends_remove_label');
                    $command = "friends.cancel";
                    break;

                case FRIENDS_BOL_Service::STATUS_PENDING:

                    if ( $dto->getUserId() == OW::getUser()->getId() )
                    {
                        //$label = $language->text('friends', 'remove_from_friends');
                        $label = $language->text('hint', 'button_friends_remove_label');
                        $command = "friends.cancel";
                    }
                    else
                    {
                        //$label = $language->text('friends', 'add_to_friends');
                        $label = $language->text('hint', 'button_friends_add_label');
                        $command = "friends.accept";
                    }
                    break;

                case FRIENDS_BOL_Service::STATUS_IGNORED:

                    if ( $dto->getUserId() == OW::getUser()->getId() )
                    {
                        $label = $language->text('hint', 'button_friends_remove_label');
                        $command = "friends.cancel";
                    }
                    else
                    {
                        $label = $language->text('hint', 'button_friends_add_label');
                        $command = "friends.activate";
                    }
            }
        }

        if ( !$blocked )
        {
            $js->jQueryEvent('#' . $uniqId, 'click', '
                var self = $(this), command = self.data("command");
                HINT.UTILS.toggleText(this, e.data.l1, e.data.l2);
                self.data("command", command == "friends.cancel" ? "friends.add" : "friends.cancel");
                HINT.UTILS.query(command, e.data.params); return false;',
            array('e'),
            array(
                "l1" => $language->text('hint', 'button_friends_add_label'),
                "l2" => $language->text('hint', 'button_friends_remove_label'),
                "params" => array(
                    "userId" => $userId
                )
            ));
        }

        OW::getDocument()->addOnloadScript($js);

        $button = array(
            "key" => "friends",
            "label" => $label,
            "attrs" => array("id" => $uniqId, "data-command" => $command),
        );

        $event->add($button);

    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        //$label = OW::getLanguage()->text("friends", "add_to_friends");
        $label =  OW::getLanguage()->text('hint', 'button_friends_add_label');

        $button = array(
            "key" => "friends",
            "label" => $label,
            "attrs" => array(
                "href" => "javascript://",
                "class" => "hint-friends"
            )
        );

        $event->add($button);
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text("friends", "add_to_friends");
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "friends");

        $button = array(
            "key" => "friends",
            "active" => $active === null ? true : $active,
            "label" => $label
        );

        $event->add($button);
    }

    public function onHintRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
    }

    public function onQuery( OW_Event $event )
    {
        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('friends', 'add_friend') )
        {
            return;
        }

        $params = $event->getParams();

        if ( !in_array($params["command"], array("friends.cancel", "friends.activate", "friends.accept", "friends.add")) )
        {
            return;
        }

        $userId = $params["params"]['userId'];

        $info = null;
        $error = null;

        switch ( $params["command"] )
        {
            case "friends.cancel":
                $this->cancel($userId);
                $info = OW::getLanguage()->text('friends', 'feedback_cancelled');

                break;

            case "friends.activate":
                $this->activate($userId);
                $info = OW::getLanguage()->text('friends', 'new_friend_added');

                break;

            case "friends.accept":
                $this->accept($userId);
                $info = OW::getLanguage()->text('friends', 'feedback_request_accepted');

                break;

            case "friends.add":
                $this->request($userId);
                $info = OW::getLanguage()->text('friends', 'feedback_request_was_sent');

                break;

        }

        $event->setData(array(
            "info" => $info,
            "error" => $error
        ));
    }
    
    
    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
        
        if ( $params["line"] != HINT_BOL_Service::INFO_LINE0 )
        {
            $event->add(array(
                "key" => "friends-list",
                "label" => OW::getLanguage()->text("hint", "info_friend_list_label")
            ));
        }
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
        
        if ( $params["key"] != "friends-list" )
        {
            return;
        }
        
        $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";
        
        $data = array();
        
        for ( $i = 0; $i < 6; $i++ )
        {
            $data[] = array(
                "src" => $staticUrl . "user_" . $i . ".jpg",
                "url" => "javascript://"
            );
        }
        
        $users = new HINT_CMP_UserList($data);
        
        $event->setData($users->render());
    }
    
    public function onInfoRender( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
        
        $entityType = $params["entityType"];
        $entityId = $params["entityId"];
        
        if ( $params["key"] != "friends-list" )
        {
            return;
        }
        
        if ( !$this->checkPrivacy($entityId) )
        {
            return;
        }
        
        $friendIds = OW::getEventManager()->call("plugin.friends.get_friend_list", array(
            "userId" => $entityId,
            "count" => 500
        ));
        
        if ( empty($friendIds) )
        {
            return;
        }
        
        $userName = BOL_UserService::getInstance()->getDisplayName($entityId);
        
        $data = BOL_AvatarService::getInstance()->getDataForUserAvatars(array_slice($friendIds, 0, 6), true, true, false, false);
        $users = new HINT_CMP_UserList($data, $friendIds, OW::getLanguage()->text("hint", "friend_list_fb_title", array("user" => $userName)));
        
        $event->setData($users->render());
    }
    
    public function checkPrivacy( $userId )
    {
        $eventParams = array(
            'action' => self::PRIVACY_ACTION,
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return false;
        }

        return true;
    }
    

    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }
        
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array($this, 'onCollectInfoConfigs'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_PREVIEW, array($this, 'onInfoPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_RENDER, array($this, 'onInfoRender'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}