<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$router = OW::getRouter();
$router->addRoute(new OW_Route('sktextcr.admin-all-message', 'admin/plugins/text-chat-recording/all', 'SKTEXTCR_CTRL_Admin', 'index'));
$router->addRoute(new OW_Route('sktextcr.admin-search-message', 'admin/plugins/text-chat-recording/search', 'SKTEXTCR_CTRL_Admin', 'search'));

SKTEXTCR_CLASS_EventHandler::getInstance()->init();