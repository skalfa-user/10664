<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$pluginKey = 'sktextcr';

$config = OW::getConfig();


$defaultConfigs = [
    'searchType' => '',
    'searchTypeResult' => '',
];

foreach ($defaultConfigs as $key => $value)
{
    if ( !$config->configExists($pluginKey, $key) )
    {
        $config->addConfig($pluginKey, $key, $value);
    }
}

// add admin settings route
OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, $pluginKey . '.admin-all-message');

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');
