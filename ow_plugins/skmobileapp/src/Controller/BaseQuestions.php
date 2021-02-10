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
use BOL_Question;
use SKMOBILEAPP_BOL_Service;
use OW;
use OW_Event;

abstract class BaseQuestions extends Base
{
    const DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_STEP = 10;
    const DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_MIN = 5;
    const DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_MAX = 100;

    /**
     * Questions service
     *
     * @var BOL_QuestionService
     */
    protected $questionsService;

    /**
     * Questions constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->questionsService = BOL_QuestionService::getInstance();
    }

    /**
     * Process questions
     *
     * @param array $fixedQuestionNames
     * @param array $questions
     * @param boolean $searchMode
     * @return array
     */
    protected  function processQuestions(array $questions, array $fixedQuestionNames = [], $searchMode = false, array $questionsData = [])
    {
        // get all questions sections
        $sections = $this->questionsService->findSortedSectionList();

        // process sections
        $allSections = array();
        foreach ( $sections as $section )
        {
            $allSections[$section->name] = [
                'order' => (int) $section->sortOrder,
                'sectionId' => (int) $section->id,
                'section' => $this->questionsService->getSectionLang($section->name),
                'items' => []
            ];
        }

        // process questions
        $viewSections = [];
        foreach ( $questions as $question )
        {
            // skip fixed questions
            if ( in_array($question['name'], $fixedQuestionNames) ) // skip fixed questions
            {
                continue;
            }

            $viewSections[$question['sectionName']] = empty($viewSections[$question['sectionName']])
                ? $allSections[$question['sectionName']]
                : $viewSections[$question['sectionName']];

            $viewSections[$question['sectionName']]['items'][] = $question['name'];
        }

        $viewSections = array_map(function( array $sectionInfo ) use ($searchMode, $questionsData) {
            $sectionInfo['items'] = !$searchMode
                ? $this->prepareQuestionList($sectionInfo['items'], $questionsData)
                : $this->prepareQuestionListForSearch($sectionInfo['items'], $questionsData);

            return $sectionInfo;
        }, $viewSections);

        usort($viewSections, function( $a, $b ) {
            return $a['order'] - $b['order'];
        });

        return $viewSections;
    }

    /**
     * Prepare questions for search
     *
     * @param array $questionNames
     * @return array
     */
    protected function prepareQuestionListForSearch( array $questionNames, array $questionsData = [])
    {
        $questionList = $this->questionsService->findQuestionByNameList($questionNames);
        $questionOptions = $this->questionsService->findQuestionsValuesByQuestionNameList($questionNames);

        $questions = array();

        usort($questionList, function( BOL_Question $a, BOL_Question $b ) {
            return $a->sortOrder - $b->sortOrder;
        });

        foreach ( $questionList as $question )
        {
            $custom = json_decode($question->custom, true);
            $value = null;

            // find a predefined value
            if ($questionsData) {
                foreach($questionsData as $questionValue) {
                    if ($questionValue['name'] == $question->name) {
                        $value = $questionValue['value'];

                        break;
                    }
                }
            }

            switch ($question->presentation) {
                case BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD :
                case BOL_QuestionService::QUESTION_PRESENTATION_URL :
                case BOL_QuestionService::QUESTION_PRESENTATION_TEXT :
                    $custom = [
                        'stacked' => true
                    ];
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA :
                    $custom = [
                        'stacked' => true
                    ];

                    $question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_TEXT;
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_SELECT :
                case BOL_QuestionService::QUESTION_PRESENTATION_RADIO :

                    $question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX;
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX :
                    if (is_null($value)) {
                        $value = false;
                    }

                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_RANGE :
                    $range = $this->questionsService->getPresentationRange();

                    $custom = [
                        'min' => $range['from'],
                        'max' => $range['to']
                    ];

                    if (is_null($value)) {
                        $value = [
                            'lower' => $range['from'],
                            'upper' => $range['to']
                        ];
                    }
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_DATE :
                    $custom = !empty($custom)
                        ? ['minDate' => $custom['year_range']['from'], 'maxDate' => $custom['year_range']['to']]
                        : [];

                    if (is_null($value)) {
                        $value = [
                            'start' => '',
                            'end' => ''
                        ];
                    }

                    $question->presentation = SKMOBILEAPP_BOL_Service::QUESTION_PRESENTATION_DATE_RANGE;
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_AGE :
                case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE :

                    if (!empty($custom)) { // do we have local question's settings

                        $minAge = (int) date("Y") - (int) $custom['year_range']['to'];
                        $maxAge = (int) date("Y") - (int) $custom['year_range']['from'];

                        $custom = [
                            'min' => $minAge,
                            'max' => $maxAge
                        ];

                        if (is_null($value)) {
                            $value = [
                                'lower' => $minAge,
                                'upper' => $maxAge
                            ];
                        }
                    }
                    else {
                        $range = $this->questionsService->getPresentationRange(); // get default range values

                        $custom = [
                            'min' => $range['from'],
                            'max' => $range['to']
                        ];

                        if (is_null($value)) {
                            $value = [
                                'lower' => $range['from'],
                                'upper' => $range['to']
                            ];
                        }
                    }

                    $question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_RANGE;
                    break;

                default :
            }

            // process googlemap location
            if ($question->name == SKMOBILEAPP_BOL_Service::QUESTION_PRESENTATION_GOOGLEMAP_LOCATION) {
                if (is_null($value)) {
                    $value = [
                        'distance' => self::DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_MIN,
                        'location' => ''
                    ];
                }
                else {
                    $value = [
                        'distance' => self::DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_MIN,
                        'location' => $value
                    ];
                }

                $custom = [
                    'min' => self::DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_MIN,
                    'max' => self::DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_MAX,
                    'step' => self::DEFAULT_EXTENDED_GOOGLEMAP_LOCATION_DISTANCE_STEP,
                    'unit' => OW::getConfig()->getValue('googlelocation', 'distance_units')
                ];

                $question->presentation = SKMOBILEAPP_BOL_Service::QUESTION_PRESENTATION_EXTENDED_GOOGLEMAP_LOCATION;
            }

            $questionLabel = $this->questionsService->getQuestionLang($question->name);

            $questions[] = array(
                'type' => $question->presentation,
                'key' => $question->name,
                'label' => $questionLabel,
                'placeholder' => $questionLabel,
                'values' => $this->formatOptionsForQuestion($question->name, $questionOptions),
                'value' => $value,
                'validators' => [],
                'params' => $custom
            );
        }

        return $questions;
    }

