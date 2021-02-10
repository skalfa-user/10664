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
    $path = OW::getPluginManager()->getPlugin('skadate')->getUserFilesDir().'mobile_promo_image.jpg';
    copy(__DIR__ . DS . 'mobile_promo_image.jpg', $path);
    chmod($path, 0666);
    Updater::getConfigService()->saveConfig('skadate', 'promo_image_uploaded', true);
}
catch(Exception $e)
{
    Updater::getLogger()->addEntry(json_encode($e));
}

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'skadate');
