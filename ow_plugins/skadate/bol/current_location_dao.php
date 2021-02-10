<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow_plugins.skadate.bol
 * @since 1.6.1
 */
class SKADATE_BOL_CurrentLocationDao extends OW_BaseDao
{
    /**
     * Class instance
     *
     * @var SKADATE_BOL_CurrentLocationDao
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
     * Returns class instance
     *
     * @return SKADATE_BOL_CurrentLocationDao
     */
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
        return OW_DB_PREFIX . 'skadate_current_location';
    }

    public function getDtoClassName()
    {
        return 'SKADATE_BOL_CurrentLocation';
    }

    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);
    }
}
