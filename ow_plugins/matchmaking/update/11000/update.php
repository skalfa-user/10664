<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$dbPrefix = OW_DB_PREFIX;

try
{
    $sql = "UPDATE `{$dbPrefix}base_question` SET `onSearch` = 0 WHERE `sectionName` = 'about_my_match'";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }