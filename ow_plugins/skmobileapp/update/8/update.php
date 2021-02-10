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

$pluginKey = 'skmobileapp';
$config = OW::getConfig();
$langService = Updater::getLanguageService();

if ( !$config->configExists($pluginKey, 'google_map_api_key') )
{
    $config->addConfig($pluginKey, 'google_map_api_key', '');
}

// import languages
$langService->importPrefixFromDir(__DIR__ . DS . 'langs', true);