<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$widget = BOL_ComponentAdminService::getInstance()->addWidget('BOOKMARKS_CMP_BookmarksWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);

try
{    
    $widget = BOL_MobileWidgetService::getInstance()->addWidget('BOOKMARKS_MCMP_BookmarksWidget', false);
    $placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
    BOL_MobileWidgetService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}


