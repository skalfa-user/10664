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
 * @package ow.ow_plugins.bookmarks.components
 * @since 1.7.5
 */

class BOOKMARKS_MCTRL_List extends OW_MobileActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        OW::getDocument()->setHeading(OW::getLanguage()->text('bookmarks', 'list_headint_title'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        $listType = empty($params['list']) ? BOOKMARKS_BOL_Service::LIST_LATEST : strtolower(trim($params['list']));
        $this->addComponent('menu', $this->getMenu($listType));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $data = $this->getData( $listType, array(), true, $this->usersPerPage );
        $cmp = new BOOKMARKS_MCMP_UserList($listType, $data, true);
        $this->addComponent('list', $cmp);
        $this->assign('listType', $listType);

        OW::getDocument()->addOnloadScript(" 
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                    'component' => 'BOOKMARKS_MCMP_UserList',
                    'listType' => $listType,
                    'excludeList' => $data,
                    'node' => '.owm_user_list',
                    'showOnline' => true,
                    'count' => $this->usersPerPage,
                    'responderUrl' => OW::getRouter()->urlForRoute('bookmarks.responder')
                )).");
        ", 50);
    }
    
    public function responder( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        $listKey = empty($_POST['list']) ? 'latest' : strtolower(trim($_POST['list']));
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? $this->usersPerPage : (int)$_POST['count'];

        $data = $this->getData( $listKey, $excludeList, $showOnline, $count );

        echo json_encode($data);
        exit;
    }

    protected function getData( $listKey, $excludeList = array(), $showOnline, $count )
    {
        $list = array();

        $start = count($excludeList);
        $itemCount = BOOKMARKS_BOL_Service::getInstance()->findBookmarksCount(OW::getUser()->getId(), $listKey);

        while ( $count > count($list) )
        {
            $itemList = BOOKMARKS_BOL_Service::getInstance()->findBookmarksUserIdList(OW::getUser()->getId(), $start, $count, $listKey);
            
            if ( empty($itemList)  )
            {
                break;
            }
            
            foreach ( $itemList as $key => $item )
            {
                if ( count($list) == $count )
                {
                    break;
                }

                if ( !in_array($item, $excludeList) )
                {
                    $list[] = $item;
                }
            }
            
            $start += $count;

            if ( $start >= $itemCount )
            {
                break;
            }
        }

        return $list;
    }

    public function getMenu( $activeListType )
    {
        $menu = new BASE_MCMP_ContentMenu();

        $menuItem = new BASE_MenuItem();
        $menuItem->setLabel(OW::getLanguage()->text('bookmarks', 'latest'));
        $menuItem->setUrl(OW::getRouter()->urlForRoute('bookmarks.list', array('list' => BOOKMARKS_BOL_Service::LIST_LATEST)));
        $menuItem->setKey(BOOKMARKS_BOL_Service::LIST_LATEST);
        $menuItem->setOrder(1);
        
        if ( $activeListType == $menuItem->getKey() )
        {
            $menuItem->setActive(true);
        }
        
        $menu->addElement($menuItem);
        
        $menuItem = new BASE_MenuItem();
        $menuItem->setLabel(OW::getLanguage()->text('bookmarks', 'online'));
        $menuItem->setUrl(OW::getRouter()->urlForRoute('bookmarks.list', array('list' => BOOKMARKS_BOL_Service::LIST_ONLINE)));
        $menuItem->setKey(BOOKMARKS_BOL_Service::LIST_ONLINE);
        $menuItem->setOrder(2);
        
        if ( $activeListType == $menuItem->getKey() )
        {
            $menuItem->setActive(true);
        }
        
        $menu->addElement($menuItem);
        
        return $menu;
    }
}

