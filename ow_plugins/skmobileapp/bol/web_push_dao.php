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

class SKMOBILEAPP_BOL_WebPushDao extends OW_BaseDao
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
     * Get dto class name
     * 
     * @return string
     */
    public function getDtoClassName()
    {
        return 'SKMOBILEAPP_BOL_WebPush';
    }

    /**
     * Get table name
     * 
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'skmobileapp_web_push';
    }

    /**
     * Find first message
     * 
     * @param integer $userId
     * @param integer $deviceId
     * @return SKMOBILEAPP_BOL_WebPush
     */
    public function findFirstMessage($userId, $deviceId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('deviceId', $deviceId);
        $example->setOrder('`id` ASC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * Clean expired messages
     * 
     * @return void
     */
    public function cleanExpiredMessages()
    {
        $example = new OW_Example();
        $example->andFieldLessThan('expirationTime', time());

        $this->deleteByExample($example);
    }
}
