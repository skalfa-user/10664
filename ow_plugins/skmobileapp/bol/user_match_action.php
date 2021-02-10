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

class SKMOBILEAPP_BOL_UserMatchAction extends OW_Entity
{
    /**
     * User id
     *
     * @var integer
     */
    public $userId;

    /**
     * Recipient id
     *
     * @var integer
     */
    public $recipientId;

    /**
     * Type
     *
     * @var string
     */
    public $type;

    /**
     * Create stamp
     *
     * @var integer
     */
    public $createStamp;

    /**
     * Expiration stamp
     *
     * @var integer
     */
    public $expirationStamp;

    /**
     * Mutual
     *
     * @var integer
     */
    public $mutual = 0;

    /**
     * Read
     *
     * @var integer
     */
    public $read = 0;

    /**
     * New
     *
     * @var integer
     */
    public $new = 0;
}
