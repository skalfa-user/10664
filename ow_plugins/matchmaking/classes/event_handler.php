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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.matchmaking.classes
 * @since 1.6.1
 */
class MATCHMAKING_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var MATCHMAKING_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return MATCHMAKING_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('base.event.on_question_delete', array($this, 'onQuestionDelete'));
        OW::getEventManager()->bind('base.questions_field_init', array($this, 'onQuestionsFieldInit'));
        OW::getEventManager()->bind('admin.before_save_lang_value', array($this, 'onBeforeSaveLangValue'));

        OW::getEventManager()->bind('matchmaking.get_list', array($this, 'getMatchList'));
        OW::getEventManager()->bind('matchmaking.get_list_count', array($this, 'getMatchListCount'));
        OW::getEventManager()->bind('matchmaking.get_compatibility', array($this, 'getCompatibility'));
        OW::getEventManager()->bind('matchmaking.get_compatibility_for_list', array($this, 'getCompatibilityForList'));

        OW::getEventManager()->bind('base.question.add_question_form.on_get_available_sections', array($this, 'onGetAvailableSections'));
    }

    public function init()
    {
        OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'onQuickLinksCollect'));
        OW::getEventManager()->bind('base.add_main_console_item', array($this, 'addConsoleItem'));
        OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, array($this, 'addJsCode'));

        OW::getEventManager()->bind('class.get_instance.BASE_CMP_UserViewSection', array($this, 'viewProfileDetailsMatchSection'));
        OW::getEventManager()->bind('admin.questions.get_account_types_checkbox_content', array($this, 'setAccountTypesCheckboxContent'));
        OW::getEventManager()->bind('admin.questions.get_edit_delete_question_buttons_content', array($this, 'setEditDeleteQuestionButtonsContent'));
        OW::getEventManager()->bind('admin.disable_fields_on_edit_profile_question', array($this, 'disableFieldsOnEditProfileQuestion'));
        OW::getEventManager()->bind('admin.questions.get_question_page_checkbox_content', array($this, 'onGetQuestionPageCheckboxContent'));
    }

    public function onGetAvailableSections( OW_Event $event )
    {
        $data = $event->getData();

        foreach ($data as $id=>$section)
        {
            if (false)
            {
                //TODO
            }
        }

        $event->setData($data);
    }

    public function onQuickLinksCollect( BASE_CLASS_EventCollector $event )
    {
        $router = OW_Router::getInstance();

        $event->add(array(
            BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('matchmaking', 'matches_index'),
            BASE_CMP_QuickLinksWidget::DATA_KEY_URL => $router->urlForRoute('matchmaking_members_page'),
            BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => MATCHMAKING_BOL_Service::getInstance()->findMatchCount(OW::getUser()->getId()),
            BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $router->urlForRoute('matchmaking_members_page'),
        ));
    }

    public function onQuestionDelete( OW_Event $event )
    {
        $params = $event->getParams();

        if ($params['questionName'] == 'sex' || $params['questionName'] == 'birthdate')
        {
            return;
        }

        MATCHMAKING_BOL_Service::getInstance()->deleteRuleByQuestionName($params['questionName']);
    }

    public function onQuestionsFieldInit( OW_Event $event )
    {
        $params = $event->getParams();

        if ($params['type'] == 'main' && $params['fieldName'] != 'match_sex' && $params['fieldName'] != 'match_age')
        {
            $question = BOL_QuestionService::getInstance()->findQuestionByName($params['fieldName']);
            if ( !empty($question->parent) )
            {
                $class = OW::getClassInstance( 'MATCHMAKING_CLASS_CheckboxGroup', $params['fieldName'] );
                $event->setData($class);
            }
        }
    }

    public function addJsCode()
    {
        $route = OW::getRouter()->getUsedRoute();

        if (!empty($route) && ( $route->getRouteName() == 'base_edit' || $route->getRouteName() == 'base_edit_user_datails' || $route->getRouteName() == 'matchmaking_preferences' ) )
        {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('matchmaking')->getStaticJsUrl() . 'checkbox_group.js');
        }
    }

    public function addConsoleItem( BASE_CLASS_EventCollector $event )
    {
        //$event->add(array('label' => OW::getLanguage()->text('matchmaking', 'matchmaking_preferences'), 'url' => OW_Router::getInstance()->urlForRoute('matchmaking_preferences')));
    }
    
    public function getMatchList( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        //newest, compatible
        $sort = $params["sort"];
        $first = (int) $params["first"];
        $count = (int) $params["count"];
        
        $service = MATCHMAKING_BOL_Service::getInstance();
        $list = $service->findMatchList($userId, $first, $count, $sort);
        
        foreach ( $list as &$item )
        {
            $item["compatibility"] = $service->getCompatibilityByValue($item['compatibility']);
        }
        
        $event->setData($list);
        
        return $list;
    }
    
    public function getMatchListCount( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $service = MATCHMAKING_BOL_Service::getInstance();
        $count = $service->findMatchCount($userId);
        
        $event->setData($count);
        
        return $count;
    }

    public function getCompatibility( OW_Event $event )
    {
        $params = $event->getParams();
        $firstUserId = $params["firstUserId"];
        $secondUserId = $params["secondUserId"];

        $service = MATCHMAKING_BOL_Service::getInstance();
        $compatibility = $service->getCompatibility($firstUserId, $secondUserId);

        $event->setData($compatibility);

        return $compatibility;
    }

    public function getCompatibilityForList( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['idList']) )
        {
            return array();
        }

        $userId = (int)$params['userId'];
        $idList = $params['idList'];
        $first = !empty($params['first']) ? (int)$params['first'] : 0;
        $count = !empty($params['count']) ? (int)$params['count'] : count($idList);
        $order = !empty($params['order']) ? $params['order'] : 'compatible';

        $service = MATCHMAKING_BOL_Service::getInstance();
        $maxCompatibility = $service->getMaxPercentValue();
        $compatibilities = $service->findCompatibilityByUserIdList($userId, $idList, $first, $count, $order);
        $result = array();

        foreach ( $compatibilities as $compatibility )
        {
            $result[$compatibility['userId']] = (int)$compatibility['compatibility'];
        }

        $event->setData($result);

        return $result;
    }

    public function viewProfileDetailsMatchSection( OW_Event $event )
    {
        $params = $event->getParams();

        if ($params['arguments'][0] == 'about_my_match')
        {
            $data = $event->getData(); 
            $data = OW::getClassInstance('MATCHMAKING_CMP_UserViewSection', $params['arguments'][0], $params['arguments'][1], $params['arguments'][2], $params['arguments'][3], $params['arguments'][4], $params['arguments'][5], $params['arguments'][6]);
            $event->setData($data);
            return $data;
        }

        return null;
    }

    public function setAccountTypesCheckboxContent( OW_Event $event )
    {
        $params = $event->getParams();

        $question = $params['question'];

        if (!empty($question['parent']) && $question['name'] != 'match_sex' )
        {
            $data = OW::getLanguage()->text('matchmaking', 'account_types_checkbox_content');
            $event->setData($data);
        }
    }

    public function setEditDeleteQuestionButtonsContent( OW_Event $event )
    {
        $params = $event->getParams();

        $question = $params['question'];

        if (!empty($question['parent']) && $question['name'] != 'match_sex')
        {
            $data = '<a href="'.OW::getRouter()->urlForRoute('matchmaking_admin_rules').'" >'.OW::getLanguage()->text('matchmaking', 'admin_page_title_settings').'</a>';
            $event->setData($data);
        }
    }

    public function disableFieldsOnEditProfileQuestion( OW_Event $event )
    {
        $params = $event->getParams();

        $questionDto = $params['questionDto'];

        if ( !empty($questionDto->parent) && !in_array($questionDto->name, array('sex', 'match_sex')) )
        {
            $disableActionList = $event->getData();

            $disableActionList['disable_on_join'] = true;
            $disableActionList['disable_on_edit'] = true;
            $disableActionList['disable_required'] = true;

            $event->setData($disableActionList);
        }
    }

    public function onBeforeSaveLangValue( OW_Event $event )
    {
        $params = $event->getParams();

        if (empty($params['dto']))
        {
            return;
        }

        if (!($params['dto'] instanceof BOL_LanguageValue))
        {
            return;
        }

        $langKey = BOL_LanguageKeyDao::getInstance()->findById($params['dto']->keyId);

        $rules = MATCHMAKING_BOL_Service::getInstance()->findAll();
        /**
         * @var MATCHMAKING_BOL_QuestionMatch $rule
         */
        foreach($rules as $rule)
        {
            if ("questions_question_{$rule->questionName}_label" == $langKey->key)
            {
                $matchQuestionLabel = OW::getLanguage()->text('matchmaking', 'match_question_lang_prefix', array('questionLabel'=>$params['dto']->value));

                BOL_LanguageService::getInstance()->addOrUpdateValue($params['dto']->languageId, BOL_QuestionService::QUESTION_LANG_PREFIX, BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL, $rule->matchQuestionName), empty($matchQuestionLabel) ? ' ' : $matchQuestionLabel );
            }
        }
    }

    public function onGetQuestionPageCheckboxContent( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( empty($params['question']) )
        {
            return;
        }

        $question = $params['question'];

        if ( preg_match('/^match_/', $question['name']) )
        {
            $data['search'] = '<div class="on_search ow_checkbox ow_checkbox_cell_lock"></div>';

            if ( $question['onSearch'] )
            {
                $data['search'] = '<div class="on_search ow_checkbox ow_checkbox_cell_marked_lock"></div>';
            }

            $event->setData($data);
        }
    }
}
