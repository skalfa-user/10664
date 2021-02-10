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

require_once  __DIR__ . '/../vendor/autoload.php';

OW::getRouter()->addRoute(new OW_Route('skmobileapp.api', 'skmobileapp/api', 'SKMOBILEAPP_MCTRL_Api', 'index'));

$eventHandler = SKMOBILEAPP_MCLASS_EventHandler::getInstance()->init();
