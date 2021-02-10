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

class MODERATION_CTRL_Moderation extends BASE_CTRL_Moderation
{
    public function approve( $params )
    {
        $ownerId = empty($params["userId"]) ? null : $params["userId"];
        
        $this->setPageTitle(OW::getLanguage()->text("base", "moderation_tools"));
        $this->setPageHeading(OW::getLanguage()->text("base", "moderation_tools"));
        
        if ( empty($ownerId) )
        {
            $this->onlyModerators();
            $menu = $this->getMenu();

            if ( $menu === null )
            {
                $this->redirect(OW::getRouter()->urlForRoute("base.moderation_tools"));
            }

            $menu->deactivateElements();
            
            $menuItem = $menu->getElement("approve");
            if ( $menuItem )
            {
                $menuItem->setActive(true);
            }
            
            $this->addComponent("menu", $menu);
        }
        else
        {
            if ( $ownerId != OW::getUser()->getId() )
            {
                throw new Redirect403Exception;
            }
            
            $this->setPageHeading(OW::getLanguage()->text("moderation", "console_pending_approval"));
        }
        
        $groups = MODERATION_BOL_Service::getInstance()->getContentGroupsWithCount($ownerId);
        
        if ( !empty($params["group"]) && empty($groups[$params["group"]]) )
        {
            if ( $ownerId ) return $this->noItems();
            
            $this->redirect(OW::getRouter()->urlForRoute("moderation.approve_index"));
        }
        
        $currentGroup = empty($params["group"])
                ? reset($groups)
                : $groups[$params["group"]];
        
        if ( empty($currentGroup) )
        {
            if ( $ownerId ) return $this->noItems();
            
            $this->redirect(OW::getRouter()->urlForRoute("base.moderation_tools"));
        }
                
        $contentMenu = new BASE_CMP_VerticalMenu();
        
        $sideMenuOrder = 1;
        foreach ( $groups as $groupKey => $group )
        {
            $item = new BASE_VerticalMenuItem();
            $item->setKey($groupKey);
            $item->setUrl($group["url"]);
            $item->setNumber($group["count"]);
            $item->setLabel($group["label"]);
            $item->setActive($currentGroup["name"] == $group["name"]);
            $item->setOrder($sideMenuOrder++);
            
            $contentMenu->addElement($item);
        }
        
        $this->addComponent("contentMenu", $contentMenu);        
        
        // Paging
        $page = (isset($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
        $perPage = self::ITEMS_PER_PAGE;
        $limit = array(
            ($page - 1) * $perPage,
            $perPage
        );
        
        $this->addComponent("paging", new BASE_CMP_Paging($page, ceil($currentGroup["count"] / $perPage), 5));
        
        // List
        
        $entityRecords = MODERATION_BOL_Service::getInstance()->findEntityListByTypes($currentGroup["entityTypes"], $limit, $ownerId);
        $entityList = array();
        $userIds = array();
        
        foreach ( $entityRecords as $record )
        {
            $entityList[$record->entityType] = empty($entityList[$record->entityType])
                    ? array()
                    : $entityList[$record->entityType];
            
            $entityList[$record->entityType][] = $record->entityId;
        }
        
        $contentData = array();
        foreach ( $entityList as $entityType => $entityIds )
        {
            $infoList = BOL_ContentService::getInstance()->getContentList($entityType, $entityIds);
            foreach ( $infoList as $entityId => $info )
            {
                $userIds[] = $info["userId"];
                $contentData[$entityType . ':' . $entityId] = $info;
            }
        }
                
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);
        $tplRecords = array();
        
        foreach ( $entityRecords as $record )
        {
            $recordData = $record->getData();
            $content = $contentData[$record->entityType . ":" . $record->entityId];
            $contentPresenter = new BASE_CMP_ContentPresenter($content);
            
            $label = empty($content["label"]) ? $content["typeInfo"]["entityLabel"] : $content["label"];
            
            $string = null;
            if ( !isset($recordData["string"]) )
            {
                $string = OW::getLanguage()->text("moderation", "list_string_" . $recordData["reason"], array(
                     "content" => strtolower($label)
                ));
            } 
            else if ( is_string($recordData["string"]) )
            {
                $string = $recordData["string"];
            }
            else 
            {
                list($langPrefix, $langKey) = explode("+", $recordData["string"]["key"]);
                $langVars = empty($recordData["string"]["vars"]) ? array() : $recordData["string"]["vars"];
                $string = OW::getLanguage()->text($langPrefix, $langKey, $langVars);
            }
            
            $tplRecords[] = array(
                "content" => $contentPresenter->render(),
                "avatar" => $avatarData[$content["userId"]],
                "string" => $string,
                "contentLabel" => strtolower($label),
                "entityType" => $record->entityType,
                "entityId" => $record->entityId,
                "reason" => $recordData["reason"],
                "time" => UTIL_DateTime::formatDate($record->timeStamp)
            );
        }
        
        $uniqId = uniqid("m-");
        $this->assign("uniqId", $uniqId);
        
        $this->assign("items", $tplRecords);
        $this->assign("group", $currentGroup);
        
        $this->assign("actions", array(
            "delete" => true,
            "approve" => empty($ownerId)
        ));
        
        $this->assign("responderUrl", OW::getRouter()->urlFor(__CLASS__, "responder", array(
            "group" => $currentGroup["name"],
            "userId" => $ownerId
        )));
        
        OW::getLanguage()->addKeyForJs("base", "are_you_sure");
        OW::getLanguage()->addKeyForJs("base", "moderation_delete_confirmation");
        OW::getLanguage()->addKeyForJs("base", "moderation_delete_multiple_confirmation");
        OW::getLanguage()->addKeyForJs("base", "moderation_no_items_warning");
        
        $options = array(
            "groupLabel" => strtolower($currentGroup["label"])
        );
        
        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction("MODERATION_ApproveInit", array(
            $uniqId, $options
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function responder( $params )
    {
        if ( !OW::getRequest()->isPost() || !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect403Exception;
        }
        
        $sevice = MODERATION_BOL_Service::getInstance();
        
        $data = $_POST;
        $data["items"] = empty($data["items"]) ? array() : $data["items"];
        list($command, $type) = explode(".", $data["command"]);
        
        $backUrl = empty($params["userId"])
                ? OW::getRouter()->urlForRoute("moderation.approve", array(
                        "group" => $params["group"]
                    ))
                : OW::getRouter()->urlForRoute("moderation.user.approve", array(
                    "group" => $params["group"],
                    "userId" => $params["userId"]
                ));
        
        $itemKeys = $type == "single" ? array($data["item"]) : $data["items"];
        
        if ( empty($itemKeys) )
        {
            OW::getFeedback()->warning(OW::getLanguage()->text("base", "moderation_no_items_warning"));
            $this->redirect($backUrl);
        }
        
        $itemIds = array();
        foreach ( $itemKeys as $itemKey )
        {
            list($entityType, $entityId) = explode(":", $itemKey);
            $itemIds[$entityType] = empty($itemIds[$entityType]) ? array() : $itemIds[$entityType];
            
            $itemIds[$entityType][] = $entityId;
        }
        
        $affected = 0;
        $lastEntityType = null;
        
        foreach ( $itemIds as $entityType => $entityIds )
        {
            if ( $command == "delete" )
            {
                $entityListToDelete = array();
                if ( empty($params["userId"]) )
                {
                    $entityListToDelete = $entityIds;
                }
                else 
                {
                    $contentList = BOL_ContentService::getInstance()->getContentList($entityType, $entityIds);
                    foreach ( $contentList as $entityId => $content )
                    {
                        if ( $content["userId"] == $params["userId"] )
                        {
                            $entityListToDelete[] = $entityId;
                        }
                    }
                }
                
                BOL_ContentService::getInstance()->deleteContentList($entityType, $entityListToDelete);
            }
            
            if ( $command == "approve" )
            {
                $sevice->updateContentsStatus($entityType, $entityIds, BOL_ContentService::STATUS_ACTIVE);
            }
            
            $sevice->deleteEntityList($entityType, $entityIds);
            
            $lastEntityType = $entityType;
            $affected += count($entityIds);
        }
        
        
        // Feedback
        $assigns = array();
        
        $multiple = $affected > 1;
        
        if ( $multiple )
        {
            $tmp = BOL_ContentService::getInstance()->getContentGroups();
            $groupInfo = $tmp[$params["group"]];
            
            $assigns["content"] = strtolower($groupInfo["label"]);
            $assigns["count"] = $affected;
        }
        else
        {
            $typeInfo = BOL_ContentService::getInstance()->getContentTypeByEntityType($lastEntityType);
            $assigns["content"] = $typeInfo["entityLabel"];
        }
        
        $feedbackKey = $command == "delete" ? "base+moderation_feedback_delete" : "moderation+feedback_approve";
        
        list($langPrefix, $langKey) = explode("+", $feedbackKey);
        OW::getFeedback()->info(OW::getLanguage()
                ->text($langPrefix, $langKey . ($multiple ? "_multiple" : ""), $assigns));
        
        // Redirection
        $this->redirect($backUrl);
    }
    
    private function noItems()
    {
        $tpl = OW::getPluginManager()->getPlugin("moderation")->getCtrlViewDir() . "moderation_no_items.html";
        $this->setTemplate($tpl);
    }
}
