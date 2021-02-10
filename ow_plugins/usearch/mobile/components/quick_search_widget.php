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

/**
 * User quick search component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.usearch.components
 * @since 1.5.3
 */
class USEARCH_MCMP_QuickSearchWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();
        $this->assign("url", OW::getRouter()->urlForRoute('usearch.quick_search'));        
        $this->assign("buttonLabel", !empty($paramObject->customParamList['buttonLabel']) ? $paramObject->customParamList['buttonLabel'] : OW::getLanguage()->text('usearch', 'quick_search'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('usearch')->getMobileCmpViewDir().'quick_search_widget.html');
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('usearch', 'quick_search'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => BASE_CLASS_Widget::ICON_LENS            
        );
    }
    
    public static function getSettingList()
    {
        $lang = OW::getLanguage();
        $settingList = array();
        
        $settingList['buttonLabel'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => OW::getLanguage()->text('usearch', 'quick_search_button_label'),
            'value' => OW::getLanguage()->text('usearch', 'quick_search')
        );
        
        return $settingList;
    }
}