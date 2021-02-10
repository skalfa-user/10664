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

$languageService = Updater::getLanguageService();
$configService = Updater::getConfigService();

if ( !$configService->configExists('usearch', 'enable_username_search') )
{
    $configService->addConfig('usearch', 'enable_username_search', '');
}

$languages = $languageService->getLanguages();
$langId = null;

foreach ($languages as $lang)
{
    if ($lang->tag == 'en')
    {
        $langId = $lang->id;
        break;
    }
}

if ($langId !== null)
{
    $languageService->addOrUpdateValue($langId, 'usearch', 'search_options', 'Search Options');
    $languageService->addOrUpdateValue($langId, 'usearch', 'enable_username_search', 'Enable search by Username');
    $languageService->addOrUpdateValue($langId, 'usearch', 'username', 'Username');
    $languageService->addOrUpdateValue($langId, 'usearch', 'search_by_username', 'Search by Username');
}
