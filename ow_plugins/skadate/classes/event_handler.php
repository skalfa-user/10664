<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_CLASS_EventHandler
{
    /**
     * @var SKADATE_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return SKADATE_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {
        
    }

    public function apiInit()
    {
        OW::getEventManager()->bind('speedmatch.display_mutual_message', array($this, 'displaySpeedmatchMutualMessageForApi'));
    }
    
    public function genericInit()
    {
        OW::getEventManager()->bind(BOL_QuestionService::EVENT_ON_GET_QUESTION_LANG, array($this, 'onGetGenderLangValue'));

        OW::getEventManager()->bind(BOL_QuestionService::EVENT_ON_ACCOUNT_TYPE_ADD, array($this, 'onUpdateAccountTypes'));
        OW::getEventManager()->bind(BOL_QuestionService::EVENT_ON_ACCOUNT_TYPE_DELETE, array($this, 'onUpdateAccountTypes'));
        OW::getEventManager()->bind(BOL_QuestionService::EVENT_ON_ACCOUNT_TYPE_REORDER, array($this, 'onUpdateAccountTypes'));

        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_USER_COMPLETE_PROFILE, array($this, 'onBeforeCompleteAccountType'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_USER_COMPLETE_ACCOUNT_TYPE, array($this, 'onBeforeCompleteAccountType'));

        OW::getEventManager()->bind('base.questions_get_data', array($this, 'onGetGenderData'));
        OW::getEventManager()->bind('base.questions_save_data', array($this, 'onUserUpdate'));
        OW::getEventManager()->bind('base.after_avatar_change', array($this, 'onAvatarChange'));

        OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onUserRegister'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));

        OW::getEventManager()->bind('usercredits.get_product_id', array($this, 'usercreditsGetProductId'));
        OW::getEventManager()->bind('membership.get_product_id', array($this, 'membershipGetProductId'));

        OW::getEventManager()->bind('speedmatch.suggest_users', array($this, 'suggestSpeedmatchUsers'));

        OW::getEventManager()->bind('photo.collect_extended_settings', array($this, 'photoCollectExtendedSettings'));
        OW::getEventManager()->bind('photo.save_extended_settings', array($this, 'photoSaveExtendedSettings'));
        OW::getEventManager()->bind('photo.getPhotoList', array($this, 'photoGetPhotoList'));
        OW::getEventManager()->bind('base.query.content_filter', array($this, 'photoContentFilter'));
        OW::getEventManager()->bind('admin.filter_themes_to_choose', array($this, 'filterAdminThemes'));
        OW::getEventManager()->bind('core.get_text', array($this, 'onGetText'));
        
        OW::getEventManager()->bind('base.questions_field_get_label', array($this, 'getQuestionLabel'));
        
        OW::getEventManager()->bind('base.questions_field_add_fake_questions', array($this, 'onAddFakeQuestions'));
        OW::getEventManager()->bind('class.get_instance.JoinForm', array($this, 'onGetJoinForm'));
        
        OW::getEventManager()->bind('class.get_instance.BASE_CMP_Users', array($this, 'onGetInstanceUsersCmp'));
        OW::getEventManager()->bind('base.members_only_exceptions', array($this, 'addMembersOnlyException'));
        OW::getEventManager()->bind('base.get_default_theme', array($this, 'onGetDefaultTheme'));
        OW::getEventManager()->bind('base.on_notify_admin_about_invalid_items', array($this, 'onNotifyAdminAboutInvalidItems'));
    }
    
    public function mobileInit()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalize'));
    }
    
    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalize'));
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'afterRouteHandler'));
        OW::getEventManager()->bind('base.on_plugin_info_update', array($this, 'onPluginInfoUpdate'));
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'adminPageHanler'));
        OW::getEventManager()->bind('admin.disable_fields_on_edit_profile_question', array($this, 'disableProfileQuestions'));
        OW::getEventManager()->bind('admin.get.possible_values_disable_message', array($this, 'genderDisableValuesMassage'));
        //OW::getEventManager()->bind('admin.questions.get_edit_delete_question_buttons_content', array($this, 'genderGetAdminLabels'));
        OW::getEventManager()->bind('admin.questions.get_edit_delete_question_buttons_content', array($this, 'genderGetEditDeleteQuestionButtonsContent'));
        OW::getEventManager()->bind('admin.questions.get_preview_question_values_content', array($this, 'getPreviewQuestionValuesContent'));

        OW::getEventManager()->bind(BOL_QuestionService::EVENT_BEFORE_ADD_QUESTIONS_TO_NEW_ACCOUNT_TYPE, array($this, 'addQuestionsToNewAccountType'));
        OW::getEventManager()->bind('admin.questions.get_question_page_checkbox_content', array($this, 'onGetQuestionPageCheckboxContent'));
        OW::getEventManager()->bind('admin.get_soft_version_text', array($this, 'getSoftVersionText'));
        OW::getEventManager()->bind('base.on_after_add_or_update_value_lang', array($this, 'onAfterAddOrUpdateValueLang'));
    }

    public function filterAdminThemes( OW_Event $e )
    {
        $themesArr = $e->getData();
        unset($themesArr[BOL_ThemeService::DEFAULT_THEME]);
        $e->setData($themesArr);
    }

    public function onGetGenderLangValue( OW_Event $event )
    {
        $params = $event->getParams();

        $type = $params['type'];
        $name = $params['name'];
        $value = $params['value'];

        $data = null;

        if ( $type == BOL_QuestionService::LANG_KEY_TYPE_QUESTION_VALUE && ( $name == 'sex' || $name == 'match_sex' ) )
        {
            $accountTypesToGender = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

            foreach ( $accountTypesToGender as $item )
            {
                /* @var $value SKADATE_BOL_AccountTypeToGender */
                if ( $item->genderValue == $value )
                {
                    $data = 'questions_account_type_' . $item->accountType;
                    break;
                }
            }

            $event->setData($data);
        }
    }

    public function onUpdateAccountTypes( OW_Event $event )
    {
        SKADATE_BOL_AccountTypeToGenderService::getInstance()->getInstance()->updateGenderValues();

        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        $sex = BOL_QuestionService::getInstance()->findQuestionByName('sex');
        $match_sex = BOL_QuestionService::getInstance()->findQuestionByName('match_sex');

        if ( !empty($sex) )
        {

            $sex->onEdit = 0;
            $sex->onJoin = 0;
            $sex->onSearch = 0;
            //$sex->onView = 1;
            $sex->required = 1;

            if ( count($accountTypes) < 2 )
            {
                $sex->onEdit = 0;
                $sex->onJoin = 0;
                $sex->onSearch = 0;
                $sex->onView = 0;
                $sex->required = 1;
            }

            BOL_QuestionService::getInstance()->saveOrUpdateQuestion($sex);
        }

        //    if ( count($accountTypes) > 1 )
        //    {
        //        if ( !empty($match_sex) )
        //        {
        //            $match_sex->onEdit = 1;
        //            $match_sex->onSearch = 1;
        //        }
        //    }
        //    else
        //    {
        if ( !empty($match_sex) )
        {
            //$match_sex->onEdit = 0;
            $match_sex->onSearch = 1;
            $match_sex->onEdit = 1;
            $match_sex->onJoin = 1;
            //$match_sex->onView = 1;

            if ( count($accountTypes) < 2 )
            {
                $match_sex->onSearch = 0;
                $match_sex->onEdit = 0;
                $match_sex->onJoin = 0;
                $match_sex->onView = 0;
            }

            BOL_QuestionService::getInstance()->saveOrUpdateQuestion($match_sex);
        }
        //    }
        //
    //    if ( !empty($match_sex) )
        //    {
        //        BOL_QuestionService::getInstance()->saveOrUpdateQuestion($match_sex);
        //    }
    }

    public function onBeforeCompleteAccountType( OW_Event $event )
    {
        $params = $event->getParams();
        $user = $params['user'];

        if ( empty($user) )
        {
            return;
        }

        $this->updateMatchSex($user);
    }

    protected function updateMatchSex( $user )
    {
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

        if ( count($accountTypes) < 2 )
        {
            $match_sex = BOL_QuestionService::getInstance()->findQuestionByName('match_sex');

            if ( !empty($match_sex) )
            {
                $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType();

                $gender = SKADATE_BOL_AccountTypeToGenderService::getInstance()->getGender($accountType->name);

                if ( !empty($gender) )
                {
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('match_sex' => $gender), $user->id);
                }
            }
        }
    }

    public function onGetGenderData( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        foreach ( $data as $userId => $questions )
        {
            foreach ( $questions as $key => $value )
            {
                if ( $key == 'sex' )
                {
                    $user = BOL_UserService::getInstance()->findUserById($userId);
                    $dtoList = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

                    if ( !empty($data[$userId][$key]) )
                    {
                        unset($data[$userId][$key]);
                    }

                    $value = 0;

                    foreach ( $dtoList as $dto )
                    {
                        /* @var $dto SKADATE_BOL_AccountTypeToGender */
                        if ( $dto->accountType == $user->accountType )
                        {
                            $value = $dto->genderValue;
                            break;
                        }
                    }

                    if ( !empty($value) )
                    {
                        $data[$userId][$key] = $value;
                    }
                    else
                    {
                        unset($data[$userId][$key]);
                    }
                }

                if ( $key == 'match_sex' )
                {
                    $dtoList = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

                    if ( empty($data[$userId][$key]) )
                    {
                        unset($data[$userId][$key]);
                        break;
                    }

                    $value = 0;
                    foreach ( $dtoList as $dto )
                    {
                        //$value = $dto->genderValue;

                        /* @var $dto SKADATE_BOL_AccountTypeToGender */

                        if ( (int) $dto->genderValue & (int) $data[$userId][$key] )
                        {
                            $value += $dto->genderValue;
                        }
                    }

                    if ( !empty($value) )
                    {
                        $data[$userId][$key] = $value;
                    }
                    else
                    {
                        unset($data[$userId][$key]);
                    }
                }
            }
        }

        $e->setData($data);
    }

    public function onUserUpdate( OW_Event $event )
    {
        $data = $event->getData();

        if ( !empty($data['accountType']) )
        {
            $genderToAccountTypeList = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

            foreach ( $genderToAccountTypeList as $value )
            {
                if ( $value->accountType == $data['accountType'] )
                {
                    $data['sex'] = $value->genderValue;
                }
            }
        }
        else if ( !empty($data['sex']) )
        {
            $genderToAccountTypeList = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
            /* @var $value SKADATE_BOL_AccountTypeToGender */
            foreach ( $genderToAccountTypeList as $value )
            {
                if ( $value->genderValue == $data['sex'] )
                {
                    $data['accountType'] = $value->accountType;
                }
            }
        }

        $event->setData($data);
    }

    public function onUserRegister( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }
        
        // ---- create user album
        $event = new OW_Event('base.create_user_album', array('userId' => $params['userId']));
        OW::getEventManager()->trigger($event);
        // ----

        $user = BOL_UserService::getInstance()->findUserById($params['userId']);

        if ( empty($user) )
        {
            return;
        }

        $data = array();

        $accountType = $user->accountType;
        $questionData = BOL_QuestionService::getInstance()->getQuestionData(array($user->id), array('sex'));

        $sex = null;

        if ( !empty($questionData[$user->id]['sex']) )
        {
            $sex = $questionData[$user->id]['sex'];
        }

        if ( !empty($accountType) )
        {
            $genderToAccountTypeList = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

            foreach ( $genderToAccountTypeList as $value )
            {
                if ( $value->accountType == $accountType )
                {
                    $data['sex'] = $value->genderValue;
                }
            }
        }
        else if ( !empty($sex) )
        {
            $genderToAccountTypeList = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
            /* @var $value SKADATE_BOL_AccountTypeToGender */
            foreach ( $genderToAccountTypeList as $value )
            {
                if ( $value->genderValue == $sex )
                {
                    $data['accountType'] = $value->accountType;
                }
            }
        }

        BOL_QuestionService::getInstance()->saveQuestionsData($data, $user->id);

        $this->updateMatchSex($user);
    }

    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $service = SKADATE_BOL_Service::getInstance();

        $service->removeBigAvatar($userId);

        $service->removeSpeedmatchRelationsByUserId($userId);

        $service->removeCurrentLocationByUserId($userId);
    }

    public function onAvatarChange( OW_Event $e )
    {
        $params = $e->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];
        SKADATE_BOL_Service::getInstance()->copyBigAvatar($userId);
    }

    public function usercreditsGetProductId( OW_Event $e )
    {
        $params = $e->getParams();

        $productId = mb_strtoupper(USERCREDITS_CLASS_UserCreditsPackProductAdapter::PRODUCT_KEY . '_' . $params['id']);

        $e->setData($productId);
    }

    public function membershipGetProductId( OW_Event $e )
    {
        $params = $e->getParams();

        $productId = mb_strtoupper(MEMBERSHIP_CLASS_MembershipPlanProductAdapter::PRODUCT_KEY . '_' . $params['id']);

        $e->setData($productId);
    }

    public function suggestSpeedmatchUsers( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];

        $first = (int) $params["first"];
        $count = (int) $params["count"];

        $service = SKADATE_BOL_Service::getInstance();
        $list = $service->findSpeedmatchOpponents($userId, $first, $count, $params['criteria'], $params['exclude']);

        $event->setData($list);

        return $list;
    }

    public function displaySpeedmatchMutualMessage( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['opponentId']) )
        {
            return '';
        }

        $userService = BOL_UserService::getInstance();

        $userId = OW::getUser()->getId() == $params['userId'] ? $params['opponentId'] : $params['userId'];

        if ( !$userService->findUserById($userId) )
        {
            return '';
        }

        $message = OW::getLanguage()->text(
            'skadate', 'speedmatch_mutual_message', array('username' => $userService->getDisplayName($userId))
        );

        $event->setData($message);

        return $message;
    }

    public function displaySpeedmatchMutualMessageForApi( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['opponentId']) )
        {
            return '';
        }

        $userService = BOL_UserService::getInstance();

        $userId = OW::getUser()->getId() == $params['userId'] ? $params['opponentId'] : $params['userId'];

        if ( !$userService->findUserById($userId) )
        {
            return '';
        }

        $message = OW::getLanguage()->text(
            'skadate', 'speedmatch_mutual_message', array('username' => $userService->getDisplayName($userId))
        );

        $data = array(
            'text' => $message
        );

        $event->setData($data);

        return $message;
    }

    public function photoCollectExtendedSettings( BASE_CLASS_EventCollector $event )
    {
        $input = new CheckboxField('matching_only');
        $input->setLabel(OW::getLanguage()->text('skadate', 'photo_setting_matching_label'));
        $input->setDescription(OW::getLanguage()->text('skadate', 'photo_setting_matching_desc'));
        $input->setValue((bool)OW::getConfig()->getValue('skadate', 'photo_filter_setting_matching'));

        $event->add(array(
            'section' => 'filter_settings',
            'section_lang' => 'skadate+photo_filter_section_label',
            'settings' => array(
                'matching_only' => $input
            )
        ));
    }

    public function photoSaveExtendedSettings( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !array_key_exists('matching_only', $params) )
        {
            return;
        }

        OW::getConfig()->saveConfig('skadate', 'photo_filter_setting_matching', (bool)$params['matching_only']);
    }

    public function photoGetPhotoList( BASE_CLASS_QueryBuilderEvent $event )
    {
        $params = $event->getParams();
        $aliases = $params['aliases'];

        if ( empty($params['listType']) || !in_array($params['listType'], array('latest', 'featured', 'toprated', 'most_discussed', 'searchByDesc', 'searchByHashtag', 'searchByUsername')) )
        {
            return;
        }

        if (
            !OW::getUser()->isAuthenticated() ||
            !(bool)OW::getConfig()->getValue('skadate', 'photo_filter_setting_matching') ||
            OW::getUser()->isAuthorized('photo') ||
            OW::getUser()->isAuthorized('base')
        )
        {
            return;
        }

        $userId = OW::getUser()->getId();
        $matchValue = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array('sex', 'match_sex'));

        if ( empty($matchValue[$userId]['match_sex']) )
        {
            return;
        }

        $join = 'INNER JOIN `' . BOL_UserDao::getInstance()->getTableName() . '` AS `sk_u` ON(`' . $aliases['album'] . '`.`userId` = `sk_u`.`id`)
            INNER JOIN `' . BOL_QuestionDataDao::getInstance()->getTableName() . '` AS `sk_qd` ON (`sk_qd`.`userId` = `sk_u`.`id` AND `sk_qd`.`questionName` = :sk_sexQuestionName AND `sk_qd`.`intValue` & :sk_matchSexValue)
            INNER JOIN `' . BOL_QuestionDataDao::getInstance()->getTableName() . '` AS `sk_qd1` ON (`sk_qd1`.`userId` = `sk_u`.`id` AND `sk_qd1`.`questionName` = :sk_matchSexQuestionName AND `sk_qd1`.`intValue` & :sk_sexValue)';
        $params = array(
            'sk_sexQuestionName' => 'sex',
            'sk_matchSexQuestionName' => 'match_sex',
            'sk_sexValue' => $matchValue[$userId]['sex'],
            'sk_matchSexValue' => $matchValue[$userId]['match_sex']
        );

        $event->addJoin($join);
        $event->addBatchQueryParams($params);
    }

    public function photoContentFilter( BASE_CLASS_QueryBuilderEvent $event )
    {
        $params = $event->getParams();
        if (($params['type'] == 'photo_comments' && $params['method'] == 'BOL_CommentDao::findMostCommentedEntityList') || $params['type'] == 'photo_rates')
        {
            if (
                !OW::getUser()->isAuthenticated() ||
                !(bool)OW::getConfig()->getValue('skadate', 'photo_filter_setting_matching') ||
                OW::getUser()->isAuthorized('photo') ||
                OW::getUser()->isAuthorized('base')
            )
            {
                return;
            }

            $userId = OW::getUser()->getId();
            $matchValue = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array('sex', 'match_sex'));

            if ( empty($matchValue[$userId]['match_sex']) )
            {
                return;
            }

            $join = 'INNER JOIN `' . BOL_UserDao::getInstance()->getTableName() . '` AS `sk_u` ON(`a`.`userId` = `sk_u`.`id`)
            INNER JOIN `' . BOL_QuestionDataDao::getInstance()->getTableName() . '` AS `sk_qd` ON (`sk_qd`.`userId` = `sk_u`.`id` AND `sk_qd`.`questionName` = :sk_sexQuestionName AND `sk_qd`.`intValue` & :sk_matchSexValue)
            INNER JOIN `' . BOL_QuestionDataDao::getInstance()->getTableName() . '` AS `sk_qd1` ON (`sk_qd1`.`userId` = `sk_u`.`id` AND `sk_qd1`.`questionName` = :sk_matchSexQuestionName AND `sk_qd1`.`intValue` & :sk_sexValue)';
            $params = array(
                'sk_sexQuestionName' => 'sex',
                'sk_matchSexQuestionName' => 'match_sex',
                'sk_sexValue' => $matchValue[$userId]['sex'],
                'sk_matchSexValue' => $matchValue[$userId]['match_sex']
            );

            $event->addJoin($join);
            $event->addBatchQueryParams($params);
        }
    }

    public function onGetText( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['prefix'] == 'base' && ($params['key'] == 'welcome_letter_template_html' 
                || $params['key'] == 'welcome_letter_template_text' 
                || $params['key'] == 'welcome_widget_content'))
        {
            $event->setData(OW::getLanguage()->text('skadate', $params['key'], $params['vars']));
        }
    }
    
    public function getQuestionLabel( OW_Event $event )
    {
        $params = $event->getParams();
        $presentation = !empty($params['presentation']) ? $params['presentation'] : null;
        $fieldName = !empty($params['fieldName']) ? $params['fieldName'] : null;
        $configs = !empty($params['configs']) ? $params['configs'] : null;
        $type = !empty($params['type']) ? $params['type'] : null;

        if ( $type == 'view' && $fieldName == 'sex' )
        {
            $event->setData(OW::getLanguage()->text('skadate', 'questions_question_sex_label'));
        }
    }
    
    public function afterRouteHandler()
    {
        $spotParams = array(
            "platform" => "skadate",
            "platform-version" => OW::getConfig()->getValue("base", "soft_version"),
            "platform-build" => OW::getConfig()->getValue("base", "soft_build"),
            "theme" => OW::getConfig()->getValue("base", "selectedTheme")
        );

        OW_ViewRenderer::getInstance()->assignVar('adminDashboardIframeUrl', OW::getRequest()->buildUrlQueryString("//static.oxwall.org/spotlight/", $spotParams));

        $config = OW::getConfig();

        if ( !$config->configExists('skadate', 'installInit') )
        {
            return;
        }

        $installDir = dirname(__FILE__) . DS . "files" . DS;
        $installPluginfiles = $installDir . 'ow_pluginfiles' . DS;
        $installUserfiles = $installDir . 'ow_userfiles' . DS;

        /* read all plugins from DB */
        $plugins = BOL_PluginService::getInstance()->findActivePlugins();
        $pluginList = array();

        /* @var $value BOL_Plugin */
        foreach ( $plugins as $value )
        {
            $pluginList[$value->getKey()] = ( $value->isSystem ?
                    new OW_SystemPlugin(array('dir_name' => $value->getModule(), 'key' => $value->getKey(), 'active' => $value->isActive(), 'dto' => $value)) :
                    new OW_Plugin(array('dir_name' => $value->getModule(), 'key' => $value->getKey(), 'active' => $value->isActive(), 'dto' => $value))
                );
        }

        /* @var $plugin OW_Plugin */
        foreach ( $pluginList as $plugin )
        {
            if ( !file_exists($plugin->getPluginFilesDir()) )
            {
                mkdir($plugin->getPluginFilesDir());
            }

            chmod($plugin->getPluginFilesDir(), 0777);

            if ( file_exists($installPluginfiles . $plugin->getModuleName()) )
            {
                UTIL_File::copyDir($installPluginfiles . $plugin->getModuleName(), $plugin->getPluginFilesDir());
            }

            if ( !file_exists($plugin->getUserFilesDir()) )
            {
                mkdir($plugin->getUserFilesDir());
            }

            chmod($plugin->getUserFilesDir(), 0777);

            if ( file_exists($installUserfiles . $plugin->getModuleName()) )
            {
                UTIL_File::copyDir($installUserfiles . $plugin->getModuleName(), $plugin->getUserFilesDir());
            }
        }

        $config->deleteConfig('skadate', 'installInit');
    }
    
    public function onPluginInfoUpdate( OW_Event $event )
    {
        $data = $event->getParams();

        $data['bundle'] = 'skadate';
        $data['bundleKey'] = OW::getConfig()->getValue('skadate', 'license_key');

        $event->setData($data);
    }
    
    public function onFinalize( OW_Event $event )
    {
        $plugin = OW::getPluginManager()->getPlugin("skadate");
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . "skadate.css");
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . "skadate.js");
    }
    
    public function adminPageHanler()
    {
        if ( (bool) OW::getConfig()->getValue("skadate", "license_key_valid") )
        {
            return;
        }

        $attrs = OW::getRequestHandler()->getHandlerAttributes();
        
        if ( !is_subclass_of($attrs[OW_RequestHandler::ATTRS_KEY_CTRL], "ADMIN_CTRL_Abstract") )
        {
            return;
        }

        $exceludeAttrs = array(
            "SKADATE_CTRL_Admin" => "invalidKey",
            "SKADATE_CTRL_Admin" => "checkLicense",
            "ADMIN_CTRL_Plugins" => "manualUpdateRequest",
            "ADMIN_CTRL_Storage" => "checkUpdates",
        );

        foreach ( $exceludeAttrs as $ctrl => $action )
        {
            if ( $attrs[OW_RequestHandler::ATTRS_KEY_CTRL] == $ctrl && $attrs[OW_RequestHandler::ATTRS_KEY_ACTION] == $action )
            {
                return;
            }
        }

        OW::getRequestHandler()->setHandlerAttributes(array(OW_RequestHandler::ATTRS_KEY_CTRL => "SKADATE_CTRL_Admin", OW_RequestHandler::ATTRS_KEY_ACTION => "invalidKey"));
    }
    
    public function disableProfileQuestions( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name == 'sex' )
        {
            $disableActionList = array(
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
                'disable_on_edit' => true
            );

            $e->setData($disableActionList);
        }

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name == 'birthdate' )
        {
            $disableActionList = array(
                'disable_account_type' => true,
                'disable_answer_type' => true,
                'disable_presentation' => true,
                'disable_column_count' => true,
                'disable_display_config' => false,
                'disable_possible_values' => true,
                'disable_required' => true,
                'disable_on_join' => true,
                'disable_on_view' => false,
                'disable_on_search' => false,
                'disable_on_edit' => false
            );

            $e->setData($disableActionList);
        }

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question && $params['questionDto']->name == 'match_sex' )
        {
            $disableActionList = array(
                'disable_account_type' => true,
                'disable_answer_type' => true,
                'disable_presentation' => true,
                'disable_column_count' => false,
                'disable_display_config' => true,
                'disable_possible_values' => true,
                'disable_required' => true,
                'disable_on_join' => true,
                'disable_on_view' => false,
                'disable_on_search' => true,
                'disable_on_edit' => false
            );

            $e->setData($disableActionList);
        }
    }
    
    public function genderDisableValuesMassage( OW_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['name']) && ( $params['name'] == 'sex' || $params['name'] == 'match_sex' ) )
        {
            $e->setData(OW::getLanguage()->text('skadate', 'disable_possible_values_disable_message'));
        }
    }
    
