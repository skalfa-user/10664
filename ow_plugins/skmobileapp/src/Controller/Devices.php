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
use SKMOBILEAPP_BOL_DeviceService;

class Devices extends Base
{
    /**
     * Device service
     *
     * @param SKMOBILEAPP_BOL_DeviceService
     */
    protected $deviceService;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->deviceService = SKMOBILEAPP_BOL_DeviceService::getInstance();
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

        // register devices
        $controllers->post('/', function (Request $request) use ($app) {
            $data = json_decode($request->getContent(), true);
            $device = $this->deviceService->findByToken($data['token']); // do we have already registered the token

            // register a new device
            if (!$device) {
                $this->deviceService->updateDevice($app['users']->getLoggedUserId(), $data);
            }

            return $app->json([], 204);
        });

        return $controllers;
    }
}
