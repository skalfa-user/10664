<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

if (
    OW::getPluginManager()->isPluginActive('skadateios') ||
    OW::getPluginManager()->isPluginActive('skandroid')
)

{
    $widgetService = BOL_ComponentAdminService::getInstance();
    $widget = $widgetService->addWidget('SKADATE_CMP_MobileExperience', false);

    try
    {
        $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
        $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);
    }
    catch ( Exception $e )
    {
        OW::getLogger('skadate.activate.widget_mobile_experience_index')->addEntry(json_encode($e));
    }

    try
    {
        $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
        $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);
    }
    catch ( Exception $e )
    {
        OW::getLogger('skadate.activate.widget_mobile_experience_dashboard')->addEntry(json_encode($e));
    }
}

try
{
    $widgetService = BOL_MobileWidgetService::getInstance();
    $widget = $widgetService->addWidget('SKADATE_MCMP_PromoImageWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 0);
}
catch ( Exception $e )
{
    OW::getLogger('skadate.activate.widget_mobile_promo_image')->addEntry(json_encode($e));
}
