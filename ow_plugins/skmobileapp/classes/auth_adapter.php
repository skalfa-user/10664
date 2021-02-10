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
class SKMOBILEAPP_CLASS_AuthAdapter extends OW_AuthAdapter
{
    /**
     * User id
     *
     * @var integer
     */
    protected $userId;

    /**
     * Constructor.
     *
     * @param integer $userId
     */
    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Authenticate
     *
     * @return OW_AuthResult
     */
    public function authenticate()
    {
        return new OW_AuthResult($this->userId ? OW_AuthResult::SUCCESS : OW_AuthResult::FAILURE, $this->userId);
    }
}