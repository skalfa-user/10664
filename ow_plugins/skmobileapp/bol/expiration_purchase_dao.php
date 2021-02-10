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
class SKMOBILEAPP_BOL_ExpirationPurchaseDao extends OW_BaseDao
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
        return 'SKMOBILEAPP_BOL_ExpirationPurchase';
    }

    /**
     * Gets table name
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'skmobileapp_expiration_purchase';
    }

    public function findExpirationPurchase( $userId, $membershipId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('membershipId', $membershipId);

        return $this->findObjectByExample($example);
    }

    public function findExpiredSubscriptions($first, $count)
    {
        $example = new OW_Example();
        $example->andFieldLessOrEqual('expirationTime', time());
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }
}