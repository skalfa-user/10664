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
 * Users main search component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>, Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.classes
 * @since 1.5.3
 */
class USEARCH_CLASS_MainSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'SearchFormSubmit';
    const FORM_SESSEION_VAR = 'MAIN_SEARCH_FORM_DATA';
    
    const SECTION_TR_PREFIX = 'usearch_section_';
    const QUESTION_TR_PREFIX = 'usearch_question_';

    protected $controller;
    protected $displayMainSearch = true;
    protected $mainSearchQuestionList = array();
    /**
     * @param OW_ActionController $controller
     * @param string $name
     */
    public function __construct( $controller, $name = '' )
    {
        if ($name)
        {
            parent::__construct($name);
        }
        else
        {
            parent::__construct('MainSearchForm');
        }

        $this->initForm($controller);
    }

    protected function initForm($controller)
    {
        $this->controller = $controller;

        $controller->assign('section_prefix', self::SECTION_TR_PREFIX);
        $controller->assign('question_prefix', self::QUESTION_TR_PREFIX);

        $questionService = BOL_QuestionService::getInstance();

        $this->setId('MainSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        // get default question values
        $questionData = $this->getQuestionData();

        // prepare account types list
        $accountList = $this->getAccountTypes();
        $keys = array_keys($accountList);

        // get default account type
        $accountType = $keys[0];
        $matchSexValue = USEARCH_BOL_Service::getInstance()->getGenderByAccounType($accountType);

        if ( isset($questionData['match_sex']) )
        {
            $aType = USEARCH_BOL_Service::getInstance()->getAccounTypeByGender($questionData['match_sex']);

            if ( !empty($aType) )
            {
                $accountType = $aType;
                $matchSexValue = $questionData['match_sex'];
            }
        }
        //-- end --

        // get search question list
        $questions = $questionService->findSearchQuestionsForAccountType('all');

        // prepare questions list
        $this->mainSearchQuestionList = array();
        $questionNameList = array('sex' => 'sex', 'match_sex' => 'match_sex');
        $accounTypeToQuestionList = array();

        foreach ( $questions as $key => $question )
        {
            $sectionName = $question['sectionName'];

            $questionNameList[] = $question['name'];
            $isRequired = in_array($question['name'], array('match_sex')) ? 1 : 0;
            $questions[$key]['required'] = $isRequired;

            if ( $question['name'] == 'sex' || $question['name'] == 'match_sex' )
            {
                unset($questions[$key]);
            }
            else
            {
                $this->mainSearchQuestionList[$sectionName][] = $question;
            }
        }
        // -- end --

        $visibilityList = $this->getVisibilityList($accountType, $this->mainSearchQuestionList);

        $controller->assign('visibilityList', $visibilityList);

        // get question values list
        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        // prepare add sex and match sex questions
        $this->addGenderQuestions($controller, $accountList, $questionValueList, $questionData);

        $this->addQuestions($questions, $questionValueList, $questionData);

        $locationField = $this->getElement('googlemap_location');
        if ( $locationField && method_exists( $locationField, 'setDistance') )
        {
            $value = $locationField->getValue();
            if ( empty($value['json']) )
            {
                $locationField->setDistance(50);
            }
        }

        $controller->assign('questionList', $this->mainSearchQuestionList);

        // add 'online' field
        $onlineField = new CheckboxField('online');
        if ( !empty($questionData) && is_array($questionData) && array_key_exists('online', $questionData) )
        {
            $onlineField->setValue($questionData['online']);
        }
        $onlineField->setLabel(OW::getLanguage()->text('usearch', 'online_only'));
        $this->addElement($onlineField);

        // add with photo field
        $withPhoto = new CheckboxField('with_photo');
        if ( !empty($questionData) && is_array($questionData) && array_key_exists('with_photo', $questionData) )
        {
            $withPhoto->setValue($questionData['with_photo']);
        }
        $withPhoto->setLabel(OW::getLanguage()->text('usearch', 'with_photo'));
        $this->addElement($withPhoto);

        $this->addOnloadJs($matchSexValue, $visibilityList['sections']);
    }

    protected function getFormSessionVar()
    {
        return self::FORM_SESSEION_VAR;
    }

    public function updateSearchData( $data )
    {        
        $data['match_sex'] = !empty($data['match_sex'])? $data['match_sex'] : null;
        $data['sex'] = !empty($data['sex'])? $data['sex'] : null;
        
        if ( empty($data['match_sex']) )
        {
            return $data;
        }
        
        $accountType = SKADATE_BOL_AccountTypeToGenderService::getInstance()->getAccountType($data['match_sex']);
        $questions = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType($accountType);
        $questionList = array();

        if ( empty($questions) )
        {
            return $data;
        }
        
        foreach($questions as $key => $value)
        {
            $questionList[$value['name']] = $value['name'];
        }

        $defaultQuestionList = array('with_photo','sex', 'match_sex', 'online');

        $event = new OW_Event('usearch.onCollectDefaultQuestionList',array(), $defaultQuestionList);

        OW::getEventManager()->trigger($event);

        if( $event->getData() )
        {
            $defaultQuestionList = $event->getData();
        }

        
        foreach ( $data as $key => $value)
        {
            if ( in_array($key, $defaultQuestionList) )
            {
                continue;
            }
            
            if ( !in_array($key, $questionList) )
            {
                unset($data[$key]);
            }
        }
        
        return $data;
    }
    
    public function process( $data )
    {

        if ( OW::getRequest()->isPost() && !$this->isAjax() && isset($data['form_name']) && $data['form_name'] === $this->getName() )
        {
            $event = new OW_Event('usearch.on_search_form_submit', $data, null);

            OW::getEventManager()->trigger($event);

            if( !empty($event->getData()) )
            {
                $data = $event->getData();
            }

            OW::getSession()->set($this->getFormSessionVar(), $data);
            OW::getSession()->set('usearch_search_data', $data);
            
            if ( isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !$this->isAjax() )
            {
                if ( !OW::getUser()->isAuthorized('base', 'search_users') )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                    OW::getFeedback()->warning($status['msg']);
                    $this->controller->redirect();
                }
                
                $data = $this->updateSearchData($data);
                $data = USEARCH_BOL_Service::getInstance()->updateSearchData( $data );

                
                $addParams = array('join' => '', 'where' => '');

                if ( !empty($data['online']) )
                {
                    $addParams['join'] .= " INNER JOIN `".BOL_UserOnlineDao::getInstance()->getTableName()."` `online` ON (`online`.`userId` = `user`.`id`) ";
                }

                if ( !empty($data['with_photo']) )
                {
                     $addParams['join'] .= " INNER JOIN `".OW_DB_PREFIX . "base_avatar` avatar ON (`avatar`.`userId` = `user`.`id`) ";
//                    $addParams['join'] .= " INNER JOIN `".OW_DB_PREFIX . "photo_album` album ON (`album`.`userId` = `user`.`id`)
//                            INNER JOIN `". OW_DB_PREFIX . "photo` `photo` ON (`album`.`id` = `photo`.`albumId`) ";
                }


                
                $userIdList = USEARCH_BOL_Service::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE, false, $addParams);
                $listId = 0;

                if ( OW::getUser()->isAuthenticated() )
                {
                    foreach ( $userIdList as $key => $id )
                    {
                        if ( OW::getUser()->getId() == $id )
                        {
                            unset($userIdList[$key]);
                        }
                    }
                }

                if ( count($userIdList) > 0 )
                {
                    $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
                }

                OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

                BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');
                $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
            }
            
            $this->controller->redirect(OW::getRouter()->urlForRoute("users-search"));
        }
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
    }

    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        parent::setFieldOptions($formField, $questionName, $questionValues);

        if ( $questionName == 'match_sex' )
        {
            $options = array_reverse($formField->getOptions(), true);
            $formField->setOptions($options);
        }

        $event = new OW_Event('usearch.questions_field_add_label_search', array('questionName' => $questionName), true);

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if( !empty($data['label']) )
        {
            $formField->setLabel($data['label']);
        }
        else
        {
            $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $questionName . '_label'));
        }


    }

    protected function setFieldValue( $formField, $presentation, $value )
    {
        if ( !empty($value) )
        {
            $value = BOL_QuestionService::getInstance()->prepareFieldValueForSearch($presentation, $value);
            $formField->setValue($value);
        }
    }
    
    protected function getQuestionData()
    {
        $questionData = OW::getSession()->get($this->getFormSessionVar());
        
        if ( $questionData === null )
        {
            $questionData = array();

            if ( OW::getUser()->isAuthenticated() )
            {
                $data = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('match_sex'));
                $questionData['match_sex'] = $data[OW::getUser()->getId()]['match_sex'];

                $questionData['googlemap_location']['distance'] = 50;
                
                OW::getSession()->set($this->getFormSessionVar(), $questionData);
            }
        }
        else if ( !empty($questionData['match_sex']) )
        {
            if ( is_array($questionData['match_sex']) )
            {
                $questionData['match_sex'] = array_shift($questionData['match_sex']);
            }
            else
            {
                for ( $i = 0; $i < BOL_QuestionService::MAX_QUESTION_VALUES_COUNT; $i++ )
                {
                    if( pow(2, $i) & $questionData['match_sex'] )
                    {
                        $questionData['match_sex'] = pow(2, $i);
                        break;
                    }
                }
            }
        }
        
        return $questionData;
    }
    
    protected function addGenderQuestions($controller, $accounts, $questionValueList, $questionData)
    {
        $controller->assign('displayGender', false);
        $controller->assign('displayAccountType', false);
        
        if ( count($accounts) > 1  )
        {
            $controller->assign('displayAccountType', true);
            
            if ( !OW::getUser()->isAuthenticated() )
            {
                $controller->assign('displayGender', true);

                $sex = new Selectbox('sex');
                $sex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('sex'));
                $sex->setRequired();
                $sex->setHasInvitation(false);

                //$accountType->setHasInvitation(false);
                $this->setFieldOptions($sex, 'sex', $questionValueList['sex']);

                if ( !empty($questionData['sex']) )
                {
                    $sex->setValue($questionData['sex']);
                }

                $this->addElement($sex);
            }
            else
            {
                $sexData = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('sex'));
                
                        
                if ( !empty($sexData[OW::getUser()->getId()]['sex']) )
                {
                    $sex = new HiddenField('sex');
                    $sex->setValue($sexData[OW::getUser()->getId()]['sex']);
                    $this->addElement($sex);
                }
            }

            $matchSex = new Selectbox('match_sex');
            $matchSex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('match_sex'));
            $matchSex->setRequired();
            $matchSex->setHasInvitation(false);

            //$accountType->setHasInvitation(false);
            $this->setFieldOptions($matchSex, 'match_sex', $questionValueList['sex']);
            
            if ( !empty($questionData['match_sex']) )
            {
                $matchSex->setValue($questionData['match_sex']);
            }

            $this->addElement($matchSex);
        }
    }
    
    protected function getVisibilityList($accountType, $questionBySectionList)
    {
        $accountTypeToQuestionList = USEARCH_BOL_Service::getInstance()->getAccountTypeToQuestionList();
        
        $visibleList = !empty($accountTypeToQuestionList[$accountType]) && is_array($accountTypeToQuestionList[$accountType]) 
                ? $accountTypeToQuestionList[$accountType] : array();
        
        $visibleQuestionsList = array();
        $visibleSectionList = array();
        
        foreach( $questionBySectionList as $section => $questions )
        {
            $visibleSectionList[$section] = false;
            
            $visibleQuestionCount = 0;
            foreach( $questions as $question )
            {
                $visibleQuestionsList[$question['name']] = false;
                if ( !empty($question['name']) && in_array($question['name'], $visibleList) )
                {
                    $visibleQuestionsList[$question['name']] = true;
                    $visibleQuestionCount++;
                }
            }
            
            if ($visibleQuestionCount > 0 )
            {
                $visibleSectionList[$section] = true;
            }
        }
        
        return array('sections' => $visibleSectionList, 'questions' => $visibleQuestionsList);
    }
    
    public function isValid( $data )
    {
        $valid = true;

        if ( !is_array($data) )
        {
            throw new InvalidArgumentException('Array should be provided for validation!');
        }
        
        $matchSex = !empty($data['match_sex']) ? $data['match_sex'] : null;
        if ($matchSex)
        {
            $accounType = USEARCH_BOL_Service::getInstance()->getAccounTypeByGender($matchSex);
            $visibilityList = $this->getVisibilityList($accounType, $this->mainSearchQuestionList);
        }

        /* @var $element FormElement */
        foreach ( $this->elements as $element )
        {
            $element->setValue(( isset($data[$element->getName()]) ? $data[$element->getName()] : null));

            if ( !empty($visibilityList['questions']) && isset($visibilityList['questions'][$element->getName()]) && $visibilityList['questions'][$element->getName()] == false )
            {
                continue;
            }
            
            if ( !$element->isValid() )
            {
                $valid = false;
            }
        }
        
        return $valid;
    }
    
    protected function addOnloadJs($gender, $sectionsVisibility)
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('usearch')->getStaticJsUrl().'main_search.js');
        
        $accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();
        
        $js = new UTIL_JsGenerator();
        $js->newObject('fieldModel', 'USearchFromQuestionModel');
        
        /*@var $dto BOL_QuestionAccountType */
        foreach ($accountTypeList as $dto)
        {
            $genderAccount = SKADATE_BOL_AccountTypeToGenderService::getInstance()->getGender($dto->name);
            $questionList = BOL_QuestionService::getInstance()->findSearchQuestionsForAccountType($dto->name);
            
            foreach ($questionList as $question)
            {
                $js->callFunction('fieldModel.addField',  array($question['name'], $genderAccount, ($genderAccount == $gender), $question['sectionName']));
            }
        }
        
        $js->newObject('sectionModel', 'USearchSectionModel');
        
        foreach( $sectionsVisibility as $section => $visibility )
        {
            $js->callFunction('sectionModel.addSection',  array($section, $visibility));
        }
        
        $params = array(
            'gender' => $gender,
            'sectionPrefix' => self::SECTION_TR_PREFIX,
            'fieldPrefix' => self::QUESTION_TR_PREFIX,
        );
        
        $js->addScript('; USearchFromPresenter.init(fieldModel, sectionModel, {$params});',  array('params' => $params));
        
        OW::getDocument()->addOnloadScript($js->generateJs());
    }
}