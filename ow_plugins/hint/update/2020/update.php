<?php

$configs = array(
    "option-user-view-newWindow" => true,
    "option-group-group-view-newWindow" => true,
    "option-event-event-view-newWindow" => true
);

foreach ($configs as $name => $value)
{
    if (!Updater::getConfigService()->configExists("hint", $name))
    {
        Updater::getConfigService()->addConfig("hint", $name, $value);
    }
}


$updateDir = dirname(__FILE__) . DS;

Updater::getConfigService()->saveConfig("hint", "admin_notified", 0);
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'hint');