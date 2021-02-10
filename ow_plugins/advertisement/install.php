<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "ads_banner` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `code` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "ads_banner_location` (
  `id` int(11) NOT NULL auto_increment,
  `bannerId` int(11) NOT NULL,
  `location` char(3) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `bannerId` (`bannerId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "ads_banner_position` (
  `id` int(11) NOT NULL auto_increment,
  `bannerId` int(11) NOT NULL,
  `position` enum('top','sidebar','bottom') NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `bannerId` (`bannerId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

$tblPrefix = OW_DB_PREFIX;

$dbo = OW::getDbo();
$logger = OW::getLogger();

// add absent columns (fix for https://github.com/skalfa/workflow/issues/1223)
try
{
    $query = "SHOW COLUMNS FROM `{$tblPrefix}base_geolocation_ip_to_country` LIKE 'ipFrom'";
    $column = $dbo->queryForRow($query);

    if ( !$column )
    {
        $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` ADD `ipFrom` bigint UNSIGNED";
        $dbo->query($query);
    }
    else
    {
        $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` CHANGE `ipFrom` `ipFrom` bigint UNSIGNED";
        $dbo->query($query);
    }
}
catch (Exception $e)
{
    $logger->addEntry(json_encode($e));
}

try
{
    $query = "SHOW COLUMNS FROM `{$tblPrefix}base_geolocation_ip_to_country` LIKE 'ipTo'";
    $column = $dbo->queryForRow($query);

    if ( !$column )
    {
        $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` ADD `ipTo` bigint UNSIGNED";
        $dbo->query($query);
    }
    else
    {
        $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` CHANGE `ipTo` `ipTo` bigint UNSIGNED";
        $dbo->query($query);
    }
}
catch (Exception $e)
{
    $logger->addEntry(json_encode($e));
}

// add index
try
{
    $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` ADD INDEX `ipRange` (`ipFrom`, `ipTo`)";
    $dbo->query($query);

}
catch (Exception $e)
{
    $logger->addEntry(json_encode($e));
}

$authorization = OW::getAuthorization();
$groupName = 'ads';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'hide_ads', true);
$action = BOL_AuthorizationService::getInstance()->findAction('ads', 'hide_ads');
BOL_AuthorizationPermissionDao::getInstance()->deleteByActionId($action->getId());

OW::getPluginManager()->addPluginSettingsRouteName('ads', 'ads.admin_settings_index');

$path = OW::getPluginManager()->getPlugin('ads')->getRootDir() . 'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'ads');