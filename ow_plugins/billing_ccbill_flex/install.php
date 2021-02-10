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

$pluginKey = 'billingccbillflex';

$billingService = BOL_BillingService::getInstance();

$gateway = new BOL_BillingGateway();
$gateway->gatewayKey = $pluginKey;
$gateway->adapterClassName = strtoupper($pluginKey).'_CLASS_CcbillFlexAdapter';
$gateway->active = 0;
$gateway->mobile = 1;
$gateway->recurring = 1;
$gateway->currencies = 'AUD,CAD,EUR,GBP,JPY,USD';

$billingService->addGateway($gateway);

$billingService->addConfig($pluginKey, 'clientAccnum', '');
$billingService->addConfig($pluginKey, 'clientSubacc', '0000');
$billingService->addConfig($pluginKey, 'clientSubaccCredits', '');
$billingService->addConfig($pluginKey, 'dynamicPricingSalt', '');
$billingService->addConfig($pluginKey, 'datalinkUsername', '');
$billingService->addConfig($pluginKey, 'datalinkPassword', '');
$billingService->addConfig($pluginKey, 'flexFormId', '');
$billingService->addConfig($pluginKey, 'sandbox', 0);


OW::getPluginManager()->addPluginSettingsRouteName($pluginKey, $pluginKey.'_admin');

// import languages
$plugin = OW::getPluginManager()->getPlugin($pluginKey);
OW::getLanguage()->importLangsFromDir($plugin->getRootDir() . 'langs');