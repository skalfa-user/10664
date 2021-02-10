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
 * @author Podiachev Evgenii <joker.OW2@gmail.com>
 * @package ow.ow_plugins.bookmarks.components
 * @since 1.7.5
 */
class BOOKMARKS_MCMP_BookmarksWidget extends BOOKMARKS_CMP_BookmarksWidget
{
    protected $forceDisplayMenu = false;

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct($params);
        $params->standartParamList->capContent = $this->getComponent('menu')->render();
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'user_list_widget.html');
    }

    protected function getUsersCmp( $list )
    {
        return new BASE_MCMP_AvatarUserList($list);
    }

    protected function getMenuCmp( $menuItems )
    {
        return new BASE_MCMP_WidgetMenu($menuItems);
    }
    
    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('bookmarks', 'widget_user_count_label'),
            'value' => OW::getConfig()->getValue('bookmarks', 'widget_user_count')
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW::getLanguage()->text('bookmarks', 'widget_title'),
            self::SETTING_ICON => self::ICON_BOOKMARK
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}
