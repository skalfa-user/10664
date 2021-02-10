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

$widget = $widgetService->addWidget('USEARCH_CMP_QuickSearchWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);

$widget = BOL_MobileWidgetService::getInstance()->addWidget('USEARCH_MCMP_QuickSearchWidget', false);
$placeWidget = BOL_MobileWidgetService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
BOL_MobileWidgetService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN );

try {
    OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'users-search', 'usearch', 'menu_item_search', OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e ) { }

try {
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'users-search', 'usearch', 'mobile_menu_item_search', OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e ) {  }

try {
    /* @var $menu BOL_MenuItem */
    $menu = BOL_NavigationService::getInstance()->findMenuItem('base', 'users_main_menu_item');

    if ( !empty($menu) )
    {
        $menu->type = BOL_NavigationService::MENU_TYPE_HIDDEN;
        BOL_NavigationService::getInstance()->saveMenuItem($menu);
    }
}
catch ( Exception $e ) { }