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

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use OW;
use USEARCH_BOL_Service;

class Tinder extends Users
{
    /**
     * Default users limit
     */
    const DEFAULT_USERS_LIMIT = 10;

    /**
     * Default distance
     */
    const DEFAULT_DISTANCE = 100;

    /**
     * Connect methods
     *
     * @param SilexApplication $app
     * @return mixed
     */
    public function connect(SilexApplication $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // get all users
        $controllers->get('/', function (Request $request, SilexApplication $app) {
            if (OW::getPluginManager()->isPluginActive('usearch')) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $relations = $request->query->get('with', []);
                $excludeIds = $request->query->get('excludeIds')
                    ? array_map('intval', explode(',', $request->query->get('excludeIds')))
                    : [];

                if (!$this->service->isPermissionAllowed($loggedUserId, 'base', 'search_users')) {
                    throw new AccessDeniedHttpException;
                }

                $this->authService->trackActionForUser($loggedUserId, 'base', 'search_users');

                $searchId = $this->service->
                        tinderSearchUsers($loggedUserId, self::DEFAULT_USERS_LIMIT, self::DEFAULT_DISTANCE, $excludeIds);

                $foundUsers = USEARCH_BOL_Service::getInstance()->
                        getSearchResultList($searchId, USEARCH_BOL_Service::LIST_ORDER_NEW, 0, self::DEFAULT_USERS_LIMIT);

                return $app->json($this->getFormattedUsersData($foundUsers, true, $relations, $loggedUserId));
            }

            throw new BadRequestHttpException('User search plugin is not activated');
        });

        return $controllers;
    }
}
