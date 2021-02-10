<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class HOTLIST_CLASS_EventHandler
{
    /**
     *
     * @var HOTLIST_BOL_Service 
     */
    private $service;
    
    public function __construct() 
    {
        $this->service = HOTLIST_BOL_Service::getInstance();
    }

    public function getCount( OW_Event $event )
    {
        $count = $this->service->getUserCount();
        $event->setData($count);

        return $count;
    }
    
    public function getListIdList( OW_Event $event )
    {
        $params = $event->getParams();
        $offset = empty($params["offset"]) ? 0 : $params["offset"];
        $count = empty($params["count"]) ? null : $params["count"];
        
        $dtoList = $this->service->getHotList($offset, $count);
        $list = array();
        foreach ( $dtoList as $dto )
        {
            $list[] = $dto->userId;
        }
        
        $event->setData($list);
        
        return $list;
    }
    
    public function addToList( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $data = array(
            "result" => true,
            "message" => null,
            "buyCredits" => false
        );
        
        $available = true;
        
        if ( !isset($params["checkCredits"]) || $params["checkCredits"] )
        {
            if ( !OW::getUser()->isAuthorized("hotlist", "add_to_list") )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus("hotlist", "add_to_list");
                $available = false;
                $data["result"] = false;
                
                if ( $status["status"] == BOL_AuthorizationService::STATUS_PROMOTED )
                {
                    $data["message"] = $status["msg"];
                    $data["buyCredits"] = true;
                }
            }
        }
        
        if ( $available )
        {
            $this->service->addUser($userId);
            BOL_AuthorizationService::getInstance()->trackAction('hotlist', 'add_to_list');
            
            $data["result"] = true;
            $data['message'] = OW::getLanguage()->text('hotlist', 'user_added');
        }
        
        $event->setData($data);
        
        return $data;
    }
    
    public function removeFromList( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $this->service->deleteUser($userId);
        
        $data = array(
            "result" => true,
            "message" => OW::getLanguage()->text('hotlist', 'user_removed')
        );

        $event->setData($data);
        
        return $data;
    }
    
    public function isUserAdded( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $userDto = $this->service->findUserById($userId);
        
        $data = $userDto !== null;
        $event->setData($data);
        
        return $data;
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $event->add(
            array(
                'hotlist' => array(
                    'label' => $language->text('hotlist', 'auth_group_label'),
                    'actions' => array(
                        'add_to_list'=>$language->text('hotlist', 'auth_action_label_add_to_list')
                    )
                )
            )
        );
    }
    
    public function genericInit()
    {
        OW::getEventManager()->bind("hotlist.count", array($this, "getCount"));
        OW::getEventManager()->bind("hotlist.get_id_list", array($this, "getListIdList"));
        OW::getEventManager()->bind("hotlist.add_to_list", array($this, "addToList"));
        OW::getEventManager()->bind("hotlist.remove_from_list", array($this, "removeFromList"));
        OW::getEventManager()->bind("hotlist.is_user_added", array($this, "isUserAdded"));
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
    }
    
    public function init()
    {
        
    }
}