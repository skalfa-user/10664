<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_BOL_AccountTypeToGenderDao extends OW_BaseDao
{
    const ACCOUNT_TYPE = 'accountType';
    const GENDER_VALUE = 'genderValue';

    /**
     * Singleton instance.
     *
     * @var BOL_AttachmentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AttachmentDao
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
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'SKADATE_BOL_AccountTypeToGender';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'skadate_account_type_to_gender';
    }

    public function findByAccountType( $accountType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ACCOUNT_TYPE, $accountType);

        return $this->findListByExample($example);
    }

    public function findByGenderValue( $gender )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::GENDER_VALUE, $gender);

        return $this->findListByExample($example);
    }

    public function deleteByAccountType( $accountType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ACCOUNT_TYPE, $accountType);

        return $this->deleteByExample($example);
    }

    public function deleteByGenderValue( $gender )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::GENDER_VALUE, $gender);

        return $this->deleteByExample($example);
    }

    public function findByGenderAndAccountType( $accountType, $gender )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ACCOUNT_TYPE, $accountType);
        $example->andFieldEqual(self::GENDER_VALUE, $gender);

        return $this->findObjectByExample($example);
    }

    public function deleteByAccountTypeList( $accountTypeList )
    {
        if ( empty($accountTypeList) || !is_array($accountTypeList) )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::ACCOUNT_TYPE, $accountTypeList);

        $this->deleteByExample($example);
    }

    public function deleteByGenderList( $genderList )
    {
        if ( empty($genderList) || !is_array($genderList) )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::GENDER_VALUE, $genderList);

        $this->deleteByExample($example);
    }

    public function batchReplace( array $dtoList )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $dtoList);
    }
}
