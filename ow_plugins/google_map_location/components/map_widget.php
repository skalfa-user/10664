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

abstract class GOOGLELOCATION_CMP_MapWidget extends BASE_CLASS_Widget
{    
    const MAX_USERS_COUNT = 2000;
    
    protected $map = null;
    protected $mapHeight = null;    
    protected $idList = array();   
    
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        
        $IdList = $this->assignList( $params );
        
        if ( empty($IdList) && !$params->customizeMode )
        {
            $this->setVisible(false);
            return;
        }

        $this->mapHeight = isset($params->customParamList['map_height']) ? (int) $params->customParamList['map_height'] : 350;
        $this->renderMap($IdList, $params);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('googlelocation')->getCmpViewDir().'map_widget.html');
    }
    
    abstract protected function assignList( BASE_CLASS_WidgetParameter $params );

    protected function getMap( BASE_CLASS_WidgetParameter $params )            
    {        
        return GOOGLELOCATION_BOL_LocationService::getInstance()->getMapComponent();
    }
    
    protected function renderMap($IdList, BASE_CLASS_WidgetParameter $params)
    {        
        $event = new OW_Event( 'googlelocation.get_map_component', array( 'userIdList' => $IdList, 'backUri' => OW::getRouter()->getUri() ) );
        OW::getEventManager()->trigger($event);
        /* @var $map GOOGLELOCATION_CMP_Map */
        $map = $event->getData();
        $map->setHeight($this->mapHeight . 'px');
        
        if ( !empty($params->customParamList['map_display_search']) )
        {
            $map->displaySearchInput(true);
        }
        
        OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));   

        $this->addComponent("map", $map);
    }
    
    public static function getSettingList()
    {
        $settingList = array();
        
        $settingList['map_height'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('googlelocation', 'widget_settings_map_height'),
            'value' => 350
        );
        
        $settingList['map_display_search'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW_Language::getInstance()->text('googlelocation', 'widget_settings_display_search'),
            'value' => false
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('googlelocation', 'widget_map_title'),
            self::SETTING_ICON => self::ICON_BOOKMARK
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}
