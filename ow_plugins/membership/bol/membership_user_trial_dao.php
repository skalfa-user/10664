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
 * Data Access Object for `membership_user_trial` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.bol
 * @since 1.0
 */
class MEMBERSHIP_BOL_MembershipUserTrialDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var MEMBERSHIP_BOL_MembershipUserTrialDao
     */
    private static $classInstance;

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
     * @return MEMBERSHIP_BOL_MembershipUserTrialDao
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
        return 'MEMBERSHIP_BOL_MembershipUserTrial';
    }

    /**
     * @see OW_BaseDao::getTableName()
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'membership_user_trial';
    }

    /**
     * Finds user trial plan usage by user Id
     * 
     * @param int $userId
     * @return MEMBERSHIP_BOL_MembershipUserTrial
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * @param $userId
     * @return array
     */
    public function findListByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }

    public function deleteByPlanId( $planId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('planId', $planId);

        $this->deleteByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);
    }
}