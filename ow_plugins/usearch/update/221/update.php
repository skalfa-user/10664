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

if ( !OW::getConfig()->configExists('usearch', 'quick_search_fields') )
{
    OW::getConfig()->addConfig('usearch', 'quick_search_fields', '');
}

OW::getPluginManager()->addPluginSettingsRouteName('usearch', 'usearch.admin_quick_search_setting');

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'usearch');

try 
{
   Updater::getLanguageService()->deleteLangKey('usearch', 'action_unfollow');
   Updater::getLanguageService()->deleteLangKey('usearch', 'action_unblock');
   Updater::getLanguageService()->deleteLangKey('usearch', 'action_removefriend');
   Updater::getLanguageService()->deleteLangKey('usearch', 'action_block');
   Updater::getLanguageService()->deleteLangKey('usearch', 'action_follow');
   Updater::getLanguageService()->deleteLangKey('usearch', 'action_addfriend');
}
catch (Exception $ex) 
{
   print_r($ex);
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'usearch');