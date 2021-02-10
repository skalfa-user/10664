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

OW::getLanguage()->importPluginLangs(__DIR__ . DS . 'langs.zip', 'protectedphotos', true);

if ( !OW::getPluginManager()->isPluginActive('photo') )
{
    OW::getFeedback()->warning(OW::getLanguage()->text('protectedphotos', 'install_error'));
    BOL_PluginService::getInstance()->uninstall('protectedphotos');

    throw new RedirectException(OW::getRouter()->urlForRoute('admin_plugins_installed'));
}

$sqls = array(
    'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'protectedphotos_passwords` (
  `id` int(10) unsigned NOT NULL,
  `albumId` int(10) unsigned NOT NULL,
  `privacy` varchar(128) NOT NULL,
  `meta` text,
  `password` varchar(128) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',

    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_passwords` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `albumId` (`albumId`);',

    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_passwords` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;',

    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_passwords` ADD KEY `privacy` (`privacy`)',

    'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'protectedphotos_accesses` (
  `id` int(10) unsigned NOT NULL,
  `passwordId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',

    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_accesses` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `passwordId` (`passwordId`,`userId`);',

    'ALTER TABLE `' . OW_DB_PREFIX . 'protectedphotos_accesses` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;'
);

foreach ( $sqls as $sql )
{
    try
    {
        OW::getDbo()->query($sql);
    }
    catch ( Exception $e )
    {
        OW::getLogger('protected_photos.install')->addEntry(json_encode($e));
    }
}
