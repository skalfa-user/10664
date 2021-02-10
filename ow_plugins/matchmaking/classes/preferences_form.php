<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.matchmaking.classes
 * @since 1.6.1
 */
class MATCHMAKING_CLASS_PreferencesForm extends BASE_CLASS_UserQuestionForm
{
    private $questionService;

    const MIN_DISTANCE = 1;
    const MAX_DISTANCE = 999;

    public function __construct()
    {
        parent::__construct('MATCHMAKING_PreferencesForm');

        $this->questionService = BOL_QuestionService::getInstance();
        $language = OW::getLanguage();

        $save = new Submit('save');
        $save->setValue($language->text('matchmaking', 'btn_label_save'));
        $this->addElement($save);

    }

    public function process($questionArray, $data)
    {
        $language = OW::getLanguage();
        $user = OW::getUser()->getUserObject();

        $saveDistance = false;

        foreach ( $questionArray as $section )
        {
            foreach ( $section as $key => $question )
            {
                if ( $question['name'] == 'googlemap_location' )
                {
                    $saveDistance = true;
                }

                switch ( $question['presentation'] )
                {
                    case 'multicheckbox':

                        if ( is_array($data[$question['name']]) )
                        {
                            $data[$question['name']] = array_sum($data[$question['name']]);
                        }
                        else
                        {
                            $data[$question['name']] = 0;
                        }

                        break;
                }
            }
        }

        // save user data
        if ( !empty($user->id) )
        {
            if ( $this->questionService->saveQuestionsData($data, $user->id) )
            {
                if ( $saveDistance )
                {
                    MATCHMAKING_BOL_Service::getInstance()->saveDistance($user->id, (float)$data['distance_from_my_location']);
                }

                $event = new OW_Event(OW_EventManager::ON_USER_EDIT, array('userId' => $user->id, 'method' => 'native',));
                OW::getEventManager()->trigger($event);

                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        if ( $questionName == 'googlemap_location' )
        {
            $class = OW::getClassInstance('MATCHMAKING_CLASS_DistanceField', 'distance_from_my_location');
            $class->addValidator(new DistanceValidator(self::MIN_DISTANCE, self::MAX_DISTANCE));
//            $class->addValidator(new GOOGLELOCATION_CLASS_DistanceValidator());
            $class->setLabel(OW::getLanguage()->text('matchmaking', 'distance_from_my_location_label'));

            return $class;
        }

        return BOL_QuestionService::getInstance()->getPresentationClass($presentation, $questionName, $configs);
    }

    protected function setLabel( $formField, $question )
    {
        $label = $formField->getLabel();

        if ( empty($label) )
        {
            $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label'));
        }

        if ( $question['name'] == 'googlemap_location' )
        {
            $formField->setLabel(OW::getLanguage()->text('matchmaking', 'distance_from_my_location_label'));
        }
    }

    protected function setFieldValue( $formField, $presentation, $value )
    {
        $value = BOL_QuestionService::getInstance()->prepareFieldValue($presentation, $value);

        if ( $formField->getName() == 'distance_from_my_location' )
        {
            $distanse = MATCHMAKING_BOL_Service::getInstance()->getDistance(OW::getUser()->getId());
            $formField->setValue($distanse);
        }
        else
        {
            $formField->setValue($value);
        }
    }
}

class DistanceValidator extends IntValidator {

    public function __construct($min, $max)
    {
        parent::__construct($min, $max);

        if ( OW::getConfig()->getValue('googlelocation', 'distance_units') == GOOGLELOCATION_BOL_LocationService::DISTANCE_UNITS_MILES )
        {
            $distanceUnits = OW::getLanguage()->text('googlelocation', 'miles');
        }
        else
        {
            $distanceUnits = OW::getLanguage()->text('googlelocation', 'kms');
        }

        $errorMessage = OW::getLanguage()->text('googlelocation', 'distance_validator_error', array( 'distance' => MATCHMAKING_CLASS_PreferencesForm::MAX_DISTANCE, 'units' => $distanceUnits ));

        $this->errorMessage = $errorMessage;
    }
}
