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
class MATCHMAKING_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $questionService;

    public function init()
    {
        $this->questionService = BOL_QuestionService::getInstance();
    }

    /**
     * Default action. Rules tab
     */
    public function index()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('matchmaking', 'admin_page_heading'));
        $this->setPageTitle($language->text('matchmaking', 'admin_page_title_rules'));
        $this->setPageHeadingIconClass('ow_ic_chat');

        $service = MATCHMAKING_BOL_Service::getInstance();
        $dtoItems = $service->findFieldsExceptRequired();
        $accTypeLabel = false;

        $allAccTypes = $this->questionService->findAllAccountTypes();
        $accTypeNumber = count($allAccTypes);

        $list = array();
        /**
         * @var BOL_Question $item
         */
        foreach ( $dtoItems as $id => $item )
        {
            $question = $this->questionService->findQuestionByName($item->questionName);
            if (!$question)
            {
                continue;
            }
            $match_question = $this->questionService->findQuestionByName($item->matchQuestionName);
            if (!$match_question)
            {
                continue;
            }
            $list[$id]['required'] = $item->required;
            $list[$id]['question_name'] = $this->questionService->getQuestionLang($item->questionName);
            $list[$id]['question_url'] = OW::getRouter()->urlFor( 'ADMIN_CTRL_Questions', 'edit', array('questionId'=>$question->getId()) );
            $list[$id]['match_question_name'] = $this->questionService->getQuestionLang($item->matchQuestionName);
            $match_question = $this->questionService->findQuestionByName($item->matchQuestionName);
            $list[$id]['match_question_url'] = OW::getRouter()->urlFor( 'ADMIN_CTRL_Questions', 'edit', array('questionId'=>$match_question->getId()) );
            $list[$id]['delete_url'] = OW::getRouter()->urlForRoute('matchmaking_delete_item', array('id' => $item->id));
            $list[$id]['coefficient'] = $item->coefficient;
            $edit_coefficient = new MATCHMAKING_CMP_EditCoefficient($item->id, $item->coefficient);
            $list[$id]['edit_coefficient'] = $edit_coefficient->render();
            $accTypes = $this->questionService->findAccountTypeListByQuestionName($item->questionName);

            $assignedAccTypesCount = count($accTypes);
            if ($assignedAccTypesCount < $accTypeNumber)
            {
                $list[$id]['acc_type_label'] = ' ';
                $accTypeLabel = true;

                if ($assignedAccTypesCount == 1)
                {
                    $accType = $this->questionService->findAccountTypeByName($accTypes[0]->accountType);
                    $lang = $this->questionService->getAccountTypeLang($accType->name);

                    $list[$id]['acc_type_label'] = $language->text('matchmaking', 'admin_rules_manage_question_property', array('accountTypeName'=>$lang)) . ' ';
                }
                else
                {
                    foreach($accTypes as $type)
                    {
                        $lang = $this->questionService->getAccountTypeLang($type->accountType);

                        $list[$id]['acc_type_label'] .= $lang . ', ';
                    }

                    $list[$id]['acc_type_label'] = substr($list[$id]['acc_type_label'], 0, -2);
                }
            }
            else
            {
                $list[$id]['acc_type_label'] = '';
            }
        }

        $this->assign('accTypeLabel', $accTypeLabel);
        $this->assign('list', $list);

        $otherListAccTypeLabel = false;
        $otherQuestionList = MATCHMAKING_BOL_Service::getInstance()->findQuestionsForNewRules();
        $otherList = array();
        foreach ( $otherQuestionList as $id => $item )
        {
            $otherList[$id]['question_name'] = $this->questionService->getQuestionLang($item->name);

            $create_coefficient = new MATCHMAKING_CMP_CreateCoefficient($item->name);
            $otherList[$id]['create_coefficient'] = $create_coefficient->render();

            $accTypes = $this->questionService->findAccountTypeListByQuestionName($item->name);
            $assignedAccTypesCount = count($accTypes);

            if ($assignedAccTypesCount < $accTypeNumber)
            {
                $otherListAccTypeLabel = true;
                $otherList[$id]['acc_type_label'] = '';

                if ($assignedAccTypesCount == 1)
                {
                    $otherListAccTypeLabel = true;
                    $accType = $this->questionService->findAccountTypeByName($accTypes[0]->accountType);
                    $lang = $this->questionService->getAccountTypeLang($accType->name);

                    $otherList[$id]['acc_type_label'] = $language->text('matchmaking', 'admin_rules_manage_question_property', array('accountTypeName'=>$lang));
                }
                else
                {
                    foreach($accTypes as $type)
                    {
                        $lang = $this->questionService->getAccountTypeLang($type->accountType);

                        $otherList[$id]['acc_type_label'] .= $lang . ', ';
                    }

                    $otherList[$id]['acc_type_label'] = substr($otherList[$id]['acc_type_label'], 0, -2);
                }
            }
            else
            {
                $otherList[$id]['acc_type_label'] = '';
            }
        }

        $this->assign('otherListAccTypeLabel', $otherListAccTypeLabel);
        $this->assign('otherList', $otherList);

        $this->assign('maxCoefficient', MATCHMAKING_BOL_Service::MAX_COEFFICIENT);

        $this->addComponent('menu', $this->getMenu());

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('matchmaking')->getStaticCssUrl() . 'matchmaking.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('matchmaking')->getStaticJsUrl() . 'matchmaking.js');

        OW::getLanguage()->addKeyForJs('matchmaking', 'confirm_delete_text');
    }

    public function ruleEditFormResponder()
    {
        if( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $service = MATCHMAKING_BOL_Service::getInstance();
        $language = OW::getLanguage();

        if( OW::getRequest()->isPost() )
        {
            if( !empty($_POST['id']) && !empty($_POST['coefficient']) )
            {
                $question_match = $service->findById((int)$_POST['id']);

                if( $question_match !== null )
                {
                    $question_match->coefficient = $_POST['coefficient'];

                    $service->saveQuestionMatch($question_match);

                    exit(json_encode(array('result' => true, 'message' => OW::getLanguage()->text('matchmaking', 'rule_edit_form_success_message'))));
                }
            }

            if (!empty($_POST['name']) && !empty($_POST['coefficient']) && !empty($_POST['create']))
            {
                if ( $_POST['name'] == 'birthdate' )
                {
                    $existingQuestion = $this->questionService->findQuestionByName('match_age');
                    $questionLabel = $this->questionService->getQuestionLang('match_age');

                    if ( !empty($existingQuestion) && $existingQuestion->removable )
                    {
                        $existingQuestion->removable = 0;
                        BOL_QuestionService::getInstance()->saveOrUpdateQuestion($existingQuestion);
                    }
                }
                else
                {
                    $existingQuestion = $this->questionService->findQuestionByName('match_'.$_POST['name']);
                    $questionLabel = $this->questionService->getQuestionLang($_POST['name']);
                }

                $matchQuestionLabel = $language->text('matchmaking', 'match_question_lang_prefix', array('questionLabel'=>$questionLabel));

                if( $_POST['name'] == 'googlemap_location' )
                {
                    $question_match = new MATCHMAKING_BOL_QuestionMatch();
                    $question_match->questionName = $_POST['name'];
                    $question_match->matchQuestionName = $_POST['name'];
                    $question_match->coefficient = $_POST['coefficient'];
                    $question_match->match_type = 'exact';

                    $service->saveQuestionMatch($question_match);

                    OW::getFeedback()->info($language->text('matchmaking', 'new_rule_form_success_message'));

                    exit(json_encode(array('result' => true)));
                }

                if (empty($existingQuestion))
                {
                    //get question by parent name. if it doesn't exist, create one and then create match record

                    /**
                     * @var BOL_Question $newQuestion
                     */
                    $newQuestion = $this->questionService->findQuestionByName($_POST['name']);
                    $newQuestion->id = null;
                    $newQuestion->onSearch = 0;
                    $newQuestion->onView = 1;

                    if ( $newQuestion->name == 'birthdate' )
                    {
                        $newQuestion->name = 'match_age';
                        $newQuestion->parent = null;
                        $newQuestion->presentation = BOL_QuestionService::QUESTION_PRESENTATION_RANGE;
                        $newQuestion->type = BOL_QuestionService::QUESTION_VALUE_TYPE_TEXT;
                        $newQuestion->required = 1;
                        $newQuestion->onJoin = 1;
                        $newQuestion->onEdit = 1;
                        $newQuestion->removable = 0;
                    }
                    else
                    {
                        $newQuestion->name = 'match_'.$newQuestion->name;
                        $newQuestion->parent = $_POST['name'];

                        $newQuestion->presentation = BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX;
                        $newQuestion->sectionName = 'about_my_match';
                        $newQuestion->required = 0;
                        $newQuestion->onJoin = 0;
                        $newQuestion->onEdit = 0;
                        $newQuestion->removable = 1;
                    }

                    $this->questionService->createQuestion($newQuestion, $matchQuestionLabel);

                    $accTypeList = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

                    $accTypeNameList = array();
                    foreach ($accTypeList as $type)
                    {
                        $accTypeNameList[] = $type->accountType;
                    }

                    $this->questionService->addQuestionToAccountType($newQuestion->name, $accTypeNameList);

                    $existingQuestion = $newQuestion;
                }
                else
                {
                    $this->questionService->setQuestionLabel($existingQuestion->name, $matchQuestionLabel);
                }

                $question_match = new MATCHMAKING_BOL_QuestionMatch();
                $question_match->questionName = $_POST['name'];
                $question_match->matchQuestionName = $existingQuestion->name;
                $question_match->coefficient = $_POST['coefficient'];
                $question_match->match_type = 'exact';

                $service->saveQuestionMatch($question_match);

                OW::getFeedback()->info($language->text('matchmaking', 'new_rule_form_success_message'));

                exit(json_encode(array('result' => true)));
            }
        }

        exit(json_encode(array('result' => false, 'message' => OW::getLanguage()->text('matchmaking', 'rule_edit_form_error_message'))));
    }

    /**
     * Settings tab
     */
    public function settings()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('matchmaking', 'admin_page_heading'));
        $this->setPageTitle($language->text('matchmaking', 'admin_page_title_settings'));
        $this->setPageHeadingIconClass('ow_ic_chat');

        $this->addComponent('menu', $this->getMenu());

        $form = new MATCHMAKING_SettingsForm();

        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $form->process();
            OW::getFeedback()->info(OW::getLanguage()->text('matchmaking', 'configuration_settings_saved'));
            $this->redirect();
        }

        $intervals = array(
            array('label'=>$language->text('matchmaking', 'admin_settings_label_input_value1'), 'value'=>1),
            array('label'=>$language->text('matchmaking', 'admin_settings_label_input_value3'), 'value'=>3),
            array('label'=>$language->text('matchmaking', 'admin_settings_label_input_value_week'), 'value'=>7),
            array('label'=>$language->text('matchmaking', 'admin_settings_label_input_value_never'), 'value'=>-1)
        );

        $selectedInterval = OW::getConfig()->getValue('matchmaking', 'send_new_matches_interval');

        $selectedInterval = $selectedInterval == 0 ? -1 : $selectedInterval;

        $this->assign('intervals', $intervals);
        $this->assign('selectedInterval', $selectedInterval);
    }

    /**
     * Rule delete action
     */
    public function delete( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $service = MATCHMAKING_BOL_Service::getInstance();
        if ( $service->delete($params['id']) )
        {
            OW::getFeedback()->info(OW::getLanguage()->text('matchmaking', 'admin_rules_delete_successful'));
        }
        else
        {
            OW::getFeedback()->warning(OW::getLanguage()->text('matchmaking', 'admin_rules_no_changes'));
        }

        $this->redirect(OW::getRouter()->urlForRoute('matchmaking_admin_rules'));
    }

    public function uninstall( $params )
    {
        if ( isset($_POST['action']) && $_POST['action'] == 'delete_rules' )
        {
            $rules = MATCHMAKING_BOL_Service::getInstance()->findFieldsExceptRequired();
            foreach ($rules as $rule)
            {
                MATCHMAKING_BOL_Service::getInstance()->delete($rule->id);
            }
            BOL_PluginService::getInstance()->uninstall('matchmaking');

            $this->redirect(OW::getRouter()->urlForRoute('admin_plugins_installed'));
        }

        $js = new UTIL_JsGenerator();

        $js->jQueryEvent('#btn-delete-rules', 'click', 'if ( !confirm("'.OW::getLanguage()->text('base', 'are_you_sure').'") ) return false;');

        OW::getDocument()->addOnloadScript($js);

    }

    private function getMenu()
    {
        $language = OW::getLanguage();

        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('matchmaking', 'admin_menu_item_rules'));
        $item->setUrl(OW::getRouter()->urlForRoute('matchmaking_admin_rules'));
        $item->setKey('matchmaking_rules');
        $item->setIconClass('ow_ic_write');
        $item->setOrder(0);

        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('matchmaking', 'admin_menu_item_settings'));
        $item->setUrl(OW::getRouter()->urlForRoute('matchmaking_admin_settings'));
        $item->setKey('matchmaking_settings');
        $item->setIconClass('ow_ic_gear_wheel');
        $item->setOrder(1);

        $menuItems[] = $item;

        return new BASE_CMP_ContentMenu($menuItems);
    }
}

class MATCHMAKING_SettingsForm extends Form
{

    public function __construct()
    {
        parent::__construct('MATCHMAKING_SettingsForm');

        $language = OW::getLanguage();

        $send_new_matches_interval = new MATCHMAKING_CLASS_RadioGroupItemField('send_new_matches_interval');
        $send_new_matches_interval->setRequired();
        $this->addElement($send_new_matches_interval);


        $btn = new Submit('save');
        $btn->setValue($language->text('matchmaking', 'btn_label_save'));
        $this->addElement($btn);
    }

    public function process()
    {
        $values = $this->getValues();
        $config = OW::getConfig();

        $config->saveConfig('matchmaking', 'send_new_matches_interval',
            $values['send_new_matches_interval'] > 0 ? $values['send_new_matches_interval'] : 0);
        
        return array('result' => true);
    }

}
