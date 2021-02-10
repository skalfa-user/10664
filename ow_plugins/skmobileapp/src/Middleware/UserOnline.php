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
namespace Skadate\Mobile\Middleware;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;
use BOL_UserService;
use OW;

class UserOnline extends Base
{

    /**
     * Get middleware
     *
     * @return mixed
     */
    public function getMiddleware()
    {
        return function (Request $request)  {
            $userId = $this->app['users']->getLoggedUserId();

            if ($request->getMethod() != Request::METHOD_OPTIONS && $userId) {
                BOL_UserService::getInstance()->updateActivityStamp($userId, OW::getApplication()->getContext());
            }
        };
    }
}
