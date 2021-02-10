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
$config = OW::getConfig();

OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, $pluginKey . '_admin_ads');

$defaultConfigs = array(
    'import_location_last_user_id' => 0,
    'ads_api_key' => '',
    'ads_enabled' => false,
    'pn_vapid_key' => '',
    'pn_sender_id' => '',
    'pn_server_key' => '',
    'pn_apns_pass_phrase' => '',
    'pn_apns_mode' => '',
    'pn_enabled' => true,
    'inapps_enable' => true,
    'inapps_apm_key' => '',
    'inapps_itunes_shared_secret' => '',
    'inapps_ios_test_mode' => false,
    'inapps_show_membership_actions' => 'app_only', // app_only | all
    'ios_app_url' => 'https://itunes.apple.com/in/app/date-finder-app/id1263891062?mt=8',
    'android_app_url' => 'https://play.google.com/store/apps/details?id=com.skmobile&hl=en',
    'search_mode' => 'both',
    'inapps_apm_package_name' => '',
    'inapps_apm_android_client_email' => '',
    'inapps_apm_android_private_key' => '',
    'service_account_auth_expiration_time' => '',
    'service_account_auth_token' => '',
    'google_map_api_key' => ''
);

foreach ($defaultConfigs as $key => $value)
{
    if ( !$config->configExists($pluginKey, $key) )
    {
        $config->addConfig($pluginKey, $key, $value);
    }
}

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

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . $pluginKey . "_device` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `userId` int(11) UNSIGNED NOT NULL,
    `deviceUuid` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `platform` varchar(10) NOT NULL,
    `activityTime` int(11) UNSIGNED DEFAULT NULL,
    `language` varchar(10) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `userId` (`userId`),
    KEY `activityTime` (`activityTime`),
    UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

// create db tables
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . $pluginKey . "_user_match_action` (
    `id` int(11) NOT NULL auto_increment,
    `userId` int(11) NOT NULL,
    `recipientId` int(11) NOT NULL,
    `type` varchar(20) NOT NULL,
    `createStamp` int(11) NOT NULL,
    `expirationStamp` int(11) NOT NULL,
    `mutual` tinyint(1) NOT NULL DEFAULT 0,
    `read` tinyint(1) NOT NULL DEFAULT 0,
    `new` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `userMatch` (`userId`, `recipientId`),
    KEY `expiration` (`userId`, `recipientId`, `type`, `expirationStamp`),
    KEY `mutual` (`userId`, `type`, `mutual`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX . $pluginKey ."_user_location` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `userId` int(11) UNSIGNED NOT NULL,
    `latitude` DECIMAL( 15, 4 ) NOT NULL,
    `longitude` DECIMAL( 15, 4 ) NOT NULL,
    `northEastLatitude` DECIMAL( 15, 4 ) NOT NULL,
    `northEastLongitude` DECIMAL( 15, 4 ) NOT NULL,
    `southWestLatitude` DECIMAL( 15, 4 ) NOT NULL,
    `southWestLongitude` DECIMAL( 15, 4 ) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `userId` (`userId`),
    KEY `userLocation` (`userId`, `southWestLatitude`, `northEastLatitude`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX . $pluginKey ."_inapps_purchase` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `membershipId` int(11) UNSIGNED NOT NULL,
    `saleId` int(11) UNSIGNED NOT NULL,
    `platform` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `membershipId` (`membershipId`),
    KEY `saleId` (`saleId`),
    KEY `platform` (`membershipId`, `platform`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX . $pluginKey ."_expiration_purchase` (
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

OW::getDbo()->query($sql);

$billingService = BOL_BillingService::getInstance();

$gateway = new BOL_BillingGateway();
$gateway->gatewayKey = $pluginKey;
$gateway->adapterClassName = 'SKMOBILEAPP_CLASS_InAppPurchaseAdapter';
$gateway->active = 0;
$gateway->mobile = 1;
$gateway->recurring = 1;
$gateway->dynamic = 0;
$gateway->hidden = 1;
$gateway->currencies = 'AUD,CAD,EUR,GBP,JPY,USD';

$billingService->addGateway($gateway);

// user preferences
try {
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
catch (Exception $e)
{
    OW::getLogger()->addEntry($e->getMessage());
}

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');
