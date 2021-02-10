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
use BOL_QuestionService;
use SKADATE_BOL_AccountTypeToGenderService;
use BOL_Question;
use SKMOBILEAPP_BOL_Service;

class JoinQuestions extends BaseQuestions
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

        // get all join questions
        $controllers->get('/{id}/', function (SilexApplication $app, $id) {
            $gender = $id;
            $accountType = $this->genderService->getAccountType($gender);

            // get all join questions for the current gender
            $allJoinQuestions = $this->questionsService->findSignUpQuestionsForAccountType($accountType);

            // these questions must be removed from the final list
            $fixedQuestionNames = [
                'sex',
                'match_sex',
                'password',
                'email',
                'username'
            ];

            $processedJoinQuestions = $this->processQuestions($allJoinQuestions, $fixedQuestionNames);

            if ($processedJoinQuestions) {
                return $app->json([
                    'id' => $gender,
                    'questions' => $processedJoinQuestions
                ]);
            }

            return $app->json([
            ]);
       });

        return $controllers;
    }
}
