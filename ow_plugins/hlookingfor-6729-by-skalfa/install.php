<?php

$questionService = BOL_QuestionService::getInstance();
$sectionName = 'f90cde5913235d172603cc4e7b9726e3';
$questionName = 'allow_suppliers_contact_you';

$question = new BOL_Question();
$question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_RADIO;
$question->type = BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT;
$question->removable = 0;
$question->required = 1;
$question->onEdit = 1;
$question->onJoin = 1;
$question->onSearch = 0;
$question->onView = 0;
$question->sectionName = $sectionName;
$question->name = $questionName;
$question->sortOrder = 0;
$question->columnCount = 1;
$question->base = 0;

try
{
    $questionService->saveOrUpdateQuestion($question);
    $questionService->setQuestionLabel($questionName, 'Allow Suppliers Contact You');

    $order = 1;
    foreach (['Yes', 'No'] as $select)
    {
        $questionService->addQuestionValue( $question->name, pow(2, $order), $select, $order );

        $order++;
    }
}
catch ( Exception $e )
{

}

$accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();

$list = [];

foreach( $accountTypeList as $accauntType )
{
    if ( $accauntType->name == '8cc28eaddb382d7c6a94aeea9ec029fb' )
    {
        $list[$accauntType->name] = $accauntType->name;
    }
}

BOL_QuestionService::getInstance()->addQuestionListToAccountTypeList([$questionName], $list);