<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$widgetService = BOL_ComponentAdminService::getInstance();

// desktop widget
$widget = $widgetService->addWidget('GDPR_CMP_UserDataWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

$widget = $widgetService->addWidget('GDPR_CMP_ThirdPartyWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

// mobile widget
$widget = $widgetService->addWidget('GDPR_MCMP_UserDataWidget');
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);

$widget = $widgetService->addWidget('GDPR_MCMP_ThirdPartyWidget');
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);




