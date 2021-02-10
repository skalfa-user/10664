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
use OW;
use SKMOBILEAPP_BOL_SmsverificationService;

class SMSVerification extends Base
{
    protected $isPluginActive = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('smsverification');
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

        $controllers->get('/countries/', function() use ($app) {

            if ($this->isPluginActive) {
                $service = \SMSVERIFICATION_BOL_Service::getInstance();

                return $app->json($service->getCountries());
            }

            throw new BadRequestHttpException('Sms verification plugin not activated');

        });

        $controllers->get('/phones/me/', function() use ($app) {

            if ($this->isPluginActive) {
                $service = SKMOBILEAPP_BOL_SmsverificationService::getInstance();
                /* @var SKMOBILEAPP_BOL_SmsverificationService $service */

                $userId = $app['users']->getLoggedUserId();

                $userData = $service->getUserDataByUserId($userId);

                return $app->json($userData);
            }

            throw new BadRequestHttpException('Sms verification plugin not activated');
        });

        $controllers->post('/sms/', function(Request $request) use ($app) {

            $vars = json_decode($request->getContent(), true);

            if ($this->isPluginActive) {

                $userId = $app['users']->getLoggedUserId();
                $service = SKMOBILEAPP_BOL_SmsverificationService::getInstance();

                /* @var SKMOBILEAPP_BOL_SmsverificationService $service */

                if ( isset($vars['countryCode']) && isset($vars['phoneNumber']) ) {

                    $result = $service->sendSms($userId, $vars['countryCode'], $vars['phoneNumber']);

                    return $app->json($result);
                }

                return $app->json([
                    'success' => false,
                    'message' => null
                ]);
            }

            throw new BadRequestHttpException('Sms verification plugin not activated');
        });

        return $controllers;
    }
}