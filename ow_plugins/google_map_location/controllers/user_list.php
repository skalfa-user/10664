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


class GOOGLELOCATION_CTRL_UserList extends OW_ActionController
{
    public function index($params)
    {
        if ( !OW::getPluginManager()->isPluginActive("skadate") )
        {
            $menu = BASE_CTRL_UserList::getMenu('map');
            $this->addComponent('menu', $menu);
        }
        
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
      
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;
        $usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
        $first = ($page - 1) * $usersPerPage;

        $userIdList = GOOGLELOCATION_BOL_LocationService::getInstance()->getEntityListFromSession($hash);
        
        //BOL_UserService::getInstance()->findUserListByIdList($userIdList);
        $userList = GOOGLELOCATION_BOL_LocationService::getInstance()->findUserListByCoordinates($lat, $lon, $first, $usersPerPage, $userIdList);
        $usersCount = GOOGLELOCATION_BOL_LocationService::getInstance()->findUserCountByCoordinates($lat, $lon, $userIdList);

        $listCmp = new GOOGLELOCATION_CMP_UserList($userList, $usersCount, $usersPerPage);
        $this->addComponent('cmp', $listCmp);
        
        $locationName = GOOGLELOCATION_BOL_LocationService::getInstance()->getLocationName($lat, $lon);
        $this->assign('locationName', $locationName);
        
        $language = OW::getLanguage();        
        $this->setPageHeading(OW::getLanguage()->text('googlelocation', 'browse_users_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('googlelocation', 'browse_users_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_bookmark');
        
        $this->assign( 'backUrl', empty($_GET['backUri']) ? null : OW_URL_HOME. $_GET['backUri'] );
        $this->setTemplate(OW::getPluginManager()->getPlugin('googlelocation')->getCtrlViewDir().'entity_list_index.html');
    }
}
