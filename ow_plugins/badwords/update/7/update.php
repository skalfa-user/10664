<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
$dbPrefix = OW_DB_PREFIX;

$dbo = Updater::getDbo();
$logger = Updater::getLogger();

$sql = "UPDATE  `{$dbPrefix}base_plugin`
            SET `developerKey` = '99d6bdd5bb6468beaf118c4664dd92ff' WHERE `key` = 'badwords';";
try
{
    $dbo->query( $sql );
}
catch (Exception $ex)
{
    $logger->addEntry($ex->getMessage());
}
