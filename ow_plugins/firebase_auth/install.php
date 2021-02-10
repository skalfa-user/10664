<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$pluginKey = 'firebaseauth';

OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, $pluginKey . '_admin_index');

$config = OW::getConfig();

$defaultConfigs = array(
    'api_key' => '',
    'auth_domain' => '',
    'max_displayed_providers' => 5,
    'email_example' => 'email@example.com',
    'enabled_providers' => '[]'
);

foreach ($defaultConfigs as $key => $value)
{
    if ( !$config->configExists($pluginKey, $key) )
    {
        $config->addConfig($pluginKey, $key, $value);
    }
}

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');
