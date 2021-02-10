<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$pluginKey = 'hotlist';
$dbPrefix = OW_DB_PREFIX.$pluginKey.'_';

$sql =
    <<<EOT

CREATE TABLE IF NOT EXISTS `{$dbPrefix}user` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `timestamp` int(10) NOT NULL,
  `expiration_timestamp` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOT;

OW::getDbo()->query($sql);

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin($pluginKey)->getRootDir() . 'langs.zip', $pluginKey);

OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, 'hotlist-admin-settings');

OW::getConfig()->addConfig($pluginKey, 'expiration_time', 86400 * 30);

OW::getAuthorization()->addGroup('hotlist', false);
OW::getAuthorization()->addAction('hotlist', 'add_to_list');