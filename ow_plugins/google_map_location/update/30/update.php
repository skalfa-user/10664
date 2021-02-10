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


    $sql = "ALTER TABLE `".OW_DB_PREFIX."googlelocation_data` CHANGE `lat` `lat` DECIMAL( 15, 4 ) NOT NULL ,
                CHANGE `lng` `lng` DECIMAL( 15, 4 ) NOT NULL ,
                CHANGE `northEastLat` `northEastLat` DECIMAL( 15, 4 ) NOT NULL ,
                CHANGE `northEastLng` `northEastLng` DECIMAL( 15, 4 ) NOT NULL ,
                CHANGE `southWestLat` `southWestLat` DECIMAL( 15, 4 ) NOT NULL ";
    Updater::getDbo()->query($sql);


    $sql = "ALTER TABLE `".OW_DB_PREFIX."googlelocation_data` CHANGE `southWestLng` `southWestLng` DECIMAL( 15, 4 ) NOT NULL ";;
    Updater::getDbo()->query($sql);






    $sql = "UPDATE `".OW_DB_PREFIX."googlelocation_data` SET `entityType` = 'user' ";
    Updater::getDbo()->query($sql);


    $sql = " DELETE g FROM  `".OW_DB_PREFIX."googlelocation_data` g
        LEFT JOIN `".OW_DB_PREFIX."base_user` u ON u.id = g.entityId
        WHERE g.entityType = 'user' AND u.id IS NULL ";
    Updater::getDbo()->query($sql);


//Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'googlelocation');
//exit;
