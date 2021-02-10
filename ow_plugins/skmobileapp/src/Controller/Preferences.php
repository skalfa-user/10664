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
use SKMOBILEAPP_BOL_PreferencesService;

class Preferences extends Base
{
    /**
     * Allowed preference sections
     */
    const ALLOWED_PREFERENCE_SECTIONS = [
        'skmobileapp_pushes'
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

        // update preferences
        $controllers->put('/me/', function(SilexApplication $app, Request $request) {
            $data = json_decode($request->getContent(), true);
            $loggedUserId = $app['users']->getLoggedUserId();
            $service = SKMOBILEAPP_BOL_PreferencesService::getInstance();
            $processedPreferences = [];

            // process preferences
            foreach($data as $preference) {
                $section = $service->findPreferenceSection($preference['name']);

                if (!$section || !in_array($section, self::ALLOWED_PREFERENCE_SECTIONS)) {
                    throw new BadRequestHttpException;
                }

                $processedPreferences[$preference['name']] = (bool) $preference['value'];
            }

            if ($processedPreferences) {
                $service->savePreferences($loggedUserId, $processedPreferences);

                return $app->json([], 204);
            }

            throw new BadRequestHttpException('Empty preference list');
        });

        // get all available preferences questions
        $controllers->get('/questions/{id}/', function(SilexApplication $app, $id) {
            if (!in_array($id, self::ALLOWED_PREFERENCE_SECTIONS)) {
                throw new BadRequestHttpException;
            }

            $loggedUserId = $app['users']->getLoggedUserId();
            $preferenceList = SKMOBILEAPP_BOL_PreferencesService::getInstance()->findPreferencesQuestions($loggedUserId, $id);

            return $app->json($preferenceList);
        });

        return $controllers;
    }
}
