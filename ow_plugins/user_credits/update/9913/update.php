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

$sql = "ALTER TABLE `".OW_DB_PREFIX."usercredits_log` ADD `groupKey` VARCHAR( 255 ) NULL DEFAULT NULL ;";
try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{

}