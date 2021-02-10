<?php

$dbPrefix = OW_DB_PREFIX;

$sql = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}articles_article` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `image` varchar(100) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `featured` int(11) NULL DEFAULT 0,
  PRIMARY KEY  (`id`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('articles', 'articles.admin_index');
OW::getLanguage()->importLangsFromDir(__DIR__ . DS . 'langs', true, true);