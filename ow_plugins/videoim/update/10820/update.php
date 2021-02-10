<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

try
{
    Updater::getDbo()->query('ALTER TABLE `' . OW_DB_PREFIX . 'videoim_notification` ADD `sessionId` VARCHAR(20) NOT NULL');
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry($e->getTraceAsString(), 'videoim_update_error');
}

Updater::getLanguageService()->importPrefixFromDir(__DIR__ . DS . 'langs');

$serverList = Updater::getConfigService()->getValue('videoim', 'server_list');
$serverList = preg_replace('/"url"/', '"urls"', $serverList);
Updater::getConfigService()->saveConfig('videoim', 'server_list', $serverList);


