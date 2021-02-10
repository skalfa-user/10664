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

OW::getPluginManager()->addPluginSettingsRouteName("lpage", "lpage.admin_settings");
OW::getPluginManager()->addUninstallRouteName("lpage", "lpage.admin_uninstall");
OW::getConfig()->addConfig("lpage", "settings", json_encode(array("label" => "Meet the love of your life. Today.", "bgFile" => null, "logoFile" => null)));
$path = OW::getPluginManager()->getPlugin("lpage")->getRootDir() . "langs.zip";
OW::getLanguage()->importPluginLangs($path, "lpage");