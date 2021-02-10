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

OW::getRouter()->removeRoute('users-search');

OW::getRouter()->addRoute(
    new OW_Route('users-search', '/users/search/', 'USEARCH_CTRL_Search', 'form')
);
OW::getRouter()->removeRoute('users-search-result');
OW::getRouter()->addRoute(
    new OW_Route('users-search-result', '/users/search-result/:orderType/', 'USEARCH_CTRL_Search', 'searchResult', array('orderType' => array(OW_Route::PARAM_OPTION_DEFAULT_VALUE => 'latest_activity')) )
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.details', '/users/search/details/', 'USEARCH_CTRL_Search', 'details')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.map', '/users/search/map/', 'USEARCH_CTRL_Search', 'map')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.follow', '/users/search/ajax/follow/', 'USEARCH_CTRL_Ajax', 'follow')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.unfollow', '/users/search/ajax/unfollow/', 'USEARCH_CTRL_Ajax', 'unfollow')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.addfriend', '/users/search/ajax/addfriend/', 'USEARCH_CTRL_Ajax', 'addfriend')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.removefriend', '/users/search/ajax/removefriend/', 'USEARCH_CTRL_Ajax', 'removefriend')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.block', '/users/search/ajax/block/', 'USEARCH_CTRL_Ajax', 'block')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.unblock', '/users/search/ajax/unblock/', 'USEARCH_CTRL_Ajax', 'unblock')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.quick_search_action', '/usearch/ajax/quick-search', 'USEARCH_CTRL_Ajax', 'quickSearch')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.load_list_action', '/usearch/ajax/load-list', 'USEARCH_CTRL_Ajax', 'loadList')
);
OW::getRouter()->addRoute(
    new OW_Route('usearch.admin_quick_search_setting', '/admin/usearch/quick-search-settings', 'USEARCH_CTRL_Admin', 'quickSearchSettings')
);
OW::getRouter()->addRoute(new OW_Route('usearch.admin_general_setting', '/admin/usearch/general-settings', 'USEARCH_CTRL_Admin', 'generalSettings'));

USEARCH_CLASS_EventHandler::getInstance()->init();
USEARCH_CLASS_EventHandler::getInstance()->genericInit();

function usearch_disable_fields_on_edit_profile_question(OW_Event $event)
{
    $params = $event->getParams();
    $data = $event->getData();

    if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question )
    {
        $dto = $params['questionDto'];

        if ( in_array( $dto->name, array('sex', 'match_sex', 'match_age') ) )
        {
            $data['disable_on_search'] = true;
            $event->setData($data);
        }
    }
}
OW::getEventManager()->bind('admin.disable_fields_on_edit_profile_question', 'usearch_disable_fields_on_edit_profile_question');
