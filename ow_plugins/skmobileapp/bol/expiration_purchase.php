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
class SKMOBILEAPP_BOL_ExpirationPurchase extends OW_Entity
{
    /**
     * @var int
     */
    public $membershipId;

    /**
     * @var int
     */
    public $typeId;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $expirationTime;

    /**
     * @var int
     */
    public $counter;
}