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
class HINT_CLASS_GheaderBridge
{
    const PLUGIN_URL = "http://www.oxwall.org/store/item/505";
    const PLUGIN_TITLE = "Group Cover";
    
    /**
     * Class instance
     *
     * @var HINT_CLASS_GheaderBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_GheaderBridge
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
        return OW::getPluginManager()->isPluginActive('gheader');
    }
    
    public function isEnabled()
    {
        $enabled = HINT_BOL_Service::getInstance()->getConfig("gheader_enabled");
        
        return $enabled === null ? $this->isActive() : (bool) $enabled;
    }
    
    public function setEnabled( $yes = true )
    {
        HINT_BOL_Service::getInstance()->saveConfig("gheader_enabled", $yes ? 1 : 0);
    }
    
    public function getCover( $groupId, $forWidth = null )
    {
        if (!$this->isActive())
        {
            return null;
        }
        
        if ( OW::getEventManager()->call("gheader.get_version" ) >= 2 )
        {
            return OW::getEventManager()->call("gheader.get_cover", array(
                "groupId" => $groupId,
                "forWidth" => $forWidth
            ));
        }
        
        // Backward compatibility
        
        $cover = GHEADER_BOL_Service::getInstance()->findCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_ACTIVE);
        
        if ( $cover === null )
        {
            return null;
        }
                
        return array(
            "groupId" => $groupId,
            "src" => $cover->getSrc(),
            "data" => $cover->getSettings(),
            "canvas" => $cover->getCanvas($forWidth),
            "css" => $cover->getCss(),
            "cssString" => $cover->getCssString(),
            "ratio" => $cover->getRatio()
        );
    }
    
    public function hasInviter()
    {
        return OW::getEventManager()->call("gheader.get_version" ) >= 3;
    }
    
    public function addStatic()
    {
        if ( !$this->isActive() ) return;
        
        $plugin = OW::getPluginManager()->getPlugin("gheader");
        $staticUrl = $plugin->getStaticUrl();
        
        OW::getDocument()->addScript($staticUrl . "gheader.min.js?" . $plugin->getDto()->build);
        OW::getDocument()->addStyleSheet($staticUrl . "gheader.min.css?" . $plugin->getDto()->build);
    }
    
    public function init()
    {
        
    }
}