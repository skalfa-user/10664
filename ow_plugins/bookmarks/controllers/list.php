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
 * Bookmarks RSP controller
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.controllers
 * @since 1.0
 */
class BOOKMARKS_CTRL_List extends OW_ActionController
{
    private $service;
    
    public function __construct()
    {
        parent::__construct();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        $sortCtrl = new BASE_CMP_SortControl();
        $sortCtrl->addItem(BOOKMARKS_BOL_Service::LIST_LATEST, OW::getLanguage()->text('bookmarks', 'latest'), OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_LATEST)));
        $sortCtrl->addItem(BOOKMARKS_BOL_Service::LIST_ONLINE, OW::getLanguage()->text('bookmarks', 'online'), OW::getRouter()->urlForRoute('bookmarks.list', array('category' => BOOKMARKS_BOL_Service::LIST_ONLINE)));
        $this->addComponent('sort', $sortCtrl);
        
        $this->service = BOOKMARKS_BOL_Service::getInstance();
    }
    
    public function init() 
    {
        parent::init();
        
        $handler = OW::getRequestHandler()->getHandlerAttributes();
        
        if ( !in_array($handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['category'], 
            array(BOOKMARKS_BOL_Service::LIST_LATEST, BOOKMARKS_BOL_Service::LIST_ONLINE)) )
        {
            throw new Redirect404Exception();
        }
        
        $this->getComponent('sort')->setActive($handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['category']);
    }

    public function getList( array $params )
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text('bookmarks', 'list_headint_title'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('bookmarks')->getCtrlViewDir() . 'list.html');
        
        $userId = OW::getUser()->getId();
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $userOnPage = (int)OW::getConfig()->getValue('base', 'users_on_page');
        $first = ($page - 1) * $userOnPage;
        
        $list = $this->service->findBookmarksUserIdList($userId, $first, $userOnPage, $params['category']);
        $count = $this->service->findBookmarksCount($userId, $params['category']);

        $sexValue = array();
        $userDataList = array();
        $questionService = BOL_QuestionService::getInstance();
        $data = $questionService->getQuestionData($list, array('sex', 'googlemap_location', 'birthdate'));

        foreach ( BOL_QuestionValueDao::getInstance()->findQuestionValues('sex') as $sexDto )
        {
            $sexValue[$sexDto->value] = $questionService->getQuestionValueLang('sex', $sexDto->value);
        }
        
        foreach ( $data as $userId => $user )
        {
            if ( isset($user['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($user['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }
            else
            {
                $age = '';
            }
            
            $userDataList[$userId] = array(
                'info_gender' => (!empty($user['sex']) && !empty($sexValue[$user['sex']])) ? $sexValue[$user['sex']] : '' . ' ' . $age,
                'location' => !empty($user['googlemap_location']) ? $user['googlemap_location']['address'] : ''
            );
        }
        
        $this->addComponent('list', OW::getClassInstance('BASE_CMP_Users', $userDataList, array(), $count));
    }
}
