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
 * Data Access Object for `membership_plan` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.bol
 * @since 1.0
 */
class MEMBERSHIP_BOL_MembershipPlanDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var MEMBERSHIP_BOL_MembershipPlanDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return MEMBERSHIP_BOL_MembershipPlanDao
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
        return 'MEMBERSHIP_BOL_MembershipPlan';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'membership_plan';
    }

    public function findPlanList( $exclude )
    {
        $example = new OW_Example();
        if ( !empty($exclude) )
        {
            $example->andFieldNotInArray('id', $exclude);
        }

        return $this->findListByExample($example);
    }

    /**
     * Finds the list of membership plans by membership type Id 
     * 
     * @param int $typeId
     * @return array of MEMBERSHIP_BOL_MembershipPlan
     */
    public function findPlanListByTypeId( $typeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('typeId', $typeId);

        $list = $this->findListByExample($example);

        foreach ( $list as $key => $period )
        {
            $period->price = floatval($period->price);
            $list[$key] = $period;
        }

        return $list;
    }

    /**
     * Deletes membership type plans
     * 
     * @param int $typeId
     * @return boolean
     */
    public function deletePlansByTypeId( $typeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('typeId', $typeId);

        $this->deleteByExample($example);

        return true;
    }
}