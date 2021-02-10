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

$plugin = OW::getPluginManager()->getPlugin('moderation');

try {
    OW::getConfig()->addConfig("moderation", "content_types", json_encode(array()));
} catch (Exception $ex) {
    // pass
}

OW::getPluginManager()->addPluginSettingsRouteName('moderation', 'moderation.admin');

$query = array();
$query[] = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "moderation_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(100) NOT NULL,
  `entityId` int(11) NOT NULL,
  `data` text NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

foreach ( $query as $q )
{
    try {
        OW::getDbo()->query($q);
    } catch (Exception $ex) {
        // Pass
    }
}

BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'moderation');