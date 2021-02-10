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

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.winks.bol
 * @since 1.0
 */
class WINKS_BOL_WinksDao extends OW_BaseDao
{
    CONST USER_ID = 'userId';
    CONST PARTNER_ID = 'partnerId';
    CONST TIMESTAMP = 'timestamp';
    CONST STATUS = 'status';
    CONST VIEWED = 'viewed';
    CONST WINKBACK = 'winkback';
    CONST MESSAGE_TYPE = 'messageType';

    CONST STATUS_ACCEPT = 'accept';
    CONST STATUS_IGNORE = 'ignore';
    CONST STATUS_WAIT = 'wait';

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        
        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'winks_winks';
    }
    
    public function getDtoClassName()
    {
        return 'WINKS_BOL_Winks';
    }
    
    public function isLimited( $userId, $partnerId )
    {
        if ( empty($userId) || empty($partnerId) )
        {
            return FALSE;
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId AND `' . self::PARTNER_ID . '` = :partnerId
            LIMIT 1';
        
        $entity = $this->dbo->queryForObject($sql, $this->getDtoClassName(), array('userId' => $userId, 'partnerId' => $partnerId));

        return ($entity !== NULL && $entity->getTimestamp() >= strtotime('-1 week'));
    }
    
    public function findByUserIdAndPartnerId( $userId, $partnerId )
    {
        if ( empty($userId) || empty($partnerId) )
        {
            return NULL;
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId AND `' . self::PARTNER_ID . '` = :partnerId
            LIMIT 1';
        
        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array('userId' => $userId, 'partnerId' => $partnerId));
    }
    
    public function countWinksForUser( $partnerId, array $status = array(), $viewed = NULL,
                                       array $activeModes = array(), array $eventParams = array() )
    {
        $params['partnerId'] = $partnerId;

        list($join, $where) = $this->parseEventParams($eventParams);

        $where .= ' AND `' . self::PARTNER_ID . '` = :partnerId';

        if ( count($status) )
        {
            $where .= ' AND `w`.`' . $this->dbo->escapeString('status') . '` IN( ' . $this->dbo->mergeInClause($status). ' )';
        }
        
        if ( $viewed === NULL )
        {
            $where .= ' AND `w`.`' . $this->dbo->escapeString('viewed') . '` IN( ' . $this->dbo->mergeInClause([0, 1]) . ' )';
        }
        else
        {
            $params['viewed'] = $viewed;

            $where .= ' AND `w`.`viewed` = :viewed ';
        }

        if ( $activeModes )
        {
            $where .= ' AND `w`.`' . $this->dbo->escapeString('messageType') . '` IN( ' . $this->dbo->mergeInClause($activeModes) . ' )';
        }

        $sql = 'SELECT COUNT( `w`.`id` ) FROM `' . $this->getTableName() . '` as `w` ' . $join . ' WHERE ' . $where;

        return (int) $this->dbo->queryForColumn($sql, $params);
    }
    
    public function countWinksForPartner( $userId, $status = NULL, $viewed = NULL )
    {
        if ( empty($userId) )
        {
            return 0;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        
        if ( !empty($status) )
        {
            $example->andFieldEqual(self::STATUS, $status);
        }
        
        if ( $viewed === NULL )
        {
            $example->andFieldInArray(self::VIEWED, array(0, 1));
        }
        else
        {
            $example->andFieldEqual(self::VIEWED, $viewed);
        }
        
        return (int)$this->countByExample($example);
    }
    
    public function countWinkBackedByUserId( $userId, array $activeModes = array(), array $eventParams = array() )
    {
        list($join, $where) = $this->parseEventParams($eventParams);
        
        $filterMode = '';
        if ( $activeModes ) 
        {
            $filterMode = ' AND `w`.`' . self::MESSAGE_TYPE . '` IN(' . $this->dbo->mergeInClause($activeModes) . ')';
        }

        $sql = 'SELECT COUNT(`w`.`id`) FROM `' . $this->getTableName() . '` as `w` ' . $join . '
                 WHERE ' . $where . ' AND  `w`.`' . self::USER_ID . '` = :userId AND `w`.`' . self::WINKBACK . '` > 0' . $filterMode;

        return (int) $this->dbo->queryForColumn($sql, array('userId' => $userId));
    }

    public function findWinkList( $partnerId, $first, $limit, array $activeModes = array(), array $eventParams = array() )
    {
        list($join, $where) = $this->parseEventParams($eventParams);

        $filterMode = '';

        if ( $activeModes ) 
        {
            $filterMode = ' AND `w`.`' . self::MESSAGE_TYPE . '` IN(' . $this->dbo->mergeInClause($activeModes) . ')';
        }
        $sql = 'SELECT `w`.* FROM `' . $this->getTableName() . '` as `w`
            ' . $join . '
            WHERE ' . $where . ' AND (`w`.`' . self::USER_ID . '` = :userId OR `w`.`' . self::PARTNER_ID . '` = :partnerId) AND
                `w`.`' . self::STATUS . '` IN("accept", "wait")' . $filterMode . '
            ORDER BY `w`.`' . self::VIEWED . '`, `w`.`' . self::STATUS . '` DESC
            LIMIT :first, :limit';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('userId' => $partnerId, 'partnerId' => $partnerId, 'first' => $first, 'limit' => (int)$limit));
    }

    public function findWinkListByStatus( $partnerId, $first, $limit, $status, $eventParams )
    {
        if ( empty($partnerId) )
        {
            return array();
        }

        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '` as `winks_w` ' . $eventParams['join'] . '
            WHERE  ' . $eventParams['where'] . ' AND (`winks_w`.`' . self::PARTNER_ID . '` = :partnerId) AND
                `winks_w`.`' . self::STATUS . '` = :status
            ORDER BY `winks_w`.`' . self::TIMESTAMP . '` DESC
            LIMIT :first, :limit';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('status' => $status, 'partnerId' => $partnerId, 'first' => $first, 'limit' => (int)$limit));
    }

    public function markViewedByIds( array $winksIds )
    {
        if ( count($winksIds) === 0 )
        {
            return 0;
        }
        
        $sql = 'UPDATE `' . $this->getTableName() . '`
            SET `' . self::VIEWED . '` = 1
            WHERE `id` IN (' . implode(',', array_map('intval', array_unique($winksIds))) . ')';
        
        return $this->dbo->query($sql);
    }
    
    public function findWinkByUserIdAndPartnerId( $userId, $partnerId )
    {
        if ( empty($userId) || empty($partnerId) )
        {
            return NULL;
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId AND `' . self::PARTNER_ID . '` = :partnerId
            LIMIT 1';

        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array('userId' => $userId, 'partnerId' => $partnerId));
    }
    
    public function findExpiredDate( $timestamp )
    {
        $example = new OW_Example();
        $example->andFieldLessOrEqual(self::TIMESTAMP, $timestamp);
        
        return $this->findListByExample($example);
    }
    
    public function deleteWinkByUserId( $userId )
    {
        if ( empty($userId) )
        {
            return FALSE;
        }
        
        $sql = 'DELETE FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId OR `' . self::PARTNER_ID . '` = :partnerId';
        
        return $this->dbo->delete($sql, array('userId' => $userId, 'partnerId' => $userId));
    }
    
    public function isCompleted( $userId, $partnerId )
    {
        if ( empty($userId) || empty($partnerId) )
        {
            return FALSE;
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE (`' . self::USER_ID . '` = :userId AND `' . self::PARTNER_ID . '` = :partnerId) OR
                (`' . self::PARTNER_ID . '` = :userId AND `' . self::USER_ID . '` = :partnerId)
            LIMIT 1';
        
        $wink = $this->dbo->queryForObject($sql, $this->getDtoClassName(), array('userId' => $userId, 'partnerId' => $partnerId));
        
        return !empty($wink) && (bool)$wink->getWinkback();
    }
    
    public function setStatusByUserId( $userId, $status = self::STATUS_IGNORE )
    {
        if ( empty($userId) )
        {
            return FALSE;
        }
        
        if ( !in_array($status, array(self::STATUS_ACCEPT, self::STATUS_IGNORE, self::STATUS_WAIT)) )
        {
            $status = self::STATUS_IGNORE;
        }
        
        $sql = 'UPDATE `' . $this->getTableName() . '`
            SET `' . self::STATUS . '` = :status
            WHERE `' . self::USER_ID . '` = :userId OR `' . self::PARTNER_ID . '` = :partnerId';
        
        return $this->dbo->update($sql, array('status' => $status, 'userId' => $userId, 'partnerId' => $userId));
    }

    private function parseEventParams( $eventParams )
    {
        $join = ( isset($eventParams['join']) ) ? $eventParams['join'] : '';
        $where = ( isset($eventParams['where']) ) ? $eventParams['where'] : '1';
        $order = ( isset($eventParams['order']) ) ? $eventParams['order'] : '';

        return array($join, $where, $order);
    }
}
