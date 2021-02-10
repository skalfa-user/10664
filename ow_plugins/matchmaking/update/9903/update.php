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

$distanceFromMyLocation = BOL_PreferenceService::getInstance()->findPreference('matchmaking_distance_from_my_location');

if ( empty($distanceFromMyLocation) )
{
    $distanceFromMyLocation = new BOL_Preference();
    $distanceFromMyLocation->key = 'matchmaking_distance_from_my_location';
    $distanceFromMyLocation->defaultValue = 10;
    $distanceFromMyLocation->sectionName = 'matchmaking';
    $distanceFromMyLocation->sortOrder = 1;
    BOL_PreferenceService::getInstance()->savePreference($distanceFromMyLocation);
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'matchmaking');
