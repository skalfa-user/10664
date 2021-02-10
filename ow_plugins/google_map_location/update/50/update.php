<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location
 * @since 1.6
 */

try
{
    if ( !Updater::getConfigService()->configExists('googlelocation', 'country_restriction') )
    {
        Updater::getConfigService()->addConfig('googlelocation', 'country_restriction', '');
    }
}
catch ( Exception $e ){ }

try
{
    $widgetService = Updater::getWidgetService();

    $widget = $widgetService->addWidget('GOOGLELOCATION_CMP_GroupsWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
}
catch ( Exception $e ){ }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'googlelocation');
