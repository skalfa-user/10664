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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.hotlist.components
 * @since 1.0
 */
class HOTLIST_CMP_IndexWidget extends BASE_CLASS_Widget
{

    public function __construct(BASE_CLASS_WidgetParameter $params)
    {
        parent::__construct();

        $hotListComponent = OW::getClassInstanceArray('HOTLIST_CMP_Index', [$params->customParamList]);

        $this->addComponent('hotListComponent', $hotListComponent);
    }

    public static function getStandardSettingValueList()
    {
        $list = array(
            self::SETTING_TITLE => OW::getLanguage()->text('hotlist', 'userlist'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => 'ow_ic_heart'
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

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

}
