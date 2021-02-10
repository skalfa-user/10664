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
class HINT_CLASS_EventsBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_EventsBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_EventsBridge
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
        return OW::getPluginManager()->isPluginActive("event");
    }
    
    /**
     * 
     * @param int $groupId
     * @return GROUPS_BOL_Group
     */
    public function getEventById( $eventId )
    {
        $eventDto = EVENT_BOL_EventService::getInstance()->findEvent($eventId);
        
        if ( $eventDto === null )
        {
            return null;
        }
        
        $out = array();
        
        $out["id"] = $eventDto->id;
        $out["startTimeStamp"] = $eventDto->startTimeStamp;
        $out["endTimeStamp"] = $eventDto->endTimeStamp;
        $out["timeStamp"] = $eventDto->createTimeStamp;
        
        $out["accessibility"] = $eventDto->whoCanView;
        $out["whoCanInvite"] = $eventDto->whoCanInvite;
        
        $out["status"] = !isset($eventDto->status) ? 1 : $eventDto->status;
        
        $out["userId"] = $eventDto->userId;
        $out["location"] = $eventDto->location;
        $out["title"] = $eventDto->title;
        $out["description"] = $eventDto->description;
        $out["url"] = OW::getRouter()->urlForRoute('event.view', array(
            'eventId' => $eventDto->id
        ));
        
        $out["avatar"] = !empty($eventDto->image)
                ? EVENT_BOL_EventService::getInstance()->generateImageUrl($eventDto->image, true) 
                : EVENT_BOL_EventService::getInstance()->generateDefaultImageUrl();
        
        return $out;
    }
    
    public function getUserIds( $eventId, $status, $count )
    {
        $users = EVENT_BOL_EventService::getInstance()->findEventUsers($eventId, $status, null, $count);
        
        $out = array();
        foreach ( $users as $user )
        {
            $out[] = $user->userId;
        }
        
        return $out;
    }
    
    public function isEventUser( $userId, $eventId )
    {
        $user = EVENT_BOL_EventService::getInstance()->findEventUser($eventId, $userId);
        
        return !empty($user);
    }
    
    public function getUserStatus( $eventId, $userId )
    {
        $user = EVENT_BOL_EventService::getInstance()->findEventUser($eventId, $userId);
        
        if ( empty($user) )
        {
            return null;
        }
        
        return $user->status;
    }
    
    public function hasContentProvider()
    {
        if ( !$this->isActive() ) return false;
        
        return OW::getPluginManager()->getPlugin("event")->getDto()->build >= 8520;
    }
        
    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }

        $language = OW::getLanguage();
        
        $eventId = $params["entityId"];
        $eventInfo = $this->getEventById($eventId);
        
        if ( empty($eventInfo) )
        {
            return;
        }
        
        // View Event button
        $attrs = array(
            "href" => $eventInfo["url"]
        );

        $openInNewWindow = HINT_BOL_Service::getInstance()->getActionOption(
            HINT_BOL_Service::ENTITY_TYPE_EVENT,
            "event-view",
            "newWindow"
        );

        if ($openInNewWindow) {
            $attrs["target"] = "_blank";
        }

        $event->add(array(
            "key" => "event-view",
            "label" => $language->text("hint", "button_view_event_label"),
            "attrs" => $attrs
        ));
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        $isCreator = $eventInfo["userId"] == OW::getUser()->getId();
        $js = new UTIL_JsGenerator();
        
        // Flag Event button
        
        if ( !$isCreator && $this->hasContentProvider() )
        {
            $flagId = uniqid("flag-");
            $event->add(array(
                "key" => "event-flag",
                "label" => $language->text("base", "flag"),
                "attrs" => array(
                    "id" => $flagId,
                    "href" => "javascript://"
                )
            ));
            
            $js->addScript('$("#' . $flagId . '").click(function() { OW.flagContent("event", {$eventId}) });', array(
                "eventId" => $eventId
            ));
        }
        
        
        // Invite Event button
        
        $canInvite = $eventInfo["whoCanInvite"] == 1 && $isCreator
                || $eventInfo["whoCanInvite"] == 2;
        
        $inviteId = uniqid("invite-");
        
        if ( $canInvite && $eventInfo["status"] == 1 )
        {
            $isEventUser = $this->isEventUser(OW::getUser()->getId(), $eventId);
            
            $event->add(array(
                "key" => "event-invite",
                "label" => $language->text("event", "invite_btn_label"),
                "attrs" => array(
                    "id" => $inviteId,
                    "href" => "javascript://",
                    "style" => !$isEventUser ? "display: none;" : ""
                )
            ));

            $options = array(
                "inviteRsp" => OW::getRouter()->urlFor("EVENT_CTRL_Base", "inviteResponder"),
                "eventId" => $eventId,
                "title" => $language->text("hint", "invite_users_title"),
                "eheader" => false,
                "for" => "event"
            );

            if ( HINT_CLASS_EheaderBridge::getInstance()->isActive() )
            {
                HINT_CLASS_EheaderBridge::getInstance()->addStatic();
                $options["eheader"] = true;
            }

            $js->newObject("inviter", "HINT.Inviter", array($options));
            $js->jQueryEvent("#" . $inviteId, "click", "inviter.show(); return false;");
        }
                
        // Attend Event button
        $attendId = uniqid("attend-");
        
        $attendContent = new HINT_CMP_EventAttendContext($eventId, OW::getUser()->getId(), $attendId, $inviteId);
        $contextBtn = new HINT_CMP_ContextButton($attendContent->current["label"], $attendContent->render());
        $contextBtn->setId($attendId);
        $event->add(array(
            "key" => "event-attend",
            "html" => '<li id="event-attend">' . $contextBtn->render() . '</li>'
        ));
        
        $jsStr = $js->generateJs();
        if ( trim($jsStr) )
        {
            OW::getDocument()->addOnloadScript($js);
        }
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }

        $language = OW::getLanguage();

        
        // Event Flag
        
        if ( $this->hasContentProvider() )
        {
            $event->add(array(
                "key" => "event-flag",
                "label" => $language->text("base", "flag"),
                "attrs" => array("href" => "javascript://")
            ));
        }
        
        // Event View
        
        $event->add(array(
            "key" => "event-view",
            "label" => $language->text("hint", "button_view_event_label"),
            "attrs" => array("href" => "javascript://")
        ));
        
        // Event Invite
        
        $event->add(array(
            "key" => "event-invite",
            "label" => $language->text("event", "invite_btn_label"),
            "attrs" => array("href" => "javascript://")
        ));
        
        // Event Attend
        
        $contextBtn = new HINT_CMP_ContextButton($language->text("hint", "button_attend_event_label"));
        
        $event->add(array(
            "key" => "event-attend",
            "html" => '<li id="event-attend" class="h-preview">' . $contextBtn->render() . '</li>'
        ));
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }

        $language = OW::getLanguage();
        $service = HINT_BOL_Service::getInstance();
        
        // Attend Event
        
        $attendEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-attend");
        $event->add(array(
            "key" => "event-attend",
            "active" => $attendEvent === null ? true : $attendEvent,
            "label" => $language->text("hint", "button_attend_event_config")
        ));
        
        // Invite Event
        
        $inviteEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-invite");
        $event->add(array(
            "key" => "event-invite",
            "active" => $inviteEvent === null ? true : $inviteEvent,
            "label" => $language->text("event", "invite_btn_label")
        ));
        
        // View Event
        
        $viewEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-view");
        $event->add(array(
            "key" => "event-view",
            "active" => $viewEvent === null ? true : $viewEvent,
            "label" => $language->text("hint", "button_view_event_config"),
            "options" => array(
                array(
                    "key" => "newWindow",
                    "active" => $service->getActionOption(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-view", "newWindow"),
                    "label" => OW::getLanguage()->text("hint", "button_view_event_option_new_window")
                )
            )
        ));
        
        // Flag Event
        if ( $this->hasContentProvider() )
        {
            $flagEvent = $service->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "event-flag");
            $event->add(array(
                "key" => "event-flag",
                "active" => $flagEvent === null ? true : $flagEvent,
                "label" => $language->text("base", "flag")
            ));
        }
    }
    
    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }
        
        $event->add(array(
            "key" => "event-date",
            "label" => $language->text("hint", "info_event_date_label")
        ));
        
        $event->add(array(
            "key" => "event-start-date",
            "label" => $language->text("hint", "info_event_start_date_label")
        ));
        
        $event->add(array(
            "key" => "event-end-date",
            "label" => $language->text("hint", "info_event_end_date_label")
        ));
        
        $event->add(array(
            "key" => "event-created-by",
            "label" => $language->text("hint", "info_event_created_by_label")
        ));
        
        $event->add(array(
            "key" => "event-location",
            "label" => $language->text("hint", "info_event_location_label")
        ));
        
        $event->add(array(
            "key" => "event-access",
            "label" => $language->text("hint", "info_event_access_label")
        ));
        
        $event->add(array(
            "key" => "event-access-creator",
            "label" => $language->text("hint", "info_event_access_and_creator_label")
        ));
        
        if ( $params["line"] != HINT_BOL_Service::INFO_LINE0 )
        {
            $event->add(array(
                "key" => "event-desc",
                "label" => $language->text("hint", "info_event_desc_label")
            ));
            
            $event->add(array(
                "key" => "event-users",
                "label" => $language->text("hint", "info_event_users_label")
            ));
        }
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }
        
        $userEmbed = '<a href="javascript://">Angela Smith</a>';
        
        switch ( $params["key"] )
        {
            case "event-access-creator":
                
                $event->setData($language->text("hint", "event_info_access_and_creator", array(
                    "user" => $userEmbed,
                    "accessibility" => $language->text("hint", "event_info_access_by_invitation")
                )));
                break;
            
            case "event-access":
                
                $event->setData($language->text("hint", "event_info_access_by_invitation"));
                break;
            
            case "event-created-by":
                
                $event->setData($language->text("hint", "event_info_created_by", array(
                    "user" => $userEmbed
                )));
                break;
            
            case "event-date":
                
                $event->setData($language->text("hint", "event_info_date", array(
                    "startDate" => UTIL_DateTime::formatSimpleDate(strtotime("1/1/15"), true),
                    "endDate" => UTIL_DateTime::formatSimpleDate(strtotime("12/31/15"), true)
                )));
                break;
            
            case "event-start-date":
                
                $event->setData($language->text("hint", "event_info_start_date", array(
                    "startDate" => UTIL_DateTime::formatSimpleDate(strtotime("1/1/15"))
                )));
                break;
            
            case "event-end-date":
                
                $event->setData($language->text("hint", "event_info_end_date", array(
                    "endDate" => UTIL_DateTime::formatSimpleDate(strtotime("12/31/15"))
                )));
                break;
            
            case "event-users":
                $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";
        
                $data = array();

                for ( $i = 0; $i < 9; $i++ )
                {
                    $data[] = array(
                        "src" => $staticUrl . "user_" . $i . ".jpg",
                        "url" => "javascript://"
                    );
                }

                $users = new HINT_CMP_UserList($data, array(), null, 9);

                $event->setData($users->render());
                break;
            
            case "event-desc":
                $description = UTIL_String::truncate($language->text("hint", "info_event_desc_preview"), 170, "...");
                $event->setData('<span class="ow_remark ow_small">' . $description . '</span>');
                break;
            
            case "event-location":
                $event->setData($language->text("hint", "info_event_location_preview"));
                break;
        }
    }
    
    public function onInfoRender( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_EVENT )
        {
            return;
        }
        
        $eventId = $params["entityId"];
        $eventInfo = $this->getEventById($eventId);
        
        if ( empty($eventInfo) )
        {
            return;
        }
        
        $language = OW::getLanguage();
        
        $type = HINT_BOL_Service::getInstance()->getConfig("ehintType");
        
        $userName = BOL_UserService::getInstance()->getDisplayName($eventInfo["userId"]);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($eventInfo["userId"]);
        
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';
        
        $access = $eventInfo["accessibility"] == 1
                    ? $language->text("hint", "event_info_access_public")
                    : $language->text("hint", "event_info_access_by_invitation");
        
        switch ( $params["key"] )
        {
            case "event-access-creator":
                $event->setData($language->text("hint", "event_info_access_and_creator", array(
                    "user" => $userEmbed,
                    "accessibility" => $access
                )));
                break;
            
            case "event-access":
                
                $event->setData($access);
                break;
            
            case "event-created-by":
                
                $event->setData($language->text("hint", "event_info_created_by", array(
                    "user" => $userEmbed
                )));
                break;
            
            case "event-date":
                
                $event->setData($language->text("hint", "event_info_date", array(
                    "startDate" => UTIL_DateTime::formatSimpleDate($eventInfo["startTimeStamp"], true),
                    "endDate" => UTIL_DateTime::formatSimpleDate($eventInfo["endTimeStamp"], true)
                )));
                break;
            
            case "event-start-date":
                
                $event->setData($language->text("hint", "event_info_start_date", array(
                    "startDate" => UTIL_DateTime::formatSimpleDate($eventInfo["startTimeStamp"])
                )));
                break;
            
            case "event-end-date":
                
                $event->setData($language->text("hint", "event_info_end_date", array(
                    "endDate" => UTIL_DateTime::formatSimpleDate($eventInfo["endTimeStamp"])
                )));
                break;
            
            case "event-users":
                $count = $type == "image" ? 6 : 9;
                $userIds = $this->getUserIds($eventId, 1, 200);

                if ( empty($userIds) )
                {
                    return;
                }
                
                $title = OW::getLanguage()->text("hint", "event_users_list_title");
                $data = BOL_AvatarService::getInstance()->getDataForUserAvatars(array_slice($userIds, 0, $count), true, true, false, false);
                $users = new HINT_CMP_UserList($data, $userIds, $title, $count);

                $event->setData($users->render());
                break;
            
            case "event-desc":
                $description = UTIL_String::truncate(strip_tags($eventInfo["description"]), $type == "image" ? 110 : 180, "...");
                $event->setData('<span class="ow_remark ow_small">' . $description . '</span>');
                break;
            
            case "event-location":
                $event->setData($eventInfo["location"]);
                break;
        }
    }
    
    
    public function prepareParsers()
    {
        HINT_CLASS_ParseManager::getInstance()->addParser(new HINT_CLASS_EventParser());
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