<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('googlelocation_admin', 'admin/plugins/googlelocation', 'GOOGLELOCATION_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('googlelocation_user_map', 'users/map', 'GOOGLELOCATION_CTRL_UserMap', 'map'));
OW::getRouter()->addRoute(new OW_Route('googlelocation_event_map', 'event/map', 'GOOGLELOCATION_CTRL_EventMap', 'map'));
OW::getRouter()->addRoute(new OW_Route('googlelocation_user_list', 'users/map/:lat/:lng/user-list/:hash', 'GOOGLELOCATION_CTRL_UserList', 'index' ));
OW::getRouter()->addRoute(new OW_Route('googlelocation_event_list', 'event/map/:lat/:lng/event-list/:hash', 'GOOGLELOCATION_CTRL_EventList', 'index' ));
OW::getRouter()->addRoute(new OW_Route('googlelocation_users_map', 'users_map', 'GOOGLELOCATION_CTRL_UserMap', 'map'));

$handler = new GOOGLELOCATION_CLASS_EventHandler();
$handler->genericInit();
$handler->init();