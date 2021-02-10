<?php
/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location.components
 * @since 1.0
 */

class GOOGLELOCATION_CMP_MapEventList extends GOOGLELOCATION_CMP_MapEntityList
{
    public function __construct( $IdList, $lat, $lng, $backUri = null )
    {
        parent::__construct($IdList, $lat, $lng, $backUri);
        
        if ( count($IdList) > self::DISPLAY_COUNT )
        {
            $hash = GOOGLELOCATION_BOL_LocationService::getInstance()->saveEntityListToSession($IdList);

            $this->display = true;
            $this->url = ow::getRouter()->urlForRoute('googlelocation_event_list', array( 'lat' => $this->lat, 'lng' => $this->lng, 'hash' => $hash ) );
            $this->label = OW::getLanguage()->text('googlelocation', 'map_user_list_view_all_button_label', array( 'count' => count($IdList) ) );
        }
    }
    
    protected function getListCmp()
    {
        $bridge = new GOOGLELOCATION_CLASS_EventBridge();
        
        $data = $bridge->getAvatarData(array_slice($this->IdList, 0, self::DISPLAY_COUNT));
        $new = new GOOGLELOCATION_CMP_MiniAvatarList($data);
        
        switch(true)
        {
            case $this->count <= 8:
                    $new->setCustomCssClass('ow_big_avatar');
                break;
            default:
                    //$new->setCustomCssClass(BASE_CMP_MiniAvatarUserList::CSS_CLASS_MINI_AVATAR);
                break;
        }
        
        return $new;
    }
}