<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$plugin = OW::getPluginManager()->getPlugin('hotlist');

$key = strtoupper($plugin->getKey());

OW::getRouter()->addRoute(new OW_Route('hotlist-list', 'hotlist', "{$key}_MCTRL_List", 'index'));
OW::getRouter()->addRoute(new OW_Route('hotlist-add-remove-responder', 'hotlist/add-remove-responder', "{$key}_MCTRL_Responder", 'responder'));
OW::getRouter()->addRoute(new OW_Route('hotlist-responder', 'hotlist/responder', "{$key}_MCTRL_List", 'responder'));

$credits = new HOTLIST_CLASS_Credits();
OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));

$hlEventHandler = new HOTLIST_CLASS_EventHandler();
$hlEventHandler->genericInit();