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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use USERCREDITS_BOL_CreditsService;
use BOL_QuestionService;
use SKMOBILEAPP_BOL_CreditsService;
use SKMOBILEAPP_BOL_Service;
use OW;

class Credits extends Base
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

        // get credits info
        $controllers->get('/info/', function () use ($app) {
            if (OW::getPluginManager()->isPluginActive(SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY)) {
                $loggedUserId = $app['users']->getLoggedUserId();

                return $app->json(SKMOBILEAPP_BOL_CreditsService::getInstance()->getCreditsInfo($loggedUserId));
            }

            throw new BadRequestHttpException('User credits plugin is not activated');
        });

        // get all packs 
        $controllers->get('/', function () use ($app) {
            if  (OW::getPluginManager()->isPluginActive(SKMOBILEAPP_BOL_Service::USER_CREDITS_PLUGIN_KEY)) {
                $creditsService = USERCREDITS_BOL_CreditsService::getInstance();
                $loggedUserId = $app['users']->getLoggedUserId();
                $balance = $creditsService->getCreditsBalance($loggedUserId);

                // get user account type
                $user = $this->userService->findUserById($loggedUserId);

                $accTypeName = $user->getAccountType();
                $accType = BOL_QuestionService::getInstance()->findAccountTypeByName($accTypeName);

                $packs = $creditsService->getPackList($accType->id);
                $creditsInfo = SKMOBILEAPP_BOL_CreditsService::getInstance()->getCreditsInfo($loggedUserId);

                return $app->json([
                    'isInfoAvailable' => !empty($creditsInfo['earning']) || !empty($creditsInfo['losing']),
                    'packs' => $packs, 
                    'balance' => $balance
                ]);
            }

            throw new BadRequestHttpException('User credits plugin is not activated');
        });

        return $controllers;
    }
}
