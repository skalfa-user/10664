<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

$sql = "UPDATE `".OW_DB_PREFIX."base_authorization_group` SET  `moderated` =  '0' WHERE `name`='hotlist'";
Updater::getDbo()->query($sql);