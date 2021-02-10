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

// credits
require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new VIDEOIM_CLASS_Credits();
$credits->triggerCreditActionsAdd();

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
    BOL_ComponentAdminService::getInstance()->addWidget('VIDEOIM_CMP_VideoCallWidget', false),
    BOL_ComponentAdminService::PLACE_PROFILE
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 1);