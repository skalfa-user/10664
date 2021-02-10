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
$router->addRoute(new OW_Route('cvideoupload.admin-settings', 'admin/plugins/video-upload/settings', 'CVIDEOUPLOAD_CTRL_Admin', 'index'));
$router->addRoute(new OW_Route('cvideoupload.video-upload', 'video-upload', 'CVIDEOUPLOAD_CTRL_Video', 'upload'));
$router->addRoute(new OW_Route('cvideoupload.ajax-video-upload', 'ajax-video-upload', 'CVIDEOUPLOAD_CTRL_Ajax', 'upload'));
$router->addRoute(new OW_Route('cvideoupload.ajax-video-autocomplete', 'ajax-video-autocomplete', 'CVIDEOUPLOAD_CTRL_Ajax', 'autocomplete'));
$router->addRoute(new OW_Route('cvideoupload.stream', 'custom-video-upload/stream/:name/:extension', 'CVIDEOUPLOAD_CTRL_Stream', 'index'));

require_once __DIR__ . '/vendor/autoload.php';

CVIDEOUPLOAD_CLASS_EventHandler::getInstance()->init();
CVIDEOUPLOAD_CLASS_ContentProvider::getInstance()->init();