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
 * User search ajax actions controller.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow.ow_plugins.usearch.mobile
 * @since 1.7.4
 */

/* $router->addRoute(new OW_Route('users', 'users', 'BASE_MCTRL_UserList', 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
  $router->addRoute(new OW_Route('base_user_lists', 'users/:list', 'BASE_MCTRL_UserList', 'index'));
  $router->addRoute(new OW_Route('base_user_lists_responder', 'responder', 'BASE_MCTRL_UserList', 'responder')); */

OW::getRouter()->removeRoute('users-search');

OW::getRouter()->addRoute(
        new OW_Route('users-search', '/user-search/', 'USEARCH_MCTRL_Search', 'form')
);

OW::getRouter()->removeRoute('users-search-result');
OW::getRouter()->addRoute(
    new OW_Route('users-search-result', '/users/search-result/:orderType/', 'USEARCH_MCTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')) )
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.map', '/users/search/map/', 'USEARCH_MCTRL_Search', 'map')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.load_list_action', '/usearch/ajax/load-list', 'USEARCH_CTRL_Ajax', 'loadList')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.quick_search', '/usearch/quick-search', 'USEARCH_MCTRL_Search', 'quickSearch')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.quick_search_action', '/usearch/ajax/quick-search', 'USEARCH_CTRL_Ajax', 'quickSearch')
);

USEARCH_MCLASS_EventHandler::getInstance()->init();
USEARCH_CLASS_EventHandler::getInstance()->genericInit();
