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
class SKADATE_BOL_SpeedmatchRelation extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $oppUserId;

    /**
     * @var int
     */
    public $status = 0;

    /**
     * @var int
     */
    public $addTimestamp;
}
