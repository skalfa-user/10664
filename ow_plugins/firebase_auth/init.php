<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

require_once  __DIR__ . '/vendor/autoload.php';

OW::getRouter()->addRoute(new OW_Route('firebaseauth_admin_index', 'admin/plugin/firebaseauth', 'FIREBASEAUTH_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('firebaseauth_authenticate', 'firebaseauth/authenticate', 'FIREBASEAUTH_CTRL_Connect', 'authenticate'));
OW::getRouter()->addRoute(new OW_Route('firebaseauth_display_token', 'firebaseauth/display-token', 'FIREBASEAUTH_CTRL_Connect', 'displayToken'));

FIREBASEAUTH_CLASS_EventHandler::getInstance()->init();
