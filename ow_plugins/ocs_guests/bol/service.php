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

final class OCSGUESTS_BOL_Service
{
    /**
     * @var OCSGUESTS_BOL_GuestDao
     */
    private $guestDao;

    /**
     * Class instance
     *
     * @var OCSGUESTS_BOL_Service
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->guestDao = OCSGUESTS_BOL_GuestDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return OCSGUESTS_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @param $userId
     * @param $guestId
     * @return bool
     */
    public function trackVisit( $userId, $guestId )
    {
        if ( !$userId || !$guestId || ($guestId == $userId) || BOL_AuthorizationService::getInstance()->isModerator($guestId) )
        {
            return false;
        }

        $guest = $this->guestDao->findGuest($userId, $guestId);
        $new = true;

        if ( $guest )
        {
            $guest->visitTimestamp = time();
            $new = (bool) $guest->viewed;
        }
        else
        {
            $guest = new OCSGUESTS_BOL_Guest();
            $guest->userId = $userId;
            $guest->guestId = $guestId;
            $guest->visitTimestamp = time();
        }

        $guest->viewed = 0;
        $this->guestDao->save($guest);

        $event = new OW_Event("guests.after_visit", array(
            "userId" => $guest->userId,
            "guestId" => $guest->guestId,
            "new" => $new
        ));

        OW::getEventManager()->trigger($event);

        return true;
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findGuestsForUser( $userId, $page, $limit )
    {
        if ( !$userId )
        {
            return array();
        }

        $options = array(
            'userId' => $userId,
            'page' => $page,
            'limit' => $limit,
        );

        $eventParams = $this->getQueryFilter('ocsguests.on_before_find_guests_for_user', $options);

        $guests = $this->guestDao->findUserGuests($userId, $page, $limit, $eventParams);

        foreach ( $guests as &$g )
        {
            $g->visitTimestamp = UTIL_DateTime::formatDate($g->visitTimestamp, false);
        }

        return $guests;
    }
    
    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findGuestsForUserList( $userId, $userList )
    {
        if ( !$userId || empty($userList) )
        {
            return array();
        }

        return $this->guestDao->getVisitStampByGuestIds($userId, $userList);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findGuestUsers( $userId, $page, $limit )
    {
        if ( !$userId )
        {
            return array();
        }

        $options = array(
            'userId' => $userId,
            'page' => $page,
            'limit' => $limit,
        );

        $eventParams = $this->getQueryFilter('ocsguests.on_before_find_guests_user', $options);

        $guests = $this->guestDao->findUserGuests($userId, $page, $limit, $eventParams);

        return $guests;
    }

    /**
     * @param $userId
     * @return int
     */
    public function findNewGuestsCount( $userId )
    {
        if ( !$userId )
        {
            return 0;
        }

        return (int) $this->guestDao->countNewGuests($userId);
    }

    /**
     * @param $userId
     * @return int
     */
    public function countGuestsForUser( $userId )
    {
        $options = array(
            'userId' => $userId
        );

        $eventParams = $this->getQueryFilter('ocsguests.on_before_count_guests_for_user', $options);

        return $this->guestDao->countUserGuests( $userId, $eventParams );
    }

    /**
     * @return bool
     */
    public function checkExpiredGuests()
    {
        $months = (int) OW::getConfig()->getValue('ocsguests', 'store_period');
        $timestamp = $months * 30 * 24 * 60 * 60;

        $this->guestDao->deleteExpired($timestamp);

        return true;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function deleteUserGuests( $userId )
    {
        $this->guestDao->deleteUserGuests($userId);

        return true;
    }

    public function getVisitedStampByGuestsIds( $userId, $guestIds )
    {
        $list =$this->guestDao->getVisitStampByGuestIds($userId, $guestIds);
        $result = array();
        
        foreach ( $list as $key => $value )
        {
            $result[$key] = UTIL_DateTime::formatDate($value, false);
        }
        
        unset($list);
        return $result;
    }

    public function findGuestsByGuestIds( $userId, $guestIds )
    {
        return $this->guestDao->findGuestsByGuestIds($userId, $guestIds);
    }

    public function setViewedStatusByGuestIds( $userId, $guestIds, $viewed = true )
    {
        return $this->guestDao->setViewedStatusByGuestIds($userId, $guestIds, $viewed);
    }

    /**
     * Get query filter
     *
     * @param $eventName
     * @param array $options
     *
     * @return array
     */
    public function getQueryFilter( $eventName, array $options = array() )
    {
        $event = new BASE_CLASS_QueryBuilderEvent($eventName, $options);

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('oc', 'guestId', array(
            'method' => 'OCSGUESTS_BOL_Service::findGuestList'
        ));

        OW::getEventManager()->trigger($event);

        return array(
            'join' => $event->getJoin() . ' ' . $queryParts['join'],
            'where' => $event->getWhere() . ' AND ' . $queryParts['where'],
            'order' => $event->getOrder() . ' ' . $queryParts['order']
        );
    }
}
