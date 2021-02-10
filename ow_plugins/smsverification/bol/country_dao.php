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
 * Data Access Object for `ow_smsverification_country_phone_code` table.
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.smsverification.bol
 * @since 1.7.6
 */
class SMSVERIFICATION_BOL_CountryDao extends OW_BaseDao
{
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var SMSVERIFICATION_BOL_CountryDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return SMSVERIFICATION_BOL_CountryDao
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
        return 'SMSVERIFICATION_BOL_Country';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'smsverification_country_phone_code';
    }

    /**
     * @return array
     */
    public function getCountries()
    {        
        $sql = "SELECT DISTINCT(`phoneCode`), `title` FROM `".$this->getTableName()."` 
            ORDER BY `title` ASC";
        
        return $this->dbo->queryForList($sql);
    }
    
}