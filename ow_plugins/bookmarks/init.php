<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

OW::getRouter()->addRoute(new OW_Route('bookmarks.admin', 'bookmarks/admin', 'BOOKMARKS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('bookmarks.mark_state', 'bookmarks/rsp/mark-state', 'BOOKMARKS_CTRL_Rsp', 'markState'));
OW::getRouter()->addRoute(new OW_Route('bookmarks.list', 'bookmarks/list/:category', 'BOOKMARKS_CTRL_List', 'getList', array('category' => array('default' => 'latest'))));

BOOKMARKS_CLASS_EventHandler::getInstance()->init();
