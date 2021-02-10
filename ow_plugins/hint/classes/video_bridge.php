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
class HINT_CLASS_VideoBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_VideoBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_VideoBridge
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

    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
        
        $event->add(array(
            "key" => "video-count",
            "label" => $language->text("hint", "info_video_count_label")
        ));
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
        
        if ( $params["key"] == "video-count" )
        {
            $event->setData($language->text("hint", "info_video_count_preview"));
        }
    }
    
    public function onInfoRender( OW_Event $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
        
        $userId = $params["entityId"];
        
        if ( $params["key"] != "video-count" )
        {
            return;
        }
        
        $count = VIDEO_BOL_ClipService::getInstance()->findUserClipsCount($userId);
        $url = OW::getRouter()->urlForRoute("video_user_video_list", array(
            "user" => BOL_UserService::getInstance()->getUserName($userId)
        ));
        
        $event->setData($language->text("hint", "info_vide_count", array(
            "count" => $count,
            "url" => $url
        )));
    }

    public function init()
    {
        if ( !OW::getPluginManager()->isPluginActive("video") )
        {
            return;
        }
        
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array($this, 'onCollectInfoConfigs'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_PREVIEW, array($this, 'onInfoPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_RENDER, array($this, 'onInfoRender'));
    }
}