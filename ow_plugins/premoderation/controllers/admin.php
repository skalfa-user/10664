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

class MODERATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $groups = MODERATION_BOL_Service::getInstance()->getContentGroups();
        
        if ( OW::getRequest()->isPost() )
        {
            $selectedGroups = empty($_POST["groups"]) ? array() : $_POST["groups"];
            
            $types = array(); 
            foreach ( $groups as $group )
            {
                $selected = in_array($group["name"], $selectedGroups);
                foreach ( $group["entityTypes"] as $type )
                {
                    $types[$type] = $selected;
                }
            }
            
            // Sync with mandatory approve config
            if ( isset($types[BASE_CLASS_ContentProvider::ENTITY_TYPE_PROFILE]) )
            {
                OW::getConfig()->saveConfig('base', 'mandatory_user_approve', (int) $types[BASE_CLASS_ContentProvider::ENTITY_TYPE_PROFILE]);
            }
            
            OW::getConfig()->saveConfig("moderation", "content_types", json_encode($types));
            
            OW::getFeedback()->info(OW::getLanguage()->text("moderation", "content_types_saved_message"));
            $this->redirect(OW::getRouter()->urlForRoute("moderation.admin"));
        }
        
        $this->setPageHeading(OW::getLanguage()->text("moderation", "admin_heading"));
        $this->setPageTitle(OW::getLanguage()->text("moderation", "admin_title"));
        
        $form = new Form("contentTypes");
        
        $submit = new Submit("save");
        $submit->setLabel(OW::getLanguage()->text("admin", "save_btn_label"));
        $form->addElement($submit);
        
        $this->addForm($form);
        
        $this->assign("groups", $groups);
    }
}
