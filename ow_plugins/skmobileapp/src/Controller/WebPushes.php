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
use SKMOBILEAPP_BOL_WebPushService;
use SKMOBILEAPP_BOL_DeviceService;
use SKMOBILEAPP_BOL_Service;

class WebPushes extends Base
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

        // get a message
        $controllers->get('/{id}/', function(SilexApplication $app, $id) {
            $id = urldecode($id);

            // find a registered device
            $device = SKMOBILEAPP_BOL_DeviceService::getInstance()->findByToken($id);

            if ($device) {
                $webPushService = SKMOBILEAPP_BOL_WebPushService::getInstance();

                // find a first message
                $message = $webPushService->findFirstMessage($device->userId, $device->id);

                if ($message) {
                    $webPushService->deleteMessage($message->id);

                    return $app->json([
                        'id' => $message->id,
                        'title' => $message->title,
                        'message' => $message->message,
                        'url' => SKMOBILEAPP_BOL_Service::getInstance()->getPwaUrl(),
                        'icon' => SKMOBILEAPP_BOL_Service::getInstance()->getPwaIcon(),
                        'params' => $message->pushParams
                            ? json_decode($message->pushParams)
                            : []
                    ]);
                }
            }

            return $app->json([]);
        });

        return $controllers;
    }
}
