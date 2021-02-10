<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Data Access Object for `membership_user` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.bol
 * @since 1.0
 */
class MEMBERSHIP_BOL_MembershipUserDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var MEMBERSHIP_BOL_MembershipUserDao
     */
    private static $classInstance;

    const MEMBERSHIP_EXPIRATION_INTERVAL = 7200; // 2 hours

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class
     *
     * @return MEMBERSHIP_BOL_MembershipUserDao
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
        return 'MEMBERSHIP_BOL_MembershipUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'membership_user';
    }

    /**
     * Finds user membership by user Id
     * 
     * @param int $userId
     * @return MEMBERSHIP_BOL_MembershipUser
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    /**
     * Finds users by membership type
     * 
     * @param int $typeId
     * @param int $page
     * @param int $onPage
     * @return array
     */
    public function findByTypeId( $typeId, $page, $onPage )
    {
        $limit = (int) $onPage;
        $first = ( $page - 1 ) * $limit;
        
        $sql = "SELECT `m`.*
            FROM `".$this->getTableName()."` AS `m`
            LEFT JOIN `".BOL_UserDao::getInstance()->getTableName()."` AS `u` ON (`u`.`id` = `m`.`userId`)
            WHERE `m`.`typeId` = :typeId
            ORDER BY `u`.`activityStamp` DESC
            LIMIT :first, :limit";
        
        return $this->dbo->queryForList($sql, array('typeId' => $typeId, 'first' => $first, 'limit' => $limit));
    }

    public function findByTypeIdList( $typeIdList, $page, $onPage )
    {
        $limit = (int) $onPage;
        $first = ( $page - 1 ) * $limit;

        $sql = "SELECT `m`.*
            FROM `".$this->getTableName()."` AS `m`
            LEFT JOIN `".BOL_UserDao::getInstance()->getTableName()."` AS `u` ON (`u`.`id` = `m`.`userId`)
            WHERE `m`.`typeId` IN (".$this->dbo->mergeInClause($typeIdList).")
            ORDER BY `u`.`activityStamp` DESC
            LIMIT :first, :limit";

        return $this->dbo->queryForList($sql, array('first' => $first, 'limit' => $limit));
    }

    public function findObjectsByTypeId( $typeId, $page, $onPage )
    {
        $limit = (int) $onPage;
        $first = ( $page - 1 ) * $limit;

        $sql = "SELECT `m`.*
            FROM `".$this->getTableName()."` AS `m`
            LEFT JOIN `".BOL_UserDao::getInstance()->getTableName()."` AS `u` ON (`u`.`id` = `m`.`userId`)
            WHERE `m`.`typeId` = :typeId
            ORDER BY `u`.`activityStamp` DESC
            LIMIT :first, :limit";

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('typeId' => $typeId, 'first' => $first, 'limit' => $limit));
    }
    
    public function countByTypeId( $typeId )
    {
        $example = new OW_Example();
        
        $example->andFieldEqual('typeId', $typeId);
        
        return $this->countByExample($example);
    }

    public function countByTypeIdList( $typeIdList )
    {
        $example = new OW_Example();

        $example->andFieldInArray('typeId', $typeIdList);

        return $this->countByExample($example);
    }

    /**
     * Find users' expired memberships
     *
     * @param $eventParams
     * @return array
     */
    public function findExpiredMemberships( $eventParams )
    {
        $sql = "SELECT `membership_user`.* {$eventParams['select']} FROM `".$this->getTableName()."` as membership_user {$eventParams['join']}
            WHERE {$eventParams['where']} AND ( `membership_user`.`recurring` = 1 AND `membership_user`.`expirationStamp` <= ?
            OR `membership_user`.`recurring` = 0 AND `membership_user`.`expirationStamp` <= ? ) {$eventParams['group_by']}";

        $now = time();
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array($now - self::MEMBERSHIP_EXPIRATION_INTERVAL, $now));
    }

    public function getExpiringSoonMemberships( $days, $limit )
    {
        $sql = "SELECT * FROM `".$this->getTableName()."`
            WHERE `expirationNotified` = 0 AND `recurring` = 0
            AND `expirationStamp` BETWEEN :from AND :to
            ORDER BY `expirationStamp` ASC
            LIMIT 0, :limit";

        $now = time();
        $dayPeriod = 24 * 3600;

        $params = array('from' => $now + $dayPeriod, 'to' => $now + $days * $dayPeriod, 'limit' => $limit);

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $params);
    }

    public function getExpiringTodayMemberships( $limit )
    {
        $sql = "SELECT * FROM `".$this->getTableName()."`
            WHERE `expirationNotified` != 2 AND `recurring` = 0
            AND `expirationStamp` BETWEEN :from AND :to
            ORDER BY `expirationStamp` ASC
            LIMIT 0, :limit";

        $now = time();
        $dayPeriod = 24 * 3600;

        $params = array('from' => $now, 'to' => $now + $dayPeriod, 'limit' => $limit);

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $params);
    }
    
    public function deleteByTypeId( $typeId )
    {
        $example = new OW_Example();
        
        $example->andFieldEqual('typeId', $typeId);
        
        $this->deleteByExample($example);
    }

    public function findRecurringExpiredSale()
    {
        $sql = "SELECT `membership_user`.*, `sale`.`id` as `saleId`, `sale`.`extraData`, `sale`.`entityKey`, `sale`.`entityId`
            FROM `".$this->getTableName()."` as membership_user
            LEFT JOIN  " . MEMBERSHIP_BOL_MembershipPlanDao::getInstance()->getTableName() . " as `plan` ON ( `membership_user`.`typeId` = `plan`.`typeId` ) 
            LEFT JOIN (
                SELECT * FROM " . BOL_BillingSaleDao::getInstance()->getTableName() . " as `sale2` 
                WHERE `sale2`.`extraData` IS NOT NULL AND  `sale2`.`recurring` =1 ORDER BY id DESC
            ) as `sale` ON( `membership_user`.`userId` = `sale`.`userId` 
            AND `plan`.`id` = `sale`.`entityId` AND `sale`.`entityKey` = 'membership_plan' )
            WHERE `membership_user`.`recurring` = 1 AND `sale`.`entityKey` IS NOT NULL 
            AND `sale`.`entityId` IS NOT NULL AND `membership_user`.`expirationStamp` <= ? group by `membership_user`.`id`";

        $now = time();

        return $this->dbo->queryForList($sql, array($now - self::MEMBERSHIP_EXPIRATION_INTERVAL));
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);
    }
}