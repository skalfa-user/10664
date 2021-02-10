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
use SKMOBILEAPP_BOL_EmailNotificationsService;
use OW;

class EmailNotifications extends Base
{
    /**
     * Allowed settings
     */
    const ALLOWED_SETTINGS = [
        'mailbox-new_chat_message',
        'skmobileapp-new_match_message'
    ];

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

        // update settings
        $controllers->put('/me/', function(SilexApplication $app, Request $request) {
            if (OW::getPluginManager()->isPluginActive('notifications')) {
                $data = json_decode($request->getContent(), true);
                $loggedUserId = $app['users']->getLoggedUserId();
                $processedSettings = [];

                // process settings
                foreach($data as $setting) {
                    if (!in_array($setting['name'], self::ALLOWED_SETTINGS)) {
                        throw new AccessDeniedHttpException;
                    }

                    $processedSettings[$setting['name']] = (int) $setting['value'];
                }

                if ($processedSettings) {
                    SKMOBILEAPP_BOL_EmailNotificationsService::
                        getInstance()->saveSettings($loggedUserId, $processedSettings);

                    return $app->json([], 204);
                }

                throw new BadRequestHttpException('Empty setting list');
            }

            throw new BadRequestHttpException('Notifications plugin is not activated');
        });

        // get all available email notifications questions
        $controllers->get('/questions/', function(SilexApplication $app) {
            if (OW::getPluginManager()->isPluginActive('notifications')) {
                $loggedUserId = $app['users']->getLoggedUserId();
                $settingsList = SKMOBILEAPP_BOL_EmailNotificationsService::
                        getInstance()->findSettingsQuestions($loggedUserId, self::ALLOWED_SETTINGS);

                return $app->json($settingsList);
            }

            throw new BadRequestHttpException('Notifications plugin is not activated');
        });

        return $controllers;
    }
}
