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

class OCSGUESTS_CLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var OCSGUESTS_CLASS_EventHandler
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
     * @return OCSGUESTS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function onProfilePageRender( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityId']) || empty($params['placeName']) || $params['placeName'] != 'profile' )
        {
            return;
        }

        $userId = (int) $params['entityId'];
        $viewerId = OW::getUser()->getId();

        $authService = BOL_AuthorizationService::getInstance();
        $isAdmin = $authService->isActionAuthorizedForUser($viewerId, 'admin') || $authService->isActionAuthorizedForUser($viewerId, 'base');

        if ( $userId && $viewerId && ($viewerId != $userId) && !$isAdmin )
        {
            OCSGUESTS_BOL_Service::getInstance()->trackVisit($userId, $viewerId);
        }
    }

    public function trackVisit( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['guestId']) )
        {
            return;
        }

        $userId = $params['userId'];
        $guestId = $params['guestId'];

        $authService = BOL_AuthorizationService::getInstance();
        $isAdmin = $authService->isActionAuthorizedForUser($guestId, 'admin') || $authService->isActionAuthorizedForUser($guestId, 'base');

        if ( $userId && $guestId && ($guestId != $userId) && !$isAdmin )
        {
            OCSGUESTS_BOL_Service::getInstance()->trackVisit($userId, $guestId);
        }
    }

    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];

        OCSGUESTS_BOL_Service::getInstance()->deleteUserGuests($userId);
    }

    public function getList( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $page = empty($params['page']) ? 1 : $params['page'];
        $limit = empty($params['limit']) ? 1000000 : $params['limit'];

        $guests = OCSGUESTS_BOL_Service::getInstance()->findGuestUsers($userId, $page, $limit);
        $guestsIdList = array();
        foreach ( $guests as $guest )
        {
            /* @var OCSGUESTS_BOL_Guest $guest*/

            $guestsIdList[] = $guest->guestId;
        }

        unset($guests, $guest);

        $guests = OCSGUESTS_BOL_Service::getInstance()->findGuestsByGuestIds($userId, $guestsIdList);
        $out = array();

        foreach ( $guests as $guest )
        {
            $out[] = array(
                "userId" => $guest->guestId,
                "viewed" => $guest->viewed,
                "timeStamp" => $guest->visitTimestamp
            );
        }

        $event->setData($out);

        return $out;
    }

    public function getNewCount( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];

        $count = OCSGUESTS_BOL_Service::getInstance()->findNewGuestsCount($userId);

        $event->setData($count);

        return $count;
    }

    public function markViewed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['guestIds']) )
        {
            return;
        }

        $userId = $params['userId'];
        $guestIds = $params['guestIds'];

        OCSGUESTS_BOL_Service::getInstance()->setViewedStatusByGuestIds($userId, $guestIds);
    }

    public function genericInit()
    {
        $em = OW::getEventManager();

        $em->bind("guests.get_guests_list", array($this, "getList"));
        $em->bind("guests.get_new_guests_count", array($this, "getNewCount"));
        $em->bind("guests.mark_guests_viewed", array($this, "markViewed"));
        $em->bind("guests.track_visit", array($this, "trackVisit"));

        $em->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
    }

    public function init()
    {
        $this->genericInit();
        $em = OW::getEventManager();

        $em->bind('base.widget_panel.content.top', array($this, 'onProfilePageRender'));
    }
}
