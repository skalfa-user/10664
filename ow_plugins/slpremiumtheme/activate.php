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

/*
$widget = $widgetService->addWidget('SLPREMIUMTHEME_CMP_UserCarouselWidget', false);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
 */

$widgetService->deleteWidget('PHOTO_CMP_PhotoListWidget');
$widgetService->deleteWidget('BASE_CMP_UserListWidget');

$widget = $widgetService->addWidget('PHOTO_CMP_PhotoListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);

$widget = $widgetService->addWidget('BASE_CMP_UserListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);