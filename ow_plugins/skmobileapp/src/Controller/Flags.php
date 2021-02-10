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
use BOL_FlagService;

class Flags extends Base
{
    /**
     * Flag service
     *
     * @var BOL_FlagService
     */
    protected $flagService;

    /**
     * Users constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->flagService = BOL_FlagService::getInstance();
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

        // create a flag
        $controllers->post('/', function (Request $request, SilexApplication $app) {
            $vars = json_decode($request->getContent(), true);
            $loggedUserId = $app['users']->getLoggedUserId();

            if (!empty($vars['identityId']) && !empty($vars['entityType']) && !empty($vars['reason'])) {
                $this->flagService->addFlag($vars['entityType'], $vars['identityId'], $vars['reason'], $loggedUserId);

                return $app->json([], 204);
            }

            throw new BadRequestHttpException('identityId or entityType or reason is missing');
        });

        return $controllers;
    }
}
