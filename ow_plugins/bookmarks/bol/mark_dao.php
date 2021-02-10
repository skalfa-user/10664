<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Bookmarks Mark DAO
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.bol
 * @since 1.0
 */
class BOOKMARKS_BOL_MarkDao extends OW_BaseDao
{
    CONST USER_ID = 'userId';
    CONST MARK_USER_ID = 'markUserId';
    
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
        return 'BOOKMARKS_BOL_Mark';
    }
    
    public function getTableName() 
    {
        return OW_DB_PREFIX . 'bookmarks_mark';
    }
    
    public function isMarked($userId, $markUserId)
    {
        if ( empty($userId) || empty($markUserId) )
        {
            return FALSE;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId AND 
                `' . self::MARK_USER_ID . '` = :markUserId';
        
        $result = $this->dbo->queryForColumn($sql, array('userId' => $userId, 'markUserId' => $markUserId));
        
        return (int)$result === 1 ? TRUE : FALSE;
    }
    
    public function mark( $userId, $markUserId )
    {
        if ( empty($userId) || empty($markUserId) )
        {
            return NULL;
        }
        
        $sql = 'INSERT IGNORE INTO `' . $this->getTableName() . '`
            VALUES(NULL, :userId, :markUserId)';
        
        return $this->dbo->insert($sql, array('userId' => $userId, 'markUserId' => $markUserId));
    }
    
    public function unmark( $userId, $markUserId )
    {
        if ( empty($userId) || empty($markUserId) )
        {
            return NULL;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->andFieldEqual(self::MARK_USER_ID, $markUserId);
        
        return $this->deleteByExample($example);
    }
    
    public function findBookmarksCount( $userId, $list = BOOKMARKS_BOL_Service::LIST_LATEST, $eventParams = array() )
    {
        if ( empty($userId) )
        {
            return 0;
        }

        list($join, $where) = $this->parseEventParams($eventParams);
        
        switch ( $list )
        {
            case BOOKMARKS_BOL_Service::LIST_LATEST:
                $sql = 'SELECT COUNT(*) 
                    FROM `' . $this->getTableName() . '`AS `b` ' . $join . '
                    WHERE `b`.`' . self::USER_ID . '` = :userId AND ' . $where;
                break;
            
            case BOOKMARKS_BOL_Service::LIST_ONLINE:
            default:
                $sql = 'SELECT COUNT(*)
                    FROM `' . $this->getTableName() . '` AS `b` ' . $join . '
                        INNER JOIN `' . BOL_UserOnlineDao::getInstance()->getTableName() . '` AS `o`
                            ON(`b`.`' . self::MARK_USER_ID . '` = `o`.`'.BOL_UserOnlineDao::USER_ID . '`)
                    WHERE `b`.`' . self::USER_ID . '` = :userId AND ' . $where;
                break;
        }

        return (int)$this->dbo->queryForColumn($sql, array('userId' => $userId));
    }
    
    public function findBookmarksUserIdList( $userId, $first = 0, $count = NULL, $list = BOOKMARKS_BOL_Service::LIST_LATEST, $eventParams = array() )
    {
        if ( empty($userId) )
        {
            return array();
        }

        list($join, $where) = $this->parseEventParams($eventParams);
        
        empty($count) ? $count = (int)OW::getConfig()->getValue('bookmarks', 'widget_user_count') : NULL;

        switch ( $list )
        {
            case BOOKMARKS_BOL_Service::LIST_LATEST:
                $sql = 'SELECT `b`.`' . self::MARK_USER_ID . '`
                    FROM `' . $this->getTableName() . '` AS `b`
                    ' . $join . '
                    WHERE `b`.`' . self::USER_ID . '` = :userId AND ' . $where . '
                    ORDER BY `b`.`id` DESC
                    LIMIT :first, :count';
                break;
            
            case BOOKMARKS_BOL_Service::LIST_ONLINE:
                $sql = 'SELECT `b`.`' . self::MARK_USER_ID . '`
                    FROM `' . $this->getTableName() . '` AS `b`
                        ' . $join . '
                        INNER JOIN `' . BOL_UserOnlineDao::getInstance()->getTableName() . '` AS `o`
                            ON `b`.`' . self::MARK_USER_ID . '` = `o`.`'.BOL_UserOnlineDao::USER_ID . '`
                    WHERE `b`.`' . self::USER_ID . '` = :userId AND ' . $where . '
                    ORDER BY `o`.`activityStamp` DESC
                    LIMIT :first, :count';
                break;
            
            case BOOKMARKS_BOL_Service::LIST_NOTIFY:
            default:
                $sql = 'SELECT `b`.`' . self::MARK_USER_ID . '`
                    FROM `' . $this->getTableName() . '` AS `b`
                        ' . $join . '
                        INNER JOIN `' . BOL_UserDao::getInstance()->getTableName() . '` AS `u`
                            ON `b`.`' . self::MARK_USER_ID . '` = `u`.`id`
                    WHERE `b`.`' . self::USER_ID . '` = :userId AND ' . $where . '
                    ORDER BY `u`.`activityStamp` DESC
                    LIMIT :first, :count';
                break;
        }

        return $this->dbo->queryForColumnList($sql, array('userId' => $userId, 'first' => $first, 'count' => $count));
    }
    
    public function findAllBookmarkIdList( $userId )
    {
        if ( empty($userId) )
        {
            return array();
        }
        
        $sql = 'SELECT `' . self::MARK_USER_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId';
        
        return $this->dbo->queryForColumnList($sql, array('userId' => $userId));
    }
    
    public function deleteMarksByUserId( $userId )
    {
        if ( empty($userId) )
        {
            return;
        }
        
        $sql = 'DELETE FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :id OR `' . self::MARK_USER_ID . '` = :markId';
        
        return $this->dbo->query($sql, array('id' => $userId, 'markId' => $userId));
    }
    
    public function getMarkedListByUserId( $userId, $markIdList )
    {
        if ( empty($userId) || empty($markIdList) )
        {
            return array();
        }
        
        $sql = 'SELECT `' . self::MARK_USER_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId AND
                `' . self::MARK_USER_ID . '` IN (' . implode(',', array_map('intval', $markIdList)) . ');';
        
        $result = $this->dbo->queryForColumnList($sql, array('userId' => $userId));
        
        $out = array();
        foreach ( $markIdList as $id )
        {
            $out[$id] = in_array($id, $result);
        }
        
        return $out;
    }

    private function parseEventParams( $eventParams )
    {
        $join = ( isset($eventParams['join']) ) ? $eventParams['join'] : '';
        $where = ( isset($eventParams['where']) ) ? $eventParams['where'] : '1';
        $order = ( isset($eventParams['order']) ) ? $eventParams['order'] : '';

        return array($join, $where, $order);
    }
}
