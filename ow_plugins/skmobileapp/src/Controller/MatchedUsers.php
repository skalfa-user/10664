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

class MatchedUsers extends Base
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

        // update matched user
        $controllers->put('/{id}/', function (Request $request, SilexApplication $app, $id) {
            $vars = json_decode($request->getContent(), true);
            $loggedUserId = $app['users']->getLoggedUserId();

            if (isset($vars['isRead']) || isset($vars['isNew'])) {
                // update user
                $matchedUser = $this->service->findUserMatchById($id);

                if ($matchedUser && $matchedUser->userId == $loggedUserId) {
                    $matchedUser->new = isset($vars['isNew'])  
                        ? (int) $vars['isNew'] 
                        : $matchedUser->new;

                    $matchedUser->read = isset($vars['isRead']) 
                        ? (int) $vars['isRead'] 
                        : $matchedUser->read;

                    $this->service->saveUserMatch($matchedUser);

                    return $app->json([], 204);
                }

                throw new BadRequestHttpException('Matched user cannot be updated');
            }

            throw new BadRequestHttpException('Both isRead and isNew params are missing');
        });

        return $controllers;
    }
}
