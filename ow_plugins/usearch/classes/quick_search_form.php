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
 * Users quick search component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>, Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.classes
 * @since 1.5.3
 */
class USEARCH_CLASS_QuickSearchForm extends BASE_CLASS_UserQuestionForm
{
    const FORM_SESSEION_VAR = 'MAIN_SEARCH_FORM_DATA';

    public $questionService;

    public $searchService;
    
    public $displayAccountType;

    public function __construct( $controller )
    {
        parent::__construct('QuickSearchForm');

        $this->questionService = BOL_QuestionService::getInstance();
        $this->searchService = USEARCH_BOL_Service::getInstance();
        $lang = OW::getLanguage();
        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlForRoute('usearch.quick_search_action'));
        $this->setAjaxResetOnSuccess(false);

        $questionNameList = $this->searchService->getQuickSerchQuestionNames();
        $questionValueList = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $sessionData = OW::getSession()->get(self::FORM_SESSEION_VAR);
        
        if ( $sessionData === null )
        {
            $sessionData = array();

            if ( OW::getUser()->isAuthenticated() )
            {
                $data = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('sex', 'match_sex'));
                
                if ( !empty($data[OW::getUser()->getId()]['sex']) )
                {
                    $sessionData['sex'] = $data[OW::getUser()->getId()]['sex'];
                }
                
                if ( !empty($data[OW::getUser()->getId()]['match_sex']) )
                {
                    for ( $i = 0; $i < BOL_QuestionService::MAX_QUESTION_VALUES_COUNT; $i++ )
                    {
                        if( pow(2, $i) & $data[OW::getUser()->getId()]['match_sex'] )
                        {
                            $sessionData['match_sex'] = pow(2, $i);
                            break;
                        }
                    }
                }

                $sessionData['googlemap_location']['distance'] = 50;

                OW::getSession()->set(self::FORM_SESSEION_VAR, $sessionData);
            }
        }

        if ( !empty($sessionData['match_sex']) )
        {
            if ( is_array($sessionData['match_sex']) )
            {
                $sessionData['match_sex'] = array_shift($sessionData['match_sex']);
            }
            else
            {
                for ( $i = 0; $i < BOL_QuestionService::MAX_QUESTION_VALUES_COUNT; $i++ )
                {
                    if( pow(2, $i) & $sessionData['match_sex'] )
                    {
                        $sessionData['match_sex'] = pow(2, $i);
                        break;
                    }
                }
            }
        }

        /* ------------------------- */
        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);

        $questions = array();
        $questionList = array();
        $orderedQuestionList = array();

        /* @var $question BOL_Question */
        foreach ( $questionDtoList as $key => $question )
        {
            $dataList = (array) $question;
            $questions[$question->name] = $dataList;

            $isRequired = in_array($question->name, array('match_sex')) ? 1 : 0;
            $questions[$question->name]['required'] = $isRequired;

            if ( $question->name == 'sex' || $question->name == 'match_sex' )
            {
                unset($questions[$question->name]);
            }
            else
            {
                $questionList[$question->name] = $dataList;
            }
        }

        foreach ( $questionNameList as $questionName )
        {
            if ( !empty($questionDtoList[$questionName]) )
            {
                $orderedQuestionList[] = $questionDtoList[$questionName];
            }
        }

        $controller->assign('displayGender', false);

        $accounts = $this->getAccountTypes();

        $this->addQuestions($questions, $questionValueList, array());

        $locationField = $this->getElement('googlemap_location');
        if ( $locationField && method_exists( $locationField, 'setDistance') )
        {
            $value = $locationField->getValue();
            if ( empty($value['distance']) )
            {
                $locationField->setDistance(50);
            }
        }

        if ( count($accounts) > 1 )
        {
            $this->displayAccountType = true;

            $controller->assign('displayGender', true);

            $sex = new Selectbox('sex');
            $sex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('sex'));
            $sex->setHasInvitation(false);
            $sex->setRequired();

            //$accountType->setHasInvitation(false);
            $this->setFieldOptions($sex, 'sex', $questionValueList['sex']);

            if ( !empty($sessionData['sex']) )
            {
                $sex->setValue($sessionData['sex']);
            }

            $this->addElement($sex);

            $matchSex = new Selectbox('match_sex');
            $matchSex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('match_sex'));
            $matchSex->setRequired();
            $matchSex->setHasInvitation(false);

            //$accountType->setHasInvitation(false);
            $this->setFieldOptions($matchSex, 'match_sex', $questionValueList['sex']);

            if ( !empty($sessionData['match_sex']) && !is_array($sessionData['match_sex']) )
            {
                $matchSex->setValue($sessionData['match_sex']);
            }

            $this->addElement($matchSex);
        }

        $controller->assign('questionList', $orderedQuestionList);
        $controller->assign('displayAccountType', $this->displayAccountType);

        // 'online' field
        $onlineField = new CheckboxField('online');
        if ( is_array($sessionData) && array_key_exists('online', $sessionData) )
        {
            $onlineField->setValue((int) $sessionData['online']);
        }
        $onlineField->setLabel($lang->text('usearch', 'online_only'));
        $this->addElement($onlineField);
        
