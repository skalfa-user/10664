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

$_entityList = MODERATION_BOL_Service::getInstance()->findAllEntityList();
$entityList = array();
foreach ( $_entityList as $entity )
{
    /* @var $entity MODERATION_BOL_Entity */
    $entityList[$entity->entityType] = empty($entityList[$entity->entityType]) 
            ? array() 
            : $entityList[$entity->entityType];
    
    $entityList[$entity->entityType][] = $entity->entityId;
}

foreach ( $entityList as $entityType => $entityIds )
{
    try 
    {
        MODERATION_BOL_Service::getInstance()
                ->updateContentsStatus($entityType, $entityIds, BOL_ContentService::STATUS_ACTIVE);
    } 
    catch (Exception $ex) {
        // Pass
    }
}
