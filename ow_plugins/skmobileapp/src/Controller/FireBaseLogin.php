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
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FIREBASEAUTH_BOL_Service;
use OW;

class FireBaseLogin extends Base
{
    /**
     * Is plugin active
     *
     * @var bool
     */
    protected $isPluginActive = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('firebaseauth');
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

        // login
        $controllers->post('/login/', function(Request $request) use ($app) {
            if ($this->isPluginActive) {
                $response = [
                    'isSuccess' => false
                ];

                $vars = json_decode($request->getContent(), true);

                if (empty($vars['providerId']) || empty($vars['uid'])) {
                    throw new BadRequestHttpException('Some important params are missing');
                }

                try {
                    list($action, $userId) = FIREBASEAUTH_BOL_Service::getInstance()->
                            authenticateUser($vars['providerId'], $vars['uid'], $vars['displayName'], $vars['email'], $vars['photoURL']);

                    $response['isSuccess'] = true;
                    $response['token'] = $app['security.jwt.encoder']->encode($this->service->getUserDataForToken($userId));
                    $response['action'] = $action;

                    return $app->json($response);
                }
                catch (UsernameNotFoundException $e) {}

                return $app->json($response);
            }

            throw new BadRequestHttpException('Firebase auth plugin is not activated');
        });

        return $controllers;
    }
}
