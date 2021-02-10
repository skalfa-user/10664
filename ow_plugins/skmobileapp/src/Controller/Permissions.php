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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use BOL_AuthorizationService;

class Permissions extends Base
{
    /**
     * Authorization service
     *
     * @var BOL_AuthorizationService
     */
    protected $authService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->authService = BOL_AuthorizationService::getInstance();
    }

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

        $controllers->post('/track-actions/', function (Request $request, SilexApplication $app) {
            $vars = json_decode($request->getContent(), true);
            $loggedUserId = $app['users']->getLoggedUserId();

            if (!empty($vars['groupName']) && !empty($vars['actionName'])) {
                return $app->json($this->authService->
                    trackActionForUser($loggedUserId, $vars['groupName'], $vars['actionName'], isset($vars['extra']) ? $vars['extra'] : []));
            }

            throw new BadRequestHttpException('groupName or actionName is missing');
        });

        return $controllers;
    }
}
