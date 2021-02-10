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

class OCSGUESTS_BOL_GuestDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var OCSGUESTS_BOL_GuestDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return OCSGUESTS_BOL_GuestDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     */
    public function getDtoClassName()
    {
        return 'OCSGUESTS_BOL_Guest';
    }
    
    /**
     * @see OW_BaseDao::getTableName()
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'ocsguests_guest';
    }
    
    /**
     * @param $userId
     * @param $guestId
     * @return mixed
     */
    public function findGuest( $userId, $guestId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('userId', $userId);
    	$example->andFieldEqual('guestId', $guestId);
    	
    	return $this->findObjectByExample($example);
    }
    
    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @param array $eventParams
     *
     * @return array|mixed
     */
    public function findUserGuests( $userId, $page, $limit, array $eventParams = array() )
    {
        list($join, $where) = $this->parseEventParams($eventParams);

    	$first = ( $page - 1 ) * $limit;

        $sql = 'SELECT `oc`.* FROM `' . $this->getTableName() . '` as `oc` ' . $join . '
                WHERE ' . $where . ' AND `oc`.`userId` = ?
                ORDER BY `oc`.`visitTimestamp` DESC
                LIMIT ?, ? ';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array($userId, $first, $limit));
    }
    
    public function setViewedStatusByGuestIds( $userId, $guestIds, $viewed = true  )
    {
        if ( empty($guestIds) )
        {
            return;
        }
        
        $query = "UPDATE " . $this->getTableName() . " SET `viewed`=:viewed "
                . "WHERE `guestId` IN ( " . implode(",", $guestIds) . " ) "
                    . "AND `userId`=:u";
        
        $this->dbo->query($query, array(
            "u" => $userId,
            "viewed" => $viewed
        ));

        return true;
    }
    
    public function getViewedStatusByGuestIds( $userId, $guestIds  )
    {
        $dtoList = $this->findGuestsByGuestIds($userId, $guestIds);
        
        $out = array();
        foreach ( $dtoList as $dto )
        {
            $out[$dto->guestId] = $dto->viewed;
        }
        
        return $out;
    }
    
    public function getVisitStampByGuestIds( $userId, $guestIds )
    {
        $dtoList = $this->findGuestsByGuestIds($userId, $guestIds);
        
        $out = array();
        foreach ( $dtoList as $dto )
        {
            $out[$dto->guestId] = $dto->visitTimestamp;
        }
        
        return $out;
    }
    
    
    public function findGuestsByGuestIds( $userId, $guestIds  )
    {
        if ( empty($guestIds) )
        {
            return array();
        }
        
        $example = new OW_Example();
        $example->andFieldEqual("userId", $userId);
        $example->andFieldInArray("guestId", $guestIds);
        $example->setOrder("visitTimestamp DESC");
        
        return $this->findListByExample($example);
    }

    /**
     * @param $userId
     * @param array $eventParams
     * 
     * @return mixed|null|string
     */
    public function countUserGuests( $userId, array $eventParams = array() )
    {
        list($join, $where) = $this->parseEventParams($eventParams);

        $sql = 'SELECT COUNT(`oc`.`id`) FROM `' . $this->getTableName() . '`  as `oc` ' . $join . '
                WHERE ' . $where . ' AND `oc`.`userId` = :userId';

        return (int) $this->dbo->queryForColumn($sql, ['userId' => $userId]);
    }

    public function countNewGuests( $userId )
    {
        $query = "SELECT COUNT(*)
            FROM `".$this->getTableName()."` AS `g`
            INNER JOIN `" . BOL_UserDao::getInstance()->getTableName() . "` as `u`
                ON (`g`.`guestId` = `u`.`id`)
            LEFT JOIN `" . BOL_UserSuspendDao::getInstance()->getTableName() . "` as `s`
                ON( `u`.`id` = `s`.`userId` )
            LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` as `d`
                ON( `u`.`id` = `d`.`userId` )
            WHERE `s`.`id` IS NULL AND `d`.`id` IS NULL
            AND `g`.`userId` = ? AND `g`.`viewed` = 0";

        return $this->dbo->queryForColumn($query, array($userId));
    }

    /**
     * @param $timestamp
     */
    public function deleteExpired( $timestamp )
    {
    	$example = new OW_Example();
    	$example->andFieldLessThan('visitTimestamp', time() - $timestamp);
    	
    	$this->deleteByExample($example);
    }
    
    /**
     * @param $userId
     */
    public function deleteUserGuests( $userId )
    {
    	$sql = "DELETE FROM `".$this->getTableName()."` 
    	   WHERE `userId` = ? OR `guestId` = ?";
    	
    	$this->dbo->query($sql, array($userId, $userId));
    }

    private function parseEventParams( $eventParams )
    {
        $join = ( isset($eventParams['join']) ) ? $eventParams['join'] : '';
        $where = ( isset($eventParams['where']) ) ? $eventParams['where'] : '1';
        $order = ( isset($eventParams['order']) ) ? $eventParams['order'] : '';

        return array($join, $where, $order);
    }
}
