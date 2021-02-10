<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$pluginKey = 'cvideoupload';

$authorization = OW::getAuthorization();
$config = OW::getConfig();


$authorization->addGroup($pluginKey);
$authorization->addAction($pluginKey, 'upload_video');

try {
    $ffmpePath = exec("which ffmpeg");

    if ( is_array($ffmpePath) )
    {
        unset($ffmpePath);
    }
}
catch (Exception $exception)
{

}

$defaultConfigs = [
    'ffmpegPath' => !empty($ffmpePath) ? $ffmpePath : '/usr/bin/ffmpeg',
    'fileSize' => '52428800',
    'maxDuration' => 30,
    'typeOutput' => '.mp4',
    'typeInput' => 'a:6:{i:0;s:9:"video/mp4";i:1;s:11:"video/x-flv";i:2;s:10:"video/webm";i:3;s:9:"video/ogg";i:4;s:15:"application/ogg";i:5;s:9:"video/ogv";}',
    'watermark' => '',
    'watermarkEnabled' => false,
];

foreach ($defaultConfigs as $key => $value)
{
    if ( !$config->configExists($pluginKey, $key) )
    {
        $config->addConfig($pluginKey, $key, $value);
    }
}

// create DB tables
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "cvideoupload_video` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `fileName` char(16) NOT NULL,
    `readableFileName` varchar(100) NOT NULL,
    `fileType` varchar(20) NOT NULL,
    `status` enum('not_confirmed', 'in_process','not_processed','processed') NOT NULL DEFAULT 'not_confirmed',
    `authorization` enum('approval','approved','blocked') NOT NULL DEFAULT 'approval',
    `privacy` enum('everybody','only_owner','certain_users') NOT NULL DEFAULT 'everybody',
    `userId` int(11) DEFAULT NULL,
    `timestamp` int(11) NOT NULL,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `fileName` (`fileName`),
    KEY `video` (`userId`, `status`),
    KEY `authorization` (`userId`, `authorization`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "cvideoupload_privacy` ( 
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `userId` int(11) DEFAULT NULL,
    `friendId` int(11) DEFAULT NULL,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `users` (`userId`, `friendId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

// add admin settings route
OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, $pluginKey . '.admin-settings');

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');
