<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class OCSGUESTS_BOL_Guest extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $guestId;
    /**
     * @var int
     */
    public $viewed;
    /**
     * @var int
     */
    public $visitTimestamp;
}