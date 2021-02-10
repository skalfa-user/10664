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
 * Data Access Object for `usercredits_pack` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.user_credits.bol
 * @since 1.0
 */
class USERCREDITS_BOL_PackDao extends OW_BaseDao
{
    /**
     * Plugin key
     */
    CONST PLUGIN_KEY = 'usercredits';

    /**
     * Entity key
     */
    CONST ENTITY_KEY = 'user_credits_pack';

    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_PackDao
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
     * @return USERCREDITS_BOL_PackDao
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
        return 'USERCREDITS_BOL_Pack';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'usercredits_pack';
    }
    
    /**
     * Returns list of packs for account type
     *
     * @param $accountTypeId
     * @return array
     */
    public function getAllPacks( $accountTypeId = null )
    {
        $example = new OW_Example();
        if ( $accountTypeId )
        {
            $example->andFieldEqual('accountTypeId', $accountTypeId);
        }
        $example->setOrder('`price` ASC');
        
        return $this->findListByExample($example);
    }

    /**
     * Returns packs history count
     *
     * @return integer
     */
    public function findPacksHistoryCount()
    {
        $billingDao = BOL_BillingSaleDao::getInstance();

        $sql = '
            SELECT
                COUNT(*) AS `logsCount`
            FROM
                `' . $billingDao->getTableName() . '` AS `a`
            INNER JOIN
                `' . $this->getTableName() . '` AS `b`
            ON
                `b`.`id` = a.`entityId`
            WHERE
                `a`.`pluginKey` = :pluginKey
                    AND
                `a`.`entityKey` = :entityKey
                    AND
                `a`.`status` = :status';

        return $this->dbo->queryForColumn($sql, array(
            'pluginKey' => self::PLUGIN_KEY,
            'entityKey' => self::ENTITY_KEY,
            'status' => BOL_BillingSaleDao::STATUS_DELIVERED
        ));
    }

    /**
     * Returns packs history
     *
     * @param integer $page
     * @param integer $limit
     * @return array
     */
    public function findPacksHistory( $page, $limit )
    {
        $billingDao = BOL_BillingSaleDao::getInstance();

        $start = ($page - 1) * $limit;
        $sql = '
            SELECT
                  `a`.`id`,
                  `a`.`userId`,
                  `a`.`entityDescription`,
                  ROUND(`a`.`price`) AS `price`,
                  `a`.`currency`,
                  `a`.`timeStamp`
            FROM
                `' . $billingDao->getTableName() . '` AS `a`
            INNER JOIN
                `' . $this->getTableName() . '` AS `b`
            ON
                `b`.`id` = a.`entityId`
            WHERE
                `a`.`pluginKey` = :pluginKey
                    AND
                `a`.`entityKey` = :entityKey
                    AND
                `a`.`status` = :status
            ORDER BY
                `a`.`timeStamp` DESC
            LIMIT
                :start, :limit
        ';

        return $this->dbo->queryForList($sql, array(
            'pluginKey' => self::PLUGIN_KEY,
            'entityKey' => self::ENTITY_KEY,
            'status' => BOL_BillingSaleDao::STATUS_DELIVERED,
            'start' => $start,
            'limit' => $limit
        ));
    }
}