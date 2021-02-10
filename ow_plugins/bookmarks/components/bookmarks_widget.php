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
 * Bookmarks Widget
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.components
 * @since 1.0
 */
class BOOKMARKS_CMP_BookmarksWidget extends BASE_CLASS_Widget
{
    protected $forceDisplayMenu = false;

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'users_widget.html');
        $randId = UTIL_HtmlTag::generateAutoId('base_users_widget');
        $this->assign('widgetId', $randId);

        $data = $this->getData($params);
        $menuItems = array();
        $dataToAssign = array();

        if ( !empty($data) )
        {
            foreach ( $data as $key => $item )
            {
                $contId = "{$randId}_users_widget_{$key}";
                $toolbarId = (!empty($item['toolbar']) ? "{$randId}_toolbar_{$key}" : false );

                $menuItems[$key] = array(
                    'label' => $item['menu-label'],
                    'id' => "{$randId}_users_widget_menu_{$key}",
                    'contId' => $contId,
                    'active' => !empty($item['menu_active']),
                    'toolbarId' => $toolbarId
                );

                $usersCmp = $this->getUsersCmp($item['userIds']);

                $dataToAssign[$key] = array(
                    'users' => $usersCmp->render(),
                    'active' => !empty($item['menu_active']),
                    'toolbar' => (!empty($item['toolbar']) ? $item['toolbar'] : array() ),
                    'toolbarId' => $toolbarId,
                    'contId' => $contId
                );
            }
        }
        $this->assign('data', $dataToAssign);

        $displayMenu = true;

        if( count($data) == 1 && !$this->forceDisplayMenu )
        {
            $displayMenu = false;
        }

        if ( !$params->customizeMode && ( count($data) != 1 || $this->forceDisplayMenu ) )
        {
            $menu = $this->getMenuCmp($menuItems);

            if ( !empty($menu) )
            {
                $this->addComponent('menu', $menu);
            }
        }
    }

    protected function getIdList( $users )
    {
        $resultArray = array();

        if ( $users )
        {
            foreach ( $users as $user )
            {
                $resultArray[] = $user->getId();
            }
        }

        return $resultArray;
    }

    protected function getUsersCmp( $list )
    {
        $cmp = new BASE_CMP_AvatarUserList($list);
        $cmp->setTemplate(OW::getPluginManager()->getPlugin('bookmarks')->getCmpViewDir() . 'avatar_user_list.html');
        
        return $cmp;
    }

    protected function getMenuCmp( $menuItems )
    {
        return new BASE_CMP_WidgetMenu($menuItems);
    }

    protected function forceDisplayMenu( $value )
    {
        $this->forceDisplayMenu = (boolean) $value;
    }
    
    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $count = (int)$params->customParamList['count'];
        $service = BOOKMARKS_BOL_Service::getInstance();
        $userId = $params->additionalParamList['entityId'];

        $toolbar = array(
            'latest' => array(
                'label' => OW::getLanguage()->text('bookmarks', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST))
            ),
            'online' => array(
                'label' => OW::getLanguage()->text('bookmarks', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_ONLINE))
            )
        );

        $bookmarksCount = $service->findBookmarksCount($userId);
        $onlineCount = $service->findBookmarksCount($userId, BOOKMARKS_BOL_Service::LIST_ONLINE);
        
        if ($bookmarksCount > $count)
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar['latest']));
        }

        $resultList = array(
            'latest' => array(
                'menu-label' => OW::getLanguage()->text('bookmarks', 'latest'),
                'menu_active' => true,
                'userIds' => $service->findBookmarksUserIdList($userId, 0, $count),
                'toolbar' => ($bookmarksCount > $count ? array($toolbar['latest']) : false),
            ),
            'online' => array(
                'menu-label' => OW::getLanguage()->text('bookmarks', 'online'),
                'userIds' => $service->findBookmarksUserIdList($userId, 0, $count, BOOKMARKS_BOL_Service::LIST_ONLINE),
                'toolbar' => ($onlineCount > $count ? array($toolbar['online']) : false),
            )
        );

        $event = new OW_Event('bookmarks.onToolbarReady', array(), $resultList);
        OW::getEventManager()->trigger($event);

        return $event->getData();
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
            self::SETTING_TITLE => OW::getLanguage()->text('bookmarks', 'widget_title'),
            self::SETTING_ICON => self::ICON_BOOKMARK
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}
