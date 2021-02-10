<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('BASE_CMP_UserAvatarWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);

try 
{
    $widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);
}
catch ( Exception $e ) {}
