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
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow_plugins.smsverification.bol
 * @since 1.7.6
 */
class SMSVERIFICATION_BOL_Service
{    
      /**
     *
     * @var SMSVERIFICATION_BOL_CountryDao
     */
    private $countryDao;
     /**
      *
      * @var SMSVERIFICATION_BOL_UserDao 
      */
    private $usersDao;


    private static $classInstance;
    
    public static function getInstance()
    {
      if ( null === self::$classInstance )
      {
          self::$classInstance = new self();
      }
      
      return self::$classInstance;
    }
    private function __construct() 
    {
      $this->countryDao = SMSVERIFICATION_BOL_CountryDao::getInstance();
      $this->usersDao = SMSVERIFICATION_BOL_UserDao::getInstance();
    }
    
    public function getCountries()
    {
        $list = $this->countryDao->getCountries();        
        return $list;       
    }
    
    public function saveRegisteredUser ( $userData )
    {
        $this->usersDao->save($userData);
    }
    
    public function getUserDataByUserId( $userId )
    {
        return $this->usersDao->getUserDataByUserId($userId);
    }
    
    public function checkCode( $userId, $userCode )
    {       
        return $this->usersDao->checkCode($userId, $userCode);        
    }
    
    public function setUserAsAutorized( $userId )
    {
        $this->usersDao->setUserAsAutorized($userId);
    }
    
    public function getCountryByCode()
    {
        return $this->countryDao->getCountryByCode($countryCode);
    }
    
    public function getVerifyed($userId)
    {
        return $this->usersDao->getVerifyed($userId);
    }
    
    public function setUserData($userId, $telNumber, $userCode, $countryCode, $userCountry)
    {
        $this->usersDao->setUserData($userId, $telNumber, $userCode, $countryCode, $userCountry);
    }
    
    public function resetUserData($userId)
    {
        return $this->usersDao->resetUserData($userId);
    }

}