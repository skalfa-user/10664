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

$billingService = BOL_BillingService::getInstance();

$billingService->deleteConfig('billingstripe', 'livePK');
$billingService->deleteConfig('billingstripe', 'testPK');
$billingService->deleteConfig('billingstripe', 'liveSK');
$billingService->deleteConfig('billingstripe', 'testSK');
$billingService->deleteConfig('billingstripe', 'sandboxMode');
$billingService->deleteConfig('billingstripe', 'requireData');

$billingService->deleteGateway('billingstripe');