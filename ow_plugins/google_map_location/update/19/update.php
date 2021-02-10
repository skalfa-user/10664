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
 * @since 1.0
 */

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'googlelocation');

$params = $e->getParams();
$pluginKey = $params['pluginKey'];

if ( $pluginKey == 'groups' )
{
    $widgetService = BOL_ComponentAdminService::getInstance();

    $widget = $widgetService->addWidget('GOOGLELOCATION_CMP_GroupsWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0);
}

"ALTER TABLE `ow_googlelocation_data` CHANGE `lat` `lat` DECIMAL( 15, 4 ) NOT NULL ,
CHANGE `lng` `lng` DECIMAL( 15, 4 ) NOT NULL ,
CHANGE `northEastLat` `northEastLat` DECIMAL( 15, 4 ) NOT NULL ,
CHANGE `northEastLng` `northEastLng` DECIMAL( 15, 4 ) NOT NULL ,
CHANGE `southWestLat` `southWestLat` DECIMAL( 15, 4 ) NOT NULL ";
"ALTER TABLE `ow_googlelocation_data` CHANGE `southWestLng` `southWestLng` DECIMAL( 15, 4 ) NOT NULL ";
"ALTER TABLE `ow_googlelocation_data` CHANGE `userId` `entityId` INT( 11 ) NOT NULL ";
"ALTER TABLE `ow_googlelocation_data` ADD `entityType` ENUM( 'user', 'event' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `entityId` ";
"ALTER TABLE `ow_googlelocation_data` DROP INDEX `entityId` ";
"ALTER TABLE `ow2_dev`.`ow_googlelocation_data` ADD INDEX `entityId` ( `entityId` , `entityType` ) ";