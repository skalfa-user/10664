<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

OW::getRouter()->addRoute(new OW_Route('bookmarks.list', 'bookmarks/list/:list', 'BOOKMARKS_MCTRL_List', 'index', array('list' => array('default' => 'latest'))));
OW::getRouter()->addRoute(new OW_Route('bookmarks.responder', 'bookmarks/responder', 'BOOKMARKS_MCTRL_List', 'responder'));
OW::getRouter()->addRoute(new OW_Route('bookmarks.mark_state', 'bookmarks/rsp/mark-state', 'BOOKMARKS_MCTRL_Rsp', 'markState'));

BOOKMARKS_MCLASS_EventHandler::getInstance()->init();