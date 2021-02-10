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


class GOOGLELOCATION_MCTRL_UserList extends OW_MobileActionController
{
    public function __construct()
    {
        $this->setPageHeading(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_bookmark');
    }
    
    public function index($params)
    {
        $menu = BASE_MCTRL_UserList::getMenu('');
        $this->addComponent('menu', $menu);

        $lat = null; 
        $lon = null;
        $hash = null;
        
        if ( !empty($params['lat']) )
        {
            $lat = (float)$params['lat'];
        }
        
        if ( !empty($params['lat']) )
        {
            $lat = (float)$params['lat'];
        }
        
        if ( !empty($params['lng']) )
        {
            $lon = (float)$params['lng'];
        }

        if ( !empty($params['hash']) )
        {
            $hash = $params['hash'];
        }
        
        $usersPerPage = 500;//(int)OW::getConfig()->getValue('base', 'users_count_on_page');

        $userIdList = GOOGLELOCATION_BOL_LocationService::getInstance()->getEntityListFromSession($hash);
        
        $userList = GOOGLELOCATION_BOL_LocationService::getInstance()->findUserListByCoordinates($lat, $lon, 0, $usersPerPage, $userIdList);
        $usersCount = GOOGLELOCATION_BOL_LocationService::getInstance()->findUserCountByCoordinates($lat, $lon, $userIdList);
        
        $idList = array();
        
        foreach ( $userList as $dto )
        {
            $idList[$dto->id] = $dto->id;
        }
        
        $language = OW::getLanguage();
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js', "text/javascript", GOOGLELOCATION_BOL_LocationService::JQUERY_LOAD_PRIORITY + 1);
        
        $cmp = new BASE_MCMP_BaseUserList('google_map_mobile_userlist', $idList, true);
        $this->addComponent('list', $cmp);
        //$this->assign('listType', 'google_map_mobile_userlist');

        /* OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                    'component' => 'BASE_MCMP_BaseUserList',
                    'listType' => 'google_map_mobile_userlist',
                    'excludeList' => $idList,
                    'node' => '.owm_user_list',
                    'showOnline' => true,
                    'count' => $usersPerPage,
                    'responderUrl' => OW::getRouter()->urlForRoute('base_user_lists_responder')
                )).");
        ", 50); */
        
        //$locationName = GOOGLELOCATION_BOL_LocationService::getInstance()->getLocationName($lat, $lon);
        //$this->assign('locationName', $locationName);
    }
}
