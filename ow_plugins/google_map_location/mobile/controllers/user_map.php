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
 * @package ow_plugins.google_maps_location.controllers
 * @since 1.0
 */


class GOOGLELOCATION_MCTRL_UserMap extends OW_MobileActionController
{
    const MAX_USERS_COUNT = 16;
    
    public function map()
    {
        $menu = BASE_MCTRL_UserList::getMenu('map');
        $this->addComponent('menu', $menu);

        $language = OW::getLanguage();
        $this->setPageHeading($language->text('googlelocation', 'map_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_bookmark');


        $map = GOOGLELOCATION_BOL_LocationService::getInstance()->getMobileUserListMapCmp('all',OW::getRouter()->getUri());        
        $this->addComponent("map", $map);
    }
}
