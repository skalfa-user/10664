<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CVIDEOUPLOAD_BOL_PrivacyDao extends OW_BaseDao
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
     * Get Dto class name
     *
     * @return string
     */
    public function getDtoClassName()
    {
        return 'CVIDEOUPLOAD_BOL_Privacy';
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'cvideoupload_privacy';
    }

    public function getFriendListByUserId( $userId )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter('privacy', 'userId', [
            'method' => __METHOD__
        ]);

        $join = ( isset($queryParts['join']) ) ? $queryParts['join'] : '';
        $where = ( isset($queryParts['where']) ) ? $queryParts['where'] : '1';


        $sql = "SELECT `privacy`.`friendId` FROM `" . $this->getTableName() . "` as `privacy`
                " . $join . "
                WHERE " . $where . " AND `privacy`.`userId` = :userId ";

        return $this->dbo->queryForColumnList($sql, ['userId' => $userId]);
    }
}
