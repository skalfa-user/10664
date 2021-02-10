<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class MATCHMAKING_MCLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var MATCHMAKING_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return MATCHMAKING_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function init()
    {
        OW::getEventManager()->bind("base.user_list.get_fields", array($this, 'getUserListFields'));
    }

    public function getUserListFields( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();
        $list = !empty($params['list']) ? $params['list'] : null;
        $userIdList = !empty($params['userIdList']) ? $params['userIdList'] : null;
        
        if ( !in_array($list, array( 'matchmaking-newest', 'matchmaking-compatible' )) )
        {
            return;
        }
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        if ( empty($userIdList) )
        {
            return;
        }
        
        $matchList = MATCHMAKING_BOL_Service::getInstance()->findCompatibilityByUserIdList(OW::getUser()->getId(), $userIdList, 0, 500);
        $match = array();
        
        foreach ( $matchList as $item )
        {
            $match[$item['userId']] = $item['compatibility'];
        }
        
        foreach ( $userIdList as $userId )
        {
            $compatibility = !empty($match[$userId]) ? $match[$userId] : 0;
            $data[$userId][] = OW::getLanguage()->text('matchmaking', 'compatibility') . ":" . $compatibility . "%";
        }
        
        $e->setData($data);
    }
}
