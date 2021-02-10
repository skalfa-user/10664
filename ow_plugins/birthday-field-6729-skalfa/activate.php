<?php

$questionService = BOL_QuestionService::getInstance();

$questionName = 'birthdate';
$question = $questionService->findQuestionByName($questionName);

if ( !empty($question) )
{
    $question->required = false;
    $question->onJoin = false;
    $question->onEdit = false;
    $question->onSearch = false;
    $question->onView = false;

    $questionService->saveOrUpdateQuestion($question);

    $accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();

    $list = [];

    foreach( $accountTypeList as $accountType )
    {
        $list[$accountType->name] = $accountType->name;
    }

    BOL_QuestionService::getInstance()->deleteQuestionToAccountType($questionName, $list);
}

if ( OW::getPluginManager()->isPluginActive('matchmaking') )
{
    OW::getDbo()->query("DELETE FROM `" . OW_DB_PREFIX . "matchmaking_question_match` WHERE `questionName` = 'birthdate' AND `matchQuestionName` = 'match_age';");

    $questionNameMatchAge = 'match_age';
    $question = $questionService->findQuestionByName($questionNameMatchAge);

    if ( !empty($question) )
    {
        $question->required = false;
        $question->onJoin = false;
        $question->onEdit = false;
        $question->onSearch = false;
        $question->onView = false;

        $questionService->saveOrUpdateQuestion($question);
    }

    $accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();

    $list = [];

    foreach( $accountTypeList as $accountType )
    {
        $list[$accountType->name] = $accountType->name;
    }

    BOL_QuestionService::getInstance()->deleteQuestionToAccountType($questionName, $list);
}

// TODO remove birthdate value to hidden type
try
{
    $sql = "DELETE `data` FROM `" . OW_DB_PREFIX . "base_question_data` as `data`
        INNER JOIN `" . OW_DB_PREFIX . "base_user` as `base_user` on ( `base_user`.`id` = `data`.`userId`)
        WHERE `data`.`questionName` = 'birthdate' OR `data`.`questionName` = 'match_age'";

    OW::getDbo()->query($sql);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}