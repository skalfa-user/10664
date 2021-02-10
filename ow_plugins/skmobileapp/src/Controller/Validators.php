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
use UTIL_Validator;
use BOL_EmailVerifyService;

class Validators extends Base
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

        // user email validator
        $controllers->post('/user-email/', function(Request $request) use ($app) {
            $vars = json_decode($request->getContent(), true);

            $email   = !empty($vars['email']) ? $vars['email'] : null;
            $user    = !empty($vars['user'])  ? $vars['user']  : null;
            $userDto = null;

            // get user info
            if ($user) {
                // get user info
                $userDto = $this->userService->findUserForStandardAuth($user);
            }

            // both old and new user emails are equal
            if ($userDto && $userDto->email == $email) {
                return $app->json([
                    'valid' => true
                ]);
            }
            else if ($email && !$this->userService->isExistEmail($email)) {
                return $app->json([
                    'valid' => true
                ]);
            }

            return $app->json([
                'valid' => false
            ]);
        });

        // user name validator
        $controllers->post('/user-name/', function(Request $request) use ($app) {
            $vars = json_decode($request->getContent(), true);

            $userName = !empty($vars['userName']) ? $vars['userName'] : null;

            if (preg_match(UTIL_Validator::USER_NAME_PATTERN, $userName)) {
                $oldUserName = !empty($vars['oldUserName']) ? $vars['oldUserName'] : null;
                $userDto = null;

                // get user info
                if ($oldUserName) {
                    // get user info
                    $userDto = $this->userService->findUserForStandardAuth($oldUserName);
                }

                // both old and new user names are equal
                if ($userDto && $userDto->username == $userName) {
                    return $app->json([
                        'valid' => true
                    ]);
                } else if ($userName && !$this->userService->isExistUserName($userName)
                    && !$this->userService->isRestrictedUsername($userName)
                ) {

                    return $app->json([
                        'valid' => true
                    ]);
                }
            }

            return $app->json([
                'valid' => false
            ]);
        });

        // forgot password code validator
        $controllers->post('/forgot-password-code/', function(Request $request) use ($app) {
            $vars = json_decode($request->getContent(), true);
            if (!empty($vars['code']) && $this->userService->findResetPasswordByCode($vars['code']) !== null) {
                return $app->json([
                    'valid' => true
                ]);
            }

            return $app->json([
                'valid' => false
            ]);
        });

        // verify email validator
        $controllers->post('/verify-email-code/', function(Request $request) use ($app) {
            $emailVerifyService = BOL_EmailVerifyService::getInstance();
            $vars = json_decode($request->getContent(), true);
            if (!empty($vars['code']) && $emailVerifyService->verifyEmailCode($vars['code'], false)['isValid']) {
                return $app->json([
                    'valid' => true
                ]);
            }

            return $app->json([
                'valid' => false
            ]);
        });

        $controllers->post('/verify-sms-code/', function(Request $request) use ($app) {

            $vars = json_decode($request->getContent(), true);

            $isValid = false;

            if ( \OW::getPluginManager()->isPluginActive('smsverification') ) {
                $service = \SMSVERIFICATION_BOL_Service::getInstance();

                $userId = intval($vars['userId']);
                $verificationCode = $vars['code'];

                $isValid = $service->checkCode($userId, $verificationCode);

                if( $isValid )
                {
                    $service->setUserAsAutorized($userId);
                }
            }

            return $app->json([
                'valid' => $isValid
            ]);
        });


        return $controllers;
    }
}
