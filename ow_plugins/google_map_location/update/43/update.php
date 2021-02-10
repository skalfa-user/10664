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

try
{
    $sql = " ALTER TABLE `".OW_DB_PREFIX."googlelocation_data` DROP INDEX entityId; ";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }
        
try
{
    $sql = "ALTER IGNORE TABLE `".OW_DB_PREFIX."googlelocation_data` ADD UNIQUE `entityId` ( `entityId` , `entityType` ) ";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }