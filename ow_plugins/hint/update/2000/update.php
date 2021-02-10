<?php

$configs = array(
    "ehintType" => "date"
);

$configs["info_user_line0"] = json_encode(array(
    "key" => "base-activity",
    "question" => null
));

$configs["info_user_line1"] = json_encode(array(
    "key" => "base-gender-age",
    "question" => null
));

$configs["info_user_line2"] = json_encode(array(
    "key" => "friends-list",
    "question" => null
));


$configs["info_group_line0"] = json_encode(array(
    "key" => "group-access",
    "question" => null
));

$configs["info_group_line1"] = json_encode(array(
    "key" => "group-created-admin",
    "question" => null
));

$configs["info_group_line2"] = json_encode(array(
    "key" => "group-users",
    "question" => null
));


$configs["info_event_line0"] = json_encode(array(
    "key" => "event-location",
    "question" => null
));

$configs["info_event_line1"] = json_encode(array(
    "key" => "event-access-creator",
    "question" => null
));

$configs["info_event_line2"] = json_encode(array(
    "key" => "event-desc",
    "question" => null
));

foreach ( $configs as $name => $value )
{
    if ( !Updater::getConfigService()->configExists("hint", $name) )
    {
        Updater::getConfigService()->addConfig("hint", $name, $value);
    }
}

$updateDir = dirname(__FILE__) . DS;

Updater::getConfigService()->saveConfig("hint", "admin_notified", 0);
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'hint');