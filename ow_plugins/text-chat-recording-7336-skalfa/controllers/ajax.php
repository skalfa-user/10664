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

/**
 * Class SKTEXTCR_CTRL_Ajax
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_CTRL_Ajax extends OW_ActionController
{
    /**
     * Response success
     */
    const RESPONSE_SUCCESS = 'success';

    /**
     * Response error
     */
    const RESPONSE_ERROR = 'error';

    /**
     * Service
     *
     * @var SKTEXTCR_BOL_Service
     */
    protected $service;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = SKTEXTCR_BOL_Service::getInstance();
    }
}
