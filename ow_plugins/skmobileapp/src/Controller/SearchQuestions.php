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
use SKADATE_BOL_AccountTypeToGenderService;
use BOL_QuestionService;

class SearchQuestions extends BaseQuestions
{
    /**
     * Gender service
     *
     * @var SKADATE_BOL_AccountTypeToGenderService
     */
    protected $genderService;

    /**
     * Questions constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->genderService = SKADATE_BOL_AccountTypeToGenderService::getInstance();
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

        // get all questions
        $controllers->get('/', function (SilexApplication $app) {
            $userId = $app['users']->getLoggedUserId();
            $questions = [];
            $accountTypes = $this->genderService->findAll();
            $allQuestionsData = $this->service->getAllUserQuestionData($userId);

            foreach($accountTypes as $accountType) {
                $allSearchQuestions = $this->questionsService->findSearchQuestionsForAccountType($accountType->accountType);

                if ($allSearchQuestions) {
                    $questions[$accountType->genderValue] = $this->processQuestions($allSearchQuestions, [
                        'sex',
                        'match_sex',
                        'username'
                    ], true);
                }
            }

            $preferredAccountType = 0;

            // try to find a preferred account type
            foreach($allQuestionsData as $questionData) {
                if ($questionData['name'] == 'match_sex') {
                    $preferredAccountType = $questionData['value'][0];

                    break;
                }
            }

            return $app->json([
                'preferredAccountType' => $preferredAccountType,
                'questions' => $questions
            ]);
        });

        return $controllers;
    }
}
