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

$pluginKey = 'videoim';

// add permissions
$authorization = OW::getAuthorization();
$authorization->addGroup($pluginKey);
$authorization->addAction($pluginKey, 'video_im_call');
$authorization->addAction($pluginKey, 'video_im_receive');
$authorization->addAction($pluginKey, 'video_im_preferences');

try {
    // create a preference's section
    $preferenceSection = new BOL_PreferenceSection();
    $preferenceSection->name = $pluginKey;
    $preferenceSection->sortOrder = -1;
    BOL_PreferenceService::getInstance()->savePreferenceSection($preferenceSection);

    // create a preference
    $preference = new BOL_Preference();
    $preference->key = $pluginKey . '_decline_calls';
    $preference->sectionName = $pluginKey;
    $preference->defaultValue = 0;
    $preference->sortOrder = 1;
    BOL_PreferenceService::getInstance()->savePreference($preference);
}
catch (Exception $e)
{
    OW::getLogger()->addEntry($e->getMessage());
}

// create db tables
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "videoim_notification` (
    `id` int(11) NOT NULL auto_increment,
    `userId` int(11) NOT NULL,
    `recipientId` int(11) NOT NULL,
    `notification` TEXT NOT NULL,
    `createStamp` int(11) NOT NULL,
    `sessionId` varchar(20) NOT NULL,
    `accepted` tinyint(1) NOT NULL,
    PRIMARY KEY  (`id`),
    KEY `recipient` (`recipientId`, `createStamp`),
    KEY `notification` (`userId`, `recipientId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importPluginLangs($plugin->getRootDir() . 'langs.zip', $pluginKey);

// add the plugin's config route
OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, 'videoim_admin_config');

// add the plugin's config values
OW::getConfig()->addConfig($pluginKey, 'server_list', json_encode(array(
    array(
        'urls' => 'stun:stun01.sipphone.com',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.ekiga.net',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.fwdnet.net',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.ideasip.com',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.iptel.org',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.rixtelecom.se',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.schlund.de',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.l.google.com:19302',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun1.2.google.com:19302',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun1.3.google.com:19302',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun1.4.google.com:19302',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stunserver.org',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.softjoys.com',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.voiparound.com',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.voipbuster.com',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.voipstunt.com',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.voxgratia.org',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'stun:stun.xten.com',
        'username' => null,
        'credential' => null
    ),
    array(
        'urls' => 'turn:numb.viagenie.ca',
        'username' => 'webrtc@live.com',
        'credential' => 'muazkh'
    ),
    array(
        'urls' => 'turn:192.158.29.39:3478?transport=udp',
        'username' => '28224511:1379330808',
        'credential' => 'JZEOEt2V3Qb0y27GRntt2u2PAYA='
    ),
    array(
        'urls' => 'turn:192.158.29.39:3478?transport=tcp',
        'username' => '28224511:1379330808',
        'credential' => 'JZEOEt2V3Qb0y27GRntt2u2PAYA='
    )
)));
OW::getConfig()->addConfig($pluginKey, 'track_credits_type', 'both');