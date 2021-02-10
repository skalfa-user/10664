<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'controllers' . DS . 'join.php';

class SKADATE_CLASS_JoinForm extends JoinForm
{
    protected function init( array $accounts )
    {
        
    }

    public function getRealValues()
    {
        $list = $this->sortedQuestionsList;

        $values = $this->getValues();
        $result = array();

        if ( !empty($list) )
        {
            foreach ( $values as $fakeName => $value )
            {
                if ( !empty($list[$fakeName]) && isset($list[$fakeName]['fake']) && $list[$fakeName]['fake'] == false )
                {
                    $result[$list[$fakeName]['realName']] = $value;
                }

                if ( $fakeName == 'accountType' )
                {
                    $result[$fakeName] = $value;
                }
            }

            if ( !empty($result['sex']) )
            {

                $gender2accountType = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

                if ( !empty($gender2accountType) )
                {
                    /* @var $dto SKADATE_BOL_AccountTypeToGender */
                    foreach ( $gender2accountType as $dto )
                    {
                        if ( $dto->genderValue == $result['sex'] )
                        {
                            $result['accountType'] = $dto->accountType;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getQuestions()
    {
        $this->questions = array();

        if ( $this->isLastStep )
        {
            $this->questions = BOL_QuestionService::getInstance()->findSignUpQuestionsForAccountType($this->accountType);

            foreach ( $this->questions as $key => $question )
            {
                if ( in_array($question['name'], array('sex', 'match_sex')) )
                {
                    unset($this->questions[$key]);
                }
            }
        }
        else
        {
            $this->questions = BOL_QuestionService::getInstance()->findBaseSignUpQuestions();

            $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList(array('sex', 'match_sex'));

            if ( !empty($questionDtoList['sex']) )
            {
                $sex = get_object_vars($questionDtoList['sex']);
                array_push($this->questions, $sex);
            }
            
            if ( !empty($questionDtoList['match_sex']) )
            {
                $matchSex = get_object_vars($questionDtoList['match_sex']);
                array_push($this->questions, $matchSex);
            }
        }
    }
}
