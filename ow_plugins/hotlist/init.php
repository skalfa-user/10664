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

OW::getRouter()->addRoute(new OW_Route('hotlist-admin-settings', 'admin/hotlist/settings', "{$key}_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('hotlist-add-to-list', 'hotlist/ajax', "{$key}_CTRL_Index", 'ajax'));

$credits = new HOTLIST_CLASS_Credits();
OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));


function hotlist_usercredits_active( BASE_CLASS_EventCollector $event )
{
    if ( !OW::getPluginManager()->isPluginActive('usercredits') )
    {
        $language = OW::getLanguage();

        $event->add($language->text('hotlist', 'error_usercredits_not_installed'));
    }
}
OW::getEventManager()->bind('admin.add_admin_notification', 'hotlist_usercredits_active');

$hlEventHandler = new HOTLIST_CLASS_EventHandler();
$hlEventHandler->init();
$hlEventHandler->genericInit();