//        if ( OW::getPluginManager()->isPluginActive('photo') )
//        {
            // with photo
            $withPhoto = new CheckboxField('with_photo');
            if ( is_array($sessionData) && array_key_exists('with_photo', $sessionData) )
            {
                $withPhoto->setValue((int) $sessionData['with_photo']);
            }
            $withPhoto->setLabel($lang->text('usearch', 'with_photo'));
            $this->addElement($withPhoto);
//        }

        // submit
        $submit = new Submit('search');
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $submit->addAttribute('disabled', 'disabled');
        $submit->addAttribute('class', 'ow_inprogress');
        $this->addElement($submit);

        OW::getDocument()->addOnloadScript("; $('#{$submit->getId()}')
        .removeAttr('disabled')
        .removeClass('ow_inprogress')
        .bind('click', function(){
            $(this).addClass('ow_inprogress');
        })
        .parent().removeClass('ow_inprogress');");
        
        $this->bindJsFunction(Form::BIND_SUCCESS, "function(data){
            if ( data.result ) {
                document.location.href = data.url;
            }
            else {
                OW.warning(data.error);
            }
        }");
    }

    public function setColumnCount( $formElement, $question )
    {
        $formElement->setColumnCount(1);
    }

    protected function setFieldValue( $formField, $presentation, $value )
    {
        if ( !empty($value) )
        {
            if ( $presentation == BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX )
            {
                if( is_array($value) )
                {
                    $value = array_shift();
                }
                else
                {
                    for ( $i = 0; $i < BOL_QuestionService::MAX_QUESTION_VALUES_COUNT; $i++ )
                    {
                        if( pow(2, $i) & $value )
                        {
                            $value = pow(2, $i);
                            break;
                        }
                    }


                }
            }
            else
            {
                $value = BOL_QuestionService::getInstance()->prepareFieldValueForSearch($presentation, $value);
            }
            
            $formField->setValue($value);
        }
    }

    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        parent::setFieldOptions($formField, $questionName, $questionValues);

        if ( $questionName == 'match_sex' )
        {
            $options = array_reverse($formField->getOptions(), true);
            $formField->setOptions($options);
        }

        $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $questionName . '_label'));
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        $event = new OW_Event('base.questions_field_get_label', array(
            'presentation' => $presentation,
            'fieldName' => $questionName,
            'configs' => $configs,
            'type' => 'edit'
        ));

        OW::getEventManager()->trigger($event);

        $label = $event->getData();

        $class = null;

        $event = new OW_Event('base.questions_field_init', array(
            'type' => 'search',
            'presentation' => $presentation,
            'fieldName' => $questionName,
            'configs' => $configs
        ));

        OW::getEventManager()->trigger($event);

        $class = $event->getData();

        if ( empty($class) )
        {
            switch ( $presentation )
            {
                case BOL_QuestionService::QUESTION_PRESENTATION_TEXT :
                case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA :
                    $class = new TextField($questionName);
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX :
                    $class = new CheckboxField($questionName);
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_RADIO :
                case BOL_QuestionService::QUESTION_PRESENTATION_SELECT :
                case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX :
                    $class = new Selectbox($questionName);
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE :
                case BOL_QuestionService::QUESTION_PRESENTATION_AGE :

                    $class = new USEARCH_CLASS_AgeRangeField($questionName);
                    
                    if ( !empty($configs) && mb_strlen( trim($configs) ) > 0 )
                    {
                        $configsList = json_decode($configs, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinYear($value['from']);
                                $class->setMaxYear($value['to']);
                            }
                        }
                    }

                    $class->addValidator(new USEARCH_CLASS_AgeRangeValidator($class->getMinAge(), $class->getMaxAge()));
                    
                    break;
                    
                case BOL_QuestionService::QUESTION_PRESENTATION_RANGE :
                    $class = new Range($questionName);

                    if ( empty($this->birthdayConfig) )
                    {
                        $birthday = $this->findQuestionByName("birthdate");
                        if ( !empty($birthday) )
                        {
                            $this->birthdayConfig = ($birthday->custom);
                        }
                    }
                    
                    $rangeValidator = new RangeValidator();
                    
                    if ( !empty($this->birthdayConfig) && mb_strlen( trim($this->birthdayConfig) ) > 0 )
                    {
                        $configsList = json_decode($this->birthdayConfig, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinValue(date("Y") - $value['to']);
                                $class->setMaxValue(date("Y") - $value['from']);
                                
                                $rangeValidator->setMinValue(date("Y") - $value['to']);
                                $rangeValidator->setMaxValue(date("Y") - $value['from']);
                            }
                        }
                    }

                    $class->addValidator($rangeValidator);
                    
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_DATE :
                    $class = new DateRange($questionName);

                    if ( !empty($configs) && mb_strlen( trim($configs) ) > 0 )
                    {
                        $configsList = json_decode($configs, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinYear($value['from']);
                                $class->setMaxYear($value['to']);
                            }
                        }
                    }

                    $class->addValidator(new DateValidator($class->getMinYear(), $class->getMaxYear()));
                    break;

                case BOL_QuestionService::QUESTION_PRESENTATION_URL :
                    $class = new TextField($questionName);
                    $class->addValidator(new UrlValidator());
                    break;
            }

            if ( !empty($label) )
            {
                $class->setLabel($label);
            }

            if ( empty($class) )
            {
                $class = BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
            }
        }

        return $class;
    }
}