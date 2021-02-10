<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$config = OW::getConfig();

if ( !$config->configExists('bookmarks', 'notify_interval') )
{
    $config->addConfig('bookmarks', 'notify_interval', 10);
}

if ( !$config->configExists('bookmarks', 'widget_user_count') )
{
    $config->addConfig('bookmarks', 'widget_user_count', 9);
}

OW::getDbo()->query('DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'bookmarks_mark`;
CREATE TABLE `' . OW_DB_PREFIX . 'bookmarks_mark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `markUserId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`markUserId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'bookmarks_notify_log`;
CREATE TABLE `' . OW_DB_PREFIX . 'bookmarks_notify_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getPluginManager()->addPluginSettingsRouteName('bookmarks', 'bookmarks.admin');

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('bookmarks')->getRootDir() . 'langs.zip', 'bookmarks');
