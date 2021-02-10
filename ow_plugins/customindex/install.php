<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$pluginKey = 'customindex';

OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, $pluginKey . '.admin');


// create db tables
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . $pluginKey . "_banner` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(128) NOT NULL,
      `html` text,
      PRIMARY KEY (`id`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');
