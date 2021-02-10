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

OW::getRouter()->addRoute(new OW_Route('matchmaking_members_page', 'profile/matches/', 'MATCHMAKING_MCTRL_List', 'index'));
OW::getRouter()->addRoute(new OW_Route('matchmaking.responder', 'profile/matches/responder', 'MATCHMAKING_MCTRL_List', 'responder'));
OW::getRouter()->addRoute(new OW_Route('matchmaking_list', 'profile/matches/:sortOrder', 'MATCHMAKING_MCTRL_List', 'index', array('sortOrder' => array('default' => 'latest'))));

MATCHMAKING_MCLASS_EventHandler::getInstance()->init();