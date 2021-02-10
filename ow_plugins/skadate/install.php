<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$plugin = OW::getPluginManager()->getPlugin('skadate');

$from = $plugin->getUserFilesDir() . 'default_mobile_promo_image.jpg';
$to = $plugin->getUserFilesDir() . 'mobile_promo_image.jpg';

copy($from, $to);
chmod($to, 0666);

unlink($from);

$config = OW::getConfig();

if ( !$config->configExists('skadate', 'notify_admin_about_invalid_items') )
{
    $config->addConfig('skadate', 'notify_admin_about_invalid_items', false);
}