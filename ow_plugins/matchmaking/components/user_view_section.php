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
 * Matchmaking section on profile view page
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.matchmaking.components
 * @since 1.6.1
 */
class MATCHMAKING_CMP_UserViewSection extends OW_Component
{
    public function __construct( $section, $sectionQuestions, $data, $labels, $template = 'table', $hideSection = false, $additionalParams = array() )
    {
        parent::__construct();

        $user = BOL_UserService::getInstance()->findUserById($additionalParams['userId']);

        if (!isset($user))
        {
            $this->setVisible(false);
            return;
        }

        $sections = array();
        foreach($sectionQuestions as $question)
        {
            if ($question['parent'] == null) {
                $accTypeList = BOL_QuestionService::getInstance()->findAccountTypeListByQuestionName($question['name']);
            }
            else
            {
                $accTypeList = BOL_QuestionService::getInstance()->findAccountTypeListByQuestionName($question['parent']);
            }

            if (count($accTypeList) > 1)
            {
                if (!isset($sections['general']))
                {
                    $sections['general'] = array();
                    $sections['general']['name'] = 'general';
                    $sections['general']['label'] = OW::getLanguage()->text('matchmaking', "questions_general_label");
                    $sections['general']['questions'] = array();
                }

                $sections['general']['questions'][] = $question;
            }
            else
            {
                if (!isset($sections[$accTypeList[0]->accountType]))
                {
                    $sections[$accTypeList[0]->accountType] = array();
                    $sections[$accTypeList[0]->accountType]['name'] = $accTypeList[0]->accountType;
                    $sections[$accTypeList[0]->accountType]['label'] = BOL_QuestionService::getInstance()->getAccountTypeLang($accTypeList[0]->accountType);
                    $sections[$accTypeList[0]->accountType]['questions'] = array();
                }

                $sections[$accTypeList[0]->accountType]['questions'][] = $question;
            }
        }

        $this->assign('sections', $sections);

        $this->assign('sectionName', $section);
        $this->assign('questions', $sectionQuestions);
        $this->assign('generalQuestions', !empty($sections['general']['questions']) ? $sections['general']['questions'] : array() );
        $this->assign('questionsData', $data);
        $this->assign('labels', $labels);
        $this->assign('display', !$hideSection);


        $genderAccTypes = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
        $lookingForValue = BOL_QuestionService::getInstance()->getQuestionData(array($user->getId()), array('match_sex'));

        $lookingForValues = array();
        foreach($genderAccTypes as $type)
        {
            if (!empty($lookingForValue[$user->getId()]['match_sex']) && $lookingForValue[$user->getId()]['match_sex'] & $type->genderValue)
            {
                if (!in_array($type->genderValue, $lookingForValues))
                {
                    $lookingForValues[] = $type->genderValue;
                }
            }
        }

        $mode = (count($lookingForValues) > 1) ? 'floatbox' : 'table';

        switch ($mode)
        {
            case 'floatbox':
                $this->setTemplate(OW::getPluginManager()->getPlugin('matchmaking')->getCmpViewDir() . 'user_view_section_floatbox.html' );
                break;
            case 'table':
                $this->setTemplate(OW::getPluginManager()->getPlugin('matchmaking')->getCmpViewDir() . 'user_view_section_table.html' );
                break;
        }

        $title = json_encode(OW::getLanguage()->text('base', 'questions_section_about_my_match_label'));
        $js = UTIL_JsGenerator::composeJsString(" $('#matchmakingViewMatchSectionBtn').click(function(){

            var matchmakingViewMatchSectionFloatbox = new OW_FloatBox({\$title: {$title}, \$contents: $('#matchmakingViewMatchSectionFloatbox'), width: '550px'});

        }); ");

        OW::getDocument()->addOnloadScript($js);
    }
}