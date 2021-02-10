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

$config = Updater::getConfigService();
if ( !$config->configExists('membership', 'notify_period') )
{
    $config->addConfig('membership', 'notify_period', '3', 'Remind users by email that membership expires in days');
}

$sql = "ALTER TABLE  `".OW_DB_PREFIX."membership_user` ADD `expirationNotified` TINYINT NOT NULL DEFAULT '0';";

try
{
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'membership');