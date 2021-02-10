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


if ( !Updater::getConfigService()->configExists('googlelocation', 'display_map_profile_pages') )
{
    Updater::getConfigService()->addConfig('googlelocation', 'display_map_on_profile_pages', 0);
}

Updater::getLanguageService()->importPrefixFromDir(__DIR__ . DS . "langs", true);