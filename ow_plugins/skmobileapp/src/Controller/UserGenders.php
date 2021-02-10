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

class UserGenders extends Base
{
    /**
     * Questions service
     *
     * @var BOL_QuestionService
     */
    protected $questionsService;

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

        $this->questionsService = BOL_QuestionService::getInstance();
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
        $controllers->get('/', function() use ($app) {
            $proceedGenders = [];
            $genders = $this->genderService->findAll();

            if ($genders) {
                foreach ($genders as $gender) {
                    $proceedGenders[] = [
                        'id' => $gender->genderValue,
                        'name' => $this->questionsService->getAccountTypeLang($gender->accountType)
                    ];
                }
            }

            return $app->json($proceedGenders);
        });

        return $controllers;
    }
}
