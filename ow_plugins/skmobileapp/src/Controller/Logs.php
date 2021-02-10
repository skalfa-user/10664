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

use Symfony\Component\HttpFoundation\Request;
use Silex\Application as SilexApplication;
use OW;

class Logs extends Base
{
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

        // create log
        $controllers->post('/', function (SilexApplication $app, Request $request) {
            $vars = json_decode($request->getContent(), true);

            $logger = OW::getLogger('skmobileapp');
            $logger->addEntry(json_encode($vars), 'exception');
            $logger->writeLog();

            return $app->json($vars);
        });

        return $controllers;
    }
}
