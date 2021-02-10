<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
final class SKADATE_BOL_AccountTypeToGenderService
{
    /**
     * Singleton instance.
     *
     * @var SKADATE_BOL_AccountTypeToGenderService
     */
    private static $classInstance;
    private $accountTypeToGenderDao;
    private $cache;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SKADATE_BOL_AccountTypeToGenderService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->accountTypeToGenderDao = SKADATE_BOL_AccountTypeToGenderDao::getInstance();
        $this->cache = array();
    }

    public function updateGenderValues()
    {
        $this->cache = array();

        $genderToAccountTypeList = $this->accountTypeToGenderDao->findAll();
        $accountTypesList = BOL_QuestionService::getInstance()->findAllAccountTypes();

        $genderValues = array();
        $accountTypes = array();
        $accountNameList = array();
        $questionValues = array();

        $deleteList = array();
        $addList = array();

        foreach ( $accountTypesList as $value )
        {
            $accountTypes[$value->name] = $value;
            $accountNameList[$value->name] = $value->name;
        }

        foreach ( $genderToAccountTypeList as $value )
        {
            if ( !empty($accountTypes[$value->accountType]) )
            {
                $genderValues[$value->genderValue] = $value->accountType;
            }
            else
            {
                $deleteList[$value->genderValue] = $value->genderValue;
            }
        }

        $addList = array_diff($accountNameList, $genderValues);

        // delete not exists gender values
        if ( !empty($deleteList) )
        {
            BOL_QuestionService::getInstance()->deleteQuestionValues('sex', $deleteList);
            $this->deleteByGenderList($deleteList);
        }

        // get question values dto
        $values = BOL_QuestionService::getInstance()->findQuestionValues('sex');

        foreach ( $values as $value )
        {
            $questionValues[$value->value] = $value;
        }

        // update gender values
        $itemNumber = 0;

        for ( $i = 0; $i < 31; $i++ )
        {
            $intValue = pow(2, $i);

            if ( empty($questionValues[$intValue]) )
            {
                $accountType = array_shift($addList);

                // question has empty value
                if ( !empty($accountType) )
                {
                    // add values
                    $value = new BOL_QuestionValue();
                    $value->questionName = 'sex';
                    $value->value = $intValue;
                    $value->sortOrder = $accountTypes[$accountType]->sortOrder;

                    BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($value);

                    $a2gDto = new SKADATE_BOL_AccountTypeToGender();
                    $a2gDto->accountType = $accountType;
                    $a2gDto->genderValue = $intValue;

                    $this->save($a2gDto);

                    $itemNumber++;
                }
            }
            else if ( empty($genderValues[$intValue]) )
            {
                $accountType = array_shift($addList);

                // question has value which not join to account type
                if ( !empty($accountType) )
                {
                    $a2gDto = new SKADATE_BOL_AccountTypeToGender();
                    $a2gDto->accountType = $accountType;
                    $a2gDto->genderValue = $intValue;

                    $this->save($a2gDto);

                    $itemNumber++;
                }
                else
                {
                    BOL_QuestionService::getInstance()->deleteQuestionValue('sex', $intValue);
                }
            }
            else
            {
                // question has value which join to account type
                $accountType = $genderValues[$intValue];

                if ( $questionValues[$intValue]->sortOrder != $accountTypes[$accountType]->sortOrder )
                {
                    // update values order
                    $value = $questionValues[$intValue];
                    $value->sortOrder = $accountTypes[$accountType]->sortOrder;

                    BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($value);
                }
            }
        }
    }

    public function findAll()
    {
        if ( empty($this->cache) )
        {
            $this->cache = $this->accountTypeToGenderDao->findAll();
        }

        return $this->cache;
    }

    public function getGender( $accountType )
    {
        if ( empty($accountType) )
        {
            return null;
        }

        $dtoList = $this->findAll();

        /* @var $dto SKADATE_BOL_AccountTypeToGender */
        foreach ( $dtoList as $dto )
        {
            if ( $accountType == $dto->accountType )
            {
                return $dto->genderValue;
            }
        }

        return null;
    }

    public function getAccountType( $gender )
    {
        if ( empty($gender) )
        {
            return null;
        }

        $dtoList = $this->findAll();

        /* @var $dto SKADATE_BOL_AccountTypeToGender */
        foreach ( $dtoList as $dto )
        {
            if ( $gender == $dto->genderValue )
            {
                return $dto->accountType;
            }
        }

        return null;
    }

    public function deleteByAccountTypeList( array $accountTypeList )
    {
        $this->cache = array();
        $this->accountTypeToGenderDao->deleteByAccountTypeList($accountTypeList);
    }

    public function deleteByGenderList( array $genderlist )
    {
        $this->cache = array();
        $this->accountTypeToGenderDao->deleteByGenderList($genderlist);
    }

    public function save( SKADATE_BOL_AccountTypeToGender $dto )
    {
        $this->accountTypeToGenderDao->batchReplace(array($dto));
    }
}
