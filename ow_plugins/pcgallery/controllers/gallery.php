<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class PCGALLERY_CTRL_Gallery extends OW_ActionController
{
    public function saveSettings()
    {
        $source = $_POST["source"] == "all" ? "all" : "album";
        $album = $_POST["album"];
        $userId = $_POST["userId"];
        
        if ( $userId != OW::getUser()->getId() && !OW::getUser()->isAuthorized("pcgallery") )
        {
            throw new Redirect403Exception();
        }
        
        BOL_PreferenceService::getInstance()->savePreferenceValue("pcgallery_album", $album, $userId);
        BOL_PreferenceService::getInstance()->savePreferenceValue("pcgallery_source", $source, $userId);
        
        OW::getFeedback()->info(OW::getLanguage()->text("pcgallery", "settings_saved_message"));
        $this->redirect(BOL_UserService::getInstance()->getUserUrl($userId));
    }
}
