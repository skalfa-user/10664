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

class SKMOBILEAPP_BOL_Device extends OW_Entity
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $userId;

    /**
     * @var string
     */
    public $deviceUuid;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $platform;

    /**
     * @var integer
     */
    public $activityTime;

    /**
     * @var string
     */
    public $language;
}
