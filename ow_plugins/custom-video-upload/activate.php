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


require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';

$credits = new CVIDEOUPLOAD_CLASS_Credits();
$credits->triggerCreditActionsAdd();

$widget = BOL_ComponentAdminService::getInstance()->addWidget('CVIDEOUPLOAD_CMP_UserVideoWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('CVIDEOUPLOAD_CMP_MyVideoWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);
