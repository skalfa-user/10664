<?php

$updateDir = dirname(__FILE__) . DS;

Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'hint');

if ( Updater::getConfigService()->configExists("hint", "admin_notified") )
{
    Updater::getConfigService()->saveConfig("hint", "admin_notified", 0);
}
else 
{
    Updater::getConfigService()->addConfig("hint", "admin_notified", 0);
}