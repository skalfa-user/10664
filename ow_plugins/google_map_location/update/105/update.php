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


if ( !Updater::getConfigService()->configExists('googlelocation', 'is_api_key_exists') )
{
    Updater::getConfigService()->addConfig('googlelocation', 'is_api_key_exists', "1");
}

Updater::getLanguageService()->importPrefixFromDir(__DIR__ . DS . "langs", true);