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

Updater::getLanguageService()->deleteLangKey('usearch', 'match_compatibitity');
Updater::getLanguageService()->deleteLangKey('usearch', 'order_match_compatibitity');
Updater::getLanguageService()->deleteLangKey('usearch', 'match_compatibitity_description');
Updater::getLanguageService()->deleteLangKey('usearch', 'distance_description');

Updater::getLanguageService()->importPrefixFromZip( dirname(__FILE__) . DS . 'langs.zip', 'usearch' );

try {
$widget = Updater::getMobileWidgeteService()->addWidget( 'USEARCH_MCMP_QuickSearchWidget', false );
$placeWidget = Updater::getMobileWidgeteService()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
Updater::getMobileWidgeteService()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 3);
}
catch ( Exception $e ) {  }

try {
    Updater::getNavigationService()->addMenuItem(OW_Navigation::MOBILE_TOP, 'users-search', 'usearch', 'mobile_menu_item_search', OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e ) {  }

if ( !Updater::getConfigService()->configExists( 'usearch', 'users_limit' ) )
{
    Updater::getConfigService()->addConfig( 'usearch', 'users_limit', 10000 );
}