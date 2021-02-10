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

/**
 * User search page controller.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.mobile.controllers
 * @since 1.5.3
 */
class USEARCH_MCTRL_Search extends USEARCH_CTRL_Search
{
    public function form()
    {
        $url = OW::getPluginManager()->getPlugin('usearch')->getStaticCssUrl() . 'search.css';
        OW::getDocument()->addStyleSheet($url);

        $mainSearchForm = OW::getClassInstance('USEARCH_MCLASS_MainSearchForm', $this);
        $mainSearchForm->process($_POST);        
        $this->addForm($mainSearchForm);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('usearch')->getMobileCtrlViewDir() . 'search_form.html');
        $this->assign('presentationToClass', BASE_MCLASS_JoinFormUtlis::presentationToCssClass());
    }
    
    public function quickSearch()
    {
        $this->addComponent("searchCmp", OW::getClassInstance('USEARCH_MCMP_QuickSearch'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('usearch')->getMobileCtrlViewDir() . 'search_quick_search.html');
    }
    
    public function searchResult($params)
    {
        parent::searchResult($params);
        $orderType = $this->getOrderType($params);
        $this->assign('listLabel', $this->getListLabel($orderType));
        $this->setTemplate(OW::getPluginManager()->getPlugin('usearch')->getMobileCtrlViewDir() . 'search_search_result.html');
        
        $this->assign('noUsersTxt', strip_tags(OW::getLanguage()->text('usearch', 'no_users_found')));
    }
    
    public function map()
    {
        $searchResultMenu = $this->searchResultMenu(USEARCH_BOL_Service::LIST_ORDER_WITHOUT_SORT);
        
        if ( !empty($searchResultMenu) )
        {
            $this->addComponent('searchResultMenu', $searchResultMenu);
        }
        
        parent::map();
        $this->setTemplate(OW::getPluginManager()->getPlugin('usearch')->getMobileCtrlViewDir() . 'search_map.html');
    }
    
    protected function getListLabel($order)
    {
        $items = USEARCH_BOL_Service::getInstance()->getSearchResultMenu($order);
        
        foreach($items as $item) {
            if( !empty($item['isActive']) ) 
            {
                return $item['label'];
            }
        }
        
        return null;
    }
    
    public function searchResultMenu( $orderType )
    {
        $items = USEARCH_BOL_Service::getInstance()->getSearchResultMenu($orderType);
        
        $list = array();
        $order = 0;
        if ( !empty($items) )
        {
            foreach( $items as $item ) {
                $list[] = array( 
                    'label' => $item['label'], 
                    'href' => $item['url'],
                    'class' => '', 
                    'order' => $order++
                );
            }
        }
        
        $list[] = array( 
                    'label' => OW::getLanguage()->text('usearch', 'map'), 
                    'href' => OW::getRouter()->urlForRoute('usearch.map'),
                    'class' => '', 
                    'order' => $order++
                );
            
        $list[] = array( 
                    'label' => OW::getLanguage()->text('usearch', 'new_search'), 
                    'href' => OW::getRouter()->urlForRoute('users-search'),
                    'class' => '', 
                    'order' => $order++
                );
        
        $actions = new BASE_MCMP_ContextAction($list);
        
        return $actions;
    }
}