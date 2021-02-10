<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_MCMP_PromoImageWidget extends BASE_CLASS_Widget
{
    public function __construct(BASE_CLASS_WidgetParameter $params)
    {
        parent::__construct();
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('skadate')->getMobileCmpViewDir().'promo_image_widget.html');
        
        if ( !SKADATE_BOL_Service::getInstance()->isPromoImageUploaded() )
        {
            $this->setVisible(false);
            return;
        }
        
        $this->assign('imageUrl', SKADATE_BOL_Service::getInstance()->getPromoImageUrl());
    }
    
    public static function getSettingList()
    {
        $lang = OW::getLanguage();
        $settingList = array();
        
        $settingList['imageUpload'] = array(
            'presentation' => self::PRESENTATION_CUSTOM,
            'render' => array('SKADATE_MCMP_PromoImageWidget', 'renderCustomSettingsCmp'),
            'display' => 'block'
        );
        
        return $settingList;
    }
    
    public static function renderCustomSettingsCmp( $uniqName )
    {
        $cmp = new SKADATE_CMP_UploadPromoImage($uniqName);
        return $cmp->render();
    }
    
    public static function getStandardSettingValueList()
    {
        $list = array(
            self::SETTING_TITLE => OW::getLanguage()->text('skadate', 'promo_image'),
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_SHOW_TITLE => false,
        );

        return $list;
    }
    
    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

}