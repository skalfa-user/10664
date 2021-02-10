<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$pluginKey = 'skmobileapp';
$config = Updater::getConfigService();

$configs = [
    'inapps_enable' => true,
    'inapps_show_membership_actions' => 'app_only',
    'inapps_apm_package_name' => '',
    'inapps_apm_android_client_email' => '',
    'inapps_apm_android_private_key' => '',
    'service_account_auth_expiration_time' => '',
    'service_account_auth_token' => ''
];

// add configs
foreach ( $configs as $key => $value )
{
    if ( !$config->configExists($pluginKey, $key) )
    {
        $config->addConfig($pluginKey, $key, $value);
    }
}

$sql = [];

$sql[] = "RENAME TABLE `" . OW_DB_PREFIX . $pluginKey . "_devices` TO `" . OW_DB_PREFIX . $pluginKey . "_device`;";
$sql[] = "ALTER TABLE `" . OW_DB_PREFIX . $pluginKey . "_device` CHANGE COLUMN `uuid` `deviceUuid` varchar(255) NOT NULL;";
$sql[] = "ALTER TABLE `" . OW_DB_PREFIX . $pluginKey . "_device` CHANGE COLUMN `token` `token` varchar(255) NOT NULL;";
$sql[] = "ALTER TABLE `" . OW_DB_PREFIX . $pluginKey . "_device` CHANGE COLUMN `platform` `platform` varchar(10) NOT NULL;";
$sql[] = "ALTER TABLE `" . OW_DB_PREFIX . $pluginKey . "_device` DROP COLUMN `properties`; ";
$sql[] = "ALTER TABLE `" . OW_DB_PREFIX . $pluginKey . "_device` DROP COLUMN `addTime`; ";
$sql[] = "ALTER TABLE `" . OW_DB_PREFIX . $pluginKey . "_device` ADD `language` varchar(10) NOT NULL;";

// Alter table devices
foreach ( $sql as $query )
{
    try
    {
        Updater::getDbo()->query($query);
    }
    catch ( Exception $e )
    {
        Updater::getLogger()->addEntry(json_encode($e));
    }
}

// create table web_push
try
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . $pluginKey . "_web_push` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `userId` int(11) UNSIGNED NOT NULL,
        `deviceId` int(11) UNSIGNED NOT NULL,
        `title` text NOT NULL,
        `message` text NOT NULL,
        `pushParams` varchar(255) DEFAULT NULL,
        `expirationTime` int(11) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        KEY `userId` (`userId`, `deviceId`),
        KEY `expirationTime` (`expirationTime`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

// create table inapps_purchase
try
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . $pluginKey ."_inapps_purchase` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `membershipId` int(11) UNSIGNED NOT NULL,
        `saleId` int(11) UNSIGNED NOT NULL,
        `platform` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `membershipId` (`membershipId`),
        KEY `saleId` (`saleId`),
        KEY `platform` (`membershipId`, `platform`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

// create table expiration_purchase
try
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . $pluginKey ."_expiration_purchase` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `membershipId` int(11) UNSIGNED NOT NULL,
        `typeId` int(11) NOT NULL,
        `userId` int(11) NOT NULL,
        `expirationTime` int(11) NOT NULL,
        `counter` tinyint(4) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        KEY `membershipId` (`membershipId`),
        KEY `userId` (`userId`),
        KEY `expirationTime` (`expirationTime`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    
    Updater::getDbo()->query($sql);

}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

// update billing adapterClassName
try
{
    $billing = BOL_BillingService::getInstance()->findGatewayByKey('skmobileapp');
    $billing->adapterClassName = 'SKMOBILEAPP_CLASS_InAppPurchaseAdapter';

    BOL_BillingGatewayDao::getInstance()->save($billing);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

// add user preferences
try
{
    $sectionName = $pluginKey . '_pushes';
    $preferenceSection = new BOL_PreferenceSection();
    $preferenceSection->name = $sectionName;
    $preferenceSection->sortOrder = -1;
    BOL_PreferenceService::getInstance()->savePreferenceSection($preferenceSection);

    $preference = new BOL_Preference();
    $preference->key = $pluginKey . '_new_matches_push';
    $preference->sectionName = $sectionName;
    $preference->defaultValue = 'true';
    $preference->sortOrder = 1;
    BOL_PreferenceService::getInstance()->savePreference($preference);

    $preference = new BOL_Preference();
    $preference->key = $pluginKey . '_new_messages_push';
    $preference->sectionName = $sectionName;
    $preference->defaultValue = 'true';
    $preference->sortOrder = 2;
    BOL_PreferenceService::getInstance()->savePreference($preference);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry($e->getMessage());
}

$langService = Updater::getLanguageService();

$langService->deleteLangKey($pluginKey, 'delete_photo_error');
$langService->deleteLangKey($pluginKey, 'delete_avatar_error');
$langService->deleteLangKey($pluginKey, 'choose_avatar_from_library');
$langService->deleteLangKey($pluginKey, 'take_avatar');
$langService->deleteLangKey($pluginKey, 'profile_create_error');
$langService->deleteLangKey($pluginKey, 'take_photo');
$langService->deleteLangKey($pluginKey, 'choose_photo_from_library');
$langService->deleteLangKey($pluginKey, 'error_storing_file');
$langService->deleteLangKey($pluginKey, 'error_getting_file_info');
$langService->deleteLangKey($pluginKey, 'error_selecting_image');

// import languages
$langService->importPrefixFromDir(__DIR__ . DS . 'langs', true);