//    function genderGetAdminLabels( OW_Event $e )
//    {
//        $params = $e->getParams();
//        $data = $e->getData();
//
//        if ( !empty($params['question']['name']) && $params['question']['name'] == 'sex' )
//        {
//            $e->setData(OW::getLanguage()->text('skadate', 'admin_sex_values_label'));
//        }
//
//        if ( !empty($params['question']['name']) && $params['question']['name'] == 'sex' )
//        {
//            $e->setData(OW::getLanguage()->text('skadate', 'admin_match_sex_values_label'));
//        }
//    }
    
    public function genderGetEditDeleteQuestionButtonsContent( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['question']['name']) && $params['question']['name'] == 'sex' )
        {
            $e->setData(" ");
        }
    }
    
    public function getPreviewQuestionValuesContent( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['question']['name']) )
        {
            if ( $params['question']['name'] == 'sex' )
            {
                //$e->setData(OW::getLanguage()->text('skadate', 'preview_sex_values'));
            }

            if ( $params['question']['name'] == 'match_sex' )
            {

                $values = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList(array('match_sex'));
                $values = $values['match_sex']['values'];
                $valuesHtml = '';

                /* @var $value BOL_QuestionValue */
                foreach ( $values as $value )
                {
                    $valuesHtml .= '<li>' . BOL_QuestionService::getInstance()->getQuestionValueLang('match_sex', $value->value) . '</li>';
                }

                $html = '';
                if ( !empty($valuesHtml) )
                {
                    $html = '<div class="question_values_div">
                        <center><a class="question_values" href="javascript://">' . OW::getLanguage()->text('admin', 'questions_values_count', array('count' => count($values))) . '</a></center>

                        <div style="padding:0 0 0 15px;text-align:left;display:none;width:100px;overflow:hidden;" >
                            <ul style="list-style-type:disc;">
                                ' . $valuesHtml . '
                            </ul>
                        </div>
                    </div>';
                }

                $e->setData($html);
            }
        }
    }
    
    public function addQuestionsToNewAccountType( OW_Event $e )
    {
        $params = $e->getParams();

        $data = $e->getData();

        if ( !empty($params['dto']) && $params['dto'] instanceof BOL_QuestionAccountType )
        {
            $data['sex'] = 'sex';
            $data['match_sex'] = 'match_sex';
            $e->setData($data);
        }
        
        return $data;
    }
    
    public function onGetJoinForm( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();
        $data = OW::getClassInstance('SKADATE_CLASS_JoinForm', $params['arguments'][0]);
        $e->setData($data);

        return $data;
    }
    
    public function onAddFakeQuestions( OW_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['name']) && $params['name'] == 'match_age' )
        {
            $e->setData(false);
        }
    }
    
    public function onGetInstanceUsersCmp( OW_Event $event )
    {
        $params = $event->getParams();

        $data =  OW::getClassInstance('SKADATE_CMP_UserList', $params['arguments'][0], $params['arguments'][1], $params['arguments'][2]);

        $event->setData($data);

        return $data;
    }
    
    public function onGetQuestionPageCheckboxContent( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( empty($params['question']['name']) )
        {
            return;
        }

        if ( $params['question']['name'] == 'sex' )
        {
            $data['join'] = '<div class="on_join ow_checkbox ow_checkbox_cell_marked_lock"></div>';
            $event->setData($data);
        }
    }
    
    public function getSoftVersionText( OW_Event $event )
    {
        $plugin = OW::getPluginManager()->getPlugin('skadate')->getDto();

        $var = array(
            'skadate_version' => 'Skadate', // skadate version name
            'skadate_build' => $plugin->build,
            'oxwall_version' => OW::getConfig()->getValue('base', 'soft_version'),
            'oxwall_build' => OW::getConfig()->getValue('base', 'soft_build')
        );

        $text = OW::getLanguage()->text('skadate', 'soft_version', $var);

        $event->setData($text);
    }
    
    public function addMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'SKADATE_CTRL_Join', 'action' => 'index'));
        $event->add(array('controller' => 'SKADATE_CTRL_Join', 'action' => 'joinFormSubmit'));
        $event->add(array('controller' => 'SKADATE_MCTRL_Join', 'action' => 'index'));
        $event->add(array('controller' => 'SKADATE_MCTRL_Join', 'action' => 'joinFormSubmit'));
        $event->add(array('controller' => 'SKADATE_MCTRL_Join', 'action' => 'ajaxResponder'));
    }
    
    public function onGetDefaultTheme()
    {
        // temp solution to use `friends` as Skadate default theme
        $defaultTheme = BOL_ThemeService::getInstance()->findThemeByKey("friends");
        
        if ( $defaultTheme )
        {
            return $defaultTheme->getKey();
        }
    }

    public function onNotifyAdminAboutInvalidItems( OW_Event $event )
    {
        $items = $event->getData();

        $isNotify = (bool) OW::getConfig()->getValue('skadate', 'notify_admin_about_invalid_items');

        if ( !$isNotify && !empty($items) )
        {
            $event->setData(array());
        }
    }

    public function onAfterAddOrUpdateValueLang( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['prefix'] == 'base' && $params['key'] == 'welcome_widget_content' )
        {
            BOL_LanguageService::getInstance()->addOrUpdateValue($params['languageId'], 'skadate', $params['key'], $params['value']);
        }
    }
}
