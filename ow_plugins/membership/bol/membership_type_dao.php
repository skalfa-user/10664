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
 * Data Access Object for `membership_type` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.membership.bol
 * @since 1.0
 */
class MEMBERSHIP_BOL_MembershipTypeDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var MEMBERSHIP_BOL_MembershipTypeDao
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
     * @return MEMBERSHIP_BOL_MembershipTypeDao
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
        return 'MEMBERSHIP_BOL_MembershipType';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'membership_type';
    }

    /**
     * Returns membership type list
     *
     * @param $accTypeId
     * @return array
     */
    public function getTypeList( $accTypeId )
    {
        $roleDao = BOL_AuthorizationRoleDao::getInstance();
        $accTypeCond = !empty($accTypeId) ? " WHERE `mt`.`accountTypeId` = " . $this->dbo->escapeString($accTypeId) : "";

        $query = "SELECT `mt`.*, `r`.`name` FROM `" . $this->getTableName() . "` AS `mt`
            LEFT JOIN `" . $roleDao->getTableName() . "` AS `r` ON(`mt`.`roleId`=`r`.`id`)"
            . $accTypeCond;

        return $this->dbo->queryForList($query);
    }

    /**
     * Returns membership type list
     *
     * @param $accTypeId
     * @return array
     */
    public function getAllTypeList( $accTypeId )
    {
        $roleDao = BOL_AuthorizationRoleDao::getInstance();
        $accTypeCond = !empty($accTypeId) ? " WHERE `mt`.`accountTypeId` = " . $this->dbo->escapeString($accTypeId) : "";

        $query = "SELECT `mt`.* FROM `" . $this->getTableName() . "` AS `mt`
            LEFT JOIN `" . $roleDao->getTableName() . "` AS `r` ON(`mt`.`roleId`=`r`.`id`) " . $accTypeCond . "
            GROUP BY `mt`.`roleId`
            ORDER BY `r`.`sortOrder` ASC";



        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }
    
    public function deleteByRoleId( $roleId )
    {
        $example = new OW_Example();
        
        $example->andFieldEqual('roleId', $roleId);
        
        $this->deleteByExample($example);
    }
    
    public function getTypeIdListByRoleId( $roleId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('roleId', $roleId);
        
        return $this->findIdListByExample($example);
    }
}