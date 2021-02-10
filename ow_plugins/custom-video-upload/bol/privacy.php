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

class CVIDEOUPLOAD_BOL_Privacy extends OW_Entity
{
    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var int
     */
    public $friendId = 0;

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getFriendId()
    {
        return $this->friendId;
    }

    /**
     * @param int $friendId
     */
    public function setFriendId($friendId)
    {
        $this->friendId = $friendId;
    }
}
