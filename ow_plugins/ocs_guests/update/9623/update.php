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

try
{
    Updater::getNavigationService()->addMenuItem(OW_Navigation::MOBILE_TOP, 'ocsguests_list', 'ocsguests', 'ocsguests_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);
}
catch(Exception $ex)
{
    
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'ocsguests');