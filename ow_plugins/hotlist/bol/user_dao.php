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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.hotlist.bol
 * @since 1.0
 */
class HOTLIST_BOL_UserDao extends OW_BaseDao
{

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Class instance
     *
     * @var HOTLIST_BOL_UserDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MessageDao
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
     *
     */
    public function getDtoClassName()
    {
        return 'HOTLIST_BOL_User';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'hotlist_user';
    }

    public function clearExpiredUsers()
    {
        $query = "DELETE FROM `" . $this->getTableName() . "` WHERE `expiration_timestamp` <= ".time();
        $this->dbo->query($query);
    }
    
    public function findExpiredUsers()
    {
        $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE `expiration_timestamp` <= ".time();

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }
    
    public function findHotList( $start = 0, $count = null, $excludeList = array(), array $eventParams = array() )
    {
        $join = ( isset($eventParams['join']) ) ? $eventParams['join'] : '';
        $where = ( isset($eventParams['where']) ) ? $eventParams['where'] : '1';

        $limit = '';

        $params = [];

        if ( $count !== null )
        {
            $params = ['first' => $start, 'count' => $count];

            $limit = ' LIMIT :first, :count ';
        }
        
        if ( !empty($excludeList) && is_array($excludeList) )
        {
            $where .= ' AND `hu`.`userId` NOT IN( ' . $this->dbo->mergeInClause($excludeList). ' )';
        }

        $sql = "SELECT `hu`.* FROM `" . $this->getTableName() . "` as `hu` 
                " . $join . "
                WHERE " . $where . " ORDER BY `hu`.`timestamp` DESC " . $limit;

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $params);
    }

    public function deleteByUserId($userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);
    }

    public function findUserById($userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findIdByExample($example);
    }
}
