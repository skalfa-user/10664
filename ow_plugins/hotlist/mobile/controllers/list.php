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
 * @package ow.ow_plugins.mobile.hotlist.mobile.controllers
 * @since 1.7.6
 */
class HOTLIST_MCTRL_List extends OW_MobileActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        $this->setPageTitle(OW::getLanguage()->text('hotlist', 'userlist'));
        $this->setPageHeading(OW::getLanguage()->text('hotlist', 'userlist'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $data = $this->getData( array(), true, $this->usersPerPage );
        $cmp = new HOTLIST_MCMP_UserList('hotlist', $data, true);
        $this->addComponent('list', $cmp);

        OW::getDocument()->addOnloadScript(" 
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                    'component' => 'HOTLIST_MCMP_UserList',
                    'listType' => 'hotlist',
                    'excludeList' => $data,
                    'node' => '.owm_user_list',
                    'showOnline' => true,
                    'count' => $this->usersPerPage,
                    'responderUrl' => OW::getRouter()->urlForRoute('hotlist-responder')
                )).");
        ", 50);
    }
    
    public function responder( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? $this->usersPerPage : (int)$_POST['count'];

        $data = $this->getData( $excludeList, $showOnline, $count );

        echo json_encode($data);
        exit;
    }

    protected function getData( $excludeList = array(), $showOnline, $count )
    {
        $list = array();
        
        $itemList = HOTLIST_BOL_Service::getInstance()->getHotList( 0, $count, $excludeList );
        
        if ( empty($itemList) )
        {
            return array();
        }
        
        /* @var $item HOTLIST_BOL_User */
        foreach($itemList as $item)
        {
            $list[] = $item->userId;
        }
        
        return $list;
    }
}