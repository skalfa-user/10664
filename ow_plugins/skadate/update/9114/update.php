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
    $widgetService = Updater::getWidgetService();
    $widget = $widgetService->addWidget('SKADATE_CMP_MobileExperience', false);

    try
    {
        $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
        $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);
    }
    catch ( Exception $e )
    {
        Updater::getLogger()->addEntry(json_encode($e));
    }

    try
    {
        $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
        $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);
    }
    catch ( Exception $e )
    {
        Updater::getLogger()->addEntry(json_encode($e));
    }
}

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'skadate');
