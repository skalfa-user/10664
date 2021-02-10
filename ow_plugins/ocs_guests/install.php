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

$config = OW::getConfig();

if ( !$config->configExists('ocsguests', 'store_period') )
{
    $config->addConfig('ocsguests', 'store_period', 3, 'Guests visit period, months');
}

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."ocsguests_guest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `guestId` int(11) NOT NULL,
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  `visitTimestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`guestId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('ocsguests', 'ocsguests.admin');

$path = OW::getPluginManager()->getPlugin('ocsguests')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'ocsguests');
