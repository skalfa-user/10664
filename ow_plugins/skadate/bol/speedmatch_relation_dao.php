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
class SKADATE_BOL_SpeedmatchRelationDao extends OW_BaseDao
{
    /**
     * Class instance
     *
     * @var SKADATE_BOL_SpeedmatchRelationDao
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
     * @return SKADATE_BOL_SpeedmatchRelationDao
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
        return OW_DB_PREFIX . 'skadate_speedmatch_relation';
    }

    public function getDtoClassName()
    {
        return 'SKADATE_BOL_SpeedmatchRelation';
    }

    public function findRelation( $userId, $oppUserId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('oppUserId', $oppUserId);

        return $this->findObjectByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $this->deleteByExample($example);

        $example = new OW_Example();
        $example->andFieldEqual('oppUserId', $userId);
        $this->deleteByExample($example);

        return true;
    }
}
