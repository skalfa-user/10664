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

class OCSGUESTS_MCLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var OCSGUESTS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        
    }

    /**
     * Returns class instance
     *
     * @return OCSGUESTS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function trackVisit( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $viewerId = OW::getUser()->getId();

        $authService = BOL_AuthorizationService::getInstance();
        $isAdmin = $authService->isActionAuthorizedForUser($viewerId, 'admin') || $authService->isActionAuthorizedForUser($viewerId, 'base');

        if ( $userId && $viewerId && ($viewerId != $userId) && !$isAdmin )
        {
            OCSGUESTS_BOL_Service::getInstance()->trackVisit($userId, $viewerId);
        }
    }

    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];

        OCSGUESTS_BOL_Service::getInstance()->deleteUserGuests($userId);
    }

    public function getUserListFields( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();
        
        $list = !empty($params['list']) ? $params['list'] : null;
        
        if ( empty($list) || $list != 'ocsguests' || !OW::getUser() || !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        $userIdList = !empty($params['userIdList']) ? $params['userIdList'] : null;
        
        if ( empty($userIdList) )
        {
            return;
        }
        
        $visitTimeList = OCSGUESTS_BOL_Service::getInstance()->getVisitedStampByGuestsIds(OW::getUser()->getId(), $userIdList);
        
        foreach ( $userIdList as $userId )
        {
            $data[$userId][] = OW::getLanguage()->text('ocsguests', 'visited') . ' ' . '<span class="owm_remark">' . $visitTimeList[$userId]. '</span>';
        }
        
        $e->setData($data);
    }
    
    public function init()
    {
        OCSGUESTS_CLASS_EventHandler::getInstance()->genericInit();
        $em = OW::getEventManager();

        $em->bind('mobile.content.profile_view_top', array($this, 'trackVisit'));
        $em->bind("base.user_list.get_fields", array($this, 'getUserListFields'));
    }
}
