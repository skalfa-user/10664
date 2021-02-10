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

$billingService->deleteConfig($pluginKey, 'clientAccnum');
$billingService->deleteConfig($pluginKey, 'clientSubacc');
$billingService->deleteConfig($pluginKey, 'ccFormName');
$billingService->deleteConfig($pluginKey, 'ckFormName');
$billingService->deleteConfig($pluginKey, 'dynamicPricingSalt');
$billingService->deleteConfig($pluginKey, 'datalinkUsername');
$billingService->deleteConfig($pluginKey, 'datalinkPassword');
$billingService->deleteConfig($pluginKey, 'flexFormId');
$billingService->deleteConfig($pluginKey, 'sandbox');

$billingService->deleteGateway($pluginKey);