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
 * User search by name component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.usearch.classes
 * @since 1.5.3
 */
class USEARCH_CLASS_DisplaynameSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'DisplayNameSearchFormSubmit';

    public $controller;

    /**
     * @param OW_ActionController $controller
     */
    public function __construct( $controller )
    {
        parent::__construct('DisplayNameSearchForm');

        $this->controller = $controller;

        $questionService = BOL_QuestionService::getInstance();

        $this->setId('DisplayNameSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        $questionName = OW::getConfig()->getValue('base', 'display_name_question');

        $question = $questionService->findQuestionByName($questionName);

        $questionPropertyList = array();
        foreach ( $question as $property => $value )
        {
            $questionPropertyList[$property] = $value;
        }

        $this->addQuestions(array($questionName => $questionPropertyList), array(), array());

        $controller->assign('displayNameQuestion', $questionPropertyList);
    }

    public function process( $data )
    {
        if ( OW::getRequest()->isPost() && isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !$this->isAjax() )
        {
            if ( !OW::getUser()->isAuthorized('base', 'search_users') )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                OW::getFeedback()->warning($status['msg']);
                $this->controller->redirect();
            }

            $userIdList = USEARCH_BOL_Service::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE);
            $listId = 0;

            if ( count($userIdList) > 0 )
            {
                $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
            }

            OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

            BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');
            $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
        }
    }
}