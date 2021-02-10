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

$preference = BOL_PreferenceService::getInstance()->findPreference("pcgallery_source");

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'pcgallery_source';
$preference->sectionName = 'general';
$preference->defaultValue = "all";
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$preference = BOL_PreferenceService::getInstance()->findPreference('pcgallery_album');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'pcgallery_album';
$preference->sectionName = 'general';
$preference->defaultValue = 0;
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);


Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', "pcgallery");