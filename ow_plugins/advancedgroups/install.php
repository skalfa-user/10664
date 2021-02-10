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

if (!OW::getPluginManager()->isPluginActive('groups')) {
	return;
}

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('advancedgroups')->getRootDir() . 'langs.zip', 'advancedgroups');

$config = OW::getConfig();

OW::getPluginManager()->addPluginSettingsRouteName('advancedgroups', 'groups-admin-categories');
OW::getPluginManager()->addUninstallRouteName('advancedgroups', 'advancedgroups-uninstall');

$dbPrefix = OW_DB_PREFIX;

if(!OW::getDbo()->query("SHOW COLUMNS FROM `". OW_DB_PREFIX ."groups_group` LIKE 'categoryId'")) {
	$sql = "ALTER TABLE `{$dbPrefix}groups_group` ADD `categoryId` int( 11 ) default 1,
	ADD `viewed_count` int( 11 ) DEFAULT NULL DEFAULT '0';";
	OW::getDbo()->query($sql);
}

$sql = "
CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."advancedgroups_categories` (
  `id` int(11) NOT NULL auto_increment,
  `order` TINYINT(2) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `".OW_DB_PREFIX."advancedgroups_categories` (`id`, `order`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8)
;";

OW::getDbo()->query($sql);
// insert cats into langs
// should do it under importing langs
$langService = BOL_LanguageService::getInstance();
$currentLang = $langService->getCurrent();
$categories = array('Cooking & Health', 'Travel & Events', 'Sports', 'Science & Technology', 'Pets & Animals',
  'People & Blogs', 'Nonprofits & Activism', 'News & Politics');
foreach($categories as $keyArr => $title) {
  $description = "...";
  $keyArr += 1;
  $key = $langService->findKey('advancedgroups', 'category_title_' . $keyArr);
  if ( $key && $langService->findValue($currentLang->getId(), $key->getId()) )
  {
    continue;
  }
  $langService->addValue($currentLang->getId(), 'advancedgroups', 'category_title_' . $keyArr, $title);
  $langService->addValue($currentLang->getId(), 'advancedgroups', 'category_description_' . $keyArr, $description);
  $langService->generateCache($currentLang->getId());
}

OW::getPluginManager()->addPluginSettingsRouteName('advancedgroups', 'groups-admin-categories');
