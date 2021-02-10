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

$path = OW::getPluginManager()->getPlugin('usearch')->getRootDir() . 'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'usearch');

if ( !OW::getConfig()->configExists('usearch', 'quick_search_fields') )
{
    OW::getConfig()->addConfig('usearch', 'quick_search_fields', '');
}

if ( !OW::getConfig()->configExists('usearch', 'order_latest_activity') )
{
    OW::getConfig()->addConfig('usearch', 'order_latest_activity', 1);
}

if ( !OW::getConfig()->configExists('usearch', 'order_recently_joined') )
{
    OW::getConfig()->addConfig('usearch', 'order_recently_joined', 1);
}

if ( !OW::getConfig()->configExists('usearch', 'order_match_compatibitity') )
{
    OW::getConfig()->addConfig('usearch', 'order_match_compatibitity', 0);
}

if ( !OW::getConfig()->configExists('usearch', 'order_distance') )
{
    OW::getConfig()->addConfig('usearch', 'order_distance', 0);
}

if ( !OW::getConfig()->configExists('usearch', 'hide_user_activity_after') )
{
    OW::getConfig()->addConfig('usearch', 'hide_user_activity_after', 400);
}

if ( !OW::getConfig()->configExists('usearch', 'enable_username_search') )
{
    OW::getConfig()->addConfig('usearch', 'enable_username_search', 0);
}

OW::getPluginManager()->addPluginSettingsRouteName('usearch', 'usearch.admin_general_setting');
