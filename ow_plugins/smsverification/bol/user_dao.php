<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Data Access Object for `ow_smsverification_country_phone_code` table.
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.smsverification.bol
 * @since 1.7.6
 */
class SMSVERIFICATION_BOL_UserDao extends OW_BaseDao
{
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var SMSVERIFICATION_BOL_CountryDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return SMSVERIFICATION_BOL_CountryDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'SMSVERIFICATION_BOL_User';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'smsverification_users';
    }
    
    public function setUserData($userId, $telNumber, $userCode, $countryCode, $userCountry)
    {
        $sql = "UPDATE {$this->getTableName()} SET number = :telNumber, code = :code, countryCode = :countryCode, country = :country  WHERE userId = :userId ";
        $this->dbo->query($sql, array('telNumber' => $telNumber, 'country' => $userCountry, 'countryCode' => $countryCode, 'code' => $userCode,  'userId' => $userId));
    }
    
    public function resetUserData($userId)
    {
        $sql = "UPDATE {$this->getTableName()} SET number = :telNumber, code = :code, countryCode = :countryCode, country = :country  WHERE userId = :userId ";
        $this->dbo->query($sql, array('telNumber' => null, 'country' => null, 'countryCode' => null, 'code' => null,  'userId' => $userId));
    }
    
    public function getUserDataByUserId($userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', (int)$userId);
        return $this->findListByExample($example);
    }   
    
    public function checkCode($userId, $userCode)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $userData = $this->findObjectByExample($example);
        
        if ($userData->code == $userCode)
        {
            return true;
        }
        return false;
    }
    
    public function getVerifyed($userId)
    {
        $example =  new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        $userData = $this->findObjectByExample($example);
        
        if( is_null($userData) )
        {
            return -1;
            
        }
        
        else if ( (int) $userData->isVeryfied != 1 &&  is_null($userData->code) )
        {
            return 0;
        }
        else if( (int) $userData->isVeryfied != 1 &&  !is_null($userData->code) )
        {
            return 1;
        }        
        
        else return null;
        
        
    }
    
    public function setUserAsAutorized( $userId )
    {
        $sql = "UPDATE {$this->getTableName()} SET isVeryfied = 1 WHERE userId = :userId ";
        $this->dbo->query($sql, array('userId' => $userId));
    }
    
    

}