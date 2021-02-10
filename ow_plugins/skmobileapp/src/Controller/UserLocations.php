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

class UserLocations extends Base
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

        // update location
        $controllers->put('/me/', function(Request $request) use ($app) {
            $loggedUserId = $app['users']->getLoggedUserId();
            $data = json_decode($request->getContent(), true);

            return $app->json($this->service->
                updateUserLocation($loggedUserId, $data['latitude'], $data['longitude']));
        });

        return $controllers;
    }
}
