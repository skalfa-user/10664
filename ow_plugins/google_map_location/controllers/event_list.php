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


class GOOGLELOCATION_CTRL_EventList extends OW_ActionController
{
    public function index($params)
    {        
        if( !OW::getPluginManager()->isPluginActive('event') )
        {
            throw new Redirect404Exception();
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

        $entityIdList = GOOGLELOCATION_BOL_LocationService::getInstance()->getEntityListFromSession($hash);
        
        $bridge = new GOOGLELOCATION_CLASS_EventBridge();
        $listCmp = $bridge->getEventListCmp($entityIdList); 
        $this->addComponent('cmp', $listCmp);
        
        $locationName = GOOGLELOCATION_BOL_LocationService::getInstance()->getLocationName($lat, $lon);
        $this->assign('locationName', $locationName);
        
        $language = OW::getLanguage();        
        $this->setPageHeading(OW::getLanguage()->text('googlelocation', 'browse_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('googlelocation', 'events_browse_page_title'));
        $this->setPageHeadingIconClass('ow_ic_bookmark');
        
        $this->assign( 'backUrl', empty($_GET['backUri']) ? null : OW_URL_HOME. $_GET['backUri'] );
        $this->setTemplate(OW::getPluginManager()->getPlugin('googlelocation')->getCtrlViewDir().'entity_list_index.html');
    }
}
