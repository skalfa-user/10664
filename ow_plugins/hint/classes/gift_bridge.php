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
class HINT_CLASS_GiftBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_GiftBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_GiftBridge
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
        return OW::getPluginManager()->isPluginActive('virtualgifts');
    }

    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $userId = $params["entityId"];

        if ( !OW::getUser()->isAuthenticated() || $userId == OW::getUser()->getId() )
        {
            return;
        }

        $uniqId = uniqid("hint-vg-");

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
            $userName = BOL_UserService::getInstance()->getUserName($userId);

            $js->jQueryEvent("#" . $uniqId, "click",
                'sendGiftFloatBox = OW.ajaxFloatBox("VIRTUALGIFTS_CMP_SendGift", {recipientId: e.data.userId } , {width:580, iconClass: "ow_ic_heart", title: e.data.title});',
            array('e'), array(
                "title" => OW::getLanguage()->text('virtualgifts', 'send_gift_to', array('user' => $userName)),
                "userId" => $userId
            ));
        }

        OW::getDocument()->addOnloadScript($js);

        $button = array(
            "key" => "virtualgift",
            "label" => OW::getLanguage()->text('virtualgifts', 'profile_toolbar_item_send_gift'),
            "attrs" => array("id" => $uniqId)
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

        $label = OW::getLanguage()->text("virtualgifts", "profile_toolbar_item_send_gift");

        $button = array(
            "key" => "virtualgift",
            "label" => $label,
            "attrs" => array("href" => "javascript://")
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

        $label = OW::getLanguage()->text("virtualgifts", "profile_toolbar_item_send_gift");
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "virtualgift");

        $button = array(
            "key" => "virtualgift",
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
        if ( !$this->isActive() )
        {
            return;
        }

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}