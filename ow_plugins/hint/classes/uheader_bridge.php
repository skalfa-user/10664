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
class HINT_CLASS_UheaderBridge
{
    const PLUGIN_URL = "http://www.oxwall.org/store/item/483";
    const PLUGIN_TITLE = "Profile Cover";
    
    /**
     * Class instance
     *
     * @var HINT_CLASS_UheaderBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_UheaderBridge
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
        return OW::getPluginManager()->isPluginActive('uheader');
    }
    
    public function isEnabled()
    {
        $enabled = HINT_BOL_Service::getInstance()->getConfig("uheader_enabled");
        
        return $enabled === null ? $this->isActive() : (bool) $enabled;
    }
    
    public function setEnabled( $yes = true )
    {
        HINT_BOL_Service::getInstance()->saveConfig("uheader_enabled", $yes ? 1 : 0);
    }
    
    public function getCoverForUser( $userId, $forWidth = null )
    {
        if ( OW::getEventManager()->call("uheader.get_version" ) >= 2 )
        {
            return OW::getEventManager()->call("uheader.get_cover", array(
                "userId" => $userId,
                "forWidth" => $forWidth
            ));
        }
        
        // Backward compatibility
        
        $dto = $this->findCoverDtoByUserId($userId);
        if ( $dto === null )
        {
            return null;
        }
        
        $src = $this->getCoverUrl($dto);
        
        return array(
            "src" => $src,
            "data" => $dto->getSettings()
        );
    }

    public function findCoverDtoByUserId( $userId )
    {
        if ( !$this->isActive() ) return null;
        
        $permited = UHEADER_CLASS_PrivacyBridge::getInstance()->checkPrivacy($userId);
        
        if ( !$permited )
        {
            return null;
        }
        
        return UHEADER_BOL_Service::getInstance()->findCoverByUserId($userId);
    }
    
    public function getCoverUrl( UHEADER_BOL_Cover $cover )
    {
        if ( !$this->isActive() ) return null;
        
        return UHEADER_BOL_Service::getInstance()->getCoverUrl($cover);
    }
    
    public function init()
    {
        
    }
}