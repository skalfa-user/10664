<?php

$pluginKey = 'skmobileapp';
$config = Updater::getConfigService();
$langService = Updater::getLanguageService();

// add config
if ( !$config->configExists($pluginKey, 'pn_vapid_key') )
{
    $config->addConfig($pluginKey, 'pn_vapid_key', '');
}

// import languages
$langService->importPrefixFromDir(__DIR__ . DS . 'langs', true);