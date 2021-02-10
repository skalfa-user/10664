<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

try
{
    $sql = "UPDATE  `" . OW_DB_PREFIX . "base_plugin` SET  `adminSettingsRoute` =  'skadate.settigns' WHERE  `key` = 'skadate'";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    
}

if ( !UPDATER::getConfigService()->configExists('skadate', 'license_info') )
{
    UPDATER::getConfigService()->addConfig('skadate', 'license_info', json_encode(array()));
}

if ( !UPDATER::getConfigService()->configExists('skadate', 'brand_removal') )
{
    UPDATER::getConfigService()->addConfig('skadate', 'brand_removal', 0);
}

if ( !UPDATER::getConfigService()->configExists('skadate', 'license_key') )
{
    UPDATER::getConfigService()->addConfig('skadate', 'license_key', '');
}

if ( !UPDATER::getConfigService()->configExists('skadate', 'license_key_valid') )
{
    UPDATER::getConfigService()->addConfig('skadate', 'license_key_valid', 0);
}

try
{
    UPDATER::getNavigationService()->deleteMenuItem('base', 'mobile_version_menu_item');
}
catch ( Exception $e )
{

}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'skadate');
