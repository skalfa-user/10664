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
 * @package ow_plugins.matchmaking.controllers
 * @since 1.0
 */
class MATCHMAKING_CTRL_Base extends OW_ActionController
{
    private $service;
    private $questionService;

    public function __construct()
    {
        $this->service = MATCHMAKING_BOL_Service::getInstance();
        $this->questionService = BOL_QuestionService::getInstance();

    }
    /**
     * Default action. Rules tab
     */
    public function index($params)
    {
        $userId = OW::getUser()->getId();

        if ( OW::getRequest()->isAjax() )
        {
            exit;
        }

        if ( !OW::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_chat');
        $this->setPageTitle($language->text('matchmaking', 'matches_index'));

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = (int)OW::getConfig()->getValue('base', 'users_on_page');
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findMatchList($userId, $first, $count, $params['sortOrder']);
        $listCount = $this->service->findMatchCount($userId);

        $event = OW::getEventManager()->trigger(new OW_Event('matchmaking.on_match_list', $params, $dtoList));
        $dtoList = $event->getData();

        $userService = BOL_UserService::getInstance();

        $idList = array();
        $compatibilityList = array();
        foreach ( $dtoList as $id => $item )
        {
            $idList[] = (int)$item['id'];
            $compatibilityList[$item['id']] = $this->service->getCompatibilityByValue($item['compatibility']);
        }

        $userDataList = array();
        $fields = $this->service->getFieldsForMatchList($idList);
        foreach ( $idList as $userId )
        {
            $userDataList[$userId] = array(
                'info_gender' => !empty($fields[$userId]) ? $fields[$userId] : '',
                'compatibility' => !empty($compatibilityList[$userId]) ? OW::getLanguage()->text('matchmaking', 'compatibility') . ': <span class="ow_txt_value">'.$compatibilityList[$userId].'%</span>' : ''
            );
        }

        $event = OW::getEventManager()->trigger(new OW_Event('matchmaking.on_user_data_list', $params, $userDataList));
        $userDataList = $event->getData();

        $listCmp = OW::getClassInstance('BASE_CMP_Users', $userDataList, array(), $listCount);
        $listCmp->setDisplayActivity(false);

        $this->addComponent('listCmp', $listCmp);

        $sortControl = new BASE_CMP_SortControl();
        $sortControl->setSortItems($this->service->getMatchPageMenuItems());
        $sortControl->setActive($params['sortOrder']);

        $this->addComponent('sortControl', $sortControl);
        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5));

    }

    public function preferences($params)
    {
        if (!OW::getUser()->isAuthenticated())
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();
        OW::getDocument()->setHeading($language->text('matchmaking', 'preferences_page_heading'));
        OW::getDocument()->setTitle($language->text('matchmaking', 'preferences_page_title'));

        $userId = OW::getUser()->getId();

        $form = new MATCHMAKING_CLASS_PreferencesForm();
        $form->setId('matchmakingPreferencesForm');

        $questions = $this->service->findMatchQuestionsForUser($userId);

        if (empty($questions))
        {
            $this->assign('noQuestions', true);
            return;
        }

        $this->assign('noQuestions', false);

        $section = '';
        $questionArray = array();
        $questionNameList = array();

        foreach ( $questions as $sort => $question )
        {
            if ( $section !== $question['sectionName'] )
            {
                $section = $question['sectionName'];
            }

            $accTypes = $this->questionService->findAccountTypeListByQuestionName($question['parent']);

            if (count($accTypes) == 1)
            {
                $accType = $this->questionService->findAccountTypeByName($accTypes[0]->accountType);
                $lang = $this->questionService->getAccountTypeLang($accType->name);

                $questions[$sort]['acc_type_label'] = $language->text('matchmaking', 'admin_rules_manage_question_property', array('accountTypeName'=>$lang));
            }
            else
            {
                $questions[$sort]['acc_type_label'] = '';
            }

            $questionArray[$section][$sort] = $questions[$sort];
            $questionNameList[] = $questions[$sort]['name'];
        }

        $this->assign('questionArray', $questionArray);

        $questionData = $this->questionService->getQuestionData(array($userId), $questionNameList);

        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $form->addQuestions($questions, $questionValues, $questionData[$userId]);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                if ($form->process($questionArray, $data))
                {
                    $event = new OW_Event('matchmaking.preferences_saved', $data);
                    OW::getEventManager()->trigger($event);

                    OW::getFeedback()->info($language->text('base', 'edit_successfull_edit'));

                    $this->redirect();
                }
                else
                {
                    OW::getFeedback()->info($language->text('base', 'edit_edit_error'));
                }
            }
        }
    }
}