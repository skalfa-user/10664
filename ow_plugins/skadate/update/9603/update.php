<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$navigation = Updater::getNavigationService();

try
{    
    $navigation->addMenuItem(
        BOL_MenuItemDao::VALUE_TYPE_HIDDEN,
        'base.mobile_version',
        'base',
        'mobile_version_menu_item',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

try
{
    Updater::getConfigService()->addConfig('skadate', 'promo_image_uploaded', false);
}
catch ( Exception $ex )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

try
{

    $document = new BOL_Document();
    $document->key = 'mobile_terms_of_use';
    $document->uri = 'terms-of-use';
    $document->isStatic = 1;
    $document->isMobile = 1;

    BOL_NavigationService::getInstance()->saveDocument($document);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

try
{
    $menu = BOL_NavigationService::getInstance()->findMenuItem('mobile','mobile_terms_of_use');
    if ( !empty($menu) )
    {
        BOL_NavigationService::getInstance()->deleteMenuItem($menu);
    }

    $order =  BOL_NavigationService::getInstance()->findMaxSortOrderForMenuType(BOL_NavigationService::MENU_TYPE_MOBILE_BOTTOM);

    $menuItem = new BOL_MenuItem();
    $menuItem->setType(BOL_NavigationService::MENU_TYPE_MOBILE_BOTTOM);
    $menuItem->setRoutePath('mobile_terms_of_use');
    $menuItem->setDocumentKey('mobile_terms_of_use');
    $menuItem->setPrefix(BOL_MobileNavigationService::MENU_PREFIX);
    $menuItem->setKey('mobile_terms_of_use');
    $menuItem->setOrder(($order + 1));
    $menuItem->setVisibleFor(BOL_NavigationService::VISIBLE_FOR_ALL);

    BOL_NavigationService::getInstance()->saveMenuItem($menuItem);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

$widgetService = Updater::getMobileWidgeteService();

try
{
    $widget = $widgetService->addWidget('SKADATE_MCMP_PromoImageWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, UPDATE_MobileWidgetService::PLACE_MOBILE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, UPDATE_MobileWidgetService::SECTION_MOBILE_MAIN, 0);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

try
{
    BOL_MobileWidgetService::getInstance()->deleteWidget('BASE_MCMP_UserListWidget');

    $widget = BOL_MobileWidgetService::getInstance()->addWidget('BASE_MCMP_UserListWidget', false);
    BOL_MobileWidgetService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

try
{
    $widgetService->deleteWidget('PHOTO_MCMP_PhotoListWidget');
    
    $widget = $widgetService->addWidget('PHOTO_MCMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 4);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'skadate');