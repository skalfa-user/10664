<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Bookmarks Notify DAO
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.bol
 * @since 1.0
 */
class BOOKMARKS_BOL_NotifyLogDao extends OW_BaseDao
{
    CONST USER_ID = 'userId';
    CONST TIMESTAMP = 'timestamp';
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function getDtoClassName() 
    {
        return 'BOOKMARKS_BOL_NotifyLog';
    }
    
    public function getTableName() 
    {
        return OW_DB_PREFIX . 'bookmarks_notify_log';
    }
    
    public function cleareExpiredNotifyLog( $timestamp )
    {
        if ( empty($timestamp) )
        {
            return FALSE;
        }
        
        $example = new OW_Example();
        $example->andFieldLessOrEqual(self::TIMESTAMP, $timestamp);
        
        return $this->deleteByExample($example);
    }

    public function findUserIdListForNotify( $timeStamp, $first, $count = BOOKMARKS_BOL_Service::COUNT_CRON_USER )
    {
        if ( empty($timeStamp) )
        {
            return array();
        }
        
        $sql = 'SELECT `id`
                FROM `' . BOL_UserDao::getInstance()->getTableName() . '`
                WHERE `activityStamp` <= :stamp AND `id` NOT IN (
                    SELECT `userId`
                    FROM `' . $this->getTableName() . '`)
                LIMIT :first, :count';
        
        return $this->dbo->queryForColumnList($sql, array('stamp' => $timeStamp, 'first' => (int)$first, 'count' => (int)$count));
    }
    
    public function notifyLogSave( $userId )
    {
        if ( empty($userId) )
        {
            return NULL;
        }
        
        $entity = new BOOKMARKS_BOL_NotifyLog();
        $entity->setUserId($userId);
        $entity->setTimestamp(time());
        
        $this->save($entity);
    }
    
    public function notifyLogDeleteByUserId( $userId )
    {
        if ( empty($userId) )
        {
            return NULL;
        }
        
        $sql = 'DELETE FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId';
        
        return $this->dbo->query($sql, array('userId' => $userId));
    }
}
