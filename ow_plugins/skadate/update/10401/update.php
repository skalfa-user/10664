<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langId = null;

foreach ( $languages as $lang )
{
    if ( $lang->tag == "en" )
    {
        $langId = $lang->id;
        break;
    }
}

if ( $langId !== null )
{
    $prefix = "skadate";

    $languageService->addOrUpdateValue($langId, $prefix, "settings_page_headeing", '{$pluginTitle} settings');
    $languageService->addOrUpdateValue($langId, $prefix, "validate_license", "Validate license");
    $languageService->addOrUpdateValue($langId, $prefix, "validation_success",
        "Skadate license key validated successfully");

    $langVal = 'Your license key appears to be invalid. This error can appear due to several reasons: '
        . '<br /> - Changed site domain<br /> - Changed site IP address<br /> - '
        . 'Changed server path to the installed copy of SkaDate Dating Software<br /> '
        . '<br /> To resolve this problem please reissue your license in SkaDate Customer Area by following this guide - '
        . '<a href="https://hello.skadate.com/docs/manuals/licensing" target="_blank">https://hello.skadate.com/docs/manuals/licensing</a>. '
        . '<br /> Once the key is reissued, click the "Validate License" button below.';

    $languageService->addOrUpdateValue($langId, $prefix, "invalid_key_text", $langVal);
    $languageService->addOrUpdateValue($langId, $prefix, "validation_failed",
        "License key is invalid. Check the license key for typos and try again. Contact support team in case of repeated failure.");
    $languageService->addOrUpdateValue($langId, $prefix, "validation_failed_tech",
        "Cannot connect to remote server. Please contact support team for assistance.");
}