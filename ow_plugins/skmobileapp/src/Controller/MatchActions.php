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

class MatchActions extends Base
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

        // delete match
        $controllers->delete('/user/{id}/', function (SilexApplication $app, $id) {
            $loggedUserId = $app['users']->getLoggedUserId();
            $action = $this->service->findUserMatch($loggedUserId, $id);

            if ($action && $action->userId == $loggedUserId) { // check the ownership
                $this->service->deleteUserMatch($action->id);
            }

            return $app->json([], 204); // ok
        });

        // create match
        $controllers->post('/user/', function (Request $request, SilexApplication $app) {
            $vars = json_decode($request->getContent(), true);
            $loggedUserId = $app['users']->getLoggedUserId();

            if (!empty($vars['userId']) && !empty($vars['type'])) {
                $result = $this->service->createUserMatchAction($loggedUserId, $vars['userId'], $vars['type']);

                return $app->json([
                    'id' => (int) $result->id,
                    'type' => $result->type,
                    'isMutual' => boolval($result->mutual),
                    'createStamp' => (int) $result->createStamp,
                    'isRead' => boolval($result->read),
                    'isNew' => boolval($result->new),
                    'userId' => (int) $result->recipientId,
                    'user' => [
                        'id' => (int) $result->recipientId
                    ]
                ]);
            }

            throw new BadRequestHttpException('userId or type are missing');
        });

        return $controllers;
    }
}
