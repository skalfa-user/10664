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
use OW;

class EditQuestions extends BaseQuestions
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

        // get all questions
        $controllers->get('/', function (SilexApplication $app) {
            $userId = $app['users']->getLoggedUserId();
            $userDto = $this->userService->findUserById($userId);

            // get all edit questions
            $allEditQuestions = $this->questionsService->findEditQuestionsForAccountType($userDto->getAccountType());

            // TODO match_sex
            if ( OW::getPluginManager()->isPluginActive('hlookingfor') ) {
                foreach( $allEditQuestions as $key => $question )
                {
                    if ( $question['name'] == 'match_sex' )
                    {
                        unset($allEditQuestions[$key]);
                    }
                }
            }

            $processedEditQuestions = $this->processQuestions(
                    $allEditQuestions, [], false, $this->service->getAllUserQuestionData($userId));

            if ($processedEditQuestions) {
                return $app->json($processedEditQuestions);
            }

            return $app->json([]);
        });

        return $controllers;
    }
}
