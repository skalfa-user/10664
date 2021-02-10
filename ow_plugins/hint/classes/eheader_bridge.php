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
class HINT_CLASS_EheaderBridge
{
    const PLUGIN_URL = "http://www.oxwall.org/store/item/1010";
    const PLUGIN_TITLE = "Event Cover";
    
    /**
     * Class instance
     *
     * @var HINT_CLASS_EheaderBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_EheaderBridge
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
        return OW::getPluginManager()->isPluginActive('eheader');
    }
    
    public function addStatic()
    {
        if ( !$this->isActive() ) return;
        
        $plugin = OW::getPluginManager()->getPlugin("eheader");
        $staticUrl = $plugin->getStaticUrl();
        
        OW::getDocument()->addScript($staticUrl . "eheader.min.js?" . $plugin->getDto()->build);
        OW::getDocument()->addStyleSheet($staticUrl . "eheader.min.css?" . $plugin->getDto()->build);
    }

    public function isEnabled()
    {
        $enabled = HINT_BOL_Service::getInstance()->getConfig("eheader_enabled");
        
        return $enabled === null ? $this->isActive() : (bool) $enabled;
    }
    
    public function setEnabled( $yes = true )
    {
        HINT_BOL_Service::getInstance()->saveConfig("eheader_enabled", $yes ? 1 : 0);
    }
    
    public function getCover( $eventId, $forWidth = null )
    {
        if (!$this->isActive())
        {
            return null;
        }
        
        return OW::getEventManager()->call("eheader.get_cover", array(
            "eventId" => $eventId,
            "forWidth" => $forWidth
        ));
    }
    
    public function init()
    {
        
    }
}