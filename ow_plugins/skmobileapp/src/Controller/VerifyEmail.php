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
use Exception;
use BOL_EmailVerifyService;
use OW;

class VerifyEmail extends Base
{
    /**
     * User service
     *
     * @param BOL_UserService
     */
    protected $emailVerifyService;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->emailVerifyService = BOL_EmailVerifyService::getInstance();
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

        // send email
        $controllers->post('/', function (Request $request) use ($app) {
            $data = json_decode($request->getContent(), true);
            $language = OW::getLanguage();

            try {
                $user = $this->userService->findByEmail($data['email']);

                $this->emailVerifyService->sendUserVerificationMail($user, false);
            }
            catch (Exception $e) {
                return $app->json([
                    'success' => false,
                    'message' => $language->text('skmobileapp', 'error_occurred')
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
