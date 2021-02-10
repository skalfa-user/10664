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

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."billingstripe_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `stripeCustomerId` varchar(50) NOT NULL,
  `defaultCard` varchar(50) NOT NULL,
  `createStamp` int(11) NOT NULL,
  `subscriptions` mediumtext NOT NULL,
  `cards` mediumtext NOT NULL,
  `currency` varchar(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."billingstripe_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `saleId` int(11) NOT NULL,
  `stripeSubscriptionId` varchar(50) NOT NULL,
  `stripeCustomerId` varchar(50) NOT NULL,
  `stripeInitialInvoiceId` VARCHAR(50) NULL DEFAULT NULL,
  `startStamp` int(11) NOT NULL,
  `currentStartStamp` int(11) NOT NULL,
  `currentEndStamp` int(11) NOT NULL,
  `plan` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`,`saleId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

// add payment table
$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."billingstripe_payment` (
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

$billingService = BOL_BillingService::getInstance();

$gateway = new BOL_BillingGateway();
$gateway->gatewayKey = 'billingstripe';
$gateway->adapterClassName = 'BILLINGSTRIPE_CLASS_StripeAdapter';
$gateway->active = 0;
$gateway->mobile = 1;
$gateway->recurring = 1;
$gateway->dynamic = 0;
$gateway->currencies = 'USD,EUR,GBP,CAD,AUD,CHF,DKK,NOK,SEK';

$billingService->addGateway($gateway);

$billingService->addConfig('billingstripe', 'livePK', '');
$billingService->addConfig('billingstripe', 'testPK', '');
$billingService->addConfig('billingstripe', 'liveSK', '');
$billingService->addConfig('billingstripe', 'testSK', '');
$billingService->addConfig('billingstripe', 'sandboxMode', '0');
$billingService->addConfig('billingstripe', 'requireData', '1');

// set default view settings
$configKey = 'billingstripe';
OW::getConfig()->addConfig($configKey, 'card_detail_font_color', '#383a40');
OW::getConfig()->addConfig($configKey, 'card_detail_icon_color', '#383a40');
OW::getConfig()->addConfig($configKey, 'card_detail_font_family', '"Helvetica Neue", Helvetica, sans-serif');
OW::getConfig()->addConfig($configKey, 'card_detail_font_size', 14);
OW::getConfig()->addConfig($configKey, 'card_detail_placeholder_color', '#afaeae');
OW::getConfig()->addConfig($configKey, 'card_detail_error_font_color', '#ee3d32');
OW::getConfig()->addConfig($configKey, 'card_detail_error_icon_color', '#ee3d32');


OW::getPluginManager()->addPluginSettingsRouteName('billingstripe', 'billingstripe.admin');

// import languages
$plugin = OW::getPluginManager()->getPlugin('billingstripe');
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');
