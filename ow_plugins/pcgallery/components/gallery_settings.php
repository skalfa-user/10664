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

class PCGALLERY_CMP_GallerySettings extends OW_Component
{
    public function __construct( $userId ) 
    {
        parent::__construct();
        
        $data = OW::getEventManager()->call("photo.entity_albums_find", array(
            "entityType" => "user",
            "entityId" => $userId
        ));
        
        $albums = empty($data["albums"]) ? array() : $data["albums"];
        
        $source = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_source", $userId);
        $this->assign("source", $source == "album" ? "album": "all");
        
        $selectedAlbum = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_album", $userId);
        
        $form = new Form("pcGallerySettings");
        $form->setEmptyElementsErrorMessage(null);
        $form->setAction(OW::getRouter()->urlFor("PCGALLERY_CTRL_Gallery", "saveSettings"));
        
        $element = new HiddenField("userId");
        $element->setValue($userId);
        $form->addElement($element);
        
        $element = new Selectbox("album");
        $element->setHasInvitation(true);
        $element->setInvitation(OW::getLanguage()->text("pcgallery", "settings_album_invitation"));
        
        $validator = new PCGALLERY_AlbumValidator();
        $element->addValidator($validator);
        
        $albumsPhotoCount = array();
        
        foreach ( $albums as $album ) 
        {
            $element->addOption($album["id"], $album["name"] . " ({$album["photoCount"]})");
            $albumsPhotoCount[$album["id"]] = $album["photoCount"];
            
            if ( $album["id"] == $selectedAlbum )
            {
                $element->setValue($album["id"]);
            }
        }
        
        OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString('window.pcgallery_settingsAlbumCounts = {$albumsCount};', array(
            "albumsCount" => $albumsPhotoCount
        )));
        
        $element->setLabel(OW::getLanguage()->text("pcgallery", "source_album_label"));
        
        $form->addElement($element);
        
        $submit = new Submit("save");
        $submit->setValue(OW::getLanguage()->text("pcgallery", "save_settings_btn_label"));
        $form->addElement($submit);
        
        $this->addForm($form);
    }
}

class PCGALLERY_AlbumValidator extends OW_Validator
{
    public function getError() {
        return OW::getLanguage()->text("pcgallery", "settings_album_required");
    }

    public function isValid($value) {
        return true;
    }
    
    public function getJsValidator() {
        return "{
            validate : function( value ){
                if ( $('#ugallery-source-album').get(0).checked ) {
                    if ( !value ) throw " . json_encode($this->getError()) . ";
                    var photoCount = parseInt(window.pcgallery_settingsAlbumCounts[value]);
                    
                    if ( photoCount < 7 ) {
                        throw " . json_encode(OW::getLanguage()->text("pcgallery", "settings_album_not_enough_photos")) . ";
                    }
                }
            }
        }";
    }
}