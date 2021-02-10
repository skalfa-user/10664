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

$dbPrefix = OW_DB_PREFIX;

$dbo = Updater::getDbo();
$logger = Updater::getLogger();

$sql = "UPDATE  `{$dbPrefix}base_billing_gateway`
            SET `mobile` = 1 WHERE adapterClassName = 'BILLINGPAYPAL_CLASS_PaypalAdapter';";
try
{
    $dbo->query( $sql );
}
catch (Exception $ex)
{
    $logger->addEntry($ex->getMessage());
}
