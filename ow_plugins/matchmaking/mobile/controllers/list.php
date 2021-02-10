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

class MATCHMAKING_MCTRL_List extends OW_MobileActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        OW::getDocument()->setHeading(OW::getLanguage()->text('matchmaking', 'matches_index'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        $listType = empty($params['sortOrder']) ? 'newest' : strtolower(trim($params['sortOrder']));

        $this->addComponent('menu', $this->getMenu($listType));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $data = $this->getData( $listType, array(), true, $this->usersPerPage );
        $cmp = OW::getClassInstanceArray('MATCHMAKING_MCMP_UserList', [$listType, $data, true]);
        $this->addComponent('list', $cmp);
        $this->assign('listType', $listType);

        OW::getDocument()->addOnloadScript(" 
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                    'component' => 'MATCHMAKING_MCMP_UserList',
                    'listType' => $listType,
                    'excludeList' => $data,
                    'node' => '.owm_user_list',
                    'showOnline' => true,
                    'count' => $this->usersPerPage,
                    'responderUrl' => OW::getRouter()->urlForRoute('matchmaking.responder')
                )).");
        ", 50);
    }
    
    public function responder( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        $listKey = empty($_POST['list']) ? 'newest' : strtolower(trim($_POST['list']));
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
        $itemCount = MATCHMAKING_BOL_Service::getInstance()->findMatchCount(OW::getUser()->getId());
        
        while ( $count > count($list) )
        {
            $itemList = MATCHMAKING_BOL_Service::getInstance()->findMatchList(OW::getUser()->getId(), $start, $count, $listKey);
            
            $event = OW::getEventManager()->trigger(new OW_Event('matchmaking.on_match_list', array('sortOrder' => $listKey), $itemList));
            $itemList = $event->getData();

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

                if ( !in_array($item['id'], $excludeList) )
                {
                    $list[] = $item['id'];
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
        $menuItem->setLabel(OW::getLanguage()->text('matchmaking', 'newest_first'));
        $menuItem->setUrl(OW::getRouter()->urlForRoute('matchmaking_list', array('sortOrder' => 'newest')));
        $menuItem->setKey('newest');
        $menuItem->setOrder(1);
        
        if ( $activeListType === $menuItem->getKey() )
        {
            $menuItem->setActive(true);
        }
        
        $menu->addElement($menuItem);
        
        $menuItem1 = new BASE_MenuItem();
        $menuItem1->setLabel(OW::getLanguage()->text('matchmaking', 'most_compatible_first'));
        $menuItem1->setUrl(OW::getRouter()->urlForRoute('matchmaking_list', array('sortOrder' => 'compatible')));
        $menuItem1->setKey('compatible');
        $menuItem1->setOrder(2);
        
        if ( $activeListType === $menuItem1->getKey() )
        {
            $menuItem1->setActive(true);
        }
        
        $menu->addElement($menuItem1);
        
        return $menu;
    }
}

