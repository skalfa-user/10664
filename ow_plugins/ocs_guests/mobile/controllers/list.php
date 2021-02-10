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

class OCSGUESTS_MCTRL_List extends OW_MobileActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $lang = OW::getLanguage();
        $this->setPageHeading($lang->text('ocsguests', 'viewed_profile'));
        $this->setPageTitle($lang->text('ocsguests', 'viewed_profile'));
        
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        $language = OW::getLanguage();
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $data = $this->getData( array(), true, $this->usersPerPage );
        $cmp = new BASE_MCMP_BaseUserList('ocsguests', $data, true);
        $this->addComponent('list', $cmp);

        OW::getDocument()->addOnloadScript(" 
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                    'component' => 'BASE_MCMP_BaseUserList',
                    'listType' => 'ocsguests',
                    'excludeList' => $data,
                    'node' => '.owm_user_list',
                    'showOnline' => true,
                    'count' => $this->usersPerPage,
                    'responderUrl' => OW::getRouter()->urlForRoute('ocsguests_responder')
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

        $start = count($excludeList);
        $itemCount = OCSGUESTS_BOL_Service::getInstance()->countGuestsForUser(OW::getUser()->getId());
        
        while ( $count > count($list) )
        {
            $page = (int) (($start/$count) + 1);
            $itemList = OCSGUESTS_BOL_Service::getInstance()->findGuestsForUser(OW::getUser()->getId(), $page, $count);
            
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

                if ( !in_array($item->guestId, $excludeList) )
                {
                    $list[] = $item->guestId;
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
}

