<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */
class SKMOBILEAPP_BOL_InappsPurchaseDao extends OW_BaseDao
{
    use OW_Singleton;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets DTO class name
     *
     * @return string
     */
    public function getDtoClassName()
    {
        return 'SKMOBILEAPP_BOL_InappsPurchase';
    }

    /**
     * Gets table name
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'skmobileapp_inapps_purchase';
    }

    public function findByMembershipId( $membershipId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('membershipId', $membershipId);

        return $this->findObjectByExample($example);
    }
}