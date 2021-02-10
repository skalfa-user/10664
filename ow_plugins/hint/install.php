<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

try
{
    OW::getPluginManager()->addPluginSettingsRouteName('hint', 'hint-configuration');
}
catch ( Exception $e )
{
    // Log
}

OW::getConfig()->addConfig("hint", "admin_notified", 0);

// Users

OW::getConfig()->addConfig("hint", "info_user_line0", json_encode(array(
    "key" => "base-activity",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "info_user_line1", json_encode(array(
    "key" => "base-gender-age",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "info_user_line2", json_encode(array(
    "key" => "friends-list",
    "question" => null
)));


// Groups

OW::getConfig()->addConfig("hint", "info_group_line0", json_encode(array(
    "key" => "group-access",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "info_group_line1", json_encode(array(
    "key" => "group-created-admin",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "info_group_line2", json_encode(array(
    "key" => "group-users",
    "question" => null
)));


// Events

OW::getConfig()->addConfig("hint", "ehintType", "date");

OW::getConfig()->addConfig("hint", "info_event_line0", json_encode(array(
    "key" => "event-location",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "info_event_line1", json_encode(array(
    "key" => "event-access-creator",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "info_event_line2", json_encode(array(
    "key" => "event-desc",
    "question" => null
)));

OW::getConfig()->addConfig("hint", "option-user-view-newWindow", true);
OW::getConfig()->addConfig("hint", "option-group-group-view-newWindow", true);
OW::getConfig()->addConfig("hint", "option-event-event-view-newWindow", true);

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('hint')->getRootDir() . 'langs.zip', 'hint');
