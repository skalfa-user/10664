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

$widget = $widgetService->addWidget('OCSGUESTS_CMP_MyGuestsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);

// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'ocsguests_list', 'ocsguests', 'ocsguests_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);