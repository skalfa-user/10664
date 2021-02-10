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
class HINT_CLASS_McomposeBridge
{
    const PLUGIN_URL = "http://www.oxwall.org/store/item/580";
    const PLUGIN_TITLE = "Compose Message";
    
    /**
     * Class instance
     *
     * @var HINT_CLASS_McomposeBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_McomposeBridge
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
        return OW::getPluginManager()->isPluginActive('mcompose');
    }

    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        $button = null;
        
        switch ($params["entityType"])
        {
            case HINT_BOL_Service::ENTITY_TYPE_USER:
                $button = $this->collectUserButton($params["entityId"]);
                
                break;
            
            case HINT_BOL_Service::ENTITY_TYPE_GROUP:
                $button = $this->collectGroupButton($params["entityId"]);
                
                break;
            
            case HINT_BOL_Service::ENTITY_TYPE_EVENT:
                $button = $this->collectEventButton($params["entityId"]);
                
                break;
        }

        if ( $button !== null )
        {
            $event->add($button);
        }
    }
    
    public function collectUserButton( $userId )
    {
        if ( !OW::getUser()->isAuthenticated() || $userId == OW::getUser()->getId() )
        {
            return null;
        }

        $uniqId = uniqid("hint-mc-");

        $js = UTIL_JsGenerator::newInstance();

        if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId) )
        {
            $js->jQueryEvent("#" . $uniqId, "click",
                'OW.error(e.data.msg);',
            array('e'), array(
                "msg" => OW::getLanguage()->text('base', 'user_block_message')
            ));
        }
        else
        {
            $recipients = array(MCOMPOSE_CLASS_BaseBridge::ID_PREFIX . '_' . $userId);
            $recipientsData = MCOMPOSE_BOL_Service::getInstance()->getDataForIds($recipients);
            
            $js->jQueryEvent("#" . $uniqId, "click", 'HINT.getShown().hide(); OW.trigger("mailbox.open_new_message_form", e.data.data); return false;', array("e"), array(
                "data" => array(
                    "opponentId" => $recipients,
                    "mcompose" => array(
                        "context" => MCOMPOSE_BOL_Service::CONTEXT_USER,
                        "data" => $recipientsData
                    )
                )
            ));
        }

        OW::getDocument()->addOnloadScript($js);

        return array(
            "key" => "mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_send_message_label'),
            "attrs" => array("id" => $uniqId)
        );
    }
    
    public function collectGroupButton( $groupId )
    {
        $group = HINT_CLASS_GroupsBridge::getInstance()->getGroupById($groupId);
        
        if ( empty($group) || $group["userId"] != OW::getUser()->getId() )
        {
            return null;
        }
        
        $uniqId = uniqid("hint-mc-");

        $js = UTIL_JsGenerator::newInstance();
        
        $recipients = array(MCOMPOSE_CLASS_GroupsBridge::ID_PREFIX . '_' . $groupId);
        $recipientsData = MCOMPOSE_BOL_Service::getInstance()->getDataForIds($recipients);

        $js->jQueryEvent("#" . $uniqId, "click", 'HINT.getShown().hide(); OW.trigger("mailbox.open_new_message_form", e.data.data); return false;', array("e"), array(
            "data" => array(
                "opponentId" => $recipients,
                "mcompose" => array(
                    "context" => MCOMPOSE_BOL_Service::CONTEXT_GROUP,
                    "data" => $recipientsData
                )
            )
        ));

        OW::getDocument()->addOnloadScript($js);

        return array(
            "key" => "g-mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_group_send_message_label'),
            "attrs" => array("id" => $uniqId)
        );
    }
    
    public function collectEventButton( $eventId )
    {
        $event = HINT_CLASS_EventsBridge::getInstance()->getEventById($eventId);
        
        if ( empty($event) || $event["userId"] != OW::getUser()->getId() )
        {
            return null;
        }
        
        $uniqId = uniqid("hint-mc-");

        $js = UTIL_JsGenerator::newInstance();
        
        $recipients = array(MCOMPOSE_CLASS_EventsBridge::ID_PREFIX . '_' . $eventId);
        $recipientsData = MCOMPOSE_BOL_Service::getInstance()->getDataForIds($recipients);

        $js->jQueryEvent("#" . $uniqId, "click", 'HINT.getShown().hide(); OW.trigger("mailbox.open_new_message_form", e.data.data); return false;', array("e"), array(
            "data" => array(
                "opponentId" => $recipients,
                "mcompose" => array(
                    "context" => MCOMPOSE_BOL_Service::CONTEXT_EVENT,
                    "data" => $recipientsData
                )
            )
        ));

        OW::getDocument()->addOnloadScript($js);

        return array(
            "key" => "e-mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_event_send_message_label'),
            "attrs" => array("id" => $uniqId)
        );
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        $previews = array();
        
        $previews[HINT_BOL_Service::ENTITY_TYPE_USER] = array(
            "key" => "mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_send_message_label'),
            "attrs" => array("href" => "javascript://")
        );
        
        $previews[HINT_BOL_Service::ENTITY_TYPE_GROUP] = array(
            "key" => "g-mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_group_send_message_label'),
            "attrs" => array("href" => "javascript://")
        );
        
        $previews[HINT_BOL_Service::ENTITY_TYPE_EVENT] = array(
            "key" => "e-mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_event_send_message_label'),
            "attrs" => array("href" => "javascript://")
        );
        
        if ( !empty($previews[$params["entityType"]]) )
        {
            $event->add($previews[$params["entityType"]]);
        }
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $mcomposeInstalled = $this->isActive();
        
        $configs = array();
        
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "mcompose");
        $configs[HINT_BOL_Service::ENTITY_TYPE_USER] = array(
            "key" => "mcompose",
            "label" => OW::getLanguage()->text('mailbox', 'create_conversation_button'),
            "active" => $active === null ? $mcomposeInstalled : $active,
            "shortLabel" => OW::getLanguage()->text('mailbox', 'create_conversation_button')
        );
        
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_GROUP, "g-mcompose");
        $configs[HINT_BOL_Service::ENTITY_TYPE_GROUP] = array(
            "key" => "g-mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_group_send_message'),
            "active" => $active === null ? false : $active,
            "shortLabel" => OW::getLanguage()->text('hint', 'button_group_send_message_label')
        );
        
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_EVENT, "e-mcompose");
        $configs[HINT_BOL_Service::ENTITY_TYPE_EVENT] = array(
            "key" => "e-mcompose",
            "label" => OW::getLanguage()->text('hint', 'button_event_send_message'),
            "active" => $active === null ? false : $active,
            "shortLabel" => OW::getLanguage()->text('hint', 'button_event_send_message_label')
        );
             
        if ( empty($configs[$params["entityType"]]) )
        {
            return;
        }
        
        $button = $configs[$params["entityType"]];
        
        if ( !$mcomposeInstalled )
        {
            $button["requirements"]["short"] = OW::getLanguage()->text("hint", "mcompose_required_short", array(
                "plugin" => '<a href="' . self::PLUGIN_URL . '" target="_blank">' . self::PLUGIN_TITLE . '</a>'
            ));
            
            $button["requirements"]["long"] = OW::getLanguage()->text("hint", "mcompose_required_long", array(
                "plugin" => '<a href="' . self::PLUGIN_URL . '" target="_blank">' . self::PLUGIN_TITLE . '</a>',
                "feature" => $button["shortLabel"]
            ));
        }

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
        $params = $event->getParams();

        if ( !in_array($params["command"], array()) )
        {
            return;
        }

        $userId = $params["params"]['userId'];

        $info = null;
        $error = null;

        $event->setData(array(
            "info" => $info,
            "error" => $error
        ));
    }

    public function init()
    {
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));
        
        if ( !$this->isActive() )
        {
            return;
        }

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}