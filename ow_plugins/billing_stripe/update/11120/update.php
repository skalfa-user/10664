<?php

$pluginKey = 'billingstripe';
$config = Updater::getConfigService();

// set default view settings
if ( empty($config->getValue($pluginKey, 'card_detail_font_color')) )
{
    $config->configExists($pluginKey, 'card_detail_font_color')
        ? $config->saveConfig($pluginKey, 'card_detail_font_color', '#383a40')
        : $config->addConfig($pluginKey, 'card_detail_font_color', '#383a40');
}

if ( empty($config->getValue($pluginKey, 'card_detail_icon_color')) )
{
    $config->configExists($pluginKey, 'card_detail_icon_color')
        ? $config->saveConfig($pluginKey, 'card_detail_icon_color', '#383a40')
        : $config->addConfig($pluginKey, 'card_detail_icon_color', '#383a40');
}

if ( empty($config->getValue($pluginKey, 'card_detail_font_family')) )
{
    $config->configExists($pluginKey, 'card_detail_font_family')
        ? $config->saveConfig($pluginKey, 'card_detail_font_family', '"Helvetica Neue", Helvetica, sans-serif')
        : $config->addConfig($pluginKey, 'card_detail_font_family', '"Helvetica Neue", Helvetica, sans-serif');
}

if ( empty($config->getValue($pluginKey, 'card_detail_font_size')) )
{
    $config->configExists($pluginKey, 'card_detail_font_size')
        ? $config->saveConfig($pluginKey, 'card_detail_font_size', 14)
        : $config->addConfig($pluginKey, 'card_detail_font_size', 14);
}

if ( empty($config->getValue($pluginKey, 'card_detail_placeholder_color')) )
{
    $config->configExists($pluginKey, 'card_detail_placeholder_color')
        ? $config->saveConfig($pluginKey, 'card_detail_placeholder_color', '#afaeae')
        : $config->addConfig($pluginKey, 'card_detail_placeholder_color', '#afaeae');
}

if ( empty($config->getValue($pluginKey, 'card_detail_error_font_color')) )
{
    $config->configExists($pluginKey, 'card_detail_error_font_color')
        ? $config->saveConfig($pluginKey, 'card_detail_error_font_color', '#ee3d32')
        : $config->addConfig($pluginKey, 'card_detail_error_font_color', '#ee3d32');
}

if ( empty($config->getValue($pluginKey, 'card_detail_error_icon_color')) )
{
    $config->configExists($pluginKey, 'card_detail_error_icon_color')
        ? $config->saveConfig($pluginKey, 'card_detail_error_icon_color', '#ee3d32')
        : $config->addConfig($pluginKey, 'card_detail_error_icon_color', '#ee3d32');
}

// import languages
$langService = Updater::getLanguageService();
$langService->importPrefixFromDir(__DIR__ . DS . 'langs', true);

// add payment table
$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX . "billingstripe_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `saleId` int(11) NOT NULL,
  `stripePaymentId` varchar(50) NOT NULL,
  `stripeCustomerId` varchar(50) NOT NULL,
  `createStamp` int(11) NOT NULL,
  `amount` float(9,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `card` mediumtext NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `saleId` (`saleId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);
