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
namespace Skadate\Mobile\ServerEventsChannel;

use SKMOBILEAPP_BOL_Service;

abstract class Base implements IChannel
{
    /**
     * Prev values
     *
     * @var array|null
     */
    protected $prevValues = null;

    /**
     * Service
     *
     * @var SKMOBILEAPP_BOL_Service
     */
    protected $service;

    /**
     * Configs constructor.
     */
    public function __construct() {
        $this->service = SKMOBILEAPP_BOL_Service::getInstance();
    }
}

