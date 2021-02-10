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
use BOL_QuestionService;
use OW_Event;
use OW_EventManager;
use OW;

class QuestionsData extends Base
{
    /**
     * Question service
     *
     * @var BOL_QuestionService
     */
    protected $questionService;

    /**
     * App
     *
     * @var SilexApplication
     */
    protected $app;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
    }

    /**
     * Connect methods
     *
     * @param SilexApplication $app
     * @return mixed
     */
    public function connect(SilexApplication $app)
    {
        $this->app = $app;

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // get all questions data
        $controllers->get('/', function() use ($app) {
            return $app->json($this->getAllUserQuestionData($app['users']->getLoggedUserId()));
        });

        // create questions
        $controllers->post('/', function(Request $request) use ($app) {
            $questions = json_decode($request->getContent(), true);
            $this->updateQuestions($app['users']->getLoggedUserId(), $questions);

            return $app->json(
                $this->getAllUserQuestionData($app['users']->getLoggedUserId(), $questions)
            );
        });

        // update questions
        $controllers->put('/me/', function(Request $request) use ($app) {
            $questions = json_decode($request->getContent(), true);
            $loggedUserId = $app['users']->getLoggedUserId();

            // update mode
            $mode = $request->query->get('mode');

            // trigger events before saving
            switch ($mode) {
                case 'completeRequiredQuestions' :
                    $event = new OW_Event( OW_EventManager::ON_BEFORE_USER_COMPLETE_PROFILE, [
                        'user' => $loggedUserId
                    ]);

                    OW::getEventManager()->trigger($event);

                    break;

                default :
            }

            $this->updateQuestions($loggedUserId, $questions, true);

            // trigger events after saving
            switch ($mode) {
                case 'completeRequiredQuestions' :
                    $event = new OW_Event(OW_EventManager::ON_AFTER_USER_COMPLETE_PROFILE, [
                        'userId' => $loggedUserId
                    ]);

                    OW::getEventManager()->trigger($event);

                    break;

                default :
            }

            return $app->json(
                $this->getAllUserQuestionData($app['users']->getLoggedUserId(), $questions)
            );
        });

        return $controllers;
    }

    /**
     * Update questions
     *
     * @param integer $userId
     * @param array $questions
     * @paam boolean $saveChangesInPreference
     * @return void
     */
    protected function updateQuestions($userId, array $questions, $saveChangesInPreference = false) {
        $convertedData  = [];

        // convert questions values
        foreach ($questions as $data) {
            $convertedData[$data['name']] = $this->service->
                convertQuestionValueToSkadateFormat($data['type'], $data['value'], $data['name'], $userId);
        }

        if ($saveChangesInPreference) {
            $this->service->saveEditedQuestionsInPreference($userId, $convertedData);
        }

        // save questions data
        $this->questionService->saveQuestionsData($convertedData, $userId);
    }

    /**
     * Get all user question data
     *
     * @param integer $userId
     * @param array $includeQuestionList
     * @return array
     */
    protected function getAllUserQuestionData($userId, $includeQuestionList = []) {
        $includeOnly = [];

        if ($includeQuestionList) {
            foreach($includeQuestionList as $question) {
                $includeOnly[] = $question['name'];
            }
        }

        $questions = $this->service->getAllUserQuestionData($userId, $includeOnly);

        // process questions
        if ($questions) {
            foreach ($questions as &$question) {
                if ($question['name'] == self::USERNAME_QUESTION_NAME) {
                    $question['params'] = [
                        'token' => $this->getUserToken($userId)
                    ];

                    continue;
                }

                $question['params'] = null;
            }
        }

        return $questions;
    }

    /**
     * Get user token
     *
     * @param integer $userId
     * @return string
     */
    protected function getUserToken($userId) {
        $userDto = $this->userService->findUserById($userId);

        if ($userDto) {
            return $this->app['security.jwt.encoder']->encode(
                $this->service->getUserDataForToken($userDto->getId())
            );
        }
    }
}
