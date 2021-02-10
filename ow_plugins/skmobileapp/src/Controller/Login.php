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

class Login extends Base
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

        // login
        $controllers->post('/', function(Request $request) use ($app) {
            $response = [
                'success' => false,
                'error' => 'Invalid credentials',
            ];

            try {
                $vars = json_decode($request->getContent(), true);

                if (!empty($vars['username']) && !empty($vars['password'])) {
                    $user = $app['users']->loadUserByUsername($vars['username']);

                    if ($app['security.encoder.skadate']->
                            isPasswordValid($user->getPassword(), $this->userService->hashPassword($vars['password']), '')) {

                        $userDto = $this->userService->findByUsername($user->getUsername());

                        $this->service->loginEvents($userDto->getId());

                        $response = [
                            'success' => true,
                            'token' => $app['security.jwt.encoder']->encode(
                                $this->service->getUserDataForToken($userDto->getId())
                            )
                        ];
                    }
                }
            }
            catch (UsernameNotFoundException $e) {}

            return $app->json($response);
        });

        return $controllers;
    }
}
