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

class SLPREMIUMTHEME_CMP_UserCarouselWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();
        
        $list = new SLPREMIUMTHEME_CMP_UserCarousel(array(
            "list" => $paramObj->customParamList['list']
        ));
        
        $this->addComponent("list", $list);
    }
    
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('slpremiumtheme', 'ucarousel_widget_title'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getSettingList()
    {
        $language = OW::getLanguage();
        
        $settingList['list'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $language->text('slpremiumtheme', 'ucarousel_settings_list'),
            'optionList' => array(
                "latest" => $language->text('slpremiumtheme', 'ucarousel_settings_list_latest'),
                "online" => $language->text('slpremiumtheme', 'ucarousel_settings_list_online'),
                "featured" => $language->text('slpremiumtheme', 'ucarousel_settings_list_featured')
            ),
            'value' => "latest"
        );

        return $settingList;
    }
}