    /**
     * Prepare questions
     *
     * @param array $questionNames
     * @return array
     */
    protected function prepareQuestionList( array $questionNames, array $questionsData = [] )
    {
        $questionList = $this->questionsService->findQuestionByNameList($questionNames);
        $questionOptions = $this->questionsService->findQuestionsValuesByQuestionNameList($questionNames);

        $questions = array();

        usort($questionList, function(BOL_Question $a, BOL_Question $b) {
            return $a->sortOrder - $b->sortOrder;
        });

        foreach ($questionList as $question) {
            $custom = json_decode($question->custom, true);
            $value = null;

            // find a predefined value
            if ($questionsData) {
                foreach($questionsData as $questionValue) {
                    if ($questionValue['name'] == $question->name) {
                        $value = $questionValue['value'];

                        break;
                    }
                }
            }

            switch ($question->presentation) {
                case BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD :
                case BOL_QuestionService::QUESTION_PRESENTATION_URL :
                case BOL_QuestionService::QUESTION_PRESENTATION_TEXT :
                    $custom = [
                        'stacked' => true
                    ];
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_RANGE :
                    $range = $this->questionsService->getPresentationRange();

                    $custom = [
                        'min' => $range['from'],
                        'max' => $range['to']
                    ];

                    if (is_null($value)) {
                        $value = [
                            'lower' => $range['from'],
                            'upper' => $range['to']
                        ];
                    }
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE :
                case BOL_QuestionService::QUESTION_PRESENTATION_AGE :
                case BOL_QuestionService::QUESTION_PRESENTATION_DATE :
                    $custom = !empty($custom)
                        ? ['minDate' => $custom['year_range']['from'], 'maxDate' => $custom['year_range']['to']]
                        : [];
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX :
                    if (is_null($value)) {
                        $value = false;
                    }

                    break;
            }

            $validators = [];

            // add required and other validators
            if ((bool) $question->required) {
                $validators[] = [
                    'name' => 'require'
                ];
            }

            // add base validators
            if ($question->name == self::USERNAME_QUESTION_NAME) {
                $validators[] = [
                    'name' => 'userName'
                ];
            }

            if ($question->name == self::EMAIL_QUESTION_NAME) {
                $validators[] = [
                    'name' => 'email'
                ];

                $validators[] = [
                    'name' => 'userEmail'
                ];
            }

            $questionType   = $question->presentation;
            $questionName   = $question->name;
            $questionPlaceholder = $questionLabel  = $this->questionsService->getQuestionLang($question->name);
            $questionValues = $this->formatOptionsForQuestion($question->name, $questionOptions);
            $questionValue  = $value;
            $questionValidators  = $validators;
            $questionParams  = $custom;

            // change presentation for some questions
            switch ($question->name) {
                case 'googlemap_location' :
                    $questionType = SKMOBILEAPP_BOL_Service::QUESTION_PRESENTATION_GOOGLEMAP_LOCATION;
                    break;

                default :
                    $event = new OW_Event('skmobileapp.prepare_question_list', [
                        'type' => $questionType,
                        'key' => $questionName,
                        'label' => $questionLabel,
                        'placeholder' => $questionPlaceholder,
                        'values' => $questionValues,
                        'value' => $questionValue,
                        'validators' => $questionValidators,
                        'params' => $questionParams
                    ]);

                    $data = OW::getEventManager()->trigger($event);

                    if ( $data->getData() )
                    {
                        $questionEventData = $data->getData();

                        $questionType   = $questionEventData['type'];
                        $questionName   = $questionEventData['key'];
                        $questionLabel  = $questionEventData['label'];
                        $questionPlaceholder  = $questionEventData['placeholder'];
                        $questionValues = $questionEventData['values'];
                        $questionValue  = $questionEventData['value'];
                        $questionValidators  = $questionEventData['validators'];
                        $questionParams  = $questionEventData['params'];
                    }
            }

            $questions[] = array(
                'type' => $questionType,
                'key' => $questionName,
                'label' => $questionLabel,
                'placeholder' => $questionPlaceholder,
                'values' => $questionValues,
                'value' => $questionValue,
                'validators' => $questionValidators,
                'params' => $questionParams
            );
        }

        return $questions;
    }

    /**
     * Format options
     *
     * @param $name
     * @param $allOptions
     * @return array
     */
    protected function formatOptionsForQuestion( $name, $allOptions )
    {
        $options = array();

        if ( !empty($allOptions[$name]) )
        {
            $optionList = array();
            foreach ( $allOptions[$name]['values'] as $option )
            {
                $optionList[] = array(
                    'value' => $option->value,
                    'title' => $this->questionsService->getQuestionValueLang($option->questionName, $option->value)
                );
            }

            $allOptions[$name] = $optionList;
            $options = $allOptions[$name];
        }

        return $options;
    }
}
