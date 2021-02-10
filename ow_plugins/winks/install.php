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

OW::getDbo()->query('DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'winks_winks`;
CREATE TABLE `' . OW_DB_PREFIX . 'winks_winks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `partnerId` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `status` enum("accept","ignore","wait") NOT NULL DEFAULT "wait",
  `viewed` tinyint(1) NOT NULL DEFAULT "0",
  `conversationId` int(10) unsigned NOT NULL DEFAULT "0",
  `winkback` tinyint(1) NOT NULL DEFAULT "0",
  `messageType` ENUM( "chat", "mail" ) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`partnerId`),
  KEY `status` (`status`,`viewed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;');

OW::getLanguage()->importPluginLangs( OW::getPluginManager()->getPlugin('winks')->getRootDir() . 'langs.zip', 'winks' );
