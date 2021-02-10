<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 *
 * @author Podiachev Evgenii <joker.OW2@gmail.com>
 * @package ow.ow_plugins.hotlist.mobile.components
 * @since 1.7.6
 */
class HOTLIST_MCMP_Widget extends BASE_MCMP_UserListWidget
{

    public function __construct(BASE_CLASS_WidgetParameter $params)
    {
        parent::__construct($params);
    }

    public static function getStandardSettingValueList()
    {
        $list = array(
            self::SETTING_TITLE => OW::getLanguage()->text('hotlist', 'userlist'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => 'ow_ic_heart',
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
        );

        return $list;
    }

    public static function getSettingList()
    {
        $settingList['number_of_users'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('hotlist', 'cmp_widget_number_of_users'),
            'value' => 8,
        );

        return $settingList;
    }

    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $count = (int) $params->customParamList['number_of_users'];
        $service = HOTLIST_BOL_Service::getInstance();

        $toolbar = array(
                    'label' => OW::getLanguage()->text('base', 'view_all'),
                    'href' => OW::getRouter()->urlForRoute('hotlist-list')
                );

        $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar));
        
        $list = $service->getHotList(0, $count);
        $userIdList = array();
        
        /* @var $value HOTLIST_BOL_User */
        foreach( $list as $value ) {
            $userIdList[$value->userId] = $value->userId;
        }

        $resultList = array(
            'hotlist' => array(
                'menu-label' => " ",
                'menu_active' => true,
                'userIds' => $userIdList,
                'toolbar' => $toolbar,
            ));

        return $resultList;
    }
    
    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

}
