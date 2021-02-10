<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$pluginKey = 'gdpr';

OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, 'gdpr-admin-index');

// add plugin settings
if ( !OW::getConfig()->configExists($pluginKey, 'gdpr_third_party_services') )
{
    OW::getConfig()->addConfig($pluginKey, 'gdpr_third_party_services', '0', OW::getLanguage()->text($pluginKey, 'gdpr_third_party_services_desc'));
}

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');
