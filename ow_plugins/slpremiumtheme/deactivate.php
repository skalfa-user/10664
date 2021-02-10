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

try
{
    $widget = $widgetService->addWidget('PHOTO_CMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}

try
{
    $widget = $widgetService->addWidget('BASE_CMP_UserListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}

$widgetService->deleteWidget('SLPREMIUMTHEME_CMP_UserCarouselWidget');
