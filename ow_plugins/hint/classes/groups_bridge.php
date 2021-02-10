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
class HINT_CLASS_GroupsBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_GroupsBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_GroupsBridge
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
        return OW::getPluginManager()->isPluginActive("groups");
    }
        
    /**
     * 
     * @param int $groupId
     * @return GROUPS_BOL_Group
     */
    public function getGroupById( $groupId )
    {
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        
        if ( $group === null )
        {
            return null;
        }
        
        $out = array();
        
        $out["id"] = $group->id;
        $out["timeStamp"] = $group->timeStamp;
        $out["userId"] = $group->userId;
        $out["title"] = $group->title;
        $out["description"] = $group->description;
        $out["status"] = empty($group->status) ? "active" : $group->status;
        $out["whoCanView"] = $group->whoCanView;
        
        $out["url"] = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $out["avatar"] = GROUPS_BOL_Service::getInstance()
                ->getGroupImageUrl($group, GROUPS_BOL_Service::IMAGE_SIZE_SMALL);
        
        return $out;
    }
    
    
    
    public function isCurrentUserCanInvite( $groupId )
    {
        return GROUPS_BOL_Service::getInstance()->isCurrentUserInvite($groupId);
    }
    
    public function isGroupUser( $userId, $groupId )
    {
        return GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId) !== null;
    }
    
    public function isInvitedUser( $userId, $groupId )
    {
        return GROUPS_BOL_Service::getInstance()->findInvite($groupId, $userId) !== null;
    }
    
    public function markInviteViewed( $groupId, $userId, $inviterId = null )
    {
        GROUPS_BOL_Service::getInstance()->markInviteAsViewed($groupId, $userId, $inviterId);
    }
    
    public function addUser( $groupId, $userId )
    {
        GROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);
    }
    
    public function deleteUser( $groupId, $userId )
    {
        GROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userId);
    }
        
    public function getUserIds( $groupId, $count )
    {
        $users = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId);
                
        return array_slice($users, 0, $count);
    }
    
    public function getUsersForInvite( $groupId, $userId )
    {
        $users = null;

        if ( OW::getEventManager()->call('plugin.friends') )
        {
            $users = OW::getEventManager()->call('plugin.friends.get_friend_list', array(
                'userId' => $userId,
                'count' => 100
            ));
        }

        if ( $users === null )
        {
            $users = array();
            $userDtos = BOL_UserService::getInstance()->findRecentlyActiveList(0, 100);

            foreach ( $userDtos as $u )
            {
                if ( $u->id != $userId )
                {
                    $users[] = $u->id;
                }
            }
        }

        $idList = array();

        if ( !empty($users) )
        {
            $groupUsers = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId);
            $invitedList = GROUPS_BOL_Service::getInstance()->findInvitedUserIdList($groupId, $userId);

            foreach ( $users as $uid )
            {
                if ( in_array($uid, $groupUsers) || in_array($uid, $invitedList) )
                {
                    continue;
                }

                $idList[] = $uid;
            }
        }
        
        return $users;
    }


    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_GROUP )
        {
            return;
        }

        $language = OW::getLanguage();
        
        $groupId = $params["entityId"];
        $groupInfo = $this->getGroupById($groupId);
        
        if ( empty($groupInfo) )
        {
            return;
        }
        
        // View Group button
        $attrs = array(
            "href" => $groupInfo["url"]
        );

        $openInNewWindow = HINT_BOL_Service::getInstance()->getActionOption(
            HINT_BOL_Service::ENTITY_TYPE_GROUP,
            "group-view",
            "newWindow"
        );

        if ($openInNewWindow) {
            $attrs["target"] = "_blank";
        }

        $event->add(array(
            "key" => "group-view",
            "label" => $language->text("hint", "button_view_group_label"),
            "attrs" => $attrs
        ));
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        
        $isCreator = $groupInfo["userId"] == OW::getUser()->getId();
        $js = new UTIL_JsGenerator();
        
        $isGroupUser = $this->isGroupUser(OW::getUser()->getId(), $groupId);
        
        $hintOptions = array(
            "groupId" => $groupId,
            "userId" => OW::getUser()->getId(),
            "entityType" => "group",
            "gheader" => false,
            "isGroupUser" => $isGroupUser,
            "rsp" => array(
                "invite" => OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'invite'),
                "follow" => OW::getRouter()->urlFor('HINT_CTRL_Common', 'groupFollow'),
                "join" => OW::getRouter()->urlFor('HINT_CTRL_Common', 'groupJoin')
            ),
            "text" => array(
                "inviteTitle" => $language->text("hint", "invite_users_title"),
                "follow" => $language->text('groups', 'feed_group_follow'),
                "unfollow" => $language->text('groups', 'feed_group_unfollow'),
                "join" => $language->text("hint", "group_join_btn_label"),
                "leave" => $language->text("hint", "group_leave_btn_label")
            )
        );
        $js->newObject("groupHint", "HINT.GroupHint", array(
            $hintOptions
        ));
                
        // Flag Group button
        
        if ( !$isCreator && $groupInfo["status"] == "active" )
        {
            $flagId = uniqid("flag-");
            $event->add(array(
                "key" => "group-flag",
                "label" => $language->text("base", "flag"),
                "attrs" => array(
                    "id" => $flagId,
                    "href" => "javascript://"
                )
            ));
            
            $js->jQueryEvent("#" . $flagId, "click", "groupHint.flag();");
        }
        
        
        // Join Group button
        
        if ( !($isCreator && $isGroupUser) )
        {
            $joinId = uniqid("join-");
            $event->add(array(
                "key" => "group-join",
                "label" => $isGroupUser 
                    ? $language->text("hint", "group_leave_btn_label")
                    : $language->text("hint", "group_join_btn_label"),
                "attrs" => array(
                    "id" => $joinId,
                    "href" => "javascript://"
                )
            ));

            $js->addScript('groupHint.setOptions({$options});', array(
                "options" => array(
                    "joinBtnId" => $joinId
                )
            ));

            $js->jQueryEvent("#" . $joinId, "click", "groupHint.toggleJoin();");
        }
        
        
        
        // Invite to Group button
        
        if ( $groupInfo["status"] == "active" )
        {
            $inviteId = uniqid("invite-");
            $event->add(array(
                "key" => "group-invite",
                "label" => $language->text("groups", "invite_btn_label"),
                "attrs" => array(
                    "id" => $inviteId,
                    "href" => "javascript://",
                    "style" => !$this->isCurrentUserCanInvite($groupId) ? "display: none;" : ""
                )
            ));
            
            $js->addScript('groupHint.setOptions({$options});', array(
                "options" => array(
                    "inviteBtnId" => $inviteId
                )
            ));

            if ( HINT_CLASS_GheaderBridge::getInstance()->isActive() 
                    && HINT_CLASS_GheaderBridge::getInstance()->hasInviter() )
            {
                HINT_CLASS_GheaderBridge::getInstance()->addStatic();
                
                $js->addScript('groupHint.setOptions({$options});', array(
                    "options" => array(
                        "gheader" => true
                    )
                ));
            }
            else
            {
                $users = $this->getUsersForInvite($groupId, OW::getUser()->getId());
                $js->addScript('groupHint.setOptions({$options});', array(
                    "options" => array(
                        "inviteUsers" => $users
                    )
                ));
            }
            
            $js->jQueryEvent("#" . $inviteId, "click", "groupHint.invite();");
        }
        
        // Follow Group
        
        if ( OW::getEventManager()->call('feed.is_inited') )
        {
            $followId = uniqid("follow-");
            $followBtn = array(
                "key" => "group-follow",
                "attrs" => array(
                    "id" => $followId,
                    "href" => "javascript://"
                )
            );
            
            $followed = OW::getEventManager()->call('feed.is_follow', array(
                'userId' => OW::getUser()->getId(),
                'feedType' => "groups",
                'feedId' => $groupId
            ));
            
            $js->addScript('groupHint.setOptions({$options});', array(
                "options" => array(
                    "followed" => $followed,
                    "followBtnId" => $followId
                )
            ));
            
            $followBtn["label"] = $followed 
                    ? $language->text('groups', 'feed_group_unfollow')
                    : $language->text('groups', 'feed_group_follow');
            
            $event->add($followBtn);
            
            $js->jQueryEvent("#" . $followId, "click", "groupHint.toggleFollow();");
        }
        
        $jsStr = $js->generateJs();
        if ( trim($jsStr) )
        {
            OW::getDocument()->addOnloadScript($js);
        }
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_GROUP )
        {
            return;
        }

        $language = OW::getLanguage();

        // Group Flag
        
        $event->add(array(
            "key" => "group-flag",
            "label" => $language->text("base", "flag"),
            "attrs" => array("href" => "javascript://")
        ));
        
        // Group View
        
        $event->add(array(
            "key" => "group-view",
            "label" => $language->text("hint", "button_view_group_label"),
            "attrs" => array("href" => "javascript://")
        ));
        
        // Group Follow
        
        $event->add(array(
            "key" => "group-follow",
            "label" => $language->text("groups", "feed_group_follow"),
            "attrs" => array("href" => "javascript://")
        ));
        
        // Event Invite
        
        $event->add(array(
            "key" => "group-invite",
            "label" => $language->text("groups", "invite_btn_label"),
            "attrs" => array("href" => "javascript://")
        ));
        
        
        // Group Join
        
        $event->add(array(
            "key" => "group-join",
            "label" => $language->text("groups", "widget_join_button"),
            "attrs" => array("href" => "javascript://")
        ));
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_GROUP )
        {
            return;
        }

        $language = OW::getLanguage();
        $service = HINT_BOL_Service::getInstance();
        
        // Invite to Group
        
        $inviteGroup = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_GROUP, "group-invite");
        $event->add(array(
            "key" => "group-invite",
            "active" => $inviteGroup === null ? true : $inviteGroup,
            "label" => $language->text("groups", "invite_btn_label")
        ));
        
        // View Group
        
        $viewGroup = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_GROUP, "group-view");

        $event->add(array(
            "key" => "group-view",
            "active" => $viewGroup === null ? true : $viewGroup,
            "label" => $language->text("hint", "button_view_group_config"),
            "options" => array(
                array(
                    "key" => "newWindow",
                    "active" => $service->getActionOption(HINT_BOL_Service::ENTITY_TYPE_GROUP, "group-view", "newWindow"),
                    "label" => OW::getLanguage()->text("hint", "button_view_group_option_new_window")
                )
            )
        ));
        
        // Flag Group
        
        $flagGroup = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_GROUP, "group-flag");
        $event->add(array(
            "key" => "group-flag",
            "active" => $flagGroup === null ? true : $flagGroup,
            "label" => $language->text("base", "flag")
        ));
        
        // Follow Group
        
        $followGroup = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_GROUP, "group-follow");
        $event->add(array(
            "key" => "group-follow",
            "active" => $followGroup === null ? true : $followGroup,
            "label" => $language->text("hint", "button_follow_group_config")
        ));
        
        // Join Group
        
        $joinGroup = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_GROUP, "group-join");
        $event->add(array(
            "key" => "group-join",
            "active" => $joinGroup === null ? true : $joinGroup,
            "label" => $language->text("hint", "button_join_group_config")
        ));
    }
    
    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_GROUP )
        {
            return;
        }
        
        $event->add(array(
            "key" => "group-admin",
            "label" => $language->text("hint", "info_group_admin_label")
        ));
        
        $event->add(array(
            "key" => "group-access",
            "label" => $language->text("hint", "info_group_access_label")
        ));
        
        $event->add(array(
            "key" => "group-access-creator",
            "label" => $language->text("hint", "info_group_access_and_creator_label")
        ));
        
        $event->add(array(
            "key" => "group-created-date",
            "label" => $language->text("hint", "info_group_created_date_label")
        ));
        
        $event->add(array(
            "key" => "group-created-admin",
            "label" => $language->text("hint", "info_group_created_admin_label")
        ));
        
        if ( $params["line"] != HINT_BOL_Service::INFO_LINE0 )
        {
            $event->add(array(
                "key" => "group-desc",
                "label" => $language->text("hint", "info_group_desc_label")
            ));
            
            $event->add(array(
                "key" => "group-users",
                "label" => $language->text("hint", "info_group_users_label")
            ));
        }
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_GROUP )
        {
            return;
        }
        
        $userEmbed = '<a href="javascript://">Angela Smith</a>';
        $createDate = UTIL_DateTime::formatDate(strtotime("11/10/15"));
        
        switch ( $params["key"] )
        {
            case "group-access-creator":
                
                $event->setData($language->text("hint", "group_info_access_and_creator", array(
                    "user" => $userEmbed,
                    "accessibility" => $language->text("hint", "group_info_access_by_invitation")
                )));
                break;
            
            case "group-access":
                
                $event->setData($language->text("hint", "group_info_access_by_invitation"));
                break;
            
            case "group-admin":
                
                $event->setData($language->text("hint", "group_info_admin", array(
                    "user" => $userEmbed
                )));
                break;

            case "group-created-date":
                                
                $event->setData($language->text("hint", "info_group_created_date", array(
                    "date" => $createDate
                )));
                break;
            
            case "group-created-admin":
                                
                $event->setData($language->text("hint", "info_group_created_admin", array(
                    "date" => $createDate,
                    "user" => $userEmbed
                )));
                break;
                        
            case "group-users":
                $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";
        
                $data = array();

                for ( $i = 0; $i < 6; $i++ )
                {
                    $data[] = array(
                        "src" => $staticUrl . "user_" . $i . ".jpg",
                        "url" => "javascript://"
                    );
                }

                $users = new HINT_CMP_UserList($data, array(), null);

                $event->setData($users->render());
                break;
            
            case "group-desc":
                $description = UTIL_String::truncate($language->text("hint", "info_group_desc_preview"), 125, "...");
                $event->setData('<span class="ow_remark ow_small">' . $description . '</span>');
                break;
        }
    }
    
    public function onInfoRender( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_GROUP )
        {
            return;
        }
        
        $groupId = $params["entityId"];
        $groupinfo = $this->getGroupById($groupId);
        
        if ( empty($groupinfo) )
        {
            return;
        }
        
        $language = OW::getLanguage();
                
        $userName = BOL_UserService::getInstance()->getDisplayName($groupinfo["userId"]);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($groupinfo["userId"]);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';
        
        $createDate = UTIL_DateTime::formatDate($groupinfo["timeStamp"]);
        
        $access = $groupinfo["whoCanView"] == "anyone"
                    ? $language->text("hint", "group_info_access_public")
                    : $language->text("hint", "group_info_access_by_invitation");
        
        switch ( $params["key"] )
        {
            case "group-access-creator":
                $event->setData($language->text("hint", "group_info_access_and_creator", array(
                    "user" => $userEmbed,
                    "accessibility" => $access
                )));
                break;
            
            case "group-access":
                
                $event->setData($access);
                break;
            
            case "group-admin":
                
                $event->setData($language->text("hint", "group_info_admin", array(
                    "user" => $userEmbed
                )));
                break;
            
            case "group-created-date":
                                
                $event->setData($language->text("hint", "info_group_created_date", array(
                    "date" => $createDate
                )));
                break;
            
            case "group-created-admin":
                                
                $event->setData($language->text("hint", "info_group_created_admin", array(
                    "date" => $createDate,
                    "user" => $userEmbed
                )));
                break;
            
            case "group-users":
                $userIds = $this->getUserIds($groupId, 200);

                if ( empty($userIds) )
                {
                    return;
                }
                
                $title = OW::getLanguage()->text("hint", "group_users_list_title");
                $data = BOL_AvatarService::getInstance()->getDataForUserAvatars(array_slice($userIds, 0, 6), true, true, false, false);
                $users = new HINT_CMP_UserList($data, $userIds, $title, 6);

                $event->setData($users->render());
                break;
            
            case "group-desc":
                $description = UTIL_String::truncate(strip_tags($groupinfo["description"]), 110, "...");
                $event->setData('<span class="ow_remark ow_small">' . $description . '</span>');
                break;
        }
    }
    
    public function prepareParsers()
    {
        HINT_CLASS_ParseManager::getInstance()->addParser(new HINT_CLASS_GroupParser());
    }

    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }
        
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'prepareParsers'));
        
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));
        
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array($this, 'onCollectInfoConfigs'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_PREVIEW, array($this, 'onInfoPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_RENDER, array($this, 'onInfoRender'));
    }
}