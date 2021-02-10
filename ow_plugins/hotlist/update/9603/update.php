<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

try 
{
    $widget = Updater::getMobileWidgeteService()->addWidget('HOTLIST_MCMP_Widget');
    $placeWidget = Updater::getMobileWidgeteService()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
    Updater::getMobileWidgeteService()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN ); 
} 
catch (Exception $ex) 
{
    Updater::getLogger()->addEntry(json_encode($e));
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'hotlist');
