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
 * Data Access Object for `usercredits_log` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.user_credits.bol
 * @since 1.0
 */
class USERCREDITS_BOL_LogDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_LogDao
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return USERCREDITS_BOL_LogDao
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
        return 'USERCREDITS_BOL_Log';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'usercredits_log';
    }
    
    /**
     * Finds user last action log
     * 
     * @param int $userId
     * @param int $actionId
     * @return USERCREDITS_BOL_Log
     */
    public function findLast( $userId, $actionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('actionId', $actionId);
        $example->setOrder('`logTimestamp` DESC');
        $example->setLimitClause(0, 1);
        
        return $this->findObjectByExample($example);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findListForUser( $userId, $page, $limit )
    {
        $actionDao = USERCREDITS_BOL_ActionDao::getInstance();

        $start = ($page - 1) * $limit;
        $sql =
            'SELECT l.id,l.userId,l.actionId,l.amount,l.logTimestamp,l.additionalParams,l.groupKey, `a`.`pluginKey`, `a`.`actionKey` FROM `'.$this->getTableName().'` AS `l`
            INNER JOIN `'.$actionDao->getTableName().'` AS `a` ON (`a`.`id` = `l`.`actionId`)
            WHERE `l`.`userId` = :uid AND ( groupKey IS NULL OR groupKey = \'\' )

            UNION

            SELECT l.id,l.userId,l.actionId,SUM(l.amount) as `amount`, MAX(l.logTimestamp) as `logTimestamp`,l.additionalParams,l.groupKey, `a`.`pluginKey`, `a`.`actionKey` FROM `'.$this->getTableName().'` AS `l`
            INNER JOIN `'.$actionDao->getTableName().'` AS `a` ON (`a`.`id` = `l`.`actionId`)
            WHERE `l`.`userId` = :uid AND groupKey IS NOT NULL AND groupKey != \'\'
            GROUP BY l.groupKey, `a`.`pluginKey`, `a`.`actionKey`, l.userId

            ORDER BY 5 DESC
            LIMIT :start, :limit';

        return $this->dbo->queryForList($sql, array('uid' => $userId, 'start' => $start, 'limit' => $limit));
    }

    /**
     * @param $userId
     * @return int
     */
    public function countEntriesForUser( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->countByExample($example);
    }
    
    public function deleteUserCreditLogByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->deleteByExample($example);
    }
}