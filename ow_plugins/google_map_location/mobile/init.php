<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
OW::getRouter()->addRoute(new OW_Route('googlelocation_user_map', 'users/map', 'GOOGLELOCATION_MCTRL_UserMap', 'map'));
OW::getRouter()->addRoute(new OW_Route('googlelocation_user_list', 'users/map/:lat/:lng/user-list/:hash', 'GOOGLELOCATION_MCTRL_UserList', 'index' ));

$handler = new GOOGLELOCATION_CLASS_EventHandler();
$handler->genericInit();
$handler->mobileInit();


$mhandler = new GOOGLELOCATION_MCLASS_MobileEventHandler();
$mhandler->init();


