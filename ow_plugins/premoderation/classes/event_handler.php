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

class MODERATION_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var MODERATION_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return MODERATION_CLASS_EventHandler
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
     *
     * @var MODERATION_BOL_Service
     */
    private $service;
    
    private function __construct()
    {
        $this->service = MODERATION_BOL_Service::getInstance();
    }
    
    private function isRequireApproval( $entityType )
    {
        $contentType = BOL_ContentService::getInstance()->getContentTypeByEntityType($entityType);
        
        if ( $contentType === null )
        {
            return false;
        }
        
        $isModerator = OW::getUser()->isAuthorized($contentType["authorizationGroup"]);
        $isAdmin = OW::getUser()->isAdmin();
        
        if ( $isAdmin || $isModerator )
        {
            return false;
        }
        
        return $this->service->isRequireApproval($entityType);
    }
    
    public function onAfterAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
         
        if ( !empty($params["silent"]) || !empty($data["silent"]) )
        {
            return;
        }
                
        if ( !$this->isRequireApproval($params["entityType"]) )
        {
            return;
        }
        
        $contentInfo = BOL_ContentService::getInstance()->getContent($params["entityType"], $params["entityId"]);
        
        if ( empty($contentInfo) )
        {
            return;
        }
        
        $this->service->addEntity($params["entityType"], $params["entityId"], $contentInfo["userId"], array_merge(array(
            "reason" => "create"
        ), $data));
        
        MODERATION_BOL_Service::getInstance()
                ->updateContentsStatus($params["entityType"], array($params["entityId"]), BOL_ContentService::STATUS_APPROVAL);
    }
    
    public function onAfterChange( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
                
        if ( !empty($params["silent"]) || !empty($data["silent"]) )
        {
            return;
        }
        
        if ( !$this->isRequireApproval($params["entityType"]) )
        {
            return;
        }
        
        $contentInfo = BOL_ContentService::getInstance()->getContent($params["entityType"], $params["entityId"]);
        
        if ( empty($contentInfo) )
        {
            return;
        }
        
        $this->service->addEntity($params["entityType"], $params["entityId"], $contentInfo["userId"], array_merge(array(
            "reason" => "update"
        ), $data));
        
        MODERATION_BOL_Service::getInstance()
                ->updateContentsStatus($params["entityType"], array($params["entityId"]), BOL_ContentService::STATUS_APPROVAL);
    }
    
    public function onBeforeDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $this->service->deleteEntityList($params["entityType"], array($params["entityId"]));
    }
    
    public function onCollectModerationWidgetContent( BASE_CLASS_EventCollector $event )
    {
        $contentGroups = MODERATION_BOL_Service::getInstance()->getContentGroupsWithCount();
        
        if ( empty($contentGroups) )
        {
            return;
        }
        
        $contentsCmp = new BASE_CMP_ModerationPanelList($contentGroups);
        
        $event->add(array(
            "name" => "approve",
            "label" => OW::getLanguage()->text("moderation", "for_approve"),
            "content" => $contentsCmp->render()
        ));
    }
    
    public function onCollectModerationToolsMenu( BASE_CLASS_EventCollector $event )
    {
        $contentGroups = MODERATION_BOL_Service::getInstance()->getContentGroupsWithCount();
        
        if ( empty($contentGroups) )
        {
            return;
        }
        
        $event->add(array(
            "url" => OW::getRouter()->urlForRoute("moderation.approve_index"),
            "label" => OW::getLanguage()->text("moderation", "for_approve"),
            "iconClass" => "ow_ic_delete",
            "key" => "approve"
        ));
    }

    public function onActionToolbarAddUserApproveActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( OW::getUser()->isAdmin() )
        {
            return;
        }

        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( BOL_UserService::getInstance()->isApproved($userId) )
        {
            return;
        }

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => OW::getRouter()->urlFor('BASE_CTRL_User', 'approve', array('userId' => $userId)),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_user_approve_label'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.approve_user"
        );

        $event->add($action);
    }

    public function collectConsoleItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        $pendingApproval = MODERATION_BOL_Service::getInstance()->getContentGroupsWithCount(OW::getUser()->getId());
        
        if ( !empty($pendingApproval) )
        {
            $label =OW::getLanguage()->text('moderation', 'console_pending_approval');
            $event->add(new MODERATION_CMP_ConsoleItem($pendingApproval, $label, "pending-approval", "ow_pending_approval_list"));
        }
        
        $forApproval = MODERATION_BOL_Service::getInstance()->getContentGroupsWithCount();
        
        if ( !empty($forApproval) )
        {
            $label = OW::getLanguage()->text('moderation', 'console_for_approval');
            $event->add(new MODERATION_CMP_ConsoleItem($forApproval, $label, "for-approval", "ow_for_approval_list"));
        }
    }
    
    public function approveEntity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $service = MODERATION_BOL_Service::getInstance();
        
        $content = BOL_ContentService::getInstance()->getContent($params["entityType"], $params['entityId']);
        
        if ( empty($content) )
        {
            $data["error"] = "Content not found";
            
            return;
        }
        
        $type = $content["typeInfo"];
        $authorized = OW::getUser()->isAdmin() || OW::getUser()->isAuthorized($type["authorizationGroup"]);
        
        if ( !$authorized )
        {
            $data["error"] = "Not authorized";
            
            return;
        }
        
        $service->updateContentsStatus($params["entityType"], array($params["entityId"]), BOL_ContentService::STATUS_ACTIVE);
        $service->deleteEntityList($params["entityType"], array($params['entityId']));
        
        $data["message"] = OW::getLanguage()->text("moderation", "feedback_approve", array(
            "content" => $type["entityLabel"]
        ));
        
        $event->setData($data);
        
        return $data;
    }
    
    public function onAfterConfigSave( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( !($params["key"] == "base" && $params["name"] == "mandatory_user_approve") )
        {
            return;
        }
        
        $activeTypes = json_decode(OW::getConfig()->getValue("moderation", "content_types"), true);
        $activeTypes[BASE_CLASS_ContentProvider::ENTITY_TYPE_PROFILE] = (bool) $params["value"];
        
        OW::getConfig()->saveConfig("moderation", "content_types", json_encode($activeTypes));
    }
    
    public function init()
    {
        OW::getEventManager()->bind("moderation.approve", array($this, "approveEntity"));
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectConsoleItems'));
        
        OW::getEventManager()->bind(BOL_ContentService::EVENT_AFTER_ADD, array($this, "onAfterAdd"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_AFTER_CHANGE, array($this, "onAfterChange"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_BEFORE_DELETE, array($this, "onBeforeDelete"));
        
        OW::getEventManager()->bind(BASE_CMP_ModerationToolsWidget::EVENT_COLLECT_CONTENTS, array($this, 'onCollectModerationWidgetContent'));
        OW::getEventManager()->bind("base.moderation_tools.collect_menu", array($this, 'onCollectModerationToolsMenu'));
        OW::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserApproveActionTool'));
        
        OW::getEventManager()->bind(BOL_ConfigService::EVENT_AFTER_SAVE, array($this, "onAfterConfigSave"));
    }
}