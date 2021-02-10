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
 * @package ow_plugins.smsverification.classes
 * @since 1.7.6
 */
class SMSVERIFICATION_CLASS_EventHandler
{
    /**
     * @var SMSVERIFICATION_CLASS_EventHandler
     */
    private static $classInstance;
    /**
     *
     * @var SMSVERIFICATION_BOL_Service 
     */
    private $service;
    
    /**
     *
     * @var SMSVERIFICATION_BOL_User
     */
    private $registeredUser;

    /**
     * @return SMSVERIFICATION_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() 
    {
        $this->service = SMSVERIFICATION_BOL_Service::getInstance();
        $this->registeredUser = new SMSVERIFICATION_BOL_User;
    }

    public function afterRegister( $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $this->registeredUser->userId = $params['userId'];
        $this->registeredUser->isVeryfied = 0;
        $this->service->saveRegisteredUser($this->registeredUser);  
        
    }
    
    public function onPluginsInitCheckUserStatus()
    {
        $mandatorySmsVerification = OW::getConfig()->getValue('smsverification', 'mandatorySmsVerification');
        
        if(!OW::getUser()->isAuthenticated())
        {
            return;
        }
        $userId = OW::getUser()->getId();
        $getVerifyed = $this->service->getVerifyed($userId);
        
        if ( $getVerifyed === 0 )
        {
            OW::getRequestHandler()->setCatchAllRequestsAttributes('smsverification.verified_user', array('controller' => 'SMSVERIFICATION_CTRL_MainController', 'action' => 'inputNumber'));
            OW::getRequestHandler()->addCatchAllRequestsExclude('smsverification.verified_user', 'BASE_CTRL_User', 'signOut');

        }
        else if( $getVerifyed === 1)
        {
            OW::getRequestHandler()->setCatchAllRequestsAttributes('smsverification.verified_user', array('controller' => 'SMSVERIFICATION_CTRL_MainController', 'action' => 'inputCode'));
            OW::getRequestHandler()->addCatchAllRequestsExclude('smsverification.verified_user', 'BASE_CTRL_User', 'signOut');

        }
        else if( $getVerifyed === -1 && $mandatorySmsVerification == true )
        {
             $this->registeredUser->userId = $userId;
             $this->registeredUser->isVeryfied = 0;
             $this->service->saveRegisteredUser($this->registeredUser);
             
             OW::getRequestHandler()->setCatchAllRequestsAttributes('smsverification.verified_user', array('controller' => 'SMSVERIFICATION_CTRL_MainController', 'action' => 'inputNumber'));
             OW::getRequestHandler()->addCatchAllRequestsExclude('smsverification.verified_user', 'BASE_CTRL_User', 'signOut');
 
        }
    }    

    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'afterRegister'));
        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'onPluginsInitCheckUserStatus'));
    }
}