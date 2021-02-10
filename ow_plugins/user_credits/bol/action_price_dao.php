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
 * Data Access Object for `usercredits_action_price` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.user_credits.bol
 * @since 1.6.1
 */
class USERCREDITS_BOL_ActionPriceDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_ActionPriceDao
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
     * @return USERCREDITS_BOL_ActionPriceDao
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
        return 'USERCREDITS_BOL_ActionPrice';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'usercredits_action_price';
    }

    /**
     * @param $actionId
     * @param $accTypeId
     * @return mixed
     */
    public function findActionPrice( $actionId, $accTypeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('actionId', $actionId);
        $example->andFieldEqual('accountTypeId', $accTypeId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param $actionId
     * @param $accTypeIdList
     * @return array
     */
    public function findActionPriceForAccountTypeList( $actionId, $accTypeIdList )
    {
        $example = new OW_Example();
        $example->andFieldEqual('actionId', $actionId);
        $example->andFieldInArray('accountTypeId', $accTypeIdList);

        return $this->findListByExample($example);
    }

    /**
     * @param $accountTypeId
     */
    public function deleteByAccountType( $accountTypeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('accountTypeId', $accountTypeId);

        $this->deleteByExample($example);
    }

    /**
     * @param $actionId
     */
    public function deleteByActionId( $actionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('actionId', $actionId);

        $this->deleteByExample($example);
    }
}