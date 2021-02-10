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

class CVIDEOUPLOAD_BOL_VideoDao extends OW_BaseDao
{
    use OW_Singleton;

    const VIDEO_STATUS_NOT_CONFIRMED = 'not_confirmed';
    const VIDEO_STATUS_IN_PROCESS = 'in_process';
    const VIDEO_STATUS_NOT_PROCESSED = 'not_processed';
    const VIDEO_STATUS_PROCESSED = 'processed';

    const VIDEO_AUTHORIZATION_APPROVAL = 'approval';
    const VIDEO_AUTHORIZATION_APPROVED = 'approved';
    const VIDEO_AUTHORIZATION_BLOCKED = 'blocked';

    const VIDEO_PRIVACY_EVERYBODY = 'everybody';
    const VIDEO_PRIVACY_ONLY_OWNER = 'only_owner';
    const VIDEO_PRIVACY_CERTAIN_USERS = 'certain_users';

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
        return 'CVIDEOUPLOAD_BOL_Video';
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'cvideoupload_video';
    }

    public function findAllUserVideos( $userId )
    {
        $example = new OW_Example;
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }

    public function findVideosForConverting( $limit )
    {
        $example = new OW_Example;
        $example->andFieldIsNotNull('userId');
        $example->andFieldEqual('status', self::VIDEO_STATUS_NOT_PROCESSED);
        $example->setLimitClause(0, $limit);
        $example->setOrder('timestamp');

        return $this->findListByExample($example);
    }

    public function findUsersVideoList($userIds, $isProcessed = true)
    {
        $example = new OW_Example;
        $example->andFieldInArray('userId', $userIds);

        if ( $isProcessed )
        {
            $example->andFieldEqual('status', self::VIDEO_STATUS_PROCESSED);
        }

        return $this->findListByExample($example);
    }

    public function findVideoByName($name, $isProcessed = false)
    {
        $example = new OW_Example;
        $example->andFieldEqual('fileName', $name);

        if ( $isProcessed )
        {
            $example->andFieldEqual('status', self::VIDEO_STATUS_PROCESSED);
        }

        return $this->findObjectByExample($example);
    }

    public function findUserVideo($userId, $isProcessed = true)
    {
        $example = new OW_Example;
        $example->andFieldEqual('userId', $userId);

        if ( $isProcessed )
        {
            $example->andFieldEqual('status', self::VIDEO_STATUS_PROCESSED);
        }

        return $this->findObjectByExample($example);
    }
}
