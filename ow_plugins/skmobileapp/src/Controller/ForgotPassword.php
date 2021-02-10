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
use LogicException;
use OW;

class ForgotPassword extends Base
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

        // send email
        $controllers->post('/', function (Request $request) use ($app) {
            $data = json_decode($request->getContent(), true);

            try {
                $this->userService->processResetForm($data);
            }
            catch (LogicException $e) {
                return $app->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            return $app->json([
                'success' => true,
                'message' => ''
            ]);
        });

        // reset password
        $controllers->put('/{code}/', function ($code, Request $request) use ($app) {
            $data = json_decode($request->getContent(), true);
            $language = OW::getLanguage();

            try {
                $resetCode = $this->userService->findResetPasswordByCode($code);

                if ($resetCode === null) {
                    throw new LogicException($language->text(self::LANG_PREFIX, 'forgot_password_code_invalid'));
                }

                $user = $this->userService->findUserById($resetCode->getUserId());

                if ($user === null) {
                    return $app->json([
                        'success' => false,
                        'message' => $language->text('base', 'forgot_password_no_user_error_message')
                    ]);
                }
                else if ( $user->email != $data['email']) {
                    return $app->json([
                        'success' => false,
                        'message' => $language->text(self::LANG_PREFIX, 'forgot_password_invalid_email')
                    ]);
                }

                $data['repeatPassword'] = $data['password'];
                $this->userService->processResetPasswordForm($data, $user, $resetCode);
            }
            catch (LogicException $e) {
                return $app->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            return $app->json([
                'success' => true,
                'message' => ''
            ]);
        });

        return $controllers;
    }
}
