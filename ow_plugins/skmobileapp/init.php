<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

require_once __DIR__ . '/vendor/autoload.php';

OW::getRouter()->addRoute(new OW_Route('skmobileapp_admin_ads', 'admin/plugin/skmobileapp/ads', 'SKMOBILEAPP_CTRL_Admin', 'ads'));
OW::getRouter()->addRoute(new OW_Route('skmobileapp_admin_push', 'admin/plugin/skmobileapp/push', 'SKMOBILEAPP_CTRL_Admin', 'push'));
OW::getRouter()->addRoute(new OW_Route('skmobileapp_admin_inapps', 'admin/plugin/skmobileapp/inapps', 'SKMOBILEAPP_CTRL_Admin', 'inapps'));
OW::getRouter()->addRoute(new OW_Route('skmobileapp_admin_settings', 'admin/plugin/skmobileapp/settings', 'SKMOBILEAPP_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('skmobileapp.api', 'skmobileapp/api', 'SKMOBILEAPP_CTRL_Api', 'index'));

$eventHandler = SKMOBILEAPP_CLASS_EventHandler::getInstance()->init();