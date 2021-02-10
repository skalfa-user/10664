<?php

$questionService = BOL_QuestionService::getInstance();

$questionName = 'match_sex';
$question = $questionService->findQuestionByName($questionName);

if ( !empty($question) )
{
    $question->onView = false;

    $questionService->saveOrUpdateQuestion($question);
}

$questionName = 'allow_suppliers_contact_you';
$question = $questionService->findQuestionByName($questionName);

if ( !empty($question) )
{
    $question->removable = false;

    $questionService->saveOrUpdateQuestion($question);
}
