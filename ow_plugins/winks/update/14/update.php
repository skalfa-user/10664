<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$sql = "ALTER TABLE `".OW_DB_PREFIX."winks_winks` ADD `messageType` ENUM( 'chat', 'mail' ) NOT NULL";

try
{
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    UPDater::getLogger()->addEntry(json_encode($e));
}
