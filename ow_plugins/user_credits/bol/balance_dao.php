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
 * Data Access Object for `usercredits_balance` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.user_credits.bol
 * @since 1.0
 */
class USERCREDITS_BOL_BalanceDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_BalanceDao
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
     * @return USERCREDITS_BOL_BalanceDao
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
        return 'USERCREDITS_BOL_Balance';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'usercredits_balance';
    }
    
    /**
     * Finds user balance
     * 
     * @param int $userId
     * @return USERCREDITS_BOL_Balance
     */
    public function findByUserId( $userId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('userId', $userId);
    	
    	return $this->findObjectByExample($example);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getBalanceForUserList( $ids )
    {
        if ( !count($ids) )
        {
            return array();
        }

        $ids = array_unique($ids);
        $example = new OW_Example();
        $example->andFieldInArray('userId', $ids);

        return $this->findListByExample($example);
    }
    
    public function deleteUserCreditBalanceByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->deleteByExample($example);
    }
}