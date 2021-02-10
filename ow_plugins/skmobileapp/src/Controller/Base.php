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
namespace Skadate\Mobile\Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application as SilexApplication;
use SKMOBILEAPP_BOL_Service;
use BOL_UserService;
use BOL_AuthorizationService;

abstract class Base implements ControllerProviderInterface
{
    /**
     * Lang prefix
     */
    const LANG_PREFIX = 'skmobileapp';

    /**
     * Username question name
     */
    const USERNAME_QUESTION_NAME = 'username';

    /**
     * Email question name
     */
    const EMAIL_QUESTION_NAME = 'email';

    /**
     * Email question name
     */
    const USER_ACCOUNT_QUESTION_NAME = 'accountType';

    /**
     * Service
     *
     * @param SKMOBILEAPP_BOL_Service
     */
    protected $service;

    /**
     * User service
     *
     * @param BOL_UserService
     */
    protected $userService;

    /**
     * Auth service
     *
     * @param BOL_AuthorizationService
     */
    protected $authService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->service = SKMOBILEAPP_BOL_Service::getInstance();
        $this->userService = BOL_UserService::getInstance();
        $this->authService = BOL_AuthorizationService::getInstance();
    }
}
