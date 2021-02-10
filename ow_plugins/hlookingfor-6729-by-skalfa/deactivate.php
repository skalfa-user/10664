<?php

$questionService = BOL_QuestionService::getInstance();

$questionName = 'match_sex';

$question = $questionService->findQuestionByName($questionName);

if ( !empty($question) )
{
    $question->onView = true;

    $questionService->saveOrUpdateQuestion($question);
}

$questionName = 'allow_suppliers_contact_you';
$question = $questionService->findQuestionByName($questionName);

if ( !empty($question) )
{
    $question->removable = true;

    $questionService->saveOrUpdateQuestion($question);
}
