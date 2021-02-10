<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location
 * @since 1.7.6
 */


if ( !Updater::getConfigService()->configExists('googlelocation', 'map_provider') )
{
    Updater::getConfigService()->addConfig('googlelocation', 'map_provider', "google", "Map Provider");
}

if ( !Updater::getConfigService()->configExists('googlelocation', 'bing_api_key') )
{
    Updater::getConfigService()->addConfig('googlelocation', 'bing_api_key', "", "bing maps api key");
}

Updater::getLanguageService()->importPrefixFromZip(OW::getPluginManager()->getPlugin('googlelocation')->getRootDir() . 'langs.zip', 'googlelocation');
