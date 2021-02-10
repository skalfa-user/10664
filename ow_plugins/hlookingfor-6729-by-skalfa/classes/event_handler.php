<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class HLOOKINGFOR_CLASS_EventHandler
{
    private static $classInstance;

    /**
     * @var HLOOKINGFOR_BOL_Service
     */
    private $service;

    /**
     * @return HLOOKINGFOR_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->service = HLOOKINGFOR_BOL_Service::getInstance();
    }

    /**
     * Init
     */
    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, [$this, 'afterInit']);
    }

    public function genericInit()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, [$this, 'afterGenericInit']);
    }

    public function afterInit()
    {
        $eventManager = OW::getEventManager();

        $eventManager->bind('class.get_instance.JoinForm', [$this, 'onJoinForm'], 0);
        $eventManager->bind('class.get_instance.SKADATE_CTRL_Join', [$this, 'onJoinCtrl'], 99999);
        $eventManager->bind('class.get_instance.BASE_CTRL_Edit', [$this, 'onEditCtrl'], 99999);
        $eventManager->bind('class.get_instance.USEARCH_CMP_QuickSearch', [$this, 'onQuickSearch'], 99999);
        $eventManager->bind('class.get_instance.USEARCH_CLASS_MainSearchForm', [$this, 'onMainSearchForm'], 99999);
        $eventManager->bind('admin.questions.get_question_page_checkbox_content', [$this, 'onGetQuestionPageCheckboxContent']);
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', [$this, 'disableProfileQuestions']);

        $this->afterGenericInit();
    }

    public function afterGenericInit()
    {
        $eventManager = OW::getEventManager();

        $eventManager->bind('base.get_user_view_questions', [$this, 'onGetUserViewQuestions'], 0);
        $eventManager->bind('base.question.before_user_search', [$this, 'onBeforeUserSearch'], 0);
        $eventManager->bind('base.event.on_get_empty_questions_list', [$this, 'onGetEmptyQuestionsList'], 99999);
        $eventManager->bind('base.on_after_user_complete_profile', [$this, 'onAfterUserCompleteProfile'], 99999);
        $eventManager->bind('base.query.user_filter', [$this, 'onUserFilter']);
    }

    public function onJoinCtrl( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('HLOOKINGFOR_CTRL_Join', $params['arguments']) );
    }

    public function onJoinForm( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('HLOOKINGFOR_CLASS_JoinForm', $params['arguments']) );
    }

    public function onEditCtrl( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('HLOOKINGFOR_CTRL_Edit', $params['arguments']) );
    }

    // TODO согласно просьбы от Alia хочет чтобы не было перекрестного поиска, а  зничит поле gender надо убрать из поиска
    public function onQuickSearch( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('HLOOKINGFOR_CMP_QuickSearch', $params['arguments']) );
    }

    // TODO согласно просьбы от Alia хочет чтобы не было перекрестного поиска, а  зничит поле gender надо убрать из поиска
    public function onMainSearchForm( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('HLOOKINGFOR_CLASS_MainSearchForm', $params['arguments']) );
    }

    public function onGetQuestionPageCheckboxContent( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( empty($params['question']['name']) )
        {
            return;
        }

        if ( $params['question']['name'] == HLOOKINGFOR_BOL_Service::FIELD_ALLOW_SUPPLIERS_CONTACT_YOU )
        {
            $data['join'] = '<div class="on_join ow_checkbox ow_checkbox_cell_marked_lock"></div>';
            $data['required'] = '<div class="on_join ow_checkbox ow_checkbox_cell_marked_lock"></div>';
            $data['search'] = '<div class="on_join ow_checkbox ow_checkbox_cell_marked_lock"></div>';

            $event->setData($data);
        }
    }

    public function disableProfileQuestions( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question &&
            $params['questionDto']->name == HLOOKINGFOR_BOL_Service::FIELD_ALLOW_SUPPLIERS_CONTACT_YOU )
        {
            $disableActionList = [
                'disable_account_type' => true,
                'disable_answer_type' => true,
                'disable_presentation' => true,
                'disable_column_count' => true,
                'disable_display_config' => true,
                'disable_possible_values' => true,
                'disable_required' => true,
                'disable_on_join' => true,
                'disable_on_view' => false,
                'disable_on_search' => true,
                'disable_on_edit' => false
            ];

            $event->setData($disableActionList);
        }
    }

    public function onGetUserViewQuestions( OW_Event $event )
    {
        $data = $event->getData();
        foreach ( $data as $key => $question )
        {
            if ( $question['name'] == 'match_sex' )
            {
                unset($data[$key]);
            }
        }

        $event->setData($data);
    }

    // TODO просьба от Alia хочет чтобы не было перекрестного поиска
    public function onBeforeUserSearch( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( !empty($params['data']['match_sex']) )
        {
            $value = is_array($params['data']['match_sex']) ? array_sum($params['data']['match_sex']) : (int) $params['data']['match_sex'];

            $data['data']['match_sex'] = $value;
        }

        unset($data['data']['sex']);
        unset($data['data']['accountType']);

        $event->setData($data);
    }

    // TODO на complite profile нужно убрать match_sex, я думаю нужно его захаркодить на противоположный
    // в том случаи если sex уже заполнин
    public function onGetEmptyQuestionsList( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( isset($data['match_sex']) && !empty($params['userId']) )
        {
            $userId = intval($params['userId']);

            $this->service->saveMatchSexBySex($userId);

            unset($data['match_sex']);
        }
    }

    public function onAfterUserCompleteProfile( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['userId']) )
        {
            $userId = intval($params['userId']);

            // TODO предположим что у юзера уже создан sex так как плагин skadate перехватил изменения акаунт типа и сам создал
            if ( !empty($_POST['accountType']) )
            {
                $this->service->saveMatchSexBySex($userId);
            }
        }
    }

    public function onUserFilter( BASE_CLASS_QueryBuilderEvent $event )
    {
        $params = $event->getParams();

        if ( in_array($params['method'], HLOOKINGFOR_BOL_Service::FILTERED_METHODS) )
        {
            if ( OW::getUser()->isAuthenticated() )
            {
                $accountType = OW::getUser()->getUserObject()->getAccountType();

                if ( $accountType == HLOOKINGFOR_BOL_Service::SUPPLIERS_ACCOUNT_TYPE )
                {
                    $innerJoin = "
                    LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `questionD`
                    ON (
                        `user`.`id` = `questionD`.`userId` AND 
                        `questionD`.`questionName` = '" . OW::getDbo()->escapeString(HLOOKINGFOR_BOL_Service::FIELD_ALLOW_SUPPLIERS_CONTACT_YOU) . "'
                    ) ";

                    $where = " (`questionD`.`intValue` IS NULL OR `questionD`.`intValue` = " . HLOOKINGFOR_BOL_Service::FIELD_YES . " ) ";
                }
                else
                {
                    $innerJoin = "
                    LEFT JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `questionD`
                    ON (
                        `user`.`id` = `questionD`.`userId` AND 
                        `questionD`.`questionName` = '" . OW::getDbo()->escapeString(HLOOKINGFOR_BOL_Service::FIELD_ALLOW_SUPPLIERS_CONTACT_YOU) . "'
                    ) ";

                    $where = " (`questionD`.`intValue` IS NULL ) ";
                }

                $event->addJoin($innerJoin);
                $event->addWhere($where);
            }
        }
    }


//
//    public function formattedUsersData( OW_Event $event )
//    {
//        $data = $event->getData();
//
//        if ( !empty($data) )
//        {
//            foreach ( $data as $key => $dataUser )
//            {
//                if ( OW::getUser() )
//                {
//                    if ( !isset($data[$key]['isStudent']) )
//                    {
//                        $data[$key]['isStudent'] = $this->service->isStudent($dataUser['id']);
//                    }
//
//                    if ( !isset($data[$key]['isTeacher']) )
//                    {
//                        $data[$key]['isTeacher'] = $this->service->isTeacher($dataUser['id']);
//                    }
//
//                    $data[$key]['gender'] = $this->service->getGenderByAccounType(($data[$key]['isTeacher']) ? HLOOKINGFOR_BOL_Service::TEACHERS_ACCOUNT_TYPE : HLOOKINGFOR_BOL_Service::STUDENTS_ACCOUNT_TYPE);
//                }
//            }
//
//            $event->setData($data);
//        }
//    }
//
}