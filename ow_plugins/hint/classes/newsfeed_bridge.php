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
class HINT_CLASS_NewsfeedBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_NewsfeedBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_NewsfeedBridge
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
        return OW::getPluginManager()->isPluginActive('newsfeed');
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

        if ( !OW::getUser()->isAuthenticated() || OW::getUser()->getId() == $userId )
        {
            return;
        }

        $isFollowing = OW::getEventManager()->call("feed.is_follow", array(
            "feedType" => "user",
            "feedId" => $userId,
            "userId" => OW::getUser()->getId()
        ));

        $label = $isFollowing
                ? OW::getLanguage()->text('newsfeed', 'unfollow_button')
                : OW::getLanguage()->text('newsfeed', 'follow_button');

        $toggleLabel = !$isFollowing
                ? OW::getLanguage()->text('newsfeed', 'unfollow_button')
                : OW::getLanguage()->text('newsfeed', 'follow_button');

        $command = $isFollowing ? "newsfeed.unfollow" : "newsfeed.follow";

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent('#' . $uniqId, 'click', '
            var self = $(this), command = self.data("command");
            HINT.UTILS.toggleText(this, e.data.l1, e.data.l2);
            self.data("command", command == "newsfeed.follow" ? "newsfeed.unfollow" : "newsfeed.follow");
            HINT.UTILS.query(command, e.data.params); return false;',
        array('e'),
        array(
            "l1" => $label,
            "l2" => $toggleLabel,
            "params" => array(
                "userId" => $userId
            )
        ));

        OW::getDocument()->addOnloadScript($js);

        $button = array(
            "key" => "follow",
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

        $label = OW::getLanguage()->text("newsfeed", "follow_button");

        $button = array(
            "key" => "follow",
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

        $label = OW::getLanguage()->text("newsfeed", "follow_button");
        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "follow");

        $button = array(
            "key" => "follow",
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
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $params = $event->getParams();

        if ( !in_array($params["command"], array("newsfeed.follow", "newsfeed.unfollow")) )
        {
            return;
        }

        $userId = $params["params"]['userId'];

        $info = null;
        $error = null;

        $username = BOL_UserService::getInstance()->getDisplayName($userId);

        switch ( $params["command"] )
        {
            case "newsfeed.follow":
                OW::getEventManager()->call('feed.add_follow', array(
                    'userId' => OW::getUser()->getId(),
                    'feedType' => 'user',
                    'feedId' => $userId
                ));

                $info = OW::getLanguage()->text('newsfeed', 'follow_complete_message', array('username' => $username));

                break;

            case "newsfeed.unfollow":
                OW::getEventManager()->call('feed.remove_follow', array(
                    'userId' => OW::getUser()->getId(),
                    'feedType' => 'user',
                    'feedId' => $userId
                ));

                $info = OW::getLanguage()->text('newsfeed', 'unfollow_complete_message', array('username' => $username));

                break;
        }

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