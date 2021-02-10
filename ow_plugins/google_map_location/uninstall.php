<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location
 * @since 1.0
 */

BOL_QuestionService::getInstance()->deleteSection('location');
$question = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

if ( !empty($question) )
{
    BOL_QuestionService::getInstance()->deleteQuestion(array($question->id));
    BOL_QuestionService::getInstance()->deleteQuestionToAccountTypeByQuestionName('googlemap_location');
}

BOL_QuestionDataDao::getInstance()->deleteByQuestionNamesList(array('googlemap_location'));