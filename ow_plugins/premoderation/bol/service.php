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

class MODERATION_BOL_Service
{
    const EVENT_CONTENT_STATUS_UPDATE = "moderation.after_content_status_update";
    const EVENT_CONTENT_APPROVED = "moderation.after_content_approve";
    const EVENT_CONTENT_DISSAPPROVED = "moderation.after_content_dissapprove";
    
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MODERATION_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

 
    /**
     *
     * @var MODERATION_BOL_EntityDao
     */
    private $entityDao;

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        $this->entityDao = MODERATION_BOL_EntityDao::getInstance();
    }
    
    public function addEntity( $entityType, $entityId, $userId, array $data = array() )
    {
        $dto = $this->entityDao->findEntity($entityType, $entityId);
        if ( $dto === null )
        {
            $dto = new MODERATION_BOL_Entity();
        }
        
        if ( !empty($data) )
        {
            $dto->setData($data);
        }
        
        $dto->userId = $userId;
        $dto->timeStamp = time();
        $dto->entityType = $entityType;
        $dto->entityId = $entityId;
        
        $this->entityDao->save($dto);
    }
    
    public function isRequireApproval( $entityType )
    {
        $types = $this->getContentTypes();
        
        return isset($types[$entityType]) && $types[$entityType]["requireApproval"];
    }
    
    public function deleteEntityList( $entityType, array $entityIds = null )
    {
        $this->entityDao->deleteEntityList($entityType, $entityIds);
    }
    
    public function deleteEntityByIdList( $idList )
    {
        $this->entityDao->deleteByIdList($idList);
    }
    
    public function findAllEntityList()
    {
        return $this->entityDao->findAll();
    }
    
    public function findEntityListByTypes( array $entityTypes, array $limit = null, $ownerId = null )
    {
        return $this->entityDao->findByEntityTypeList($entityTypes, $limit, $ownerId);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return int
     */
    public function findCountForEntityTypeList( $entityTypes, $ownerId = null )
    {
        return $this->entityDao->findCountForEntityTypeList($entityTypes, $ownerId);
    }
    
    public function getContentTypes()
    {
        $types = BOL_ContentService::getInstance()->getContentTypes();
        $activeTypes = json_decode(OW::getConfig()->getValue("moderation", "content_types"), true);
        $out = array();
        
        foreach ( $types as $type )
        {
            if ( !in_array(BOL_ContentService::MODERATION_TOOL_APPROVE, $type["moderation"]) )
            {
                continue;
            }
            
            $entityType = $type["entityType"];
            $type["requireApproval"] = !isset($activeTypes[$entityType]) || $activeTypes[$entityType];
            $out[$type["entityType"]] = $type;
        }
        
        return $out;
    }
    
    public function getContentGroups()
    {
        $types = $this->getContentTypes();
        $groups = BOL_ContentService::getInstance()->getContentGroups(array_keys($types));
        
        foreach ( $groups as &$group )
        {
            foreach ( $group["entityTypes"] as $type )
            {
                if ( empty($group["requireApproval"]) )
                {
                    $group["requireApproval"] =  $types[$type]["requireApproval"];
                }
            }
        }
        
        return $groups;
    }
    
    public function getContentGroupsWithCount( $ownerId = null )
    {
        $contentTypes = $this->getContentTypeListWithCount($ownerId);
        $contentGroups = BOL_ContentService::getInstance()->getContentGroups(array_keys($contentTypes));
        
        foreach ( $contentGroups as &$group )
        {
            if ( $ownerId )
            {
                $group["url"] = OW::getRouter()->urlForRoute("moderation.user.approve", array(
                    "group" => $group["name"],
                    "userId" => $ownerId
                ));
            }
            else
            {
                $group["url"] = OW::getRouter()->urlForRoute("moderation.approve", array(
                    "group" => $group["name"]
                ));
            }
            
            $group["count"] = 0;
            foreach ( $group["entityTypes"] as $entityType )
            {
                $group["count"] += $contentTypes[$entityType]["count"];
            }
        }
        
        return $contentGroups;
    }
    
    public function getContentTypeListWithCount( $ownerId = null )
    {
        $contentTypes = BOL_ContentService::getInstance()->getContentTypes();

        $entityTypes = array_keys($contentTypes);
        $counts = $this->findCountForEntityTypeList($entityTypes, $ownerId);
        $out = array();
        
        foreach ( $counts as $entityType => $count )
        {
            if ( $ownerId === null && !OW::getUser()->isAuthorized($contentTypes[$entityType]["authorizationGroup"]) )
            {
                continue;
            }
            
            $out[$entityType] = $contentTypes[$entityType];
            $out[$entityType]["count"] = $count;
        }
        
        return $out;
    }
    
    public function updateContentsStatus( $entityType, $entityIds, $status )
    {
        BOL_ContentService::getInstance()->updateContentList($entityType, $entityIds, array(
            "status" =>  $status
        ));
                
        foreach ( $entityIds as $entityId )
        {
            $entity = $this->entityDao->findEntity($entityType, $entityId);
             
            if ( $entity === null )
            {
                continue;
            }

            $data = $entity->getData();
            
            $isNew = isset($data["reason"]) && $data["reason"] == "create";
            
            $eventParams = array(
                "entityType" => $entityType,
                "entityId" => $entityId,
                "status" => $status
            );
            
            $statusUpdateEvent = new OW_Event(self::EVENT_CONTENT_STATUS_UPDATE, $eventParams, $data);
            OW::getEventManager()->trigger($statusUpdateEvent);
            
            $eventName = $status == BOL_ContentService::STATUS_APPROVAL 
                ? self::EVENT_CONTENT_DISSAPPROVED
                : self::EVENT_CONTENT_APPROVED;
            
            $event = new OW_Event($eventName, array_merge($eventParams, array(
                "isNew" => $isNew
            )), $data);
            
            OW::getEventManager()->trigger($event);
        }
    }
}