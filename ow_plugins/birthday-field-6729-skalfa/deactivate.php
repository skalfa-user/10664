<?php

$questionService = BOL_QuestionService::getInstance();

$questionName = 'birthdate';

$question = $questionService->findQuestionByName($questionName);

if ( !empty($question) )
{
    $question->required = true;
    $question->onJoin = true;
    $question->onEdit = false;
    $question->onSearch = true;
    $question->onView = true;

    $questionService->saveOrUpdateQuestion($question);

    $accountTypeList = $questionService->findAllAccountTypes();

    $list = [];

    foreach( $accountTypeList as $accountType )
    {
        $list[$accountType->name] = $accountType->name;
    }

    $questionService->addQuestionToAccountType($questionName, $list);
}