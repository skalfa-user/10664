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

OW::getRouter()->addRoute(new OW_Route('matchmaking_admin_rules', 'admin/matchmaking/rules', 'MATCHMAKING_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('matchmaking_uninstall', 'admin/matchmaking/uninstall', 'MATCHMAKING_CTRL_Admin', 'uninstall'));
OW::getRouter()->addRoute(new OW_Route('matchmaking_admin_settings', 'admin/matchmaking/settings', 'MATCHMAKING_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('matchmaking_delete_item', 'admin/matchmaking/rules/delete/:id', 'MATCHMAKING_CTRL_Admin', 'delete'));
OW::getRouter()->addRoute(new OW_Route('matchmaking_members_page_sorting', 'profile/matches/:sortOrder', 'MATCHMAKING_CTRL_Base', 'index'));
OW::getRouter()->addRoute(new OW_Route('matchmaking_members_page', 'profile/matches', 'MATCHMAKING_CTRL_Base', 'index', array('sortOrder' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'newest')) ));
OW::getRouter()->addRoute(new OW_Route('matchmaking_members_page_sorted', 'profile/matches/:sortOrder', 'MATCHMAKING_CTRL_Base', 'index' ));
OW::getRouter()->addRoute(new OW_Route('matchmaking_preferences', 'matches/preferences', 'MATCHMAKING_CTRL_Base', 'preferences' ));


$eventHandler = MATCHMAKING_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();
$eventHandler->